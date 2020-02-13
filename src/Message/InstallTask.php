<?php
/**
 * @author Anton Gorlanov <antonxacc@gmail.com>
 */
namespace whotrades\RdsSystem\Message;

class InstallTask extends DeployTaskBase
{
    public $scriptInstall;
    public $scriptPostInstall;

    /**
     * BuildTask constructor.
     * @param int $id
     * @param string $project
     * @param string $version
     * @param string $release
     * @param string $scriptInstall
     * @param string $scriptPostInstall
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($id, $project, $version, $release, $scriptInstall, $scriptPostInstall, array $projectServers)
    {
        $this->scriptInstall = $scriptInstall;
        $this->scriptPostInstall = $scriptPostInstall;

        parent::__construct($id, $project, $version, $release, $projectServers);
    }
}
