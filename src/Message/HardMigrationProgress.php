<?php
namespace RdsSystem\Message;

class HardMigrationProgress extends Base
{
    public $migration;
    public $progress;
    public $action;
    public $pid;

    public function __construct($migration, $progress, $action, $pid)
    {
        $this->migration = $migration;
        $this->progress = $progress;
        $this->action = $action;
        $this->pid = $pid;
    }
}
