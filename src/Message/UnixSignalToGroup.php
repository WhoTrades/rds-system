<?php
namespace RdsSystem\Message;

class UnixSignalToGroup extends Base
{
    public $pgid;

    public function __construct($pgid)
    {
        $this->pgid = $pgid;
    }
}
