<?php
namespace RdsSystem;

final class Factory
{
    /** @var \ServiceBase_IDebugLogger */
    private $debugLogger;

    /** @var */
    private $messagingRdsMsModel;

    public function __construct(\ServiceBase_IDebugLogger $debugLogger)
    {
        $this->debugLogger = $debugLogger;
    }

    /** @return Model\Rabbit\MessagingRdsMs */
    public function getMessagingRdsMsModel($env = \RdsSystem\Model\Rabbit\MessagingRdsMs::ENV_MAIN)
    {
        if (empty($this->messagingRdsMsModel[$env])) {
            $this->messagingRdsMsModel[$env] = new Model\Rabbit\MessagingRdsMs($this->debugLogger, $env);
        }

        return $this->messagingRdsMsModel[$env];
    }
}
