<?php namespace browner12\uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploaderInterface
{
    /**
     * upload an image
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $type
     * @param string                                              $name
     * @param bool                                                $createOptimized
     * @param bool                                                $createThumbnail
     * @return array
     */
    public function image(UploadedFile $file, $type, $name, $createOptimized, $createThumbnail);

    /**
     * upload a video
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $type
     * @param string                                              $name
     * @return array
     */
    public function video(UploadedFile $file, $type, $name);

    /**
     * upload an audio file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $type
     * @param string                                              $name
     * @return array
     */
    public function audio(UploadedFile $file, $type, $name);

    /**
     * upload a document
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $type
     * @param string                                              $name
     * @return array
     */
    public function document(UploadedFile $file, $type, $name);

    /**
     * reprocess
     *
     * @param string $path
     * @return bool
     */
    public function reprocess($path);
}
