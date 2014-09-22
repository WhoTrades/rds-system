<?php
namespace RdsSystem\Message;

class ReleaseRequestCronConfig extends Base
{
    public $taskId;
    public $text;

    public function __construct($taskId, $text)
    {
        $this->taskId = $taskId;
        $this->text = $text;
    }
}
