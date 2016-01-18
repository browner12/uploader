<?php namespace browner12\uploader;

use DirectoryIterator;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * uploader library
 *
 * Centralized location for uploading files (images and documents)
 * ran into issue once when i changed some folder structure of where certain types of images were stored, was a PITA to locate every place I used it
 */
class Uploader implements UploaderInterface
{
    /**
     * max file size in KB
     *
     * @var int
     */
    private $maxSize = 32000000;

    /**
     * original folder
     *
     * @var string
     */
    private $originalFolder = 'original/';

    /**
     * thumbnail folder
     *
     * @var string
     */
    private $thumbnailFolder = 'thumbnail/';

    /**
     * @var array
     */
    private $documentExtensions = ['pdf', 'doc', 'docx', 'ppt'];

    /**
     * @var array
     */
    private $pictureExtensions = ['jpg', 'gif', 'png'];

    /**
     * acceptable document mime types
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
     * acceptable picture mime types
     *
     * @var array
     */
    private $pictureMimeTypes = [
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
     * upload entity picture (user, coach, player, manager, school, team, multimedia)
     *
     * function only returns false if upload fails, not if optimization and thumbnails fail
     * if they do we should try and take care of them in a cron
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param int                                                 $newId
     * @return bool
     * @throws \browner12\uploader\UploaderException
     */
    function picture(UploadedFile $file, $path, $newId)
    {
        //check size
        $this->checkSize($file->getSize());

        //check extension
        $this->checkExtension($file->getClientOriginalExtension(), 'picture');

        //check mime type
        $this->checkMimeType($file->getMimeType(), 'picture');

        //new filename
        $newFilename = $newId . '.' . $file->getClientOriginalExtension();

        //successful upload
        if ($file->move($path . $this->originalFolder, $newFilename)) {

            //optimize
            $this->createOptimized($path, $newFilename);

            //thumbnail
            $this->createThumbnail($path, $newFilename);

            //return
            $return = [
                "id"            => $newId,
                "name"          => $newFilename,
                "size"          => null,
                "url"           => $path . $newFilename,
                "delete_url"    => null,
                "delete_type"   => "DELETE",
                'original_url'  => $path . $this->originalFolder . $newFilename,
                'optimized_url' => $path . $newFilename,
                'thumbnail_url' => $path . $this->thumbnailFolder . $newFilename,
            ];

            //return
            return $return;
        }

        //failed upload
        throw new UploaderException('Could not upload picture.');
    }

    /**
     * reprocess originals
     *
     * in this case we have original files, and want to recreate (or create for the first time) the optimized and thumbnail images
     * could be used when transferring over to a new server.
     * or could be used if the optimized or thumbnail methods change
     *
     * @param string $path
     */
    public function reprocess($path)
    {
        //get all files from original folder
        $files = new DirectoryIterator($path . $this->originalFolder);

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
     * create optimized picture
     *
     * we bring the quality down a bit, and make sure it's not bigger than 1000px wide
     * this is what we will serve to users
     *
     * @param string $path
     * @param string $filename
     */
    private function createOptimized($path, $filename)
    {
        $this->image->make($path . $this->originalFolder . $filename)->widen(1000, function ($constraint) {
            $constraint->upsize();
        })->save($path . $filename, 60);
    }

    /**
     * create thumbnail picture
     *
     * @param string $path
     * @param string $filename
     */
    private function createThumbnail($path, $filename)
    {
        $this->image->make($path . $this->originalFolder . $filename)->widen(100)->save($path . $this->thumbnailFolder . $filename);
    }

    /**
     * upload document
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param int                                                 $newId
     * @return bool
     * @throws \browner12\uploader\UploaderException
     */
    function document(UploadedFile $file, $path, $newId)
    {
        //check extension
        $this->checkExtension($file->getClientOriginalExtension(), 'document');

        //check mime type
        $this->checkMimeType($file->getMimeType(), 'document');

        //check file size
        $this->checkSize($file->getSize());

        //set new filename
        $newFilename = $newId . '.' . strtolower($file->getClientOriginalExtension());

        //successful upload
        if ($file->move($path, $newFilename)) {
            return true;
        }

        //failed upload
        throw new UploaderException('Could not upload document ' . $file->getClientOriginalName() . '.');
    }

    /**
     * check file size
     *
     * @param int $size
     * @throws \browner12\uploader\UploaderException
     */
    private function checkSize($size)
    {
        //too big
        if ($size > $this->maxSize) {
            throw new UploaderException('File size is greater than maximum allowed size of ' . $this->maxSize . '.');
        }
    }

    /**
     * check file extension
     *
     * @param string $extension
     * @param string $type
     * @throws \browner12\uploader\UploaderException
     */
    private function checkExtension($extension, $type)
    {
        //determine haystack
        switch ($type) {

            //picture
            case 'picture':
                $haystack = $this->pictureExtensions;
                break;

            //document
            case 'document':
                $haystack = $this->documentExtensions;
                break;

            //default
            default:
                $haystack = $this->pictureExtensions;
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
    private function checkMimeType($mimeType, $group)
    {
        //determine haystack
        switch ($group) {

            //picture
            case 'picture':
                $haystack = $this->pictureMimeTypes;
                break;

            //document
            case 'document':
                $haystack = $this->documentMimeTypes;
                break;

            //default
            default:
                $haystack = $this->pictureMimeTypes;
                break;
        }

        //not approved
        if (!in_array(strtolower($mimeType), $haystack)) {
            throw new UploaderException('File does not have an approved type: ' . implode(', ', $haystack));
        }
    }

}
