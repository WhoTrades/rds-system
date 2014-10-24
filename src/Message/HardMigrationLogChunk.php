<?php
namespace RdsSystem\Message;

class HardMigrationLogChunk extends Base
{
    public $migration;
    public $status;
    public $text;

    public function __construct($migration, $text = null)
    {
        $this->migration = $migration;
        $this->text = $text;
    }
}
