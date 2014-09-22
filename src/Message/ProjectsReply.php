<?php
namespace RdsSystem\Message;

class ProjectsReply extends Base
{
    public $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
    }
}
