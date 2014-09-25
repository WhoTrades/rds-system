<?php
namespace RdsSystem\Cron;

use \RdsSystem\Model\Rabbit\MessagingRdsMs;

abstract class RabbitDaemon extends \Cronjob\Tool\ToolBase
{
    const CHECK_STILL_CAN_RNU_INTERVAL = 5;

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
                $model->waitForMessages(null, null, $cronJob->getOption('max-duration'));
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                return 0;
            }
        }
    }
}