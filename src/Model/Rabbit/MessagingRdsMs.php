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

    /** @var AMQPChannel[]*/
    private $channels;

    public function __construct(\ServiceBase_IDebugLogger $debugLogger, $env = self::ENV_MAIN)
    {
        $this->debugLogger = $debugLogger;
        $this->env = $env;

        $this->connection = new AMQPConnection(self::HOST, self::PORT, self::USER, self::PASS, self::VHOST);
    }

    private function declareAndGetQueueAndExchange($messageType)
    {
        $exchangeName = $queueName = $this->getExchangeName($messageType);
        return [$exchangeName, $queueName];
    }

    private function writeMessage(Message\Base $message, $receiverName = '*')
    {
        $messageType = $message->type($receiverName);
        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($messageType);
        $rabbitMessage = new AMQPMessage(serialize($message));
        $channel = $this->createNewChannel($messageType);
        $channel->basic_publish($rabbitMessage, $exchangeName, $queueName);

        return $channel;
    }

    /**
     * @param $messageType
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    private function createNewChannel($messageType)
    {
        if (isset($this->channels[(string)$messageType])) {
            return $this->channels[$messageType];
        }

        $channel = $this->connection->channel();
        $exchangeName = $queueName = $this->getExchangeName($messageType);
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->exchange_declare($exchangeName, 'direct', false, true, false);
        $channel->queue_bind($queueName, $exchangeName, $queueName);

        return $this->channels[$messageType] = $channel;
    }

    private function getExchangeName($messageType)
    {
        return $this->env.":".$messageType.":";
    }

    private function readMessage($messageType, $callback, $sync = true)
    {
        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($messageType);

        $channel = $this->createNewChannel($messageType);

        $channel->basic_consume($queueName, $exchangeName, false, false, false, false, function($message) use ($callback, $channel) {
            $reply = unserialize($message->body);
            $reply->deliveryTag = $message->delivery_info['delivery_tag'];
            $reply->channel = $channel;
            $this->debugLogger->message("[x] Message received {$reply->deliveryTag}");

            $callback($reply);
        });

        if ($sync) {
            $this->waitForMessages($channel);
        }

        return $channel;
    }

    /**
     * @param AMQPChannel $channel. Ждет сообщений из канала. Если канал не передан - ждет сообщения изо всех инициированных каталов
     * @param null $count Максимальное количество сообщений, которые нужно получить
     */
    public function waitForMessages(AMQPChannel $channel = null, $count = null, $timeout = 0)
    {
        if ($channel) {
            while (count($channel->callbacks)) {
                $channel->wait(null, true, $timeout);
                if ($count > 0) {
                    $count--;
                    if ($count == 0) {
                        break;
                    }
                }
            }
        } else {
            $t = microtime(true);
            for (;;) {
                foreach ($this->channels as $key => $channel) {
                    try {
                        for (;;){
                            $channel->wait(null, true, 0.1);
                        }
                        if ($count > 0) {
                            $count--;
                            if ($count == 0) {
                                break;
                            }
                        }
                    } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                        if ($timeout && (microtime(true) - $t > $timeout)) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }

    /** Сообщает RDS об изменении состояния сборки */
    public function sendTaskStatusChanged(Message\TaskStatusChanged $message)
    {
        $this->writeMessage($message);
    }

    /** @return Message\TaskStatusChanged */
    public function readTaskStatusChanged($sync = true, $callback)
    {
        $this->readMessage(Message\TaskStatusChanged::type(), $callback, $sync);
    }

    /** Сообщает RDS об новых коммитах, которые попали в сборку */
    public function sendBuildPatch(Message\ReleaseRequestBuildPatch $message)
    {
        $this->writeMessage($message);
    }

    /** Сообщает RDS об новых коммитах, которые попали в сборку */
    public function readBuildPatch($sync = true, $callback)
    {
        $this->readMessage(Message\ReleaseRequestBuildPatch::type(), $callback, $sync);
    }

    /** Сообщает RDS список новых pre и post миграций, которые попали в сборку */
    public function sendMigrations(Message\ReleaseRequestMigrations $message)
    {
        $this->writeMessage($message);
    }

    /** Сообщает RDS список новых pre и post миграций, которые попали в сборку */
    public function readMigrations($sync = true, $callback)
    {
        $this->readMessage(Message\ReleaseRequestMigrations::type(), $callback, $sync);
    }

    /** Сообщает RDS о статусе миграций запроса релиза */
    public function readMigrationStatus($sync = true, $callback)
    {
        $this->readMessage(Message\ReleaseRequestMigrationStatus::type(), $callback, $sync);
    }

    /** Сообщает RDS cron конфиг сборки */
    public function sendCronConfig(Message\ReleaseRequestCronConfig $message)
    {
        $this->writeMessage($message);
    }

    /** Сообщает RDS cron конфиг сборки */
    public function readCronConfig($sync = true, $callback)
    {
        $this->readMessage(Message\ReleaseRequestCronConfig::type(), $callback, $sync);
    }

    /** Сообщает RDS изменении статуса миграций сборки */
    public function sendMigrationStatus(Message\ReleaseRequestMigrationStatus $message)
    {
        $this->writeMessage($message);
    }

    /** Сообщает RDS о том, что не получилось выполнить USE и описание ошибки */
    public function sendUseError(Message\ReleaseRequestUseError $message)
    {
        $this->writeMessage($message);
    }

    public function readUseError($sync = true, $callback)
    {
        $this->readMessage(Message\ReleaseRequestUseError::type(), $callback, $sync);
    }

    /** Сообщает RDS какая версия проекта была на самом деле выложена перед нажатием USE (полезно, в случае когда USE выполнят мимо RDS) */
    public function sendOldVersion(Message\ReleaseRequestOldVersion $message)
    {
        $this->writeMessage($message);
    }

    public function readOldVersion($sync = true, $callback)
    {
        $this->readMessage(Message\ReleaseRequestOldVersion::type(), $callback, $sync);
    }

    /** Сообщает RDS какая версия проекта была выложена */
    public function sendUsedVersion(Message\ReleaseRequestUsedVersion $message)
    {
        $this->writeMessage($message);
    }

    public function readUsedVersion($sync = true, $callback)
    {
        $this->readMessage(Message\ReleaseRequestUsedVersion::type(), $callback, $sync);
    }

    /** Удаляет сборку из RDS, используется сборщиком мусора */
    public function removeReleaseRequest(Message\RemoveReleaseRequest $message)
    {
        return $this->writeMessage($message);
    }

    /** Выдает новое задание на сборку */
    public function getBuildTask($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\BuildTask::type($receiverName), $callback, $sync);
    }

    /** Выдает новое задание на сборку */
    public function sendBuildTask($receiverName, Message\BuildTask $buildTask)
    {
        $this->writeMessage($buildTask, $receiverName);
    }

    /** Выдает новое задание на миграцию */
    public function sendMigrationTask(Message\MigrationTask $message)
    {
        $this->writeMessage($message);
    }

    /** Читает новое задание на миграцию */
    public function getMigrationTask($sync, $callback)
    {
        $this->readMessage(Message\MigrationTask::type(), $callback, $sync);
    }

    /** Читает новое задание на USE */
    public function getUseTask($workerName, $sync, $callback)
    {
        $this->readMessage(Message\UseTask::type($workerName), $callback, $sync);
    }

    /** Выдает новое задание на USE */
    public function sendUseTask($receiverName, Message\UseTask $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /** Выдает новое задание на KILL */
    public function getKillTask($workerName, $sync, $callback)
    {
        $this->readMessage(Message\KillTask::type($workerName), $callback, $sync);
    }

    /** Выдает новое задание на KILL */
    public function sendKillTask($receiverName, Message\KillTask $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * Синхронный метод, который возвращает текущий статус запроса на релиз
     * @param $releaseRequestId
     * @return Message\ReleaseRequestCurrentStatusReply
     */
    public function getReleaseRequestStatus($releaseRequestId, $timeout = 30)
    {
        $request = new Message\ReleaseRequestCurrentStatusRequest($releaseRequestId);

        $this->writeMessage($request);

        $resultFetched = false;
        $result = null;

        $channel = $this->readMessage(Message\ReleaseRequestCurrentStatusReply::type(), function($message) use (&$result, &$resultFetched, $request) {
            $this->debugLogger->message("Received ".json_encode($message));
            if ($message->uniqueTag != $request->getUniqueTag()) {
                $this->debugLogger->info("Skip not our packet $message->uniqueTag != {$request->getUniqueTag()}");

                if (microtime(true) - $message->timeCreated > 5) {
                    $this->debugLogger->error("Dropping too old message ".json_encode($message));
                    $message->accepted();
                }

                return;
            }
            $message->accepted();
            $this->debugLogger->info("Got our packet $message->uniqueTag != {$request->getUniqueTag()}");

            $resultFetched = true;
            $result = $message;

        }, false);

        for (;;) {
            try {
                $channel->wait(null, true, $timeout);
            } catch (\Exception $e) {
                $channel->basic_cancel($this->getExchangeName(Message\ReleaseRequestCurrentStatusReply::type()));
                throw $e;
            }

            if ($resultFetched) {
                $channel->basic_cancel($this->getExchangeName(Message\ReleaseRequestCurrentStatusReply::type()));
                return $result;
            }
        }
    }

    /** Запрашивает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable" */
    public function sendCurrentStatusRequest(Message\ReleaseRequestCurrentStatusRequest $message)
    {
        $this->writeMessage($message);
    }

    /** Запрашивает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable" */
    public function readCurrentStatusRequest($sync, $callback)
    {
        return $this->readMessage(Message\ReleaseRequestCurrentStatusRequest::type(), $callback, $sync);
    }

    /** Возвращает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable" */
    public function sendCurrentStatusReply(Message\ReleaseRequestCurrentStatusReply $message)
    {
        $this->writeMessage($message);
    }

    /** Возвращает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable" */
    public function readCurrentStatusReplyOnce($callback)
    {
        $messageType = Message\ReleaseRequestCurrentStatusReply::type();
        $channel = $this->readMessage($messageType, $callback, false);
        $this->waitForMessages($channel, 100);

        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($messageType);
        $channel->basic_cancel($exchangeName);
    }

    /** Запрашивает список всех проектов, используется сборщиком мусора */
    public function readGetProjectsRequest($sync, $callback)
    {
        $this->readMessage(Message\ProjectsRequest::type(), $callback, $sync);
    }

    /** Запрашивает список всех проектов, используется сборщиком мусора */
    public function sendGetProjectsRequest(Message\ProjectsRequest $message)
    {
        $this->writeMessage($message);
    }

    /** Созвращает список всех проектов, используется сборщиком мусора */
    public function readGetProjectsReply($sync, $callback)
    {
        $this->readMessage(Message\ProjectsReply::type(), $callback, $sync);
    }

    /** Запрашивает список всех проектов, используется сборщиком мусора */
    public function sendGetProjectsReply(Message\ProjectsReply $message)
    {
        $this->writeMessage($message);
    }

    /** Считывает все сборки, которые есть на ms машине для проверки какие из них можно удалять */
    public function readGetProjectBuildsToDeleteRequest($sync, $callback)
    {
        $this->readMessage(Message\ProjectBuildsToDeleteRequest::type(), $callback, $sync);
    }

    /** Считывает все сборки, которые есть на ms машине для проверки какие из них можно удалять */
    public function readRemoveReleaseRequest($sync, $callback)
    {
        $this->readMessage(Message\RemoveReleaseRequest::type(), $callback, $sync);
    }

    /** Отправляет все сборки, которые есть на ms машине для проверки какие из них можно удалять */
    public function sendGetProjectBuildsToDeleteRequest(Message\ProjectBuildsToDeleteRequest $message)
    {
        $this->writeMessage($message);
    }

    /** Читает список сборок, которые можно удалить на основании всего списка сборок, которые есть на ms машине */
    public function readGetProjectBuildsToDeleteReply($sync, $callback)
    {
        $this->readMessage(Message\ProjectBuildsToDeleteReply::type(), $callback, $sync);
    }

    /** Отправляет список сборок, которые можно удалить на основании всего списка сборок, которые есть на ms машине */
    public function sendGetProjectBuildsToDeleteRequestReply(Message\ProjectBuildsToDeleteReply $message)
    {
        $this->writeMessage($message);
    }

    /** Возвращает список сборок, которые можно удалить на основании всего списка сборок, которые есть на ms машине */
    public function getProjectBuildsToDelete($allBuilds)
    {
        return $this->sendRequest('getProjectBuildsToDelete', array('builds' => $allBuilds), true);
    }
}
