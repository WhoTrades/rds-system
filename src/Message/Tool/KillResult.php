<?php
namespace RdsSystem\Message\Tool;

class KillResult extends \RdsSystem\Message\RpcReply
{
    public $result;
    public $server;

    public function __construct($uniqueTag, $server, $result)
    {
        $this->result  = $result;
        $this->server  = $server;

        parent::__construct($uniqueTag);
    }
}
