<?php namespace browner12\uploader;

use browner12\uploader\Events\FileOptimized;
use browner12\uploader\Events\FilesReprocessed;
use browner12\uploader\Events\FileThumbnailed;
use browner12\uploader\Events\FileUploaded;
use browner12\uploader\Exceptions\FileUploadedTooLarge;
use browner12\uploader\Exceptions\UnapprovedExtension;
use browner12\uploader\Exceptions\UnapprovedMimeType;
use browner12\uploader\Exceptions\UploaderException;
use DirectoryIterator;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * uploader library
 */
class Uploader implements UploaderInterface
{
    /**
     * @var string
     */
    protected $baseDirectory = '';

    /**
     * @var string
     */
    protected $originalDirectory = 'original';

    /**
     * @var string
     */
    protected $optimizedDirectory = '';

    /**
     * @var string
     */
    protected $thumbnailDirectory = 'thumbnail';

    /**
     * @var bool
     */
    protected $createOptimized = true;

    /**
     * @var bool
     */
    protected $createThumbnails = true;

    /**
     * @var array
     */
    protected $documentExtensions = [];

    /**
     * @var array
     */
    protected $imageExtensions = [];

    /**
     * @var array
     */
    protected $videoExtensions = [];

    /**
     * @var array
     */
    protected $audioExtensions = [];

    /**
     * @var array
     */
    protected $documentMimeTypes = [];

    /**
     * @var array
     */
    protected $imageMimeTypes = [];

    /**
     * @var array
     */
    protected $videoMimeTypes = [];

    /**
     * @var array
     */
    protected $audioMimeTypes = [];

    /**
     * @var int
     */
    protected $maximumUploadSize = 32000000;

    /**
     * @var int
     */
    protected $optimizedImageQuality = 80;

    /**
     * @var int
     */
    protected $optimizedMaximumWidth = 1000;

    /**
     * @var int
     */
    protected $thumbnailWidth = 100;

    /**
     * @var \Intervention\Image\ImageManager
     */
    protected $image;

    /**
     * constructor
     *
     * @param \Intervention\Image\ImageManager $image
     */
    public function __construct(ImageManager $image)
    {
        //assign
        $this->image = $image;

        //set directories
        $this->setDirectory('base', config('uploader.base_directory', ''));
        $this->setDirectory('original', config('uploader.original_directory', 'original'));
        $this->setDirectory('optimized', config('uploader.optimized_directory', ''));
        $this->setDirectory('thumbnail', config('uploader.thumbnail_directory', 'thumbnail'));

        //set create optimized and thumbnail
        $this->setCreateOptimized(config('uploader.create_optimized', true));
        $this->setCreateThumbnails(config('uploader.create_thumbnails', true));

        //set valid extensions
        $this->setValidExtensions('document', config('uploader.document_extensions', []));
        $this->setValidExtensions('image', config('uploader.image_extensions', []));
        $this->setValidExtensions('video', config('uploader.video_extensions', []));
        $this->setValidExtensions('audio', config('uploader.audio_extensions', []));

        //set valid mime types
        $this->setValidMimeTypes('document', config('uploader.document_mime_types', []));
        $this->setValidMimeTypes('image', config('uploader.image_mime_types', []));
        $this->setValidMimeTypes('video', config('uploader.video_mime_types', []));
        $this->setValidMimeTypes('audio', config('uploader.audio_mime_types', []));

        //set more config
        $this->setMaximumUploadSize(config('uploader.maximum_upload_size', 32000000));
        $this->setOptimizedImageQuality(config('uploader.optimized_image_quality', 80));
        $this->setOptimizedMaximumWidth(config('uploader.optimized_maximum_width', 1000));
        $this->setThumbnailWidth(config('uploader.thumbnail_width', 100));
    }

    /**
     * upload image
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     * @throws \browner12\uploader\Exceptions\FileUploadedTooLarge
     * @throws \browner12\uploader\Exceptions\UnapprovedExtension
     * @throws \browner12\uploader\Exceptions\UnapprovedMimeType
     * @throws \browner12\uploader\Exceptions\UploaderException
     */
    public function image(UploadedFile $file, $path, $name = null)
    {
        //determine original path
        $originalPath = $this->getPath($path, 'original');

        //upload file
        $original = $this->upload($file, $originalPath, $name, 'image');

        //optimized
        if ($original && $this->createOptimized) {

            //create optimized image
            $optimized = $this->createOptimized($path, $original['name'], true);

            //append to return
            $original = array_merge($original, $optimized);
        }

        //thumbnail
        if ($original && $this->createThumbnails) {

            //create thumbnail image
            $thumbnail = $this->createThumbnail($path, $original['name'], true);

            //append to return
            $original = array_merge($original, $thumbnail);
        }

        //return
        return $original;
    }

