<?php


namespace rauwebieten\yiiactiverecordtools\components;


class MySqlModelGenerator extends AbstractModelGenerator
{
//    protected function getForeignKeys($tableName)
//    {
//        $dbName = $this->getDsnAttribute('dbname', $this->db_conn->dsn);
//
//        $sql = "
//            select
//                CONSTRAINT_NAME as name,
//                TABLE_NAME as table_name,
//                COLUMN_NAME AS column_name,
//                REFERENCED_TABLE_NAME AS referenced_table_name,
//                REFERENCED_COLUMN_NAME AS referenced_column_name
//            from INFORMATION_SCHEMA.KEY_COLUMN_USAGE
//            where TABLE_SCHEMA = :schemaName
//            and TABLE_NAME = :tableName
//        ";
//
//        $command = $this->db_conn->createCommand($sql, [
//            'schemaName' => $dbName,
//            'tableName' => $tableName,
//        ]);
//        $result = $command->queryAll(2);
//
//        return $result;
//    }
}