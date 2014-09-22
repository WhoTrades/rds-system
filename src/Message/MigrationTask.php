<?php
namespace RdsSystem\Message;

class MigrationTask extends Base
{
    public $project;
    public $version;
    public $type;

    public function __construct($project, $version, $type)
    {
        $this->project = $project;
        $this->version = $version;
        $this->type = $type;
    }
}
