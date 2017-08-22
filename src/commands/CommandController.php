<?php
/**
 * @author Artem Naumenko
 */

namespace RdsSystem\commands;

use RdsSystem\Cron\SingleInstanceController;

abstract class CommandController extends SingleInstanceController
{
    public $user;
    public $projectPath;
    public $package;

    /**
     * @param string $actionID
     * @return array
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['package']);
    }

    /**
     * @param string $user
     * @param string $projectPath
     */
    public function actionIndex($user, $projectPath)
    {
        $this->user = $user;
        $this->projectPath = $projectPath;

        if (realpath($projectPath) == false) {
            throw new \InvalidArgumentException("Path $projectPath not found");
        }

        $list = $this->getCommands();

        $this->stdout(implode("\n", $list) . "\n");
    }

    abstract protected function getCommands();

    /**
     * @param string $className
     * @param string $action
     * @param array $params
     * @param string $tagName
     * @param string $interval
     *
     * @return string
     */
    public function createCommand($className, $action, $params, $tagName, $interval = null)
    {
        $interval = $interval ?? '* * * * * *';
        $command = $this->convertCommandClassNameToCommandName($className);

        $params[] = '--sys__key=' . $this->getCommandKey($className, $params);

        if ($this->package) {
            $params[] = '--sys__package=' . $this->package;
        }

        return "$interval $this->user cd $this->projectPath && php yii.php $command/$action " . implode(" ", $params) . " | logger -p local2.info -t $tagName";
    }

    private function getCommandKey($className, $parameters)
    {
        if ($this->package) {
            $parameters[] = '--sys__package=' . preg_replace('~-[\d.]+$~', '', $this->package);
        }

        return substr(md5($className . ":" . implode(", ", $parameters)), 0, 12);
    }

    private function convertCommandClassNameToCommandName($className)
    {
        $moduleName = $this->module->getUniqueId();
        $result = preg_replace('~.*\\\~', '', $className);
        $result = preg_replace('~Controller$~', '', $result);
        $result = lcfirst($result);
        $result = preg_replace_callback('~[A-Z]~', function ($match) {
            return '-' . strtolower($match[0]);
        }, $result);

        return $moduleName ? "$moduleName/$result" : $result;
    }
}
