<?php
namespace RdsSystem\Message;

class HardMigrationTask extends Base
{
    public $migration;
    public $project;
    public $version;

    public function __construct($migration, $project, $version)
    {
        $this->migration = $migration;
        $this->project = $project;
        $this->version = $version;
    }
}
