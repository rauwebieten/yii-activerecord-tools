<?php

namespace rauwebieten\yiiactiverecordtools\commands;

use rauwebieten\yiiactiverecordtools\components\GeneratorFactory;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class GenerateController extends Controller
{
    public $defaultAction = 'all';

    /**
     * Generates models + diagrams for all tables in the database
     * @return int
     */
    public function actionAll()
    {
        $this->stdout("actionAll :)", Console::FG_YELLOW);
        return ExitCode::OK;
    }

    /**
     * Generates models for all tables in the database
     * @return int
     */
    public function actionModels($db = 'db')
    {
        (new GeneratorFactory())->createModelGenerator([
            'db' => $db,
            'console' => $this,
        ])->run();
        return ExitCode::OK;
    }

    /**
     * Generates diagrams for all table in the database
     * @return int
     */
    public function actionDiagrams()
    {
        $this->stdout("actionDiagrams :)", Console::FG_YELLOW);
        return ExitCode::OK;
    }
}