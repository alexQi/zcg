<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 17-7-11
 * Time: 下午2:46
 */
namespace frontend\controllers;

use yii;
use udokmeci\yii2beanstalk\Beanstalk;
use common\models\Mail;

class ToolController extends BaseController
{
    public function actionIndex()
    {
        $beanstalk = new Beanstalk();
        $beanstalk->useTube('oliuSaveData');
        $put = $beanstalk->put('cowsay hahahahahahah');

        if (!$put){
            exit($put);
        }

        $beanstalk->useTube('oliu.handleData');
        $put = $beanstalk->put('cowsay hahahahahahah');

        if (!$put){
            exit($put);
        }
    }

    public function actionSendMail(){
        $mail = new Mail();
        $res = $mail->SendMail();
        var_dump($res);
    }
}