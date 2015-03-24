<?php
namespace RdsSystem\Message\Tool;

class GetInfoTask extends \RdsSystem\Message\RpcRequest
{
    public $key;
    public $project;
    public $signal;
    public $timeout;

    public function __construct($key, $project)
    {
        $this->key      = $key;
        $this->project  = $project;

        parent::__construct();
    }
}