    /**
     * reprocess originals
     *
     * in this case we have original files, and want to recreate (or create for the first time) the optimized and thumbnail images
     * could be used when transferring over to a new server, or could be used if the optimized or thumbnail methods change
     *
     * @param string $path
     * @param bool   $overwrite
     * @return array
     * @throws \browner12\uploader\Exceptions\UploaderException
     */
    public function reprocess($path, $overwrite = false)
    {
        //get original path
        $originalPath = $this->getPath($path, 'original');

        //directory does not exist
        if (!file_exists($originalPath)) {
            throw new UploaderException('unable to reprocess directory ' . $originalPath . ' which does not exist');
        }

        //get all files from original folder
        $files = new DirectoryIterator($originalPath);

        //initialize counts
        $optimized = 0;
        $thumbnails = 0;

        //loop through original files
        foreach ($files as $file) {

            //reset timeout
            set_time_limit(30);

            //ignore dots, dirs, and gitignore
            if (!$file->isDot() && !$file->isDir() && $file->getExtension() != 'gitignore') {

                //optimize
                ($this->createOptimized($path, $file->getFilename(), $overwrite)) ? $optimized++ : null;

                //thumbnail
                ($this->createThumbnail($path, $file->getFilename(), $overwrite)) ? $thumbnails++ : null;
            }
        }

        //fire event
        event(new FilesReprocessed());

        //return
        return ['optimized' => $optimized, 'thumbnails' => $thumbnails];
    }

    /**
     * create optimized image
     *
     * we bring the quality down a bit, and make sure it's not bigger than 1000px wide
     * this is what we will serve to users
     *
     * @param string $path
     * @param string $filename
     * @param bool   $overwrite
     * @return array|bool
     */
    protected function createOptimized($path, $filename, $overwrite = false)
    {
        //determine optimized directory
        $optimizedPath = $this->getPath($path, 'optimized');

        //only create if optimized file does not exist or we want to overwrite existing file
        if (!file_exists($optimizedPath . $filename) || $overwrite) {

            //create directory
            $this->createDirectory($optimizedPath);

            //create optimized image
            $image = $this->image->make($this->getPath($path, 'original') . $filename);

            //orientate the image
            $image->orientate();

            //constrain optimized width
            if ($this->optimizedMaximumWidth > 0) {
                $image->widen($this->optimizedMaximumWidth, function ($constraint) {
                    $constraint->upsize();
                });
            }

            //save image
            $image->save($optimizedPath . $filename, $this->optimizedImageQuality);

            //fire event
            event(new FileOptimized());

            //return
            return ['optimized_url' => $optimizedPath . $filename];
        }

        //optimized file not created
        return false;
    }

    /**
     * create thumbnail image
     *
     * @param string $path
     * @param string $filename
     * @param bool   $overwrite
     * @return array|bool
     */
    protected function createThumbnail($path, $filename, $overwrite = false)
    {
        //determine thumbnail directory
        $thumbnailPath = $this->getPath($path, 'thumbnail');

        //only create if thumbnail file does not exist or we want to overwrite existing file
        if (!file_exists($thumbnailPath . $filename) || $overwrite) {

            //create directory
            $this->createDirectory($thumbnailPath);

            //create thumbnail image
            $this->image->make($this->getPath($path, 'original') . $filename)
                        ->orientate()
                        ->widen($this->thumbnailWidth)
                        ->save($thumbnailPath . $filename);

            //fire event
            event(new FileThumbnailed());

            //return
            return ['thumbnail_url' => $thumbnailPath . $filename];
        }

        //thumbnail file not created
        return false;
    }

    /**
     * upload video
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     * @throws \browner12\uploader\Exceptions\FileUploadedTooLarge
     * @throws \browner12\uploader\Exceptions\UnapprovedExtension
     * @throws \browner12\uploader\Exceptions\UnapprovedMimeType
     * @throws \browner12\uploader\Exceptions\UploaderException
     */
    public function video(UploadedFile $file, $path, $name = null)
    {
        return $this->upload($file, $this->getPath($path), $name, 'video');
    }

    /**
     * upload audio file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     * @throws \browner12\uploader\Exceptions\FileUploadedTooLarge
     * @throws \browner12\uploader\Exceptions\UnapprovedExtension
     * @throws \browner12\uploader\Exceptions\UnapprovedMimeType
     * @throws \browner12\uploader\Exceptions\UploaderException
     */
    public function audio(UploadedFile $file, $path, $name = null)
    {
        return $this->upload($file, $this->getPath($path), $name, 'audio');
    }

