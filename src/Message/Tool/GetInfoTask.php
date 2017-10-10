<?php
namespace whotrades\RdsSystem\Message\Tool;

class GetInfoTask extends \whotrades\RdsSystem\Message\RpcRequest
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
