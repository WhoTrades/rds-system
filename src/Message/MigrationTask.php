<?php
namespace whotrades\RdsSystem\Message;

class MigrationTask extends Base
{
    public $project;
    public $version;
    public $type;
    public $migrationName;
    public $migrationCommand;
    public $scriptMigrationUp;

    /**
     * MigrationTask constructor.
     * @param string $project
     * @param string $version
     * @param string $type
     * @param string $scriptMigrationUp
     * @param string $migrationCommand
     * @param string | null $migrationName
     */
    public function __construct($project, $version, $type, $scriptMigrationUp, $migrationCommand, $migrationName = null)
    {
        $this->project = $project;
        $this->version = $version;
        $this->type = $type;
        $this->scriptMigrationUp = $scriptMigrationUp;
        $this->migrationCommand = $migrationCommand;
        $this->migrationName = $migrationName ?? '';

        parent::__construct();
    }
}
