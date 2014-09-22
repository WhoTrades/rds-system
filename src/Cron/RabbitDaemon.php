<?php
namespace RdsSystem\Cron;

use \RdsSystem\Model\Rabbit\MessagingRdsMs;

abstract class RabbitDaemon extends \Cronjob\Tool\ToolBase
{
    public static function getCommandLineSpec()
    {
        return array(
            'max-duration' => [
                'desc' => 'Time, after thet script will try to shut down correctly',
                'default' => 300,
                'valueRequired' => true,
            ],
        );
    }

    protected function waitForMessages(MessagingRdsMs $model, \Cronjob\ICronjob $cronJob)
    {
        for (;;) {
            try {
                $model->waitForMessages(null, null, (float)$cronJob->getOption('max-duration'));
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                //$this->debugLogger->message("Checking can I run");
                //$cronJob->checkStillCanRun();

                return;
            }
        }
    }
}