<?php
namespace whotrades\RdsSystem\Message;

class TaskStatusChanged extends Base
{
    public $status;
    public $taskId;
    public $version;
    public $attach;

    public function __construct($taskId, $status, $version = null, $attach = null)
    {
        $this->taskId = $taskId;
        $this->status = $status;
        $this->version = $version;
        $this->attach = $attach;

        parent::__construct();
    }
}
