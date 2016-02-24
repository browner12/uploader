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
    private $documentExtensions = ['pdf', 'doc', 'docx', 'ppt'];

    /**
     * valid image extensions
     *
     * @var array
     */
    private $imageExtensions = ['jpg', 'gif', 'png'];

    /**
     * valid video extensions
     *
     * @var array
     */
    private $videoExtensions = ['avi', 'mov', 'mp4', 'ogg'];

    /**
     * valid audio extensions
     *
     * @var array
     */
    private $audioExtensions = ['mp3', 'wav'];

    /**
     * valid document mime types
     *
     * @var array
     */
    private $documentMimeTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    /**
     * valid image mime types
     *
     * @var array
     */
    private $imageMimeTypes = [
        'image/gif',
        'image/jpeg',
        'image/png',
    ];

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
    public function image(UploadedFile $file, $path, $name, $optimize = true, $thumbnail = true)
    {
        //upload file
        $original = $this->upload($file, $path, $name, 'image');

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
     */
    protected function createOptimized($path, $filename)
    {
        $this->image->make($path . $this->getOriginalDirectory() . $filename)
                    ->widen(config('uploader.optimized_maximum_width', 1000), function ($constraint) {
                        $constraint->upsize();
                    })
                    ->save($path . $filename, config('uploader.optimized_image_quality', 60));
    }

    /**
     * create thumbnail image
     *
     * @param string $path
     * @param string $filename
     */
    protected function createThumbnail($path, $filename)
    {
        $this->image->make($path . $this->getOriginalDirectory() . $filename)
                    ->widen(config('uploader.thumbnail_width', 100))
                    ->save($path . $this->getThumbnailDirectory() . $filename);
    }

    /**
     * upload video
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     */
    public function video(UploadedFile $file, $path, $name)
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
    public function audio(UploadedFile $file, $path, $name)
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
    public function document(UploadedFile $file, $path, $name)
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
    protected function upload(UploadedFile $file, $path, $name, $type)
    {
        //check file size
        $this->checkSize($file->getSize());

        //check extension
        $this->checkExtension($file->getClientOriginalExtension(), $type);

        //check mime type
        $this->checkMimeType($file->getMimeType(), $type);

        //set new filename
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
        switch ($type) {

            //image
            case 'image':
                $haystack = $this->getValidImageExtensions();
                break;

            //document
            case 'document':
                $haystack = $this->getValidDocumentExtensions();
                break;

            //default
            default:
                $haystack = [];
                break;
        }

        //not approved
        if (!in_array(strtolower($extension), $haystack)) {
            throw new UploaderException('File does not have an approved extension: ' . implode(', ', $haystack));
        }
    }

    /**
     * check mime type
     *
     * @param string $mimeType
     * @param string $group
     * @throws \browner12\uploader\UploaderException
     */
    protected function checkMimeType($mimeType, $group)
    {
        //determine haystack
        switch ($group) {

            //image
            case 'image':
                $haystack = $this->getValidImageMimeTypes();
                break;

            //document
            case 'document':
                $haystack = $this->getValidDocumentMimeTypes();
                break;

            //default
            default:
                $haystack = [];
                break;
        }

        //not approved
        if (!in_array(strtolower($mimeType), $haystack)) {
            throw new UploaderException('File does not have an approved type: ' . implode(', ', $haystack));
        }
    }

    /**
     * get the path to upload the file to
     *
     * @param string $type
     * @return string
     * @throws \browner12\uploader\UploaderException
     */
    public function getPath($type)
    {
        $mapper = config('uploader.mapper', []);

        if (isset($mapper[$type])) {
            $path = $mapper[$type];
        }

        else {
            throw new UploaderException('Cannot determine upload path for type ' . $type);
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
        return config('uploader.base_directory', '/');
    }

    /**
     * get the original directory
     *
     * @return string
     */
    protected function getOriginalDirectory()
    {
        return config('uploader.original_directory', 'original/');
    }

    /**
     * get the optimized directory
     *
     * @return string
     */
    protected function getOptimizedDirectory()
    {
        return config('uploader.optimized_directory', '/');
    }

    /**
     * get the thumbnail directory
     *
     * @return string
     */
    protected function getThumbnailDirectory()
    {
        return config('uploader.thumbnail_directory', 'thumbnail/');
    }

    /**
     * get the valid document extensions
     *
     * @return array
     */
    protected function getValidDocumentExtensions()
    {
        return config('uploader.document_extensions', $this->documentExtensions);
    }

    /**
     * get the valid image extensions
     *
     * @return array
     */
    protected function getValidImageExtensions()
    {
        return config('uploader.image_extensions', $this->imageExtensions);
    }

    /**
     * get the valid document mime types
     *
     * @return array
     */
    protected function getValidDocumentMimeTypes()
    {
        return config('uploader.document_mime_types', $this->documentMimeTypes);
    }

    /**
     * get the valid image mime types
     *
     * @return array
     */
    protected function getValidImageMimeTypes()
    {
        return config('uploader.image_mime_types', $this->imageMimeTypes);
    }

    /**
     * get the maximum file upload size
     *
     * @return int
     */
    protected function getMaximumUploadSize()
    {
        return config('uploader.maximum_upload_size', 32000000);
    }
}
