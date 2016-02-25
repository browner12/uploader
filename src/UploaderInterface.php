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
     * @param bool                                                $createOptimized
     * @param bool                                                $createThumbnail
     * @return array
     */
    public function image(UploadedFile $file, $path, $name = null, $createOptimized = true, $createThumbnail = true);

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
}
