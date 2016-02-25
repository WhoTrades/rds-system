<?php
/**
 * @author Artem Naumenko
 * Задача на переключение репозитория в другую ветку
 */
namespace RdsSystem\Message\PDev;

class SwitchBranch extends \RdsSystem\Message\Base
{
    public $path;
    public $branch;

    /**
     * @param string $path   - путь к репозиторию, который нужно переключить
     * @param string $branch - ветка, на которую нужно переключить
     */
    public function __construct($path, $branch)
    {
        $this->path   = $path;
        $this->branch = $branch;

        parent::__construct();
    }
}
