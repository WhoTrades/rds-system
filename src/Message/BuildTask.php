<?php
namespace RdsSystem\Message;

class BuildTask extends Base
{
    public $id;
    public $project;
    public $version;
    public $release;
    public $lastBuildTag;
    public $scriptMigrationNew;

    /**
     * BuildTask constructor.
     * @param int $id
     * @param string $project
     * @param string $version
     * @param string $release
     * @param string $lastBuildTag
     * @param string $scriptMigrationNew
     */
    public function __construct($id, $project, $version, $release, $lastBuildTag, $scriptMigrationNew)
    {
        $this->id = $id;
        $this->project = $project;
        $this->version = $version;
        $this->release = $release;
        $this->scriptMigrationNew = $scriptMigrationNew;

        parent::__construct();
    }
}
