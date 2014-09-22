<?php
namespace RdsSystem\Message;

class ReleaseRequestUseError extends Base
{
    public $releaseRequestId;
    public $text;

    public function __construct($releaseRequestId, $text)
    {
        $this->releaseRequestId = $releaseRequestId;
        $this->text = $text;
    }
}
