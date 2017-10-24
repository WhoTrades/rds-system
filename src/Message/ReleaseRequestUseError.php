<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestUseError extends Base
{
    public $releaseRequestId;
    public $initiatorUserName;
    public $text;

    /**
     * ReleaseRequestUseError constructor.
     * @param int $releaseRequestId
     * @param string $initiatorUserName
     * @param string $text
     */
    public function __construct($releaseRequestId, $initiatorUserName, $text)
    {
        $this->releaseRequestId = $releaseRequestId;
        $this->initiatorUserName = $initiatorUserName;
        $this->text = $text;

        parent::__construct();
    }
}
