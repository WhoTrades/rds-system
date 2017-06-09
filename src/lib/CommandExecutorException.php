<?php
namespace RdsSystem\lib;

class CommandExecutorException extends \ApplicationException
{
    public $output;
    private $command;

    /**
     * CommandExecutorException constructor.
     *
     * @param string|null $message
     * @param int $code
     * @param string $output
     * @param \Exception $previous
     */
    public function __construct($command, $message, $code, $output, $previous = null)
    {
        $this->output = $output;
        $this->command = $command;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    public function getCommand()
    {
        return $this->command;
    }
}

