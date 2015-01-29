<?php
/***
 * Оповещение о том, что фичевые ветки были удалены
 */
namespace RdsSystem\Message\Merge;

class DroppedBranches extends \RdsSystem\Message\Base
{
    /**
     * @param string $branch
     * @param array $skippedRepositories
     * Массив со список репозиториев, откуда ветки не были удалены и списком комитов, из-за которых не удалили вида
     * array(
            'comon' => 'ca2d15d09ce0a4d4fa8a47d230d7c833480350a8 some_commit_message
f07ee863261e0eed93de654e2fcbafbef39741b4 some_commit_message2',
            'git-tools' => '111d15d09ce0a4d4fa8a47d230d7c833480350a8 some_commit_message3
    222ee863261e0eed93de654e2fcbafbef39741b4 some_commit_message4',
     * );
     */
    public function __construct($branch, $skippedRepositories = [])
    {
        $this->branch    = $branch;
        $this->skippedRepositories    = $skippedRepositories;
    }
}
