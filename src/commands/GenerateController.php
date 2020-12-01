<?php

namespace rauwebieten\yiiactiverecordtools\commands;

use rauwebieten\yiiactiverecordtools\components\GeneratorFactory;
use yii\console\Controller;
use yii\console\ExitCode;

class GenerateController extends Controller
{
    public $defaultAction = 'models';

    /**
     * Generates models for all tables in the database
     * @return int
     * @throws \yii\base\NotSupportedException
     */
    public function actionModels()
    {
        (new GeneratorFactory())->createModelGenerator(['console' => $this])->run();
        return ExitCode::OK;
    }
}