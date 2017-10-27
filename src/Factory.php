<?php
namespace whotrades\RdsSystem;

use \whotrades\RdsSystem\Model\Rabbit\MessagingRdsMs;

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
        return new MessagingRdsMs();
    }
}
