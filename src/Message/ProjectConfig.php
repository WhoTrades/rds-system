<?php
namespace RdsSystem\Message;

class ProjectConfig extends Base
{
    public $project;
    public $config;

    public function __construct($project, $config)
    {
        $this->project  = $project;
        $this->config   = $config;
    }
}
