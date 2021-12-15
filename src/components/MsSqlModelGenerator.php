<?php


namespace rauwebieten\yiiactiverecordtools\components;


use yii\db\Expression;

class MsSqlModelGenerator extends AbstractModelGenerator
{
    public function getTableNames()
    {
        $sql = "
            SELECT SCHEMA_NAME(schema_id) AS SchemaName, name AS TableName, SCHEMA_NAME(schema_id) + '.' + name as FullName
            FROM sys.tables 
            ORDER BY SchemaName, TableName
        ";
        $command = $this->db_conn->createCommand($sql);
        $result = $command->queryAll(2);

        $result = array_column($result,'FullName');

        return $result;
    }

    public function getDefaultValue($tableSchema, $tableName, $columnName)
    {
        $sql = "
            SELECT COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '$tableSchema' AND TABLE_NAME = '$tableName' AND COLUMN_NAME = '$columnName';
        ";
        $command = $this->db_conn->createCommand($sql);

        $defaultValue = $command->queryScalar();

        if ($defaultValue === null) {
            return null;
        }
        if ($defaultValue === false) {
            return null;
        }

        // remove first and last char
        $defaultValue = substr($defaultValue, 1, -1);

        if (preg_match('/^\d+$/', $defaultValue)) {
            return preg_replace('/^(\d+)$/','$1', $defaultValue);
        }
        if (preg_match('/^\(\d\)+$/', $defaultValue)) {
            return preg_replace('/^\((\d+)\)$/','$1', $defaultValue);
        }
        if (preg_match("/^N?'.+'$/", $defaultValue)) {
            return preg_replace("/^'(.+)'$/",'$1', $defaultValue);
        }
        if ($defaultValue === 'NULL') {
            return null;
        }
        $expression = preg_replace('/^(.+)$/','$1', $defaultValue);
        return new Expression($expression);
    }
}