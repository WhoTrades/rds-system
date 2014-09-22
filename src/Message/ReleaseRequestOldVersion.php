<?php
namespace RdsSystem\Message;

class ReleaseRequestOldVersion extends Base
{
    public $releaseRequestId;
    public $version;

    public function __construct($releaseRequestId, $version)
    {
        $this->releaseRequestId = $releaseRequestId;
        $this->version = $version;
    }
}
