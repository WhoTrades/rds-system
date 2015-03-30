<?php
namespace RdsSystem\Message\Tool;

class GetInfoResult extends \RdsSystem\Message\RpcReply
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