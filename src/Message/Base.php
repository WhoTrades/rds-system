<?php
namespace whotrades\RdsSystem\Message;

class Base
{
    /** @var \PhpAmqpLib\Channel\AMQPChannel*/
    public $channel;
    public $deliveryTag;
    public $timeCreated;

    public function __construct()
    {
        $this->timeCreated = microtime(true);
    }

    /**
     * @param string $receiverName
     *
     * @return string
     */
    public static function type($receiverName = '*')
    {
        return get_called_class() . "::" . $receiverName;
    }

    public function accepted()
    {
        $this->channel->basic_ack($this->deliveryTag);
    }

    public function retry()
    {
        $this->channel->basic_nack($this->deliveryTag, false, true);
    }
}
