<?php


Route::group(['prefix' => 'facebook'], function (){
    Route::get('{id}/fanpage', 'FacebookController@fanpage');
    Route::get('{id}/count_posts', 'FacebookController@count_posts');
    Route::get('posts/{post_id}/likes', 'FacebookController@like_posts');
    Route::get('posts/{post_id}/comments', 'FacebookController@comment_posts');
});

Route::group(['prefix' => 'twitter'], function (){
    Route::get('{id}', 'TwitterController@user');
    Route::get('{id}/tweets', 'TwitterController@tweets');
    Route::get('{post_id}/retweets', 'TwitterController@retweets');
});
