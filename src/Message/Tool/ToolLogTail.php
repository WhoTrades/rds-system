<?php
namespace RdsSystem\Message\Tool;

class ToolLogTail extends \RdsSystem\Message\RpcRequest
{
    public $tag;
    public $linesCount;

    public function __construct($tag, $linesCount)
    {
        $this->tag      = $tag;
        $this->linesCount  = $linesCount;

        parent::__construct();
    }
}
