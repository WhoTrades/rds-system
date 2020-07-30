<?php
/**
 * @author Maksim Rodikov
 */
declare(strict_types=1);

namespace whotrades\RdsSystem\lib;

use whotrades\RdsSystem\lib\Exception\CommandExecutorException;
use whotrades\RdsSystem\lib\Exception\FilesystemException;
use whotrades\RdsSystem\lib\Exception\ScriptExecutorException;

class ScriptExecutor
{
    /** @var string  */
    private $script;

    /** @var string */
    private $scriptPathPrefix;

    /** @var array */
    private $env;

    /** @var bool */
    private $executionFlag = false;

    public function __construct(string $script, string $scriptPathPrefix, array $env = null)
    {
        $this->script = str_replace("\r", "", $script);
        $this->scriptPathPrefix = $scriptPathPrefix;
        $this->env = $env ?? [];
    }

    /**
     * @return string
     *
     * @throws FilesystemException
     * @throws ScriptExecutorException
     */
    public function execute(): string
    {
        if ($this->executionFlag) {
            throw new ScriptExecutorException("Script already executed");
        }

        $commandExecutor = $this->getCommandExecutor();
        $scriptPath = $this->getScriptPath();

        $scriptDirectoryPath = dirname($scriptPath);
        if (!is_writable($scriptDirectoryPath)) {
            throw new FilesystemException("Given directory isn't writable: {$scriptDirectoryPath}", FilesystemException::ERROR_WRITE_DIRECTORY);
        }

        if (false === file_put_contents($scriptPath, $this->script)) {
            throw new FilesystemException("Can't write file: {$scriptPath}", FilesystemException::ERROR_WRITE_FILE);
        }

        try {
            if (!chmod($scriptPath, 0777)) {
                throw new FilesystemException("Can't set permissions for a file: {$scriptPath}", FilesystemException::ERROR_PERMISSIONS);
            }
            $output = $commandExecutor->executeCommand("{$scriptPath} 2>&1", $this->env);
        } catch (CommandExecutorException $e) {
            throw new ScriptExecutorException("Exception was thrown while executing command with script", 0, $e, $this->script);
        } finally {
            unlink($scriptPath);
            $this->executionFlag = true;
        }

        return (string) $output;
    }

    /**
     * @return string
     */
    public function getScriptPath(): string
    {
        return $this->scriptPathPrefix . uniqid() . ".sh";
    }

    /**
     * @return CommandExecutor
     */
    public function getCommandExecutor(): CommandExecutor
    {
        return new CommandExecutor();
    }

    /**
     * @return string
     *
     * @throws CommandExecutorException
     * @throws FilesystemException
     * @throws ScriptExecutorException
     */
    public function __invoke(): string
    {
        return $this->execute();
    }
}
