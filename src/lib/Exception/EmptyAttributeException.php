<?php
/**
 * @author Maksim Rodikov
 */

namespace whotrades\RdsSystem\lib\Exception;

use Throwable;

/**
 * Class EmptyAttributeException
 *
 * @package whotrades\RdsSystem\lib\Exception
 */
class EmptyAttributeException extends \Exception
{
    /**
     * EmptyAttributeException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $message ?: "Required attribute is empty";
        parent::__construct($message, $code, $previous);
    }

}
