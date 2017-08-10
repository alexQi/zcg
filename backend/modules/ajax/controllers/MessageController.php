<?php
namespace backend\modules\ajax\controllers;

use yii;
use yii\base\Exception;
use common\models\service\MessageService;
use app\models\MessageSearch;
use app\models\UserSearch;

class MessageController extends BaseController
{

    /**
     * 处理邮件
     * @return array
     */
    public function actionDealMail()
    {

        try {
            if (!$this->postData['status'] || !in_array($this->postData['status'],MessageSearch::$STATUSLIST))
            {
                throw new Exception('邮件状态错误');
            }

            //处理邮件
            if (isset($this->postData['id']) && $this->postData['id']!='')
            {
                $messageInfo = MessageSearch::findOne($this->postData['id']);

                if (empty($messageInfo))
                {
                    throw new Exception('未查找到ID: '.$this->postData['id'].' 的邮件');
                }

                $messageInfo->to      = $this->postData['to'];
                $messageInfo->title   = $this->postData['title'];
                $messageInfo->content = $this->postData['content'];
                $messageInfo->status  = 1;
                $messageInfo->updated_at  = time();

            }else{

                $messageInfo = new MessageSearch();
                $messageInfo->title = $this->postData['title'];
                $messageInfo->from_user_id = yii::$app->user->identity->id;
                $messageInfo->from = yii::$app->user->identity->email;
                $messageInfo->to = $this->postData['to'];
                $messageInfo->content = $this->postData['content'];
                $messageInfo->created_at = time();
                $messageInfo->updated_at = time();
            }

            if (!$messageInfo->save())
            {
                throw new Exception('写入邮件内容失败');
            }

            if ($this->postData['status']==2)
            {
                //入邮件发送队列
                $data['title'] = $this->postData['title'];
                $data['from']  = [
                    $messageInfo->from => yii::$app->user->identity->username
                ];
                $data['to']      = $messageInfo->to;
                $data['content'] = $messageInfo->content;

                if (!MessageService::InToQueue(json_encode($data)))
                {
                    //更新邮件到草稿箱
                    $messageInfo->status = 3;
                    $messageInfo->save();
                    throw new Exception('发送邮件失败');
                }
            }

            $messageInfo->status = $this->postData['status'];

            if (!$messageInfo->save())
            {
                throw new Exception('修改邮件状态失败');
            }

            $this->ajaxReturn['state']   = 1;
            $this->ajaxReturn['message'] = 'sucess';
            $this->ajaxReturn['status']  = $messageInfo->status;

        }catch (Exception $e){

            $this->ajaxReturn['message'] = $e->getMessage();
        }

        return $this->ajaxReturn;
    }

    /**
     * 刷新缓存
     * @return array|mixed
     */
    public function actionRefresh()
    {
        $mailList = UserSearch::getUserMail();

        $cache = Yii::$app->cache;
        $cache->set('mailList_'.yii::$app->user->identity->id, $mailList, 60*60);

        return $mailList;
    }

    /**
     * 添加到缓存列表
     * @return array|mixed
     */
    public function actionAssign()
    {
        $mails  = Yii::$app->getRequest()->post('mail', []);

        $cache = Yii::$app->cache;
        $mailList = $cache->get('mailList_'.yii::$app->user->identity->id);

        //处理未选中的项目
        foreach ($mails as $mail)
        {
            $key = array_search($mail,$mailList['avaliable']);
            if ($key!==false)
            {
                array_splice($mailList['avaliable'], $key, 1);
            }
        }

        //处理选中项目
        $mailList['assigned'] = array_merge($mailList['assigned'],$mails);

        //重新暂存数据
        $cache->set('mailList_'.yii::$app->user->identity->id, $mailList, 60*60);

        return $mailList;
    }

    /**
     * 从缓存列表移除
     * @return array|mixed
     */
    public function actionRemove()
    {
        $mails  = Yii::$app->getRequest()->post('mail', []);

        $cache = Yii::$app->cache;
        $mailList = $cache->get('mailList_'.yii::$app->user->identity->id);

        //处理未选中的项目
        foreach ($mails as $mail)
        {
            $key = array_search($mail,$mailList['assigned']);
            if ($key!==false)
            {
                array_splice($mailList['assigned'], $key, 1);
            }
        }

        //处理选中项目
        $mailList['avaliable'] = array_merge($mailList['avaliable'],$mails);

        //重新暂存数据
        $cache->set('mailList_'.yii::$app->user->identity->id, $mailList, 60*60);

        return $mailList;
    }

    /**
     * 添加新邮件
     * @return mixed
     */
    public function actionAddNewMail(){
        $mails  = Yii::$app->getRequest()->post('mail', []);

        $cache = Yii::$app->cache;
        $mailList = $cache->get('mailList_'.yii::$app->user->identity->id);

        //处理选中项目
        array_push($mailList['assigned'],$mails);

        //重新暂存数据
        $cache->set('mailList_'.yii::$app->user->identity->id, $mailList, 60*60);

        return $mailList;
    }
}