<?php
namespace app\common\model;

interface ShowImpl
{
    /**
     * 前台可展示数据条件
     * @param  $alias string 别名
     * @return mixed
     * return $this
     */
    public function receptionShowData(string $alias);

    //后台可展示数据条件  return $this;

    /**
     * 后台可展示数据条件
     * @param  $alias string 别名
     * @return mixed
     * return $this
     */
    public function backgroundShowData(string $alias);
}