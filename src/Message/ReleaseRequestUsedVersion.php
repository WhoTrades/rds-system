<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestUsedVersion extends Base
{
    public $worker;
    public $project;
    public $version;
    public $initiatorUserName;
    public $text;

    /**
     * ReleaseRequestUsedVersion constructor.
     *
     * @param string $worker
     * @param int $project
     * @param string $version
     * @param string $initiatorUserName
     */
    public function __construct($worker, $project, $version, $initiatorUserName, $text)
    {
        $this->worker = $worker;
        $this->project = $project;
        $this->version = $version;
        $this->initiatorUserName = $initiatorUserName;
        $this->text = $text;

        parent::__construct();
    }
}
