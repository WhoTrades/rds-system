<?php
namespace RdsSystem\Message\Merge;

class CreateBranch extends \RdsSystem\Message\Base
{
    public $branch;
    public $source;

    public function __construct($branch, $source)
    {
        $this->branch    = $branch;
        $this->source    = $source;
    }
}
