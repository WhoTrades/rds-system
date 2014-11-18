<?php
namespace RdsSystem\Message\MaintenanceTool;

class Start extends \RdsSystem\Message\Base
{
    public $id;
    public $command;

    public function __construct($id, $command)
    {
        $this->id = $id;
        $this->command = $command;
    }
}
