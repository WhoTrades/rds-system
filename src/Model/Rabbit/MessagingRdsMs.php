<?php
/**
 * @author Artem Naumenko
 *
 * Класс, где перечислены все бизнес методы общения между service-rds и service-deploy
 */
namespace RdsSystem\Model\Rabbit;

use RdsSystem\Message;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

final class MessagingRdsMs
{
    const HOST = 'rds-rabbitmq-01.local';
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

    private $stopped = false;

    /** @var AMQPConnection */
    private $connection;

    /** @var AMQPChannel[]*/
    private $channels;

    /**
     * @param \ServiceBase_IDebugLogger $debugLogger
     * @param string                    $env
     */
    public function __construct(\ServiceBase_IDebugLogger $debugLogger, $env = null)
    {
        $env = $env ?: self::ENV_MAIN;
        $this->debugLogger = $debugLogger;
        $this->env = $env;

        $this->connection = new AMQPConnection(self::HOST, self::PORT, self::USER, self::PASS, self::VHOST);
    }

    /**
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * Пытается переконнектиться к rabbitmq
     * @author Artem Naumenko
     */
    public function reconnect()
    {
        $this->debugLogger->error("reconnecting to rabbitmq-server...");
        $this->connection->reconnect();
        $this->debugLogger->error("reconnecting done");
    }

    /**
     * Останавливает вычитку всех событий. Все асинхронные вычитыватели остановятся
     */
    public function stopReceivingMessages()
    {
        $this->stopped = true;

        $this->cancelAll();
    }

