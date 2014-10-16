<?php
namespace RdsSystem\Message;

class HardMigrationStatus extends Base
{
    public $migration;
    public $status;
    public $text;

    public function __construct($migration, $status, $text = null)
    {
        $this->migration = $migration;
        $this->status = $status;
        $this->text = $text;
    }
}
