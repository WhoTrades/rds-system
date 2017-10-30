<?php
namespace whotrades\RdsSystem;

use \whotrades\RdsSystem\Model\Rabbit\MessagingRdsMs;
use Yii;

class Factory
{
    /** @var MessagingRdsMs */
    private $messagingRdsMsModel;

    /**
     * @return MessagingRdsMs
     */
    public function getMessagingRdsMsModel()
    {
        if (empty($this->messagingRdsMsModel)) {
            $this->messagingRdsMsModel = $this->getMessagingModel();
        }

        return $this->messagingRdsMsModel;
    }

    protected function getMessagingModel()
    {
        $config = Yii::$app->params['messaging'];

        return new MessagingRdsMs($config['host'], $config['port'], $config['user'], $config['pass'], $config['vhost']);
    }
}
