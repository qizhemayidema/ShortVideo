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
    //分享
    Route::get('share','H5/share');


    Route::group('chat',function(){
        Route::get('list','User/privateMessageList')->name('h5.user.privateMessageList');
        Route::post('pm','User/privateMessageSave')->name('h5.user.privateMessageSave');
        Route::get('pm','User/privateMessageInfo')->name('h5.user.privateMessageInfo');


        Route::get('/','Chat/list');
        Route::get('/info','Chat/info');
    });
});