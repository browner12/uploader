<?php namespace browner12\uploader;

use DirectoryIterator;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * uploader library
 */
class Uploader implements UploaderInterface
{
    /**
     * valid document extensions
     *
     * @var array
     */
    private $documentExtensions = [];

    /**
     * valid image extensions
     *
     * @var array
     */
    private $imageExtensions = [];

    /**
     * valid video extensions
     *
     * @var array
     */
    private $videoExtensions = [];

    /**
     * valid audio extensions
     *
     * @var array
     */
    private $audioExtensions = [];

    /**
     * valid document mime types
     *
     * @var array
     */
    private $documentMimeTypes = [];

    /**
     * valid image mime types
     *
     * @var array
     */
    private $imageMimeTypes = [];

    /**
     * valid video mime types
     *
     * @var array
     */
    private $videoMimeTypes = [];

    /**
     * valid audio mime types
     *
     * @var array
     */
    private $audioMimeTypes = [];

    /**
     * maximum file upload size
     *
     * @var int
     */
    private $maximumUploadSize;

    /**
     * @var \Intervention\Image\ImageManager
     */
    private $image;

    /**
     * constructor
     *
     * @param \Intervention\Image\ImageManager $image
     */
    public function __construct(ImageManager $image)
    {
        //assign
        $this->image = $image;

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

        //set maximum file size
        $this->setMaximumUploadSize(config('uploader.maximum_upload_size', 32000000));
    }

