<?php
/**
 * @package whotrades\RdsSystem\Message
 */
namespace whotrades\RdsSystem\Message;

class AbstractMultiServerTask extends Base
{
    /**
     * @var array
     */
    public $projectServers = [];

    /**
     * AbstractMultiServerTask constructor.
     *
     * @param array $projectServers
     */
    public function __construct(array $projectServers)
    {
        $this->projectServers = $projectServers;
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getProjectServers()
    {
        return $this->projectServers;
    }
}
