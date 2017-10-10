<?php
namespace whotrades\RdsSystem\Message;

class MigrationTask extends Base
{
    public $project;
    public $version;
    public $type;
    public $scriptMigrationUp;

    /**
     * MigrationTask constructor.
     * @param string $project
     * @param string $version
     * @param string $type
     * @param string $scriptMigrationUp
     */
    public function __construct($project, $version, $type, $scriptMigrationUp)
    {
        $this->project = $project;
        $this->version = $version;
        $this->type = $type;
        $this->scriptMigrationUp = $scriptMigrationUp;

        parent::__construct();
    }
}
