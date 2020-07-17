<?php
namespace whotrades\RdsSystem\lib;

use Yii;

/**
 * Class ConsoleErrorHandler
 *
 * @package whotrades\RdsSystem\lib
 *
 * @deprecated
 */
class ConsoleErrorHandler extends \yii\console\ErrorHandler
{
    /**
     * @param \Exception $exception
     * {@inheritdoc}
     */
    public function logException($exception)
    {

        return parent::logException($exception);
    }
}
