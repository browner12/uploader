<?php namespace browner12\uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploaderInterface
{
    /**
     * upload an image
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     */
    public function image(UploadedFile $file, $path, $name = null);

    /**
     * upload a video
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     */
    public function video(UploadedFile $file, $path, $name = null);

    /**
     * upload an audio file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     */
    public function audio(UploadedFile $file, $path, $name = null);

    /**
     * upload a document
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param string                                              $name
     * @return array
     */
    public function document(UploadedFile $file, $path, $name = null);

    /**
     * reprocess
     *
     * @param string $path
     * @return bool
     */
    public function reprocess($path);

    /**
     * set a directory
     *
     * @param int $type
     * @param int $directory
     * @return void
     */
    public function setDirectory($type, $directory);

    /**
     * set if optimized images should be created
     *
     * @param bool $create
     * @return void
     */
    public function setCreateOptimized($create);

    /**
     * set if thumbnail images should be created
     *
     * @param bool $create
     * @return void
     */
    public function setCreateThumbnails($create);

    /**
     * set valid extensions
     *
     * @param string $type
     * @param array  $extensions
     * @return void
     */
    public function setValidExtensions($type, array $extensions);

    /**
     * set valid mime types
     *
     * @param string $type
     * @param array  $mimeTypes
     * @return void
     */
    public function setValidMimeTypes($type, array $mimeTypes);

    /**
     * set maximum upload size
     *
     * @param int $size
     * @return void
     */
    public function setMaximumUploadSize($size);

    /**
     * set optimized image quality
     *
     * @param int $quality
     * @return void
     */
    public function setOptimizedImageQuality($quality);

    /**
     * set optimized maximum width
     *
     * @param int $width
     * @return void
     */
    public function setOptimizedMaximumWidth($width);

    /**
     * set thumbnail width
     *
     * @param int $width
     * @return void
     */
    public function setThumbnailWidth($width);
}
