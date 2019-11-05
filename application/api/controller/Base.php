<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\User as UserModel;

class Base extends Controller
{

    const WEB_SITE_PATH = CONFIG_PATH . 'website_config.json';        //网站配置路径

    private $configObject = null;

    private $userInfo = [];

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub

        $this->getUserInfo();

    }

    protected function getUserInfo()
    {
        $token = \request()->param('token');
        $token || $token = \request()->put('token');

        if ($token) {
            $this->userInfo = (new UserModel())->receptionShowData()->where(['token' => $token])->find();
        }
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        if ($name == 'userInfo') {
            if (!$this->$name) {
                header('Content-type: application/json');
                exit(json_encode(['code' => 0, 'msg' => '请先登录账号~'], 256));

            }
            return $this->$name;
        }
    }

    /**
     * 获取配置信息
     * @param $name
     * @return mixed|null
     */
    protected function getConfig($name)
    {
        if (!$this->configObject) {
            $this->configObject = json_decode(file_get_contents(self::WEB_SITE_PATH));
        }
        $configPath = explode('.', $name);
        $temp = $this->configObject;
        try {
            foreach ($configPath as $key => $value) {
                $temp = $temp->$value;
            }
            if ($temp === null) throw new \Exception();
        } catch (\Exception $e) {
            header('Content-type: application/json');
            exit(json_encode(['code' => 0, 'msg' => '获取配置失败'], 256));
        }
        $temp = json_decode(json_encode($temp, 256), true);
        return $temp;
    }

    protected function existsToken()
    {
        $token = Request()->param('token');

        return  $token ?? false;
    }

}