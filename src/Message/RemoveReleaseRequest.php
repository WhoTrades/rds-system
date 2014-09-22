<?php
namespace RdsSystem\Message;

class RemoveReleaseRequest extends Base
{
    public $projectName;
    public $version;

    public function __construct($projectName, $version)
    {
        $this->projectName = $projectName;
        $this->version = $version;
    }
}
