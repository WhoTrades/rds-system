<?php
namespace RdsSystem\Message\Merge;

class CreateBranch extends \RdsSystem\Message\Base
{
    public $branch;
    public $source;
    public $force = false;

    public function __construct($branch, $source, $force)
    {
        $this->branch    = $branch;
        $this->source    = $source;
        $this->force     = $force;
    }
}
