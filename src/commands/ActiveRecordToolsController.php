<?php

namespace RauweBieten\YiiActiveRecordTools\commands;

use yii\console\Controller;
use yii\helpers\Console;

class ActiveRecordToolsController extends Controller
{
    public function actionIndex()
    {
        $this->stdout("Index :)", Console::FG_YELLOW);
    }
}