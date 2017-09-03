<?php namespace browner12\uploader\Exceptions;

use Exception;

class UploaderException extends Exception
{
    /**
     * base exception for package
     *
     * @param string    $message
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        //parent
        parent::__construct($message, $code, $previous);
    }
}
