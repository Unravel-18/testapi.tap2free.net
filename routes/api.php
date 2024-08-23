<?php

Route::group(['middleware' => 'filter:auth_user_agent'], function()
{
    Route::get('/api/servers', ['as' => 'servers.index', 'uses' => 'ServersController@apiServers']);
});
