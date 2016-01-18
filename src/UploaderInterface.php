<?php namespace browner12\uploader;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploaderInterface
{
    /**
     * upload a picture
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param int                                                 $id
     * @return
     */
    public function picture(UploadedFile $file, $path, $id);

    /**
     * upload a document
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string                                              $path
     * @param int                                                 $id
     * @return
     */
    public function document(UploadedFile $file, $path, $id);

    /**
     * reprocess
     *
     * @param string $path
     * @return
     */
    public function reprocess($path);

}
