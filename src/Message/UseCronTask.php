<?php
declare(strict_types=1);

namespace whotrades\RdsSystem\Message;

/**
 * Class UseCronTask
 *
 * @package whotrades\RdsSystem\Message
 */
class UseCronTask extends AbstractMultiServerTask
{
    /** @var string */
    public $project;

    /** @var string */
    public $version;

    /** @var string */
    public $initiatorUserName;

    /** @var string */
    public $scriptUseCron;

    /**
     * UseCronTask constructor.
     *
     * @param array $projectServers
     * @param string $project
     * @param string $version
     * @param string $initiatorUserName
     * @param string $scriptUseCron
     */
    public function __construct(array $projectServers, string $project, string $version, string $initiatorUserName, string $scriptUseCron)
    {
        $this->project = $project;
        $this->version = $version;
        $this->initiatorUserName = $initiatorUserName,
        $this->scriptUseCron = $scriptUseCron;

        parent::__construct($projectServers);
    }

}