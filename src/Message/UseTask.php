<?php
namespace RdsSystem\Message;

class UseTask extends Base
{
    public $project;
    public $releaseRequestId;
    public $version;
    public $useStatus;

    public function __construct($project, $releaseRequestId, $version, $useStatus)
    {
        $this->project = $project;
        $this->releaseRequestId = $releaseRequestId;
        $this->version = $version;
        $this->useStatus = $useStatus;
    }
}
