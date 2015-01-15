<?php
/***
 * Задача на удаление фичевой ветки
 */
namespace RdsSystem\Message\Merge;

class DropBranches extends \RdsSystem\Message\Base
{
    public function __construct($branch)
    {
        $this->branch    = $branch;
    }
}
