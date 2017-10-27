<?php
namespace whotrades\RdsSystem\Message;

class ReleaseRequestCronConfig extends Base
{
    public $taskId;
    public $text;

    public function __construct($taskId, $text)
    {
        $this->taskId = $taskId;
        $this->text = $text;

        parent::__construct();
    }
}
