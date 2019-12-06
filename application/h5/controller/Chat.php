<?php

namespace app\h5\controller;

use think\Controller;
use think\Request;

class Chat extends Base
{
    public function list()
    {
        return $this->fetch();
    }

    public function info()
    {
        return $this->fetch();
    }
}
