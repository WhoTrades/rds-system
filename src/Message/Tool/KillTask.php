<?php
namespace whotrades\RdsSystem\Message\Tool;

class KillTask extends \whotrades\RdsSystem\Message\RpcRequest
{
    public $key;
    public $project;
    public $signal;

    public function __construct($key, $project, $signal)
    {
        $this->key      = $key;
        $this->project  = $project;
        $this->signal   = $signal;

        parent::__construct();
    }
}
