<?php
namespace RdsSystem\Message;

class ReleaseRequestUsedVersion extends Base
{
    public $worker;
    public $project;
    public $version;
    public $initiatorUserName;

    /**
     * ReleaseRequestUsedVersion constructor.
     *
     * @param string $worker
     * @param int $project
     * @param string $version
     * @param string $initiatorUserName
     */
    public function __construct($worker, $project, $version, $initiatorUserName)
    {
        $this->worker = $worker;
        $this->project = $project;
        $this->version = $version;
        $this->initiatorUserName = $initiatorUserName;

        parent::__construct();
    }
}
