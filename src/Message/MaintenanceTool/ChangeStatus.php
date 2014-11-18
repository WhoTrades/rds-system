<?php
namespace RdsSystem\Message\MaintenanceTool;

class ChangeStatus extends \RdsSystem\Message\Base
{
    public $id;
    public $status;
    public $pid;

    public function __construct($id, $status, $pid)
    {
        $this->id = $id;
        $this->status = $status;
        $this->pid = $pid;
    }
}
