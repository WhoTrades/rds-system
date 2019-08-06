<?php
/**
 * @author Anton Gorlanov <antonxacc@gmail.com>
 */
namespace whotrades\RdsSystem\Message;

class InstallTask extends DeployTaskBase
{
    public $scriptInstall;

    /**
     * BuildTask constructor.
     * @param int $id
     * @param string $project
     * @param string $version
     * @param string $release
     * @param string $scriptInstall
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($id, $project, $version, $release, $scriptInstall, array $projectServers)
    {
        $this->scriptInstall = $scriptInstall;

        parent::__construct($id, $project, $version, $release, $projectServers);
    }
}
