<?php
namespace whotrades\RdsSystem\lib\Exception;

class CommandExecutorException extends \Exception
{
    public $output;
    private $command;

    /**
     * CommandExecutorException constructor.
     *
     * @param string $command
     * @param string|null $message
     * @param int $code
     * @param string $output
     * @param \Exception $previous
     */
    public function __construct($command, $message, $code, $output, $previous = null)
    {
        $message = $message ?: "Command execution error";
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

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }
}
