<?php
namespace RdsSystem\Message;

class BuildTask extends AbstractMultiServerTask
{
    public $id;
    public $project;
    public $version;
    public $release;
    public $lastBuildTag;
    public $scriptMigrationNew;
    public $scriptDeploy;
    public $scriptCron;

    /**
     * BuildTask constructor.
     * @param int $id
     * @param string $project
     * @param string $version
     * @param string $release
     * @param string $lastBuildTag
     * @param string $scriptMigrationNew
     * @param string $scriptDeploy
     * @param string $scriptCron
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($id, $project, $version, $release, $lastBuildTag, $scriptMigrationNew, $scriptDeploy, $scriptCron, array $projectServers)
    {
        $this->id = $id;
        $this->project = $project;
        $this->version = $version;
        $this->release = $release;
        $this->lastBuildTag = $lastBuildTag;
        $this->scriptMigrationNew = $scriptMigrationNew;
        $this->scriptDeploy = $scriptDeploy;
        $this->scriptCron = $scriptCron;

        parent::__construct($projectServers);
    }
}
