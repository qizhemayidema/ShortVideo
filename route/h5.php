<?php

use think\facade\Route;

Route::group(['name'=>'h5','prefix'=>'h5/'],function(){
    //举报
    Route::get('complain','H5/complain');
    //入驻
    Route::get('auth','H5/auth');
    //注册协议privacyPolicy
    Route::get('registerPolicy','H5/registerPolicy');
    //隐私协议
    Route::get('privacyPolicy','H5/privacyPolicy');
    //反馈
    Route::get('feedback','H5/feedback');
    //关于我们
    Route::get('aboutOur','H5/aboutOur');


});