    /**
     * upload document
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     * @throws \browner12\uploader\Exceptions\FileUploadedTooLarge
     * @throws \browner12\uploader\Exceptions\UnapprovedExtension
     * @throws \browner12\uploader\Exceptions\UnapprovedMimeType
     * @throws \browner12\uploader\Exceptions\UploaderException
     */
    public function document(UploadedFile $file, $path, $name = null)
    {
        return $this->upload($file, $this->getPath($path), $name, 'document');
    }

    /**
     * upload file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @param string                                              $type
     * @return array
     * @throws \browner12\uploader\Exceptions\FileUploadedTooLarge
     * @throws \browner12\uploader\Exceptions\UnapprovedExtension
     * @throws \browner12\uploader\Exceptions\UnapprovedMimeType
     * @throws \browner12\uploader\Exceptions\UploaderException
     */
    protected function upload(UploadedFile $file, $path, $name = null, $type)
    {
        //check file size
        $this->checkSize($file->getSize());

        //check extension
        $this->checkExtension($file->getClientOriginalExtension(), $type);

        //check mime type
        $this->checkMimeType($file->getMimeType(), $type);

        //if a name is not passed, we will use the original file name
        $name = ($name) ?: $this->sanitizeFileName($file);

        //determine filename
        $newFilename = $name . '.' . strtolower($file->getClientOriginalExtension());

        //successful upload
        if ($file->move($path, $newFilename)) {

            //fire event
            event(new FileUploaded());

            //return
            return [
                'id'            => $name,
                'name'          => $newFilename,
                'size'          => $file->getClientSize(),
                'mime_type'     => $file->getClientMimeType(),
                'extension'     => $file->getClientOriginalExtension(),
                'original_name' => $file->getClientOriginalName(),
                'url'           => $path . $newFilename,
            ];
        }

        //failed upload
        throw new UploaderException('Could not upload ' . $type . $file->getClientOriginalName() . '.');
    }

    /**
     * sanitize the file name
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return string
     */
    private function sanitizeFileName(UploadedFile $file)
    {
        //remove extension
        $name = str_replace('.' . $file->getClientOriginalExtension(), '', $file->getClientOriginalName());

        //sanitize filename
        return preg_replace('/[^a-zA-Z0-9-_]/', '-', $name);
    }

    /**
     * check file size
     *
     * @param int $size
     * @throws \browner12\uploader\Exceptions\FileUploadedTooLarge
     */
    protected function checkSize($size)
    {
        //too big
        if ($size > $this->maximumUploadSize) {
            throw new FileUploadedTooLarge('File size is greater than maximum allowed size of ' . $this->formatBytes($this->maximumUploadSize));
        }
    }

    /**
     * check file extension
     *
     * @param string $extension
     * @param string $type
     * @throws \browner12\uploader\Exceptions\UnapprovedExtension
     */
    protected function checkExtension($extension, $type)
    {
        //determine haystack
        $haystack = $this->getValidExtensions($type);

        //not approved
        if (!in_array(strtolower($extension), $haystack)) {
            throw new UnapprovedExtension('File does not have an approved extension: ' . implode(', ', $haystack));
        }
    }

    /**
     * check mime type
     *
     * @param string $mimeType
     * @param string $type
     * @throws \browner12\uploader\Exceptions\UnapprovedMimeType
     */
    protected function checkMimeType($mimeType, $type)
    {
        //determine haystack
        $haystack = $this->getValidMimeTypes($type);

        //not approved
        if (!in_array(strtolower($mimeType), $haystack)) {
            throw new UnapprovedMimeType('File does not have an approved type: ' . implode(', ', $haystack));
        }
    }

    /**
     * create a directory
     *
     * @param $directory
     * @return bool
     */
    protected function createDirectory($directory)
    {
        //directory does not exist yet
        if (!is_dir($directory)) {
            mkdir($directory);
            return true;
        }

        //directory already existed
        return false;
    }

    /**
     * get the path to upload the file to
     *
     * @param string $path
     * @param string $type
     * @return string
     */
    protected function getPath($path, $type = null)
    {
        //remove leading slashes
        $path = ltrim($path, '/');

        //remove trailing slashes and add one back
        if ($path != '') {
            $path = rtrim($path, '/') . '/';
        }

        //adjust for type
        switch ($type) {

            //original
            case 'original':
                $path .= $this->originalDirectory;
                break;

            //optimized
            case 'optimized':
                $path .= $this->optimizedDirectory;
                break;

            //thumbnail
            case 'thumbnail':
                $path .= $this->thumbnailDirectory;
                break;

            //default
            default:
                break;
        }

        return $this->baseDirectory . $path;
    }

