<?php
namespace whotrades\RdsSystem\lib;

use Yii;

/**
 * Class WebErrorHandler
 *
 * @package whotrades\RdsSystem\lib
 *
 * @deprecated
 */
class WebErrorHandler extends \yii\web\ErrorHandler
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
