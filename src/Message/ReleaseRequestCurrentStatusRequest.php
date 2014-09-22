<?php
namespace RdsSystem\Message;

class ReleaseRequestCurrentStatusRequest extends Base
{
    public $releaseRequestId;

    public function __construct($releaseRequestId)
    {
        $this->releaseRequestId = $releaseRequestId;
    }
}
