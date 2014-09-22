<?php
namespace RdsSystem\Message;

class ProjectBuildsToDeleteReply extends Base
{
    public $buildToDelete;

    public function __construct($buildToDelete)
    {
        $this->buildToDelete = $buildToDelete;
    }
}
