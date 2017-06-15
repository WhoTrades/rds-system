<?php
namespace RdsSystem\Message;

class ProjectBuildsToDeleteReply extends AbstractMultiServerTask
{
    public $buildToDelete;

    /**
     * ProjectBuildsToDeleteReply constructor.
     *
     * @param array $buildToDelete
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($buildToDelete, array $projectServers)
    {
        $this->buildToDelete = $buildToDelete;

        parent::__construct($projectServers);
    }
}
