<?php
namespace RdsSystem\Message;

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

    public static function type($receiverName = '*')
    {
        return get_called_class()."::".$receiverName;
    }

    public function accepted()
    {
        $this->channel->basic_ack($this->deliveryTag);
    }

    public function retry()
    {
        $this->channel->basic_recover(true);
    }
}