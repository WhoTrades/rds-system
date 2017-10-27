<?php
namespace whotrades\RdsSystem\Message;

class KillTask extends Base
{
    public $project;
    public $taskId;

    public function __construct($project, $taskId)
    {
        $this->project = $project;
        $this->taskId = $taskId;

        parent::__construct();
    }
}
