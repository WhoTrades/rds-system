<?php
/**
 * @author Maksim Rodikov
 */
declare(strict_types=1);

namespace whotrades\RdsSystem\lib\Exception;

use Throwable;

class ScriptExecutorException extends \Exception
{
    /**
     * @var string
     */
    protected $script;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $script = "")
    {
        $this->script = $script;
        parent::__construct($message, $code, $previous);
    }

}