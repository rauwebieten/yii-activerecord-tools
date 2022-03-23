<?php


namespace rauwebieten\yiiactiverecordtools\components;


use yii\db\Expression;

class MsSqlModelGenerator extends AbstractModelGenerator
{
    public function getForeignKeys($schemaName, $tableName)
    {
        $sql = "
            SELECT 
            C.TABLE_CATALOG [PKTABLE_QUALIFIER], 
            C.TABLE_SCHEMA [PKTABLE_OWNER], 
            C.TABLE_NAME [PKTABLE_NAME], 
            KCU.COLUMN_NAME [PKCOLUMN_NAME], 
            C2.TABLE_CATALOG [FKTABLE_QUALIFIER], 
            C2.TABLE_SCHEMA [FKTABLE_OWNER], 
            C2.TABLE_NAME [FKTABLE_NAME], 
            KCU2.COLUMN_NAME [FKCOLUMN_NAME], 
            C.CONSTRAINT_NAME [FK_NAME], 
            C2.CONSTRAINT_NAME [PK_NAME]
            FROM   INFORMATION_SCHEMA.TABLE_CONSTRAINTS C 
            INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU 
            ON C.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA 
            AND C.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME 
            INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS RC 
            ON C.CONSTRAINT_SCHEMA = RC.CONSTRAINT_SCHEMA 
            AND C.CONSTRAINT_NAME = RC.CONSTRAINT_NAME 
            INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS C2 
            ON RC.UNIQUE_CONSTRAINT_SCHEMA = C2.CONSTRAINT_SCHEMA 
            AND RC.UNIQUE_CONSTRAINT_NAME = C2.CONSTRAINT_NAME 
            INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE KCU2 
            ON C2.CONSTRAINT_SCHEMA = KCU2.CONSTRAINT_SCHEMA 
            AND C2.CONSTRAINT_NAME = KCU2.CONSTRAINT_NAME 
            AND KCU.ORDINAL_POSITION = KCU2.ORDINAL_POSITION 
            WHERE  C.CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND C.TABLE_SCHEMA = '$schemaName'
            AND C.TABLE_NAME = '$tableName'

        ";
        $command = $this->db_conn->createCommand($sql);
        $result = $command->queryAll(2);

        $array = [];
        foreach ($result as $item) {
            $fk = [];
            $fk[0] = $item['FKTABLE_OWNER'] . '.' . $item['FKTABLE_NAME'];
            $fk[$item['PKCOLUMN_NAME']] = $item['FKCOLUMN_NAME'];
            $array[$item['FK_NAME']] = $fk;
        }

        return $array;
    }

//    public function getTableNames($schemaName)
//    {
//        $sql = "
//            SELECT SCHEMA_NAME(schema_id) AS SchemaName, name AS TableName, SCHEMA_NAME(schema_id) + '.' + name as FullName
//            FROM sys.tables
//            WHERE SCHEMA_NAME(schema_id) = '$schemaName'
//            ORDER BY SchemaName, TableName
//        ";
//        $command = $this->db_conn->createCommand($sql);
//        $result = $command->queryAll(2);
//
//        $result = array_column($result, 'FullName');
//
//        return $result;
//    }

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
            return preg_replace('/^(\d+)$/', '$1', $defaultValue);
        }
        if (preg_match('/^\(\d\)+$/', $defaultValue)) {
            return preg_replace('/^\((\d+)\)$/', '$1', $defaultValue);
        }
        if (preg_match("/^N?'.+'$/", $defaultValue)) {
            return preg_replace("/^'(.+)'$/", '$1', $defaultValue);
        }
        if ($defaultValue === 'NULL') {
            return null;
        }
        $expression = preg_replace('/^(.+)$/', '$1', $defaultValue);
        return new Expression($expression);
    }
}