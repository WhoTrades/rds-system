<?php
namespace RdsSystem\Message\Merge;

class Task extends \RdsSystem\Message\Base
{
    public $featureId;
    public $sourceBranch;
    public $targetBranch;

    public function __construct($featureId, $sourceBranch, $targetBranch)
    {
        $this->featureId    = $featureId;
        $this->sourceBranch = $sourceBranch;
        $this->targetBranch = $targetBranch;
    }
}
