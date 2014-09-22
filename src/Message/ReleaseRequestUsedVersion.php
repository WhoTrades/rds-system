<?php
namespace RdsSystem\Message;

class ReleaseRequestUsedVersion extends Base
{
    public $worker;
    public $project;
    public $version;
    public $status;

    public function __construct($worker, $project, $version, $status)
    {
        $this->worker = $worker;
        $this->project = $project;
        $this->version = $version;
        $this->status = $status;
    }
}
