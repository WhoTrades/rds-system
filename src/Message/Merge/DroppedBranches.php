<?php
/***
 * Оповещение о том, что фичевые ветки были удалены
 */
namespace RdsSystem\Message\Merge;

class DroppedBranches extends \RdsSystem\Message\Base
{
    public function __construct($branch)
    {
        $this->branch    = $branch;
    }
}
