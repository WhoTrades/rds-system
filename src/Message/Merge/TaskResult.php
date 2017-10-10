<?php
namespace whotrades\RdsSystem\Message\Merge;

class TaskResult extends \whotrades\RdsSystem\Message\Base
{
    public $featureId;
    public $status;
    public $errors = [];
    public $targetBranch;
    public $sourceBranch;
    public $type;

    public function __construct($featureId, $sourceBranch, $targetBranch, $status, $errors, $type)
    {
        $this->featureId    = $featureId;
        $this->status       = $status;
        $this->errors       = $errors;
        $this->targetBranch = $targetBranch;
        $this->sourceBranch = $sourceBranch;
        $this->type         = $type;
    }
}
