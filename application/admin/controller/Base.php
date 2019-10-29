<?php

namespace app\admin\controller;

use app\http\middleware\AdminLoginCheck;
use think\Controller;
use think\Exception;
use think\Request;

class Base extends Controller
{
    private $configObject;

    const WEBSITE_CONFIG_PATH = CONFIG_PATH . 'website_config.json';

    protected $middleware = [
        AdminLoginCheck::class,
    ];

    /**
     * 获取配置信息
     * @param $name
     * @return mixed|null
     */
    protected function getConfig($name)
    {
        if (!$this->configObject){
            $this->configObject = json_decode(file_get_contents(self::WEBSITE_CONFIG_PATH));
        }
        if ($name == '*') return json_decode(file_get_contents(self::WEBSITE_CONFIG_PATH),true);
        $configPath = explode('.', $name);
        $temp = $this->configObject;
        try {
            foreach ($configPath as $key => $value) {
                $temp = $temp->$value;
            }
            if ($temp === null) throw new Exception();
        } catch (Exception $e) {
            header('Content-type: application/json');
            exit(json_encode(['code' => 0, 'msg' => '获取配置失败'], 256));
        }
        $temp = json_decode(json_encode($temp,256),true);
        return $temp;
    }
}
