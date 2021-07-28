<?php
/**
 * Interface for class which generate url to migration log aggregator
 *
 * Use LogAggregatorUrlInterface::FILTER_* filters for logging and filtering in log aggregator
 *
 * @author Anton Gorlanov <antonxacc@gmail.com>
 */
declare(strict_types=1);

namespace whotrades\RdsSystem\Migration;

interface LogAggregatorUrlInterface
{
    const FILTER_MIGRATION_NAME = 'migration_name';
    const FILTER_MIGRATION_TYPE = 'migration_type';
    const FILTER_MIGRATION_PROJECT = 'migration_project';

    /**
     * @param string $migrationName
     * @param string $migrationType
     * @param string $migrationProject
     *
     * @return string
     */
    public function generateFiltered(string $migrationName, string $migrationType, string $migrationProject): string;
}
