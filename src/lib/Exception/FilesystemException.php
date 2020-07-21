<?php
/**
 * @author Maksim Rodikov
 */

namespace whotrades\RdsSystem\lib\Exception;

use Throwable;

class FilesystemException extends \Exception
{
    const ERROR_WRITE_DIRECTORY = 100;
    const ERROR_WRITE_FILE      = 101;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            switch ($code) {
                case self::ERROR_WRITE_DIRECTORY:
                    $message = "Can't create directory";
                    break;
                case self::ERROR_WRITE_FILE:
                    $message = "Can't write into file";
                    break;
                default:
                    $message = "Filesystem error";
            }
        }
        parent::__construct($message, $code, $previous);
    }
}