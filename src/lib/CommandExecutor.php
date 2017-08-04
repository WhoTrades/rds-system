<?php
namespace RdsSystem\lib;

use Yii;

class CommandExecutor
{
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

        Yii::info("Executing `$commandWithEnv`");
        exec($commandWithEnv, $output, $returnVar);
        $text = implode("\n", $output);

        if ($returnVar) {
            throw new CommandExecutorException($commandWithEnv, "Return var is non-zero, code=$returnVar, command=$commandWithEnv", $returnVar, $text);
        }

        return $text;
    }
}
