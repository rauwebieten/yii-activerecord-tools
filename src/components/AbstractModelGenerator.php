<?php


namespace rauwebieten\yiiactiverecordtools\components;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use rauwebieten\yiiactiverecordtools\ActiveRecordToolsModule;
use yii\base\Component;
use yii\console\Controller;
use yii\db\ActiveQuery;
use yii\db\ColumnSchema;
use yii\db\Connection;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

abstract class AbstractModelGenerator extends Component
{
    public $db;
    public $baseNamespace;
    public $baseModelClass;
    public $baseQueryClass;

    /** @var Controller */
    public $console;

    /** @var Connection */
    protected $db_conn;

    protected $map;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        $module = ActiveRecordToolsModule::getInstance();

        $this->db = $module->db;
        $this->baseNamespace = $module->namespace;
        $this->baseModelClass = $module->baseModelClass;
        $this->baseQueryClass = $module->baseQueryClass;

        $this->db_conn = \Yii::$app->get($this->db);
    }

    public function run()
    {
        $this->createMap();
        $this->makeAbstractModelClasses();
        $this->makeConcreteModelClasses();
        $this->makeAbstractQueryClasses();
        $this->makeConcreteQueryClasses();
        $this->addDbMethod();
        $this->addTableNameMethod();
        $this->addFindMethod();
        $this->addColumnsAsProperties();
        $this->addRulesMethod();
        $this->addAllMethodToQueryClass();
        $this->addOneMethodToQueryClass();
        $this->addForeignKeys();
        $this->writeAllFiles();
    }

    protected function createMap()
    {
        $schema = $this->db_conn->getSchema();
        $this->map = [];

        $tableNames = $this->getTableNames();

        foreach ($tableNames as $tableName) {
            $tableSchema = $schema->getTableSchema($tableName);
            $this->map[$tableSchema->fullName] = [];
        }
    }

    public function getTableNames()
    {
        $schema = $this->db_conn->getSchema();
        return $schema->tableNames;
    }

    public function getDefaultValue($tableSchema, $tableName, $columnName)
    {
        $schema = $this->db_conn->getSchema();
        $tableSchema = $schema->getTableSchema($tableName);
        $columnSchema = $tableSchema->getColumn($columnName);

        return $columnSchema->defaultValue;
    }

    protected function makeAbstractModelClasses()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Creating abstract model classes: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            $file = new PhpFile();
            $file->setStrictTypes();
            $file->addComment('This file is generated by ActiveRecordClassGenerator.');
            $file->addComment('Do not make changes to this file.');
            $file->addComment('Instead, modify the concrete model class.');

            $namespaceText = $this->baseNamespace . '\\' . $this->db . ($tableSchema->schemaName ? '\\' . $tableSchema->schemaName : '') . '\base';
            $namespace = $file->addNamespace($namespaceText);

            $class = $namespace->addClass(Helper::arrayToClassName([$tableSchema->name]));
            $class->setAbstract();
            $class->addComment("Class " . $class->getName());
            $class->addComment("@package " . $namespace->getName());
            $class->setExtends($this->baseModelClass);

            $this->map[$tableSchema->fullName]['abstractModel'] = [];
            $this->map[$tableSchema->fullName]['abstractModel']['file'] = $file;
            $this->map[$tableSchema->fullName]['abstractModel']['namespace'] = $namespace;
            $this->map[$tableSchema->fullName]['abstractModel']['class'] = $class;

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function makeConcreteModelClasses()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Creating concrete model classes: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            $file = new PhpFile();
            $file->setStrictTypes();
            $file->addComment('This file is generated by ActiveRecordClassGenerator.');
            $file->addComment('Modify this class to your wishes.');

            $namespace = $file->addNamespace($this->baseNamespace . '\\' . $this->db . ($tableSchema->schemaName ? '\\' . $tableSchema->schemaName : ''));

            $class = $namespace->addClass(Helper::arrayToClassName([$tableSchema->name]));
            $class->addComment("Class " . $class->getName());
            $class->addComment("@package " . $namespace->getName());
            $class->setExtends(
                Helper::canonical(
                    $this->map[$tableSchema->fullName]['abstractModel']['class']
                )
            );

            $this->map[$tableSchema->fullName]['concreteModel']['file'] = $file;
            $this->map[$tableSchema->fullName]['concreteModel']['namespace'] = $namespace;
            $this->map[$tableSchema->fullName]['concreteModel']['class'] = $class;

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function makeAbstractQueryClasses()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Creating abstract query classes: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            $file = new PhpFile();
            $file->setStrictTypes();
            $file->addComment('This file is generated by ActiveRecordClassGenerator.');
            $file->addComment('Do not make changes to this file.');
            $file->addComment('Instead, modify the concrete query class.');

            $namespace = $file->addNamespace($this->baseNamespace . '\\' . $this->db . ($tableSchema->schemaName ? '\\' . $tableSchema->schemaName : '') . '\base');

            $class = $namespace->addClass(Helper::arrayToClassName([$tableSchema->name, 'query']));
            $class->setAbstract();
            $class->addComment("Class " . $class->getName());
            $class->addComment("@package " . $namespace->getName());
            $class->setExtends($this->baseQueryClass);

            $this->map[$tableSchema->fullName]['abstractQuery']['file'] = $file;
            $this->map[$tableSchema->fullName]['abstractQuery']['namespace'] = $namespace;
            $this->map[$tableSchema->fullName]['abstractQuery']['class'] = $class;

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function makeConcreteQueryClasses()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Creating concrete query classes: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            $file = new PhpFile();
            $file->setStrictTypes();
            $file->addComment('This file is generated by ActiveRecordClassGenerator.');
            $file->addComment('Modify this class to your wishes.');

            $namespace = $file->addNamespace($this->baseNamespace . '\\' . $this->db . ($tableSchema->schemaName ? '\\' . $tableSchema->schemaName : ''));

            $class = $namespace->addClass(Helper::arrayToClassName([$tableSchema->name, 'query']));
            $class->addComment("Class " . $class->getName());
            $class->addComment("@package " . $namespace->getName());
            $class->setExtends(
                Helper::canonical(
                    $this->map[$tableSchema->fullName]['abstractQuery']['class']
                )
            );

            $this->map[$tableSchema->fullName]['concreteQuery']['file'] = $file;
            $this->map[$tableSchema->fullName]['concreteQuery']['namespace'] = $namespace;
            $this->map[$tableSchema->fullName]['concreteQuery']['class'] = $class;

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function addDbMethod()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Adding db method: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            /** @var Method $method */
            $method = $this->map[$tableSchema->fullName]['abstractModel']['class']->addMethod('getDb');
            $method->setStatic();
            $method->addBody("return \Yii::\$app->get('{$this->db}');");
            $method->addComment("Returns the database connection used by this active record class.");

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function addTableNameMethod()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Adding table name method: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            /** @var Method $method */
            $method = $this->map[$tableSchema->fullName]['abstractModel']['class']->addMethod('tableName');
            $method->setStatic();
            $method->addBody("return '{$tableSchema->fullName}';");
            $method->setReturnType('string');
            $method->addComment("@return string the name of the table associated with this ActiveRecord class.");

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function addFindMethod()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Adding find method: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            $concreteQueryClass = $this->map[$tableSchema->fullName]['concreteQuery']['class'];
            $concreteQueryCanonical = Helper::canonical($concreteQueryClass);

            /** @var Method $method */
            $method = $this->map[$tableSchema->fullName]['abstractModel']['class']->addMethod('find');
            $method->setStatic();
            $method->addBody("return new {$concreteQueryCanonical}(get_called_class());");
            $method->setReturnType($concreteQueryCanonical);
            $method->addComment("Returns a $tableName query instance");
            $method->addComment("@return {$concreteQueryCanonical}");

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function addColumnsAsProperties()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Adding columns as properties: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            foreach ($tableSchema->getColumnNames() as $columnName) {
                $columnSchema = $tableSchema->getColumn($columnName);
                $type = $columnSchema->phpType;

                $description = [];
                $description[] = $columnSchema->dbType;
                if ($columnSchema->isPrimaryKey) $description[] = "primary-key";
                if ($columnSchema->autoIncrement) $description[] = "auto-increment";
                $description[] = $columnSchema->allowNull ? "null" : "not-null";
                $description = implode(", ", $description);

                $this->map[$tableSchema->fullName]['abstractModel']['class']->addComment("@property $type \${$columnName} This property represents the $columnName column: $description");
            }

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function addRulesMethod()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Adding rules method: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);
            $uniqueIndexes = $schema->findUniqueIndexes($tableSchema);
            $foreignKeys = $tableSchema->foreignKeys;

            $rules = [];

            foreach ($tableSchema->getColumnNames() as $columnName) {

                $columnSchema = $tableSchema->getColumn($columnName);

                if ($columnSchema instanceof \yii\db\mssql\ColumnSchema && $columnSchema->isComputed) {
                    continue;
                }

                if ($columnName === 'msrepl_tran_version') {
                    continue;
                }

                switch ($columnSchema->type) {
                    case 'boolean':
                        $rules[] = "['$columnName', 'boolean', 'trueValue' => 1, 'falseValue' => 0, 'strict' => false]";
                        break;
                    case 'string':
                        $maxlength = $columnSchema->size;
                        $rules[] = "['$columnName', '$columnSchema->phpType', 'length' => [0,$maxlength]]";
                        break;
                    default:
                        $rules[] = "['$columnName', '$columnSchema->phpType']";
                }

                $defaultValue = $this->getDefaultValue($tableSchema->schemaName, $tableName, $columnName);
                if ($defaultValue !== null) {
                    $rules[] = "['$columnName', 'default', 'value' => $defaultValue]";
                }

                if (!$columnSchema->allowNull && !$columnSchema->autoIncrement) {
                    $rules[] = "['$columnName', 'required']";
                }

                if ($columnName === 'email') {
                    $rules[] = "['$columnName', 'email']";
                }
            }

            foreach ($uniqueIndexes as $indexName => $columns) {
                if (count($columns) == 1) {
                    $rules[] = "['{$columns[0]}', 'unique']";
                } else {
                    $cols = Helper::var_export($columns);
                    $rules[] = "[$cols, 'unique', 'targetAttribute' => $cols]";
                }
            }

            foreach ($foreignKeys as $foreignKey) {
                $pkTableName = $foreignKey[0];
                unset($foreignKey[0]);

                if (!isset($this->map[$pkTableName])) {
                    continue;
                }

                $fkColumn = array_key_first($foreignKey);
                $pkColumn = $foreignKey[$fkColumn];

                /** @var ClassType $pkConcreteModelClass */
                $pkConcreteModelClass = $this->map[$pkTableName]['concreteModel']['class'];
                $pkConcreteModelClassCanonical = Helper::canonical($pkConcreteModelClass);

                $rules[] = "['$fkColumn', 'exist', 'targetClass' => {$pkConcreteModelClassCanonical}::class, 'targetAttribute' => ['$fkColumn' => '$pkColumn'] ]";
            }

            $rules = array_map(function (string $v) {
                return "    $v,";
            }, $rules);
            $rulesAsCode = "[\n" . implode("\n", $rules) . "\n]";

            /** @var Method $method */
            $method = $this->map[$tableSchema->fullName]['abstractModel']['class']->addMethod('rules');
            $method->setReturnType('array');
            $method->addComment("@return array");
            $method->addBody("return $rulesAsCode;");

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

    protected function addAllMethodToQueryClass()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Abstract query class: adding all method: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            $concreteModelClass = $this->map[$tableSchema->fullName]['concreteModel']['class'];
            $concreteModelCanonical = Helper::canonical($concreteModelClass);

            /** @var Method $method */
            $method = $this->map[$tableSchema->fullName]['abstractQuery']['class']->addMethod('all');
            $method->addParameter('db', null);
            $method->addBody("return parent::all(\$db);");
            $method->addComment("Fetches all results");
            $method->addComment('@param null $db');
            $method->addComment("@return array|null|{$concreteModelCanonical}[]");

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function addOneMethodToQueryClass()
    {
        $schema = $this->db_conn->getSchema();

        Console::startProgress(0, count($this->map), 'Abstract query class: adding one method: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $item) {
            $tableSchema = $schema->getTableSchema($tableName);

            /** @var ClassType $abstractQueryClass */
            $abstractQueryClass = $this->map[$tableSchema->fullName]['abstractQuery']['class'];

            $concreteModelClass = $this->map[$tableSchema->fullName]['concreteModel']['class'];
            $concreteModelClassCanonical = Helper::canonical($concreteModelClass);

            /** @var Method $method */
            $method = $abstractQueryClass->addMethod('one');
            $method->addParameter('db', null);
            $method->addBody("return parent::one(\$db);");
            $method->addComment("Fetches one result");
            $method->addComment('@param null $db');
            $method->addComment("@return array|null|{$concreteModelClassCanonical}");

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    protected function addForeignKeys()
    {
        $schema = $this->db_conn->getSchema();

        $targetCount = [];
        foreach ($this->map as $fkTableName => $item) {
            $fkTableSchema = $schema->getTableSchema($fkTableName);

            foreach ($fkTableSchema->foreignKeys as $foreignKey) {
                $pkTableName = $foreignKey[0];

                if (!isset($this->map[$pkTableName])) {
                    continue;
                }

                $pkTableSchema = $schema->getTableSchema($pkTableName);

                $sourceName = $fkTableSchema->fullName;
                $targetName = $pkTableSchema->fullName;

                $targetCount["$targetName -> $sourceName"] = isset($targetCount["$targetName -> $sourceName"]) ? $targetCount["$targetName -> $sourceName"] + 1 : 1;
                $targetCount["$sourceName -> $targetName"] = isset($targetCount["$sourceName -> $targetName"]) ? $targetCount["$sourceName -> $targetName"] + 1 : 1;
            }
        }

        Console::startProgress(0, count($this->map), 'Adding foreign keys: ', 10);
        $i = 0;

        foreach ($this->map as $fkTableName => $item) {
            $fkTableSchema = $schema->getTableSchema($fkTableName);
            $uniqueIndexes = $schema->findUniqueIndexes($fkTableSchema);

            $foreignKeys = $fkTableSchema->foreignKeys;

            foreach ($foreignKeys as $foreignKey) {

                $pkTableName = $foreignKey[0];
                unset($foreignKey[0]);

                if (!isset($this->map[$pkTableName])) {
                    continue;
                }

                $pkTableSchema = $schema->getTableSchema($pkTableName);

                /** @var ClassType $fkAbstractModelClass */
                $fkAbstractModelClass = $this->map[$fkTableName]['abstractModel']['class'];

                /** @var ClassType $fkConcreteModelClass */
                $fkConcreteModelClass = $this->map[$fkTableName]['concreteModel']['class'];

                /** @var ClassType $pkAbstractModelClass */
                $pkAbstractModelClass = $this->map[$pkTableName]['abstractModel']['class'];

                /** @var ClassType $pkConcreteModelClass */
                $pkConcreteModelClass = $this->map[$pkTableName]['concreteModel']['class'];

                $fkColumn = array_key_first($foreignKey);
                $pkColumn = $foreignKey[$fkColumn];

                // add the foreign key relation method
                // this is a hasOne relation

                $sourceName = $fkTableSchema->fullName;
                $targetName = $pkTableSchema->fullName;
                $needsVerboseMethodName = $targetCount["$sourceName -> $targetName"] > 1;

                $propertyName = Helper::arrayToMethodName(
                    $needsVerboseMethodName ? [
                        $pkConcreteModelClass->getName(),
                        'by',
                        $fkColumn
                    ] : [
                        $pkConcreteModelClass->getName(),
                    ]
                );

                $methodName = Helper::arrayToMethodName([
                    'get',
                    $propertyName
                ]);

                $method = $fkAbstractModelClass->addMethod($methodName);

                $export = var_export(array_flip($foreignKey), true);
                $pkConcreteModelClassCanonical = Helper::canonical($pkConcreteModelClass);
                $method->addBody("return \$this->hasOne($pkConcreteModelClassCanonical::class,$export);");
                $fkAbstractModelClass->addComment("@property-read {$pkConcreteModelClassCanonical} \${$propertyName}"); // TODO description

                $method->setReturnType(ActiveQuery::class);

                // add the inverse relation method
                // this is a hasMany, except when there is unique constraint on the fk-column

                $hasMany = true;
                foreach ($uniqueIndexes as $indexName => $indexColumns) {
                    if (count($indexColumns) === 1 && $indexColumns[0] === $fkColumn) {
                        $hasMany = false;
                    }
                }

                $sourceName = $fkTableSchema->fullName;
                $targetName = $pkTableSchema->fullName;
                $needsVerboseMethodName = $targetCount["$targetName -> $sourceName"] > 1;

                $propertyName = Helper::arrayToMethodName(
                    $needsVerboseMethodName ? [
                        $hasMany ? Inflector::pluralize($fkConcreteModelClass->getName()) : $fkConcreteModelClass->getName(),
                        'by',
                        $fkColumn
                    ] : [
                        $hasMany ? Inflector::pluralize($fkConcreteModelClass->getName()) : $fkConcreteModelClass->getName(),
                    ]
                );

                $methodName = Helper::arrayToMethodName([
                    'get',
                    $propertyName
                ]);

                /** @var Method $method */
                $method = $pkAbstractModelClass->addMethod($methodName);

                $export = var_export($foreignKey, true);
                $fkConcreteModelClassCanonical = Helper::canonical($fkConcreteModelClass);
                if ($hasMany) {
                    $method->addBody("return \$this->hasMany($fkConcreteModelClassCanonical::class,$export);");
                    $pkAbstractModelClass->addComment("@property-read {$fkConcreteModelClassCanonical}[] \${$propertyName}"); // TODO description
                } else {
                    $method->addBody("return \$this->hasOne($fkConcreteModelClassCanonical::class,$export);");
                    $pkAbstractModelClass->addComment("@property-read {$fkConcreteModelClassCanonical} \${$propertyName}"); // TODO description
                }

                $method->setReturnType(ActiveQuery::class);
            }

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }

    private function writeAllFiles()
    {
        Console::startProgress(0, count($this->map), 'Writing all files: ', 10);
        $i = 0;

        foreach ($this->map as $tableName => $items) {
            foreach ($items as $item) {

                /** @var PhpNamespace $namespace */
                $namespace = $item['namespace'];

                /** @var PhpFile $file */
                $file = $item['file'];

                /** @var ClassType $class */
                $class = $item['class'];

                $filepath = Helper::filepath($class);
                FileHelper::createDirectory(dirname($filepath));

                // only overwrite abstract classes
                $exists = file_exists($filepath);
                $isAbstract = preg_match('/base/', $namespace->getName()); // TODO check this

                if (!$exists || $isAbstract) {
                    file_put_contents($filepath, $file->__toString());
                }
            }

            $i++;
            Console::updateProgress($i, count($this->map));
        }

        Console::endProgress();
    }
}