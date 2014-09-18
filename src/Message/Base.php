<?php
namespace RdsSystem\Message;

class Base
{
    public $deliveryTag;

    public static function type()
    {
        return get_called_class();
    }
}