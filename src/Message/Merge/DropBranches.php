<?php
/***
 * Задача на удаление фичевой ветки
 */
namespace RdsSystem\Message\Merge;

class DropBranches extends \RdsSystem\Message\Base
{
    public $branch;

    /**
     * DropBranches constructor.
     * @param string $branch
     */
    public function __construct($branch)
    {
        $this->branch    = $branch;

        parent::__construct();
    }
}
