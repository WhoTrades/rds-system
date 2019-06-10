<?php
/**
 * @author Artem Naumenko
 *
 * Класс, где перечислены все бизнес методы общения между service-rds и service-deploy
 */
namespace whotrades\RdsSystem\Model\Rabbit;

use PhpAmqpLib\Exception\AMQPIOWaitException;
use whotrades\RdsSystem\Message;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;

class MessagingRdsMs
{
    const EXCHANGE = 'rds_exchange';
    const ENV_MAIN = 'main';
    const MESSAGE_READ_TIMEOUT_MIN = 10*3600;
    const MESSAGE_READ_TIMEOUT_MAX = 20*3600;

    private $env;

    private $stopped = false;

    /** @var AMQPConnection */
    private $connection;

    /** @var AMQPChannel[]*/
    private $channels;

    /**
     * MessagingRdsMs constructor
     */
    public function __construct($host, $port, $user, $pass, $vhost)
    {
        $this->env = static::ENV_MAIN;

        $this->connection = new AMQPConnection($host, $port, $user, $pass, $vhost);
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
        Yii::error("reconnecting to rabbitmq-server...");
        $this->connection->reconnect();
        Yii::error("reconnecting done");
    }

    /**
     * Закрываем соединение
     */
    public function disconnect()
    {
        $this->stopReceivingMessages();
        foreach ($this->channels as $channel) {
            $channel->close();
        }
        $this->connection->safeClose();
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
            while (count($channel->callbacks) && $this->stopped === false) {
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
                            try {
                                $channel->wait(null, true, 0.01);
                            } catch (AMQPIOWaitException $e) {
                                $channel->wait(null, true, 0.2);
                            }

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
     */
    public function readTaskStatusChanged($sync, $callback)
    {
        $this->readMessage(Message\TaskStatusChanged::type(), $callback, $sync);
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
     * Отправление результата раскладки нового конфига
     * @param Message\ProjectConfig $message
     */
    public function sendProjectConfigResult(Message\ProjectConfigResult $message)
    {
        $this->writeMessage($message);
    }

    /**
     * Получение результата раскладки нового конфига
     * @param bool     $sync
     * @param callable $callback
     */
    public function readProjectConfigResult($sync, $callback)
    {
        $this->readMessage(Message\ProjectConfigResult::type(), $callback, $sync);
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
        $this->readMessage(Message\MigrationStatus::type(), $callback, $sync);
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
     * @param Message\MigrationStatus $message
     */
    public function sendMigrationStatus(Message\MigrationStatus $message)
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
     * Отправка задачи к сборщику на удаление собранного пакета
     * @param string $receiverName
     * @param Message\DropReleaseRequest $message
     * @return AMQPChannel
     */
    public function sendDropReleaseRequest($receiverName, Message\DropReleaseRequest $message)
    {
        return $this->writeMessage($message, $receiverName);
    }

    /**
     * @param string $receiverName
     * @param bool $sync
     * @param callable $callback
     */
    public function readDropReleaseRequest($receiverName, $sync, $callback)
    {
        $this->readMessage(Message\DropReleaseRequest::type($receiverName), $callback, $sync);
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
     * Считывает все сборки, которые есть на ms машине для проверки какие из них можно удалять
     * @param bool     $sync
     * @param callable $callback
     */
    public function readRemoveReleaseRequest($sync, $callback)
    {
        $this->readMessage(Message\RemoveReleaseRequest::type(), $callback, $sync);
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
    protected function writeMessage(Message\Base $message, $receiverName = null)
    {
        $receiverName = is_null($receiverName) ? '*' : $receiverName;
        $messageType = $message->type($receiverName);
        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($messageType);
        $rabbitMessage = new AMQPMessage(serialize($message));
        $channel = $this->createNewChannel($messageType);

        Yii::info("Sending to $messageType");
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

    protected function getExchangeName($messageType)
    {
        return $this->env . ":" . $messageType . ":";
    }

    protected function readMessage($messageType, $callback, $sync = null)
    {
        if ($sync === null) {
            $sync = true;
        }

        list($exchangeName, $queueName) = $this->declareAndGetQueueAndExchange($messageType);
        $channel = $this->createNewChannel($messageType);
        Yii::info("Listening $queueName [$exchangeName]");

        $channel->basic_consume($queueName, $exchangeName, false, false, false, false, function ($message) use ($callback, $channel) {
            $reply = unserialize($message->body);
            $reply->deliveryTag = $message->delivery_info['delivery_tag'];
            $reply->channel = $channel;
            Yii::info("[x] Message received {$reply->deliveryTag}");

            $callback($reply);
        });

        if ($sync) {
            try {
                $this->waitForMessages($channel, null, $timeOut = rand(self::MESSAGE_READ_TIMEOUT_MIN, self::MESSAGE_READ_TIMEOUT_MAX));
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                Yii::info("[x] Reached timeout {$timeOut}");
            }
        }

        return $channel;
    }



    private function cancelAll()
    {
        foreach ($this->channels as $channel) {
            foreach ($channel->callbacks as $tag => $callback) {
                Yii::info("Cancelling $tag");
                $channel->basic_cancel($tag);
            }
        }
    }
}
