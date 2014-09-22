<?php
namespace RdsSystem\Message;

class BuildTask extends Base
{
    public $id;
    public $project;
    public $version;
    public $release;
    public $lastBuildTag;

    public function __construct($id, $project, $version, $release, $lastBuildTag)
    {
        $this->id = $id;
        $this->project = $project;
        $this->version = $version;
        $this->release = $release;
        $this->lastBuildTag = $lastBuildTag;
    }
}
