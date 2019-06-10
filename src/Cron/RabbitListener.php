<?php
namespace whotrades\RdsSystem\Cron;

use Raven_Client;
use whotrades\RdsSystem\Model\Rabbit\MessagingRdsMs;
use Yii;
use whotrades\RdsSystem\Factory;
use yii\base\ExitException;

abstract class RabbitListener extends SingleInstanceController
{
    const CHECK_STILL_CAN_RNU_INTERVAL = 5;
    const ENV = 'main';

    public $maxDuration = 300;

    protected function waitForMessages(MessagingRdsMs $model)
    {
        pcntl_async_signals(true);
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

        $rdsSystem = new Factory();
        $model  = $rdsSystem->getMessagingRdsMsModel();

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

        throw new ExitException();
    }
}
