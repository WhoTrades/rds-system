<?php
/***
 * Задача на удаление фичевой ветки
 */
namespace whotrades\RdsSystem\Message\Merge;

class DropBranches extends \whotrades\RdsSystem\Message\Base
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
