<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestCurrentStatusRequest extends RpcRequest
{
    public $releaseRequestId;

    public function __construct($releaseRequestId)
    {
        $this->releaseRequestId = $releaseRequestId;

        parent::__construct();
    }
}
