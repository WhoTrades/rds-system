<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestRemove extends Base
{
    public $projectName;
    public $version;

    public function __construct($projectName, $version)
    {
        $this->projectName = $projectName;
        $this->version = $version;
    }
}
