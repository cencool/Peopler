<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use app\models\basic\Person;
use app\models\basic\PersonAttachment;
use yii\helpers\FileHelper;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller {
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world') {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function actionTable() {
        $data = PersonAttachment::find()->select('*')->asArray()->all();
        $table = new Table();
        $headers = isset($data[0]) ? array_keys($data[0]) : [];
        echo $table->setHeaders($headers)->setRows($data)->run();



        return ExitCode::OK;
    }

    public function actionList() {
        $list = FileHelper::findFiles('./web', ['except' => ['*.php']]);
        foreach ($list as $name) {
            echo $name . PHP_EOL;
        }
        $type = FileHelper::getExtensionsByMimeType('audio/mpeg');
        foreach ($type as $name) {
            echo $name . PHP_EOL;
        }
        return ExitCode::OK;
    }
}