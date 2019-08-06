<?php
/**
 * @author Anton Gorlanov <antonxacc@gmail.com>
 */
namespace whotrades\RdsSystem\Message;

abstract class DeployTaskBase extends AbstractMultiServerTask
{
    public $id;
    public $project;
    public $version;
    public $release;

    /**
     * BuildTask constructor.
     * @param int $id
     * @param string $project
     * @param string $version
     * @param string $release
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($id, $project, $version, $release, array $projectServers)
    {
        $this->id = $id;
        $this->project = $project;
        $this->version = $version;
        $this->release = $release;

        parent::__construct($projectServers);
    }
}
