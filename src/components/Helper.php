<?php


namespace rauwebieten\yiiactiverecordtools\components;


use Nette\PhpGenerator\ClassType;
use yii\helpers\Inflector;

class Helper
{
    public static function arrayToMethodName($array)
    {
        $string = self::arrayToClassName($array);
        $string = lcfirst($string);
        return $string;
    }

    public static function arrayToClassName($array)
    {
        $array = array_values(array_filter($array, function ($item) {
            return !empty(trim($item));
        }));

        $array = array_map(function ($string) {
            return strtolower( Inflector::camel2words($string) );
        }, $array);

        $string = implode(' ', $array);
        $string = Inflector::camelize($string);
        return $string;
    }

    public static function canonical(ClassType $classType)
    {
        return '\\' . $classType->getNamespace()->getName() . '\\' . $classType->getName();
    }

    public static function filepath(ClassType $classType) {
        $path = self::canonical($classType);
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        return \Yii::getAlias('@' . $path) . '.php';
    }

    public static function var_export($var)
    {
        switch (gettype($var)) {
            case "string":
                return '\'' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '\'';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = ($indexed ? "" : self::var_export($key) . " => ") . self::var_export($value);
                }
                return "[" . implode(",", $r) . "]";
            case "boolean":
                return $var ? "true" : "false";
            default:
                return var_export($var, true);
        }
    }
}