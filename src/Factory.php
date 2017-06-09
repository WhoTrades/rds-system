<?php
namespace RdsSystem;

use \RdsSystem\Model\Rabbit\MessagingRdsMs;
use ServiceBase_IDebugLogger;

final class Factory
{
    /** @var ServiceBase_IDebugLogger */
    private $debugLogger;

    /** @var */
    private $messagingRdsMsModel;

    public function __construct(\ServiceBase_IDebugLogger $debugLogger)
    {
        $this->debugLogger = $debugLogger;
    }

    /** @return MessagingRdsMs */
    public function getMessagingRdsMsModel($env = MessagingRdsMs::ENV_MAIN)
    {
        if (empty($this->messagingRdsMsModel[$env])) {
            $this->messagingRdsMsModel[$env] = new MessagingRdsMs($this->debugLogger, $env);
        }

        return $this->messagingRdsMsModel[$env];
    }
}