    /**
     * upload image
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @param bool                                                $optimize
     * @param bool                                                $thumbnail
     * @return array
     * @throws \browner12\uploader\UploaderException
     */
    public function image(UploadedFile $file, $path, $name = null, $optimize = true, $thumbnail = true)
    {
        //determine original path
        $originalPath = $this->getPath($path, 'original');

        //upload file
        $original = $this->upload($file, $originalPath, $name, 'image');

        //optimized
        if ($original AND $optimize) {

            //create optimized image
            $optimized = $this->createOptimized($path, $original['name']);

            //append to return
            $original['optimized_url'] = $optimized;
        }

        //thumbnail
        if ($original AND $thumbnail) {

            //create thumbnail image
            $thumbnail = $this->createThumbnail($path, $original['name']);

            //append to return
            $original['thumbnail_url'] = $thumbnail;
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
     * @return bool
     */
    public function reprocess($path)
    {
        //get all files from original folder
        $files = new DirectoryIterator($path . $this->getOriginalDirectory());

        //loop through original files
        foreach ($files as $file) {

            //reset timeout
            set_time_limit(30);

            //ignore dots, dirs, and gitignore
            if (!$file->isDot() AND !$file->isDir() AND $file->getExtension() != 'gitignore') {

                //optimize
                $this->createOptimized($path, $file->getFilename());

                //thumbnail
                $this->createThumbnail($path, $file->getFilename());
            }
        }
    }

    /**
     * create optimized image
     *
     * we bring the quality down a bit, and make sure it's not bigger than 1000px wide
     * this is what we will serve to users
     *
     * @param string $path
     * @param string $filename
     * @return array
     */
    protected function createOptimized($path, $filename)
    {
        $this->image->make($this->getPath($path, 'original') . $filename)
                    ->widen(config('uploader.optimized_maximum_width', 1000), function ($constraint) {
                        $constraint->upsize();
                    })
                    ->save($this->getPath($path, 'optimized') . $filename, config('uploader.optimized_image_quality', 60));

        //return
        return ['optimized_url' => $this->getPath($path, 'optimized')];
    }

    /**
     * create thumbnail image
     *
     * @param string $path
     * @param string $filename
     * @return array
     */
    protected function createThumbnail($path, $filename)
    {
        $this->image->make($this->getPath($path, 'original') . $filename)
                    ->widen(config('uploader.thumbnail_width', 100))
                    ->save($this->getPath($path, 'thumbnail') . $filename);

        //
        return ['thumbnail_url' => $this->getPath($path, 'thumbnail')];
    }

    /**
     * upload video
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     */
    public function video(UploadedFile $file, $path, $name = null)
    {
        return $this->upload($file, $path, $name, 'video');
    }

    /**
     * upload audio file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     */
    public function audio(UploadedFile $file, $path, $name = null)
    {
        return $this->upload($file, $path, $name, 'audio');
    }

    /**
     * upload document
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     * @throws \browner12\uploader\UploaderException
     */
    public function document(UploadedFile $file, $path, $name = null)
    {
        return $this->upload($file, $path, $name, 'document');
    }

    /**
     * upload file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @param string                                              $type
     * @return array
     * @throws \browner12\uploader\UploaderException
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
        $name = ($name) ?: $file->getClientOriginalName();

        //determine filename
        $newFilename = $name . '.' . strtolower($file->getClientOriginalExtension());

        //successful upload
        if ($file->move($path, $newFilename)) {

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
     * check file size
     *
     * @param int $size
     * @throws \browner12\uploader\UploaderException
     */
    protected function checkSize($size)
    {
        //too big
        if ($size > $this->getMaximumUploadSize()) {
            throw new UploaderException('File size is greater than maximum allowed size of ' . $this->getMaximumUploadSize() . '.');
        }
    }

    /**
     * check file extension
     *
     * @param string $extension
     * @param string $type
     * @throws \browner12\uploader\UploaderException
     */
    protected function checkExtension($extension, $type)
    {
        //determine haystack
        $haystack = $this->getValidExtensions($type);

        //not approved
        if (!in_array(strtolower($extension), $haystack)) {
            throw new UploaderException('File does not have an approved extension: ' . implode(', ', $haystack));
        }
    }

    /**
     * check mime type
     *
     * @param string $mimeType
     * @param string $type
     * @throws \browner12\uploader\UploaderException
     */
    protected function checkMimeType($mimeType, $type)
    {
        //determine haystack
        $haystack = $this->getValidMimeTypes($type);

        //not approved
        if (!in_array(strtolower($mimeType), $haystack)) {
            throw new UploaderException('File does not have an approved type: ' . implode(', ', $haystack));
        }
    }

    /**
     * get the path to upload the file to
     *
     * @param string $path
     * @param string $type
     * @return string
     */
    public function getPath($path, $type = null)
    {
        //remove leading slashes
        $path = ltrim($path, '/');

        //remove trailing slashes and add one back
        if ($path != '') {
            $path = rtrim($path, '/') . '/';
        }

        //adjust for type
        switch($type){

            //original
            case 'original':
                $path .= $this->getOriginalDirectory();
                break;

            //optimized
            case 'optimized':
                $path .= $this->getOptimizedDirectory();
                break;

            //thumbnail
            case 'thumbnail':
                $path .= $this->getThumbnailDirectory();
                break;

            //default
            default:
                break;

        }

        return $this->getBaseDirectory() . $path;
    }

    /**
     * get the base directory
     *
     * @return string
     */
    protected function getBaseDirectory()
    {
        //get user defined base directory
        $baseDirectory = config('uploader.base_directory', '');

        //remove leading slashes
        $baseDirectory = ltrim($baseDirectory, '/');

        //remove trailing slashes and add one back
        if ($baseDirectory != '') {
            $baseDirectory = rtrim($baseDirectory, '/') . '/';
        }

        //return
        return $baseDirectory;
    }

    /**
     * get the original directory
     *
     * @return string
     */
    protected function getOriginalDirectory()
    {
        //get user defined original directory
        $originalDirectory = config('uploader.original_directory', 'original');

        //remove leading slashes
        $originalDirectory = ltrim($originalDirectory, '/');

        //remove trailing slashes and add one back
        if ($originalDirectory != '') {
            $originalDirectory = rtrim($originalDirectory, '/') . '/';
        }

        //return
        return $originalDirectory;
    }

    /**
     * get the optimized directory
     *
     * @return string
     */
    protected function getOptimizedDirectory()
    {
        //get user defined optimized directory
        $optimizedDirectory = config('uploader.optimized_directory', '');

        //remove leading slashes
        $optimizedDirectory = ltrim($optimizedDirectory, '/');

        //remove trailing slashes and add one back
        if ($optimizedDirectory != '') {
            $optimizedDirectory = rtrim($optimizedDirectory, '/') . '/';
        }

        //return
        return $optimizedDirectory;
    }

    /**
     * get the thumbnail directory
     *
     * @return string
     */
    protected function getThumbnailDirectory()
    {
        //get user defined thumbnail directory
        $thumbnailDirectory = config('uploader.thumbnail_directory', 'thumbnail');

        //remove leading slashes
        $thumbnailDirectory = ltrim($thumbnailDirectory, '/');

        //remove trailing slashes and add one back
        if ($thumbnailDirectory != '') {
            $thumbnailDirectory = rtrim($thumbnailDirectory, '/') . '/';
        }

        //return
        return $thumbnailDirectory;
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
     * get maximum file upload size
     *
     * @return int
     */
    protected function getMaximumUploadSize()
    {
        return $this->maximumUploadSize;
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
}
