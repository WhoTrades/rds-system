<?php
namespace whotrades\RdsSystem\Message\Merge;

class CreateBranch extends \whotrades\RdsSystem\Message\Base
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
