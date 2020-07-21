<?php
/**
 * @author Maksim Rodikov
 */

namespace whotrades\RdsSystem\lib\Exception;

use Throwable;

class EmptyAttributeException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            $message = "Required attribute is empty";
        }
        parent::__construct($message, $code, $previous);
    }

}
