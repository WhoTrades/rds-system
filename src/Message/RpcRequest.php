<?php
namespace whotrades\RdsSystem\Message;

class RpcRequest extends Base
{
    public $uniqueTag;

    public function __construct()
    {
        $this->uniqueTag = md5(microtime(true).rand(1, PHP_INT_MAX));
    }

    public function getUniqueTag()
    {
        return $this->uniqueTag;
    }
}