<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestMigrations extends Base
{
    public $project;
    public $version;
    public $migrations;
    public $type;

    public function __construct($project, $version, $migrations, $type)
    {
        $this->project = $project;
        $this->version = $version;
        $this->migrations = $migrations;
        $this->type = $type;
    }
}
