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
}