    /**
     * set directory
     *
     * @param string $type
     * @param string $directory
     * @return string
     */
    public function setDirectory($type, $directory)
    {
        //remove leading slashes
        $directory = ltrim($directory, '/');

        //remove trailing slashes and add one back
        if ($directory != '') {
            $directory = rtrim($directory, '/') . '/';
        }

        //set directory
        switch ($type) {

            //base
            case 'base':
                $this->baseDirectory = $directory;
                break;

            //original
            case 'original':
                $this->originalDirectory = $directory;
                break;

            //optimized
            case 'optimized':
                $this->optimizedDirectory = $directory;
                break;

            //thumbnail
            case 'thumbnail':
                $this->thumbnailDirectory = $directory;
                break;

            //default
            default:
                break;
        }

        //return
        return $directory;
    }

    /**
     * set if optimized images should be created
     *
     * @param bool $create
     */
    public function setCreateOptimized($create)
    {
        $this->createOptimized = (bool)$create;
    }

    /**
     * set if thumbnail images should be created
     *
     * @param bool $create
     */
    public function setCreateThumbnails($create)
    {
        $this->createThumbnails = (bool)$create;
    }

    /**
     * get valid extensions
     *
     * @param string $type
     * @return array
     */
    protected function getValidExtensions($type)
    {
        switch ($type) {

            //document
            case 'document':
                $extensions = $this->documentExtensions;
                break;

            //image
            case 'image':
                $extensions = $this->imageExtensions;
                break;

            //video
            case 'video':
                $extensions = $this->videoExtensions;
                break;

            //audio
            case 'audio':
                $extensions = $this->audioExtensions;
                break;

            //default
            default:
                $extensions = [];
                break;
        }

        //return
        return $extensions;
    }

    /**
     * set valid extensions
     *
     * @param string $type
     * @param array  $extensions
     */
    public function setValidExtensions($type, array $extensions)
    {
        switch ($type) {

            //document
            case 'document':
                $this->documentExtensions = $extensions;
                break;

            //image
            case 'image':
                $this->imageExtensions = $extensions;
                break;

            //video
            case 'video':
                $this->videoExtensions = $extensions;
                break;

            //audio
            case 'audio':
                $this->audioExtensions = $extensions;
                break;

            //default
            default:
                break;
        }
    }

    /**
     * get valid mime types
     *
     * @param string $type
     * @return array
     */
    protected function getValidMimeTypes($type)
    {
        switch ($type) {

            //document
            case 'document':
                $mimeTypes = $this->documentMimeTypes;
                break;

            //image
            case 'image':
                $mimeTypes = $this->imageMimeTypes;
                break;

            //video
            case 'video':
                $mimeTypes = $this->videoMimeTypes;
                break;

            //audio
            case 'audio':
                $mimeTypes = $this->audioMimeTypes;
                break;

            //default
            default:
                $mimeTypes = [];
                break;
        }

        //return
        return $mimeTypes;
    }

    /**
     * set valid mime types
     *
     * @param string $type
     * @param array  $mimeTypes
     */
    public function setValidMimeTypes($type, array $mimeTypes)
    {
        switch ($type) {

            //document
            case 'document':
                $this->documentMimeTypes = $mimeTypes;
                break;

            //image
            case 'image':
                $this->imageMimeTypes = $mimeTypes;
                break;

            //video
            case 'video':
                $this->videoMimeTypes = $mimeTypes;
                break;

            //audio
            case 'audio':
                $this->audioMimeTypes = $mimeTypes;
                break;

            //default
            default:
                break;
        }
    }

    /**
     * set maximum file upload size
     *
     * @param int $size
     */
    public function setMaximumUploadSize($size)
    {
        $this->maximumUploadSize = $size;
    }

    /**
     * set optimized image quality
     *
     * @param int $quality
     */
    public function setOptimizedImageQuality($quality)
    {
        if ($quality > 0 && $quality <= 100) {
            $this->optimizedImageQuality = $quality;
        }
    }

    /**
     * set optimized maximum width
     *
     * @param int $width
     */
    public function setOptimizedMaximumWidth($width)
    {
        if (is_int($width)) {
            $this->optimizedMaximumWidth = $width;
        }
    }

    /**
     * set thumbnail width
     *
     * @param int $width
     */
    public function setThumbnailWidth($width)
    {
        if (is_int($width)) {
            $this->thumbnailWidth = $width;
        }
    }

    /**
     * format the bytes to a human readable form
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        }
        else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
