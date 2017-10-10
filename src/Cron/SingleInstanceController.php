<?php
namespace whotrades\RdsSystem\Cron;

use Yii;
use yii\console\Controller;
use yii\mutex\Mutex;

abstract class SingleInstanceController extends Controller
{
    const CODE_DUPLICATE_INSTANCE = 13;

    /**
     * @var string сборка, в которой процесс работает
     */
    public $sys__package;

    /**
     * @var string уникальный идентификатор процесса
     */
    public $sys__key;

    /**
     * @param string $actionID
     *
     * @return array
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['sys__key', 'sys__package']);
    }

    /**
     * @param string $id
     * @param array $params
     */
    public function runAction($id, $params = [])
    {
        $instanceId = $this->getInstanceIdentifier($id, $params);

        /** @var $mutex Mutex */
        $mutex = Yii::$app->commandInstanceMutex;

        if ($mutex->acquire($instanceId)) {
            return parent::runAction($id, $params);
        } else {
            $this->stderr("Can't run duplicate instance of cronjobs $instanceId");

            return self::CODE_DUPLICATE_INSTANCE;
        }
    }

    /**
     * Идентификатор, по которому уникализируются экземпляры команд
     * @param string $actionId
     * @param array $params
     * @return string
     */
    public function getInstanceIdentifier($actionId, $params)
    {
        $id = $this->getUniqueId() . "-" . $actionId . json_encode($params);
        Yii::trace("Using command mutex identifier: '$id'");

        return $id;
    }
}
