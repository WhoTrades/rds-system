<?php
namespace RdsSystem\Message;

class RpcReply extends Base
{
    public $uniqueTag;

    public function __construct($uniqueTag)
    {
        $this->uniqueTag = $uniqueTag;
        parent::__construct();
    }

    public function getUniqueTag()
    {
        return $this->uniqueTag;
    }
}