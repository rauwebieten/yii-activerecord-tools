<?php


namespace RauweBieten\YiiActiveRecordTools;


use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\console\Application;

class ActiveRecordToolsModule extends Module implements BootstrapInterface
{
    public $controllerNamespace;

    public function init()
    {
        parent::init();
    }

    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->controllerNamespace = 'RauweBieten\YiiActiveRecordTools\commands';
        }
    }
}