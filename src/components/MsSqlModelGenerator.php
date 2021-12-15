<?php


namespace rauwebieten\yiiactiverecordtools\components;


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
        $defaultValue = preg_replace('/^\(N?(.+)\)$/', '$1', $defaultValue);

        return $defaultValue !== '' ? $defaultValue : null;
    }
}