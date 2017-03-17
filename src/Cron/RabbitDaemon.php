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
            'env' => [
                'desc' => 'Some string, using at basename',
                'default' => \RdsSystem\Model\Rabbit\MessagingRdsMs::ENV_MAIN,
                'valueRequired' => true,
                'useForBaseName' => true,
            ]
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

    /** @return MessagingRdsMs */
    protected function getMessagingModel(\Cronjob\ICronjob $cronJob)
    {
        static $model = null;

        if ($model !== null) {
            return $model;
        }

        $rdsSystem = new \RdsSystem\Factory($this->debugLogger);
        $this->debugLogger->message("Using env=" . $cronJob->getOption('env'));
        $model  = $rdsSystem->getMessagingRdsMsModel($cronJob->getOption('env'));

        return $model;
    }
}