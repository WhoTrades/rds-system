<?php
namespace RdsSystem\Message;

class ReleaseRequestsReply extends Base
{
    public $releaseRequests;

    public function __construct($releaseRequests)
    {
        $this->releaseRequests = $releaseRequests;
    }
}
