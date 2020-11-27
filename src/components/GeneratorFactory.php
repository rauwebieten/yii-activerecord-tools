<?php


namespace rauwebieten\yiiactiverecordtools\components;


use rauwebieten\yiiactiverecordtools\ActiveRecordToolsModule;
use yii\base\NotSupportedException;

class GeneratorFactory
{
    public function createModelGenerator($options)
    {
        $module = ActiveRecordToolsModule::getInstance();
        $conn = \Yii::$app->get($module->db);
        if ($conn->driverName === 'mysql') {
            return new MySqlModelGenerator($options);
        }
        if ($conn->driverName === 'sqlsrv') {
            return new MsSqlModelGenerator($options);
        }
        throw new NotSupportedException("{$conn->driverName} not supported");
    }
}