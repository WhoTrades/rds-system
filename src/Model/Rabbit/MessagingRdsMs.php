<?php
namespace RdsSystem\Model\Rabbit;

use RdsSystem\Message;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final class MessagingRdsMs
{
    const HOST = 'rabbitmq-01.local';
    const PORT = 5672;
    const USER = 'rds';
    const PASS = 'rds';
    const VHOST = '/';

    const EXCHANGE = 'rds_exchange';
    const ENV_MAIN = 'main';

    const TYPE_BUILD_FROM_RDS = 'rds_build_from_rds';
    const TYPE_BUILD_FROM_MS = 'rds_build_from_ms';

    /** @var \ServiceBase_IDebugLogger */
    private $debugLogger;

    private $env;

    /** @var AMQPConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

    public function __construct(\ServiceBase_IDebugLogger $debugLogger, $env = self::ENV_MAIN)
    {
        $this->debugLogger = $debugLogger;
        $this->env = $env;

        $this->connection = new AMQPConnection(self::HOST, self::PORT, self::USER, self::PASS, self::VHOST);
        $this->channel = $this->connection->channel();
    }

    private function declareAndGetQueueAndExchange($messageType)
    {
        //@todo избавиться от повторного создания эксчейнджа и очереди
        $exchangeName = $queueName = $this->env.":".$messageType.":";
        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->channel->exchange_declare($exchangeName, 'direct', false, true, false);
        $this->channel->queue_bind($queueName, $exchangeName, $queueName);

        return [$exchangeName, $queueName];
    }

    private function writeMessage(Message\Base $message)
    {
        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($message::type());
        $rabbitMessage = new AMQPMessage(serialize($message));
        $this->channel->basic_publish($rabbitMessage, $exchangeName, $queueName);
    }

    private function readMessage($messageType, $callback)
    {
        static $inited = [];

        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($messageType);

        $inited[] = $messageType;
        $this->channel->basic_consume($queueName, $exchangeName, false, false, false, false, function($message) use ($callback) {
            $reply = unserialize($message->body);
            $reply->deliveryTag = $message->delivery_info['delivery_tag'];
            echo "[x] Message received {$reply->deliveryTag}\n";

            $callback($reply);
        });

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function acceptMessage(Message\Base $message)
    {
        $this->channel->basic_ack($message->deliveryTag);
    }

    /** Сообщает RDS об изменении состояния сборки */
    public function sendStatusChange(Message\ReleaseRequestStatusChanged $message)
    {
        $this->writeMessage($message);
    }

    /** @return Message\ReleaseRequestStatusChanged */
    public function readStatusChange($callback)
    {
        $this->readMessage(Message\ReleaseRequestStatusChanged::type(), $callback);
    }

    /** Сообщает RDS об новых коммитах, которые попали в сборку */
    public function sendBuildPatch($project, $version, $output)
    {
        return $this->sendRequest("sendBuildPatch", array(
            'project' => $project,
            'version' => $version,
            'output' => $output,
        ), true);
    }

    /** Сообщает RDS список новых pre и post миграций, которые попали в сборку */
    public function sendMigrations($project, $version, $migrations, $type)
    {
        return $this->sendRequest("sendMigrations", array(
            'project' => $project,
            'version' => $version,
            'migrations' => $migrations,
            'type' => $type,
        ));
    }

    /** Сообщает RDS cron конфиг сборки */
    public function sendCronConfig($taskId, $text)
    {
        return $this->sendRequest("sendCronConfig", array(
            'taskId' => $taskId,
            'text' => $text,
        ), true);
    }

    /** Сообщает RDS изменении статуса миграций сборки */
    public function sendMigrationStatus($project, $version, $type, $status)
    {
        return $this->sendRequest('sendMigrationStatus', array('project' => $project, 'version' => $version, 'type' => $type, 'status' => $status));
    }

    /** Сообщает RDS о том, что не получилось выполнить USE и описание ошибки */
    public function setUseError($id, $text)
    {
        return $this->sendRequest('setUseError', array('id' => $id, 'text' => $text,));
    }

    /** Сообщает RDS какая версия проекта была на самом деле выложена перед нажатием USE (полезно, в случае когда USE выполнят мимо RDS) */
    public function setOldVersion($id, $version)
    {
        return $this->sendRequest('setOldVersion', array('id' => $id, 'version' => $version));
    }

    /** Сообщает RDS какая версия проекта была выложена */
    public function setUsedVersion($worker, $project, $version, $status)
    {
        return $this->sendRequest('setUsedVersion', array('worker' => $worker, 'project' => $project, 'version' => $version, 'status' => $status));
    }

    /** Удаляет сборку из RDS, используется сборщиком мусора */
    public function removeReleaseRequest($projectName, $version)
    {
        return $this->sendRequest('removeReleaseRequest', array('projectName' => $projectName, 'version' => $version));
    }

    /** Выдает новое задание на сборку */
    public function getNextTask($workerName)
    {
        return $this->sendRequest('getBuildTasks', array('worker' => $workerName));
    }

    /** Выдает новое задание на миграцию */
    public function getMigrationTask($workerName)
    {
        return $this->sendRequest('getMigrationTask', array('worker' => $workerName));
    }

    /** Выдает новое задание на USE */
    public function getUseTask($workerName)
    {
        return $this->sendRequest('getUseTasks', array('worker' => $workerName));
    }

    /** Выдает новое задание на KILL */
    public function getKillTask($workerName)
    {
        return $this->sendRequest('getKillTask', array('worker' => $workerName));
    }

    /** Возвращает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable" */
    public function getCurrentStatus($taskId)
    {
        return $this->sendRequest('getCurrentStatus', array('id' => $taskId));
    }

    /** Созвращает список всех проектов, используется сборщиком мусора */
    public function getProjects()
    {
        return $this->sendRequest('getProjects', array());
    }

    /** Возвращает список сборок, которые можно удалить на основании всего списка сборок, которые есть на ms машине */
    public function getProjectBuildsToDelete($allBuilds)
    {
        return $this->sendRequest('getProjectBuildsToDelete', array('builds' => $allBuilds), true);
    }
}
