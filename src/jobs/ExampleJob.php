<?php

namespace app\jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;

class ExampleJob extends BaseObject implements JobInterface
{
    public $message;

    public function execute($queue)
    {
        \Yii::info("Выполняется задание: " . $this->message, 'queue');
        // Здесь твой код, например запись в базу, отправка письма и т.д.
    }
}
