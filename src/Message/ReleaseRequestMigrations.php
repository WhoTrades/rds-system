<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestMigrations extends Base
{
    public $project;
    public $version;
    public $migrations;
    public $type;
    public $command;

    public function __construct($project, $version, $migrations, $type, $command)
    {
        $this->project = $project;
        $this->version = $version;
        $this->migrations = $migrations;
        $this->type = $type;
        $this->command = $command;

        parent::__construct();
    }
}
