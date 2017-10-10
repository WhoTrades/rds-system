<?php
namespace whotrades\RdsSystem\Message;

class DropReleaseRequest extends AbstractMultiServerTask
{
    public $project;
    public $version;
    public $scriptRemove;

    /**
     * BuildTask constructor.
     * @param string $project
     * @param string $version
     * @param string $scriptRemove
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($project, $version, $scriptRemove, array $projectServers)
    {
        $this->project = $project;
        $this->version = $version;
        $this->scriptRemove = $scriptRemove;

        parent::__construct($projectServers);
    }
}
