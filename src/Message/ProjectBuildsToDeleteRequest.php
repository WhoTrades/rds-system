<?php
namespace RdsSystem\Message;

class ProjectBuildsToDeleteRequest extends Base
{
    public $allBuilds;

    public function __construct($allBuilds)
    {
        $this->allBuilds = $allBuilds;
    }
}
