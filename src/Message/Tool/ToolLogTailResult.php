<?php
namespace whotrades\RdsSystem\Message\Tool;

class ToolLogTailResult extends \whotrades\RdsSystem\Message\RpcReply
{
    public $isSuccess;
    public $result;
    public $server;

    public function __construct($uniqueTag, $isSuccess, $server, $result)
    {
        $this->isSuccess  = $isSuccess;
        $this->result  = $result;
        $this->server  = $server;

        parent::__construct($uniqueTag);
    }
}
