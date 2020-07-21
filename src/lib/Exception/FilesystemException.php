<?php
/**
 * @author Maksim Rodikov
 */

namespace whotrades\RdsSystem\lib\Exception;

use Throwable;

/**
 * Class FilesystemException
 *
 * @package whotrades\RdsSystem\lib\Exception
 */
class FilesystemException extends \Exception
{
    const ERROR_WRITE_DIRECTORY = 100;
    const ERROR_WRITE_FILE      = 101;

    /**
     * FilesystemException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $message ?: $this->getDefaultMessage((int) $code);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param int $code
     *
     * @return string
     */
    protected function getDefaultMessage(int $code): string
    {
        switch ($code) {
            case self::ERROR_WRITE_DIRECTORY:
                return "Can't create directory";
            case self::ERROR_WRITE_FILE:
                return "Can't write into file";
        }

        return "Filesystem error";
    }
}