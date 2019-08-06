<?php
namespace whotrades\RdsSystem\Message;

class BuildTask extends DeployTaskBase
{
    public $scriptMigrationNew;
    public $scriptBuild;
    public $scriptCron;

    /**
     * BuildTask constructor.
     * @param int $id
     * @param string $project
     * @param string $version
     * @param string $release
     * @param string $scriptMigrationNew
     * @param string $scriptBuild
     * @param string $scriptCron
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($id, $project, $version, $release, $scriptMigrationNew, $scriptBuild, $scriptCron, array $projectServers)
    {
        $this->scriptMigrationNew = $scriptMigrationNew;
        $this->scriptBuild = $scriptBuild;
        $this->scriptCron = $scriptCron;

        parent::__construct($id, $project, $version, $release, $projectServers);
    }
}
