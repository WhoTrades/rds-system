<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestMigrationStatus extends Base
{
    public $project;
    public $version;
    public $type;
    public $status;
    public $errorText;

    public function __construct($project, $version, $type, $status, $errorText = "")
    {
        $this->project = $project;
        $this->version = $version;
        $this->type = $type;
        $this->status = $status;
        $this->errorText = $errorText;

        parent::__construct();
    }
}
