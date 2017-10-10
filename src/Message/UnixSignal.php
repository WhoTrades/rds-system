<?php
namespace whotrades\RdsSystem\Message;

class UnixSignal extends Base
{
    public $pid;
    public $signal;

    public function __construct($pid, $signal)
    {
        $this->pid = $pid;
        $this->signal = $signal;
    }
}
