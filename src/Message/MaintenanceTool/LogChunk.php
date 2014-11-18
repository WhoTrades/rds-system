<?php
namespace RdsSystem\Message\MaintenanceTool;

class LogChunk extends \RdsSystem\Message\Base
{
    public $id;
    public $text;

    public function __construct($id, $text)
    {
        $this->id = $id;
        $this->text = $text;
    }
}
