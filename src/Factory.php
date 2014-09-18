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
    public function getMessagingRdsMsModel()
    {
        if ($this->messagingRdsMsModel === null) {
            $this->messagingRdsMsModel = new Model\Rabbit\MessagingRdsMs($this->debugLogger);
        }

        return $this->messagingRdsMsModel;
    }
}
