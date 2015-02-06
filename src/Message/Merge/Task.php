<?php
namespace RdsSystem\Message\Merge;

class Task extends \RdsSystem\Message\Base
{
    const MERGE_TYPE_FEATURE    = 'feature';
    const MERGE_TYPE_BUILD      = 'build';

    public $featureId;
    public $sourceBranch;
    public $targetBranch;
    public $type = self::MERGE_TYPE_FEATURE;

    public function __construct($featureId, $sourceBranch, $targetBranch, $type = self::MERGE_TYPE_FEATURE)
    {
        $this->featureId    = $featureId;
        $this->sourceBranch = $sourceBranch;
        $this->targetBranch = $targetBranch;
        $this->type         = $type;
    }
}
