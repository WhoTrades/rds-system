<?php
namespace RdsSystem\lib;

use Yii;

class ConsoleErrorHandler extends \yii\console\ErrorHandler
{
    /**
     * @param \Exception $exception
     * {@inheritdoc}
     */
    public function logException($exception)
    {
        /** @var $sentry \mito\sentry\Component */
        $sentry = Yii::$app->sentry;
        $sentry->captureException($exception);

        return parent::logException($exception);
    }
}
