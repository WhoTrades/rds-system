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
     * @param string[]|null $env - assiciated list of env vars for command
     * @return string
     * @throws CommandExecutorException
     */
    public function executeCommand($command, $env = null)
    {
        if (!empty($env)) {
            $commandWithEnv = "(";
            foreach ($env as $key => $val) {
                $commandWithEnv .= "export $key=" . escapeshellarg($val) . "; ";
            }
            $commandWithEnv .= $command . ")";
        } else {
            $commandWithEnv = $command;
        }


        $this->debugLogger->message("Executing `$commandWithEnv`");
        exec($commandWithEnv, $output, $returnVar);
        $text = implode("\n", $output);

        if ($returnVar) {
            throw new CommandExecutorException($commandWithEnv, "Return var is non-zero, code=$returnVar, command=$commandWithEnv", $returnVar, $text);
        }

        return $text;
    }
}
