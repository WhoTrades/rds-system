<?php
namespace RdsSystem\Message;

class ReleaseRequestMigrationStatus extends Base
{
    public $project;
    public $version;
    public $type;
    public $status;

    public function __construct($project, $version, $type, $status)
    {
        $this->project = $project;
        $this->version = $version;
        $this->type = $type;
        $this->status = $status;
    }
}
