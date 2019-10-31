<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;


Route::group('api',function(){
    Route::group('complain',function(){
        Route::group('cate',function(){
            Route::get('video','api/Complain/getVideoCate')->name('api.complain.cate.video');
        });
        Route::post('video','api/Complain/VideoSave')->name('api.complain.videoSave');
    });
    Route::group('video',function(){
        Route::group('comment',function(){
            //评论一个视频
            Route::post('/','api/Comment/videoSave')->name('api.comment.video_save');
        });

        Route::group('like',function(){
            //点赞一个视频
            Route::post('/','api/Video/likeSave')->name('api.video.likeSave');
        });

    });
    Route::group('comment',function(){
        Route::post('like','api/Comment/likeSave')->name('api.comment.likeSave');
    });
    Route::group('user',function(){
        //关注 和 取消关注
        Route::post('focus','api/User/focusSave')->name('api.user.focusSave');
        //关注列表
        Route::get('focus','api/User/focusList')->name('api.user.focusList');
        //粉丝列表
        Route::get('fans','api/User/fansList')->name('api.user.fansList');
        //私信
        Route::post('pm','api/User/privateMessageSave')->name('api.user.privateMessageSave');
    });

    Route::post('feedback','api/Feedback/save')->name('api.feedback.save');
});