    /**
     * @param AMQPChannel|null $channel
     * @param null             $count
     * @param int              $timeout
     */
    public function waitForMessages(AMQPChannel $channel = null, $count = null, $timeout = null)
    {
        $timeout = $timeout ?: 0;
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
                        for ($i = 0; $i < 10; $i++) {
                            $channel->wait(null, true, 0.01);
                            if ($this->stopped) {
                                $this->stopped = false;

                                return;
                            }
                        }
                        if ($count > 0) {
                            $count--;
                            if ($count == 0) {
                                break;
                            }
                        }
                    } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                        if ($this->stopped) {
                            $this->stopped = false;

                            return;
                        }
                        if ($timeout && (microtime(true) - $t > $timeout)) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }

    /**
     * Сообщает RDS об изменении состояния сборки
     *
     * @param Message\TaskStatusChanged $message
     */
    public function sendTaskStatusChanged(Message\TaskStatusChanged $message)
    {
        $this->writeMessage($message);
    }

    /**
     * @param bool         $sync
     * @param callable     $callback
     * @return Message\TaskStatusChanged
     */
    public function readTaskStatusChanged($sync, $callback)
    {
        $this->readMessage(Message\TaskStatusChanged::type(), $callback, $sync);
    }

    /**
     * Сообщает RDS об новых коммитах, которые попали в сборку
     * @param Message\ReleaseRequestBuildPatch $message
     */
    public function sendBuildPatch(Message\ReleaseRequestBuildPatch $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Сообщает RDS об новых коммитах, которые попали в сборку
     * @param bool     $sync
     * @param callable $callback
     */
    public function readBuildPatch($sync, $callback)
    {
        $this->readMessage(Message\ReleaseRequestBuildPatch::type(), $callback, $sync);
    }

    /**
     * Отправление задачи на отправку нового конфига
     * @param string $receiverName
     * @param Message\ProjectConfig $message
     */
    public function sendProjectConfig(string $receiverName, Message\ProjectConfig $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * Сообщает RDS об новых коммитах, которые попали в сборку
     * @param string   $workerName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readProjectConfig($workerName, $sync, $callback)
    {
        $this->readMessage(Message\ProjectConfig::type($workerName), $callback, $sync);
    }

    /**
     * Сообщает RDS список новых pre и post миграций, которые попали в сборку
     * @param Message\ReleaseRequestMigrations $message
     */
    public function sendMigrations(Message\ReleaseRequestMigrations $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Сообщает RDS список новых pre и post миграций, которые попали в сборку
     * @param bool     $sync
     * @param callable $callback
     */
    public function readMigrations($sync, $callback)
    {
        $this->readMessage(Message\ReleaseRequestMigrations::type(), $callback, $sync);
    }

    /**
     * Сообщает RDS о статусе миграций запроса релиза
     * @param bool     $sync
     * @param callable $callback
     */
    public function readMigrationStatus($sync, $callback)
    {
        $this->readMessage(Message\ReleaseRequestMigrationStatus::type(), $callback, $sync);
    }

    /**
     * Сообщает RDS cron конфиг сборки
     * @param Message\ReleaseRequestCronConfig $message
     */
    public function sendCronConfig(Message\ReleaseRequestCronConfig $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Сообщает RDS cron конфиг сборки
     * @param bool     $sync
     * @param callable $callback
     */
    public function readCronConfig($sync, $callback)
    {
        $this->readMessage(Message\ReleaseRequestCronConfig::type(), $callback, $sync);
    }

    /**
     * Сообщает RDS изменении статуса миграций сборки
     * @param Message\ReleaseRequestMigrationStatus $message
     */
    public function sendMigrationStatus(Message\ReleaseRequestMigrationStatus $message)
    {
        $this->writeMessage($message);
    }


    /**
     * Сообщает RDS о том, что не получилось выполнить USE и описание ошибки
     * @param Message\ReleaseRequestUseError $message
     */
    public function sendUseError(Message\ReleaseRequestUseError $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Вычитывает ошибку операции USE
     * @param bool     $sync
     * @param callable $callback
     */
    public function readUseError($sync, $callback)
    {
        $this->readMessage(Message\ReleaseRequestUseError::type(), $callback, $sync);
    }

    /**
     * Сообщает RDS какая версия проекта была на самом деле выложена перед нажатием USE (полезно, в случае когда USE выполнят мимо RDS)
     * @param Message\ReleaseRequestOldVersion $message
     */
    public function sendOldVersion(Message\ReleaseRequestOldVersion $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Вычитывает старую выложенную версию для проекта
     * @param bool     $sync
     * @param callable $callback
     */
    public function readOldVersion($sync, $callback)
    {
        $this->readMessage(Message\ReleaseRequestOldVersion::type(), $callback, $sync);
    }

    /**
     * Сообщает RDS какая версия проекта была выложена
     * @param Message\ReleaseRequestUsedVersion $message
     */
    public function sendUsedVersion(Message\ReleaseRequestUsedVersion $message)
    {
        $this->writeMessage($message);
    }

    /**
     * @param bool     $sync
     * @param callable $callback
     */
    public function readUsedVersion($sync, $callback)
    {
        $this->readMessage(Message\ReleaseRequestUsedVersion::type(), $callback, $sync);
    }

    /**
     * Удаляет сборку из RDS, используется сборщиком мусора
     * @param Message\RemoveReleaseRequest $message
     * @return AMQPChannel
     */
    public function removeReleaseRequest(Message\RemoveReleaseRequest $message)
    {
        return $this->writeMessage($message);
    }

    /**
     * Выдает новое задание на сборку
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function getBuildTask($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\BuildTask::type($receiverName), $callback, $sync);
    }

    /**
     * Выдает новое задание на сборку
     * @param string            $receiverName
     * @param Message\BuildTask $buildTask
     */
    public function sendBuildTask($receiverName, Message\BuildTask $buildTask)
    {
        $this->writeMessage($buildTask, $receiverName);
    }

    /**
     * Выдает новое задание на миграцию
     * @param string                $workerName
     * @param Message\MigrationTask $message
     */
    public function sendMigrationTask($workerName, Message\MigrationTask $message)
    {
        $this->writeMessage($message, $workerName);
    }

    /**
     * Читает новое задание на миграцию
     * @param string   $workerName
     * @param bool     $sync
     * @param callable $callback
     */
    public function getMigrationTask($workerName, $sync, $callback)
    {
        $this->readMessage(Message\MigrationTask::type($workerName), $callback, $sync);
    }

    /**
     * Читает новое задание на USE
     * @param string   $workerName
     * @param bool     $sync
     * @param callable $callback
     */
    public function getUseTask($workerName, $sync, $callback)
    {
        $this->readMessage(Message\UseTask::type($workerName), $callback, $sync);
    }

    /**
     * Выдает новое задание на USE
     * @param string          $receiverName
     * @param Message\UseTask $message
     */
    public function sendUseTask($receiverName, Message\UseTask $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * Выдает новое задание на KILL
     * @param string   $workerName
     * @param bool     $sync
     * @param callable $callback
     */
    public function getKillTask($workerName, $sync, $callback)
    {
        $this->readMessage(Message\KillTask::type($workerName), $callback, $sync);
    }

    /**
     * Выдает новое задание на KILL
     * @param string           $receiverName
     * @param Message\KillTask $message
     */
    public function sendKillTask($receiverName, Message\KillTask $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * Синхронный метод, который возвращает текущий статус запроса на релиз
     * @param int $releaseRequestId
     * @param int $timeout
     *
     * @throws \Exception
     *
     * @return Message\ReleaseRequestCurrentStatusReply
     */
    public function getReleaseRequestStatus($releaseRequestId, $timeout = null)
    {
        $timeout = $timeout ?: 30;
        $request = new Message\ReleaseRequestCurrentStatusRequest($releaseRequestId);

        $this->writeMessage($request);

        $resultFetched = false;
        $result = null;

        $channel = $this->readMessage(
            Message\ReleaseRequestCurrentStatusReply::type(),
            function (Message\ReleaseRequestCurrentStatusReply $message) use (&$result, &$resultFetched, $request) {
                $this->debugLogger->message("Received " . json_encode($message));
                if ($message->uniqueTag != $request->getUniqueTag()) {
                    $this->debugLogger->info("Skip not our packet $message->uniqueTag != {$request->getUniqueTag()}");

                    if (microtime(true) - $message->timeCreated > 5) {
                        $this->debugLogger->error("Dropping too old message " . json_encode($message));
                        $message->accepted();
                    }

                    return;
                }
                $message->accepted();
                $this->debugLogger->info("Got our packet $message->uniqueTag != {$request->getUniqueTag()}");

                $resultFetched = true;
                $result = $message;

            },
            false
        );

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

        return null;
    }

    /**
     * Запрашивает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable"
     * @param Message\ReleaseRequestCurrentStatusRequest $message
     */
    public function sendCurrentStatusRequest(Message\ReleaseRequestCurrentStatusRequest $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Запрашивает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable"
     * @param bool     $sync
     * @param callable $callback
     *
     * @return AMQPChannel
     */
    public function readCurrentStatusRequest($sync, $callback)
    {
        return $this->readMessage(Message\ReleaseRequestCurrentStatusRequest::type(), $callback, $sync);
    }

    /**
     * Возвращает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable"
     * @param Message\ReleaseRequestCurrentStatusReply $message
     */
    public function sendCurrentStatusReply(Message\ReleaseRequestCurrentStatusReply $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Возвращает текущий статус сборки. Используется что бы понять была ли нажата ссылка "make stable"
     * @param callable $callback
     */
    public function readCurrentStatusReplyOnce($callback)
    {
        $messageType = Message\ReleaseRequestCurrentStatusReply::type();
        $channel = $this->readMessage($messageType, $callback, false);
        $this->waitForMessages($channel, 100);

        list($exchangeName, ) = $this->declareAndGetQueueAndExchange($messageType);
        $channel->basic_cancel($exchangeName);
    }

    /**
     * Запрашивает список всех проектов, используется сборщиком мусора
     * @param bool     $sync
     * @param callable $callback
     */
    public function readGetProjectsRequest($sync, $callback)
    {
        $this->readMessage(Message\ProjectsRequest::type(), $callback, $sync);
    }

    /**
     * Запрашивает список всех проектов, используется сборщиком мусора
     * @param Message\ProjectsRequest $message
     */
    public function sendGetProjectsRequest(Message\ProjectsRequest $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Возвращает список всех проектов, используется сборщиком мусора
     * @param bool     $sync
     * @param callable $callback
     */
    public function readGetProjectsReply($sync, $callback)
    {
        $this->readMessage(Message\ProjectsReply::type(), $callback, $sync);
    }

    /**
     * Запрашивает список всех проектов, используется сборщиком мусора
     * @param Message\ProjectsReply $message
     */
    public function sendGetProjectsReply(Message\ProjectsReply $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Считывает все сборки, которые есть на ms машине для проверки какие из них можно удалять
     * @param bool     $sync
     * @param callable $callback
     */
    public function readGetProjectBuildsToDeleteRequest($sync, $callback)
    {
        $this->readMessage(Message\ProjectBuildsToDeleteRequest::type(), $callback, $sync);
    }

    /**
     * Считывает все сборки, которые есть на ms машине для проверки какие из них можно удалять
     * @param bool     $sync
     * @param callable $callback
     */
    public function readRemoveReleaseRequest($sync, $callback)
    {
        $this->readMessage(Message\RemoveReleaseRequest::type(), $callback, $sync);
    }

    /**
     * Отправляет все сборки, которые есть на ms машине для проверки какие из них можно удалять
     * @param Message\ProjectBuildsToDeleteRequest $message
     */
    public function sendGetProjectBuildsToDeleteRequest(Message\ProjectBuildsToDeleteRequest $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Читает список сборок, которые можно удалить на основании всего списка сборок, которые есть на ms машине
     * @param bool     $sync
     * @param callable $callback
     */
    public function readGetProjectBuildsToDeleteReply($sync, $callback)
    {
        $this->readMessage(Message\ProjectBuildsToDeleteReply::type(), $callback, $sync);
    }

    /**
     * Отправляет список сборок, которые можно удалить на основании всего списка сборок, которые есть на ms машине
     * @param Message\ProjectBuildsToDeleteReply $message
     */
    public function sendGetProjectBuildsToDeleteRequestReply(Message\ProjectBuildsToDeleteReply $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Отправляет прогресс выполнения миграции
     * @param Message\HardMigrationProgress $message
     */
    public function sendHardMigrationProgress(Message\HardMigrationProgress $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Вычитывает изменение прогресса выполнения тяжелых миграций
     * @param bool     $sync
     * @param callable $callback
     */
    public function readHardMigrationProgress($sync, $callback)
    {
        $this->readMessage(Message\HardMigrationProgress::type(), $callback, $sync);
    }

    /**
     * Вычитывает изменение статуса выполнения тяжелых миграций
     * @param bool     $sync
     * @param callable $callback
     */
    public function readHardMigrationStatus($sync, $callback)
    {
        $this->readMessage(Message\HardMigrationStatus::type(), $callback, $sync);
    }

    /**
     * Вычитывает новый кусок лога выполнения миграции
     * @param bool     $sync
     * @param callable $callback
     */
    public function readHardMigrationLogChunk($sync, $callback)
    {
        $this->readMessage(Message\HardMigrationLogChunk::type(), $callback, $sync);
    }

    /**
     * Отправляет задачу на выполнение тяжелой миграции
     * @param string   $receiverName
     * @param Message\HardMigrationTask $message
     */
    public function sendHardMigrationTask($receiverName, Message\HardMigrationTask $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * Вычитывает задачи по накатыванию тяжелых миграций
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function getHardMigrationTask($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\HardMigrationTask::type($receiverName), $callback, $sync);
    }

    /**
     * Отправляет изменение статуса тяжелой миграции
     * @param Message\HardMigrationStatus $message
     */
    public function sendHardMigrationStatus(Message\HardMigrationStatus $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Отправляет очередной кусок лога выполнения миграции
     * @param Message\HardMigrationLogChunk $message
     */
    public function sendHardMigrationLogChunk(Message\HardMigrationLogChunk $message)
    {
        $this->writeMessage($message);
    }

    /**
     * @param string $receiverName
     * @param Message\UnixSignal $message
     */
    public function sendUnixSignal($receiverName, Message\UnixSignal $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readUnixSignals($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\UnixSignal::type($receiverName), $callback, $sync);
    }

    /**
     * @param string                    $receiverName
     * @param Message\UnixSignalToGroup $message
     */
    public function sendUnixSignalToGroup($receiverName, Message\UnixSignalToGroup $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * @param bool     $sync
     * @param callable $callback
     */
    public function readUnixSignalsToGroup($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\UnixSignalToGroup::type($receiverName), $callback, $sync);
    }

    /**
     * @param string                        $receiverName
     * @param Message\MaintenanceTool\Start $message
     */
    public function sendMaintenanceToolStart($receiverName, Message\MaintenanceTool\Start $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readMaintenanceToolStart($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\MaintenanceTool\Start::type($receiverName), $callback, $sync);
    }

    /**
     * @param Message\MaintenanceTool\ChangeStatus $message
     */
    public function sendMaintenanceToolChangeStatus(Message\MaintenanceTool\ChangeStatus $message)
    {
        $this->writeMessage($message);
    }

    /**
     * @param bool     $sync
     * @param callable $callback
     */
    public function readMaintenanceToolChangeStatus($sync, $callback)
    {
        $this->readMessage(Message\MaintenanceTool\ChangeStatus::type(), $callback, $sync);
    }

    /**
     * @param Message\MaintenanceTool\LogChunk $message
     */
    public function sendMaintenanceToolLogChunk(Message\MaintenanceTool\LogChunk $message)
    {
        $this->writeMessage($message);
    }

    /**
     * @param bool     $sync
     * @param callable $callback
     */
    public function readMaintenanceToolLogChunk($sync, $callback)
    {
        $this->readMessage(Message\MaintenanceTool\LogChunk::type(), $callback, $sync);
    }

    /**
     * @param Message\PreProd\Down $message
     */
    public function sendPreProdDown(Message\PreProd\Down $message)
    {
        $this->writeMessage($message);
    }

    /**
     * @param bool     $sync
     * @param callable $callback
     */
    public function readPreProdDown($sync, $callback)
    {
        $this->readMessage(Message\PreProd\Down::type(), $callback, $sync);
    }

    /**
     * @param Message\PreProd\Up $message
     */
    public function sendPreProdUp(Message\PreProd\Up $message)
    {
        $this->writeMessage($message);
    }

    /**
     * @param bool     $sync
     * @param callable $callback
     */
    public function readPreProdUp($sync, $callback)
    {
        $this->readMessage(Message\PreProd\Up::type(), $callback, $sync);
    }

    /**
     * @param string             $receiverName
     * @param Message\Merge\Task $message
     */
    public function sendMergeTask($receiverName, Message\Merge\Task $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readMergeTask($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\Merge\Task::type($receiverName), $callback, $sync);
    }

    /**
     * @param string                     $receiverName
     * @param Message\Merge\CreateBranch $message
     */
    public function sendMergeCreateBranch($receiverName, Message\Merge\CreateBranch $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * @param string                   $receiverName
     * @param Message\Merge\TaskResult $message
     */
    public function sendMergeTaskResult($receiverName, Message\Merge\TaskResult $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readMergeTaskResult($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\Merge\TaskResult::type($receiverName), $callback, $sync);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readMergeCreateBranch($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\Merge\CreateBranch::type($receiverName), $callback, $sync);
    }

    /**
     * @param string                     $receiverName
     * @param Message\Merge\DropBranches $message
     */
    public function sendDropBranches($receiverName, Message\Merge\DropBranches $message)
    {
        $this->writeMessage($message, $receiverName);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readDropBranches($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\Merge\DropBranches::type($receiverName), $callback, $sync);
    }

    /**
     * @param Message\Merge\DroppedBranches $message
     */
    public function sendDroppedBranches(Message\Merge\DroppedBranches $message)
    {
        $this->writeMessage($message);
    }

    /**
     * @param bool     $sync
     * @param callable $callback
     */
    public function readDroppedBranches($sync, $callback)
    {
        $this->readMessage(Message\Merge\DroppedBranches::type(), $callback, $sync);
    }

    // Tool maintenance

    /**
     * Синхронный метод, который убивает процессы и возвращает список убитых
     * @param string                $receiverName
     * @param Message\Tool\KillTask $task
     * @param string                $resultType
     * @param int                   $timeout
     *
     * @throws \Exception
     *
     * @return Message\Tool\KillResult
     */
    public function sendToolKillTask($receiverName, Message\Tool\KillTask $task, $resultType, $timeout = null)
    {
        return $this->jsonRpcCall($task, $resultType, $timeout ?: 30, $receiverName);
    }

    /**
     * @param Message\Tool\KillResult $result
     */
    public function sendToolKillResult(Message\Tool\KillResult $result)
    {
        $this->writeMessage($result);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readToolKillTaskRequest($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\Tool\KillTask::type($receiverName), $callback, $sync);
    }

    /**
     * Синхронный метод, который возвращает информацию о работающих процессах
     * @param string                   $receiverName
     * @param Message\Tool\GetInfoTask $task
     * @param string                   $resultType
     * @param int                      $timeout
     *
     * @return Message\Tool\GetInfoResult
     * @throws \Exception
     */
    public function sendToolGetInfoTask($receiverName, Message\Tool\GetInfoTask $task, $resultType, $timeout = null)
    {
        return $this->jsonRpcCall($task, $resultType, $timeout ?: 30, $receiverName);
    }

    /**
     * Синхронный метод, который возвращает последие $linesCount строк лога работы тула
     * @param string                   $receiverName
     * @param Message\Tool\ToolLogTail $task
     * @param string                   $resultType
     * @param int                      $timeout
     *
     * @return array
     * @throws \Exception
     */
    public function sendToolGetToolLogTail($receiverName, Message\Tool\ToolLogTail $task, $resultType, $timeout = null)
    {
        return $this->jsonRpcCall($task, $resultType, $timeout ?: 30, $receiverName);
    }

    /**
     * @param Message\Tool\GetInfoResult $result
     */
    public function sendToolGetInfoResult(Message\Tool\GetInfoResult $result)
    {
        $this->writeMessage($result);
    }

    /**
     * @param Message\Tool\ToolLogTailResult $result
     */
    public function sendToolGetToolLogTailResult(Message\Tool\ToolLogTailResult $result)
    {
        $this->writeMessage($result);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readToolGetInfoTaskRequest($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\Tool\GetInfoTask::type($receiverName), $callback, $sync);
    }

    /**
     * @param string   $receiverName
     * @param bool     $sync
     * @param callable $callback
     */
    public function readToolGetToolLogTail($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\Tool\ToolLogTail::type($receiverName), $callback, $sync);
    }

    private function declareAndGetQueueAndExchange($messageType)
    {
        $exchangeName = $queueName = $this->getExchangeName($messageType);

        return [$exchangeName, $queueName];
    }

    /**
     * @param Message\Base $message
     * @param string       $receiverName
     *
     * @return AMQPChannel
     */
    private function writeMessage(Message\Base $message, $receiverName = null)
    {
        $receiverName = is_null($receiverName) ? '*' : $receiverName;
        $messageType = $message->type($receiverName);
        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($messageType);
        $rabbitMessage = new AMQPMessage(serialize($message));
        $channel = $this->createNewChannel($messageType);

        $this->debugLogger->message("Sending to $messageType");
        $channel->basic_publish($rabbitMessage, $exchangeName, $queueName);

        return $channel;
    }

    /**
     * @param $messageType
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    private function createNewChannel($messageType)
    {
        if (isset($this->channels[(string) $messageType])) {
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
        return $this->env . ":" . $messageType . ":";
    }

    private function readMessage($messageType, $callback, $sync = null)
    {
        if ($sync === null) {
            $sync = true;
        }

        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($messageType);

        $channel = $this->createNewChannel($messageType);
        $this->debugLogger->message("Listening $queueName [$exchangeName]");

        $channel->basic_consume($queueName, $exchangeName, false, false, false, false, function ($message) use ($callback, $channel) {
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
     * @param Message\RpcRequest $request
     * @param                    $replyType
     * @param int                $timeout
     *
     * @return array
     * @throws \Exception
     */
    private function jsonRpcCall(Message\RpcRequest $request, $replyType, $timeout = null, $receiverName = '*')
    {
        $timeout = $timeout ?: 30;
        $this->writeMessage($request, $receiverName);

        $result = [];

        $channel = $this->readMessage($replyType, function (Message\RpcReply $message) use (&$result, &$resultFetched, $request, $timeout) {
            $this->debugLogger->message("Received " . json_encode($message));
            if ($message->uniqueTag != $request->getUniqueTag()) {
                $this->debugLogger->info("Skip not our packet $message->uniqueTag != {$request->getUniqueTag()}");

                if (microtime(true) - $message->timeCreated > $timeout) {
                    $this->debugLogger->error("Dropping too old message " . json_encode($message));
                    $message->accepted();
                }

                return;
            }
            $message->accepted();
            $this->debugLogger->info("Got our packet $message->uniqueTag == {$request->getUniqueTag()}");

            $resultFetched = true;
            $result = $message;

        }, false);

        for (;;) {
            try {
                $channel->wait(null, true, $timeout);
            } catch (\Exception $e) {
                $channel->basic_cancel($this->getExchangeName($replyType));
                throw $e;
            }

            if ($resultFetched) {
                $channel->basic_cancel($this->getExchangeName($replyType));

                return $result;
            }
        }

        return null;
    }

    private function cancelAll()
    {
        foreach ($this->channels as $channel) {
            foreach ($channel->callbacks as $tag => $callback) {
                $this->debugLogger->message("Cancelling $tag");
                $channel->basic_cancel($tag);
            }
        }
    }
}
