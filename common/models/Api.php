<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 17-6-25
 * Time: 下午12:25
 */

namespace common\models;

use common\components\Common;
use yii;
use yii\base\Model;
use common\models\service\ApiBaseService;

class Api extends Model{

    public $queryParam;
    public $apiInfo;
    public $apiName;
    public $userId;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }


    /**
     * run api
     * @return mixed|string
     */
    public function run(){

        $param['url']    = $this->apiInfo['url'].$this->apiInfo['url_path'];
        $param['method'] = $this->apiInfo['request_method'];

        $param['header'] = '';
        if ($this->apiInfo['api_name']=='Robot')
        {
            $param['header'] = ["Authorization:APPCODE ".yii::$app->params['aliyun']['AppCode']];
        }elseif ($this->apiInfo['api_name']=='Turing')
        {
            $param['header'] = ["content-type: application/x-www-form-urlencoded"];
            $param['query_string']['key']    = yii::$app->params['turing']['APIkey'];
            $param['query_string']['userid'] = $this->userId;
        }
        $param['query_string'][$this->apiInfo['query_string']] = $this->queryParam['queryString'];

        $responData = $this->invokeApi($param);

        return $this->formateData($this->apiInfo['api_name'],$responData);
    }

    /**
     * invoke api to get infomations
     *
     * @param  array  $param['query_string'] -> array
     * @return string
     */
    private function invokeApi($param)
    {
        $content = Common::httpRequest($param['url'],$param['query_string'],$param['method'],$param['header']);
        return json_decode($content);
    }


    /**
     * @param $apiName
     * @param $data
     * @return mixed|string
     */
    private function formateData($apiName,$data){
        $message = 'no respons data';
        if ($apiName == "Robot"){
            if ($data->msg=='ok')
            {
                $msg = $data->result->content;

                $realMsg = preg_replace("/\[/",'<',$msg);
                $realMsg = preg_replace("/\]/",'>',$realMsg);
                $realMsg = preg_replace("/(link)/",'a',$realMsg);
                $message = preg_replace("/(url)/",'href',$realMsg);
            }
        }else if($apiName == "Turing"){
            switch ($data->code)
            {
                case '100000':
                    $message = $data->text;
                    break;
                case '200000':
                    $message = $data->text."<a href='$data->url'>[详情链接]</a>";
                    break;
                default:
                    //.......
            }
        }

        return $message;
    }

    /**
     * get api info
     * @return array api Info
     */
    public function getApiInfo(){
        $ApiBase = new ApiBaseService();
        $this->queryParam['isDefault'] = 2;
        return $this->apiInfo = $ApiBase->getApi($this->queryParam);
    }
}