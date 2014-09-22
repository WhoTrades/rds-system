<?php
namespace RdsSystem\Message;

class ReleaseRequestCurrentStatusReply extends Base
{
    public $status;

    public function __construct($status)
    {
        $this->status = $status;
    }
}
