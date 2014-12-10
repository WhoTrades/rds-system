<?php
namespace RdsSystem\Message\Merge;

class TaskResult extends \RdsSystem\Message\Base
{
    public $featureId;
    public $status;
    public $errors = [];
    public $targetBranch;
    public $sourceBranch;

    public function __construct($featureId, $sourceBranch, $targetBranch, $status, $errors)
    {
        $this->featureId    = $featureId;
        $this->status       = $status;
        $this->errors       = $errors;
        $this->targetBranch = $targetBranch;
        $this->sourceBranch = $sourceBranch;
    }
}
