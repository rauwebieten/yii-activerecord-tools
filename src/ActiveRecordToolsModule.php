<?php

namespace rauwebieten\yiiactiverecordtools;

use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\console\Application;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ActiveRecordToolsModule extends Module implements BootstrapInterface
{
    public $controllerNamespace = 'rauwebieten\yiiactiverecordtools\controllers';

    public $db = 'db';
    public $namespace = 'app\models';
    public $baseModelClass = ActiveRecord::class;
    public $baseQueryClass = ActiveQuery::class;

    public function bootstrap($app)
    {
        \Yii::setAlias('@rauwebieten/yiiactiverecordtools', $this->getBasePath());

        if ($app instanceof Application) {
            $this->controllerNamespace = 'rauwebieten\yiiactiverecordtools\commands';
        }
    }

    public function init()
    {
        parent::init();
    }
}