<?php
namespace app\index\controller;

use think\Request;

class Index
{
    public function index(Request $request)
    {

    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
