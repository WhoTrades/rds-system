<?php
namespace RdsSystem\Message;

class Base
{
    /** @var \AMQPChannel*/
    public $channel;
    public $deliveryTag;

    public static function type($receiverName = '*')
    {
        return get_called_class()."::".$receiverName;
    }

    public function accepted()
    {
        $this->channel->basic_ack($this->deliveryTag);
    }
}