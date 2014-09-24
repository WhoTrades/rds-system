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
                $model->waitForMessages(null, null, self::CHECK_STILL_CAN_RNU_INTERVAL);
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                $this->debugLogger->insane("Checking can I run");
                $cronJob->checkStillCanRun();
            }
        }
    }
}