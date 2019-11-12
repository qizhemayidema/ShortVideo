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

//允许访问的接口版本
$version = 'V1';

$headerVersion = strtoupper(\think\facade\Request::header('version'));

if ($version == $headerVersion){

    Route::group(['name'=>'api','prefix'=>'api'.$version.'/'],function(){
        Route::group('complain',function(){
            Route::group('cate',function(){
                Route::get('video','Complain/getVideoCate')->name('api.complain.cate.video');
            });
            Route::post('video','Complain/VideoSave')->name('api.complain.videoSave');
        });
        Route::group('video',function(){
            Route::group('comment',function(){
                //获取某个视频的评论回复
                Route::get('/reply','Video/commentReplyList')->name('api.video.commentReplyList');
                //获取某个视频的评论列表
                Route::get('/','Video/commentLists')->name('api.video.commentList');
                //评论一个视频
                Route::post('/','Comment/videoSave')->name('api.comment.video_save');
            });

            Route::get('list','Video/list')->name('api.video.list');
            //点赞一个视频
            Route::post('like','Video/likeSave')->name('api.video.likeSave');

            //收藏 or 取消收藏一个视频
            Route::post('collect','Video/CollectSave')->name('api.video.collectSave');

            //分享一个视频给朋友
            Route::post('shareFriends','Video/shareFriends')->name('ap.video.shareFriends');

            //分享一个视频到朋友圈
            Route::post('shareFriendsRound','Video/shareFriendsRound')->name('ap.video.shareFriendsRound');

            //评价一个视频
            Route::post('appraise','Video/appraise')->name('api.video.appraise');

            //观看一个视频
            Route::get('/','Video/play')->name('api.video.play');

            //发布一个视频
            Route::post('/','Video/save')->name('api.video.save');

            //删除一个视频
            Route::delete('/','Video/delete')->name('api.video.delete');
        });
        Route::group('chat',function(){
            Route::get('list','User/privateMessageList')->name('api.user.privateMessageList');
            Route::post('pm','User/privateMessageSave')->name('api.user.privateMessageSave');
            Route::get('pm','User/privateMessageInfo')->name('api.user.privateMessageInfo');

        });
        Route::group('comment',function(){
            Route::post('like','Comment/likeSave')->name('api.comment.likeSave');
        });
        Route::group('user',function(){
            //关注 和 取消关注
            Route::post('focus','User/focusSave')->name('api.user.focusSave');
            //关注列表
            Route::get('focus','User/focusList')->name('api.user.focusList');
            //粉丝列表
            Route::get('fans','User/fansList')->name('api.user.fansList');
            //收藏列表
            Route::get('collect','User/collectList')->name('api.user.collectList');
            //作品列表
            Route::get('video','User/videoList')->name('api.user.videoList');
            //获赞列表
            Route::get('like','User/likeList')->name('api.user.likeList');
            //推荐关注
            Route::get('otherUser','User/otherUser')->name('api.user.otherUser');
            //用户任务
            Route::get('assignment','User/assignment')->name('api.user.assignment');

            Route::group('auth',function(){
                //个人认证
                Route::post('personal','User/authPersonal')->name('api.user.authPersonal');
                //企业认证
                Route::post('company','User/authCompany')->name('api.user.authCompany');
            });
            //获取用户数据
            Route::get('/','User/info')->name('api.user.info');
            //修改个人信息
            Route::put('/','User/update')->name('api.user.update');
        });
        Route::group('cate',function(){
            Route::get('video','Category/getVideo')->name('api.category.getVideo');
        });
        Route::group('upload',function(){
            Route::post('img','Upload/img');
            Route::post('base64Img','Upload/base64Img');
        });
        Route::group('config',function(){
            Route::get('assignmentScore','Config/assignmentScore')->name('api.config.assignmentScore');
            Route::get('startPage','Config/startPage');
            Route::get('article','Config/article');
            Route::get('videoMaxSec','Config/videoMaxSec');
        });
        Route::get('search','Index/search')->name('api.index.search');

        Route::post('feedback','Feedback/save')->name('api.feedback.save');

        Route::get('version/newest','Version/getNewest');

        Route::group('login',function(){
            Route::post('ios','Login/loginIos');
            Route::post('','Login/login');
        });
    });

}


