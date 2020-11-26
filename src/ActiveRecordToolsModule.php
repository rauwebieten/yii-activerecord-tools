<?php


namespace rauwebieten\yiiactiverecordtools;


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
        var_dump($app);exit;
//        if ($app instanceof Application) {
//            $this->controllerNamespace = 'rauwebieten\yiiactiverecordtools\commands';
//        }
    }
}