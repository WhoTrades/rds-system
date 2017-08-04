<?php
namespace RdsSystem;

use \RdsSystem\Model\Rabbit\MessagingRdsMs;

final class Factory
{
    /** @var */
    private $messagingRdsMsModel;

    /**
     * @param string $env
     * @return MessagingRdsMs
     */
    public function getMessagingRdsMsModel($env = null)
    {
        $env = $env ?? MessagingRdsMs::ENV_MAIN;
        if (empty($this->messagingRdsMsModel[$env])) {
            $this->messagingRdsMsModel[$env] = new MessagingRdsMs($env);
        }

        return $this->messagingRdsMsModel[$env];
    }
}
