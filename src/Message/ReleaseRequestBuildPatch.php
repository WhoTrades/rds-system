<?php
namespace RdsSystem\Message;

class ReleaseRequestBuildPatch extends Base
{
    public $project;
    public $version;
    public $output;
    public $type;

    public function __construct($project, $version, $output)
    {
        $this->project = $project;
        $this->version = $version;
        $this->output = $output;
    }
}
