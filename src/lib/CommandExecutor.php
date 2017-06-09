<?php
namespace RdsSystem\lib;

class CommandExecutor
{
    /** @var \ServiceBase_IDebugLogger */
    private $debugLogger;

    /**
     * CommandExecutor constructor.
     * @param \ServiceBase_IDebugLogger $debugLogger
     */
    public function __construct(\ServiceBase_IDebugLogger $debugLogger)
    {
        $this->debugLogger = $debugLogger;
    }

    /**
     * @param string $command
     * @return string
     * @throws CommandExecutorException
     */
    public function executeCommand($command)
    {
        $this->debugLogger->message("Executing `$command`");
        exec($command, $output, $returnVar);
        $text = implode("\n", $output);

        if ($returnVar) {
            throw new CommandExecutorException($command, "Return var is non-zero, code=$returnVar, command=$command", $returnVar, $text);
        }

        return $text;
    }
}
