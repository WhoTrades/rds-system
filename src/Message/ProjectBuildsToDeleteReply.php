<?php
namespace RdsSystem\Message;

class ProjectBuildsToDeleteReply extends AbstractMultiServerTask
{
    public $project;
    public $version;

    /**
     * ProjectBuildsToDeleteReply constructor.
     *
     * @param string $project
     * @param string $version
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($project, $version, array $projectServers)
    {
        $this->project = $project;
        $this->version = $version;

        parent::__construct($projectServers);
    }
}
