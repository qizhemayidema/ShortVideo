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
            //获取某个视频的评论回复
            Route::get('/reply','api/Video/commentReplyList')->name('api.video.commentReplyList');
            //获取某个视频的评论列表
            Route::get('/','api/Video/commentLists')->name('api.video.commentList');
            //评论一个视频
            Route::post('/','api/Comment/videoSave')->name('api.comment.video_save');
        });

        Route::get('list','api/Video/list')->name('api.video.list');
        //点赞一个视频
        Route::post('like','api/Video/likeSave')->name('api.video.likeSave');

        //收藏 or 取消收藏一个视频
        Route::post('collect','api/Video/CollectSave')->name('api.video.collectSave');

        //分享一个视频给朋友
        Route::post('shareFriends','api/Video/shareFriends')->name('ap.video.shareFriends');

        //分享一个视频到朋友圈
        Route::post('shareFriendsRound','api/Video/shareFriendsRound')->name('ap.video.shareFriendsRound');

        //评价一个视频
        Route::post('appraise','api/Video/appraise')->name('api.video.appraise');

        //观看一个视频
        Route::post('play','api/Video/play')->name('api.video.play');

        //发布一个视频
        Route::post('/','api/Video/save')->name('api.video.save');

        //删除一个视频
        Route::delete('/','api/Video/delete')->name('api.video.delete');
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
        //收藏列表
        Route::get('collect','api/User/collectList')->name('api.user.collectList');
        //作品列表
        Route::get('video','api/User/videoList')->name('api.user.videoList');
        //获赞列表
        Route::get('like','api/User/likeList')->name('api.user.likeList');
        //推荐关注
        Route::get('otherUser','api/User/otherUser')->name('api.user.otherUser');
        //用户任务
        Route::get('assignment','api/User/assignment')->name('api.user.assignment');

        Route::group('auth',function(){
            //个人认证
            Route::post('personal','api/User/authPersonal')->name('api.user.authPersonal');
            //企业认证
            Route::post('company','api/User/authCompany')->name('api.user.authCompany');
        });
        //获取用户数据
        Route::get('/','api/User/info')->name('api.user.info');
        //修改个人信息
        Route::put('/','api/User/update')->name('api.user.update');
    });
    Route::group('cate',function(){
        Route::get('video','api/Category/getVideo')->name('api.category.getVideo');
    });
    Route::group('upload',function(){
        Route::post('img','api/Upload/img');
        Route::post('base64Img','api/Upload/base64Img');
    });
    Route::group('config',function(){
        Route::get('assignmentScore','api/Config/assignmentScore')->name('api.config.assignmentScore');
    });
    Route::get('search','api/Index/search')->name('api.index.search');
    Route::post('feedback','api/Feedback/save')->name('api.feedback.save');
});