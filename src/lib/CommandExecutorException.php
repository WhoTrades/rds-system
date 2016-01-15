<?php
namespace RdsSystem\lib;

class CommandExecutorException extends \ApplicationException
{
    public $output;

    /**
     * CommandExecutorException constructor.
     *
     * @param string|null $message
     * @param int $code
     * @param string $output
     * @param \Exception $previous
     */
    public function __construct($message, $code, $output, $previous = null)
    {
        $this->output = $output;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}

