<?php


namespace rauwebieten\yiiactiverecordtools\components;


use yii\base\NotSupportedException;

class GeneratorFactory
{
    public function createModelGenerator($options)
    {
        $conn = \Yii::$app->get($options['db']);
        if ($conn->driverName === 'mysql') {
            return new MySqlModelGenerator($options);
        }
        if ($conn->driverName === 'sqlsrv') {
            return new MsSqlModelGenerator($options);
        }
        throw new NotSupportedException("{$conn->driverName} not supported");
    }
}