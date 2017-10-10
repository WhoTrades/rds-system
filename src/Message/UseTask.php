<?php
namespace whotrades\RdsSystem\Message;

class UseTask extends AbstractMultiServerTask
{
    public $project;
    public $releaseRequestId;
    public $version;
    public $initiatorUserName;

    /**
     * UseTask constructor.
     *
     * @param string $project - название проекта, например comon, service-crm
     * @param int $releaseRequestId comon4.rds.release_request.obj_id
     * @param string $version - например 67.00.12.1289
     * @param string $initiatorUserName - имя того, кто нажал use
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($project, $releaseRequestId, $version, $initiatorUserName, array $projectServers)
    {
        $this->project = $project;
        $this->releaseRequestId = $releaseRequestId;
        $this->version = $version;
        $this->initiatorUserName = $initiatorUserName;

        parent::__construct($projectServers);
    }
}
