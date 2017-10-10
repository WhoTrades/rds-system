<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestCurrentStatusReply extends RpcReply
{
    public $status;

    public function __construct($status, $uniqueTag)
    {
        $this->status = $status;

        parent::__construct($uniqueTag);
    }
}
