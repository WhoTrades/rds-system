<?php
/**
 * Interface for migration logger
 *
 * Use LogAggregatorUrlInterface::FILTER_* filters for logging and filtering in log aggregator
 *
 * @author Anton Gorlanov <antonxacc@gmail.com>
 */
declare(strict_types=1);

namespace whotrades\RdsSystem\Migration;

interface LoggerInterface
{
    /**
     * @param string $migrationName
     * @param string $migrationType
     * @param string $migrationProject
     */
    public function __construct(string $migrationName, string $migrationType, string $migrationProject);

    /**
     * @param string $message
     */
    public function error(string $message);

    /**
     * @param string $message
     */
    public function warning(string $message);

    /**
     * @param string $message
     */
    public function info(string $message);
}
