<?php
namespace RdsSystem\Message;

class ReleaseRequestsRequest extends Base
{
    public $project;

    public function __construct($project)
    {
        $this->project = $project;
    }
}
