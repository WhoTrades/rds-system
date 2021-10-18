<?php

namespace whotrades\RdsSystem\Message;

class MigrationStatus extends Base
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    public $project;
    public $version;
    public $type;
    public $migrationName;
    public $status;
    public $error;

    public function __construct($project, $version, $type, $migrationName, $status, $error = null)
    {
        $this->project = $project;
        $this->version = $version;
        $this->type = $type;
        $this->migrationName = $migrationName;
        $this->status = $status;
        $this->error = $error ?? '';

        parent::__construct();
    }
}
