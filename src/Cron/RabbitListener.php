<?php
namespace RdsSystem\Cron;

use Raven_Client;
use \RdsSystem\Model\Rabbit\MessagingRdsMs;
use Yii;
use yii\console\Controller;

abstract class RabbitListener extends SingleInstanceController
{
    const CHECK_STILL_CAN_RNU_INTERVAL = 5;
    const ENV = 'main';

    public $maxDuration = 300;

    protected function waitForMessages(MessagingRdsMs $model)
    {
        declare(ticks = 1);
        pcntl_signal_dispatch();
        pcntl_signal(SIGTERM, array($this, 'onTerm'));
        pcntl_signal(SIGINT, array($this, 'onTerm'));

        for (;;) {
            try {
                $model->waitForMessages(null, null, $this->maxDuration);
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                break;
            }
        }

        return 0;
    }

    /**
     * @param string $actionID
     * @return \string[]
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['maxDuration']);
    }

    /** @return MessagingRdsMs */
    protected function getMessagingModel()
    {
        static $model = null;

        if ($model !== null) {
            return $model;
        }

        $rdsSystem = new \RdsSystem\Factory();
        $model  = $rdsSystem->getMessagingRdsMsModel(self::ENV);

        return $model;
    }

    /**
     * @param int $signo
     */
    public function onTerm($signo)
    {
        $this->getMessagingModel()->stopReceivingMessages();
        /** @var $sentry \mito\sentry\Component */
        $sentry = Yii::$app->sentry;
        $sentry->captureMessage("tool terminated by SIGTERM", ['signo' => $signo], Raven_Client::FATAL, true);
        Yii::error("tool terminated without termination behavior");
    }
}
