<?php

Route::get('/', ['as' => 'main.index', 'uses' => 'MainController@index']);
Route::get('/test', ['as' => 'main.index', 'uses' => 'MainController@test']);

Route::get('/servers/update_crt', ['as' => 'servers.update_crt', 'uses' => 'ServersController@update_crt']);
Route::get('/servers/ss-update_crt', ['as' => 'servers.update_crt', 'uses' => 'ServersController@update_crt_ss']);

Route::group(['middleware' => 'filter:ip'], function()
{
    Route::get('/login', ['as' => 'auth.login', 'uses' => 'Auth\AuthController@login']);
    Route::post('/auth', ['as' => 'auth.auth', 'uses' => 'Auth\AuthController@auth']);
    Route::get('/logout', ['as' => 'auth.logout', 'uses' => 'Auth\AuthController@logout']);
    Route::get('apis/servers/no-check', ['as' => 'apis.servers.no_check', 'uses' => 'ServersController@noCheck']);


    Route::group(['middleware' => 'filter:auth_admin'], function()
    {
        Route::get('/oauth2/google', ['as' => 'auth2.google', 'uses' => 'Auth\AuthController@auth2Google']);
        Route::get('/oauth2/google/token', ['as' => 'auth2.google.token', 'uses' => 'Auth\AuthController@auth2GoogleToken']);
        Route::get('/oauth2/google/token/delete', ['as' => 'auth2.google.token.delete', 'uses' => 'Auth\AuthController@auth2GoogleTokenDelete']);
        
        Route::get('/main', ['as' => 'servers.index', 'uses' => 'ApiController@index']);

        Route::get('/tokens', ['as' => 'tokens.index', 'uses' => 'TokenController@index']);
        Route::get('/tokens/settings', ['as' => 'tokens.settings', 'uses' => 'TokenController@settings']);
        Route::post('/tokens/settings/save', ['as' => 'tokens.settings.save', 'uses' => 'TokenController@settingsSave']);
        Route::get('/tokens/add', ['as' => 'tokens.add', 'uses' => 'TokenController@item']);
        Route::post('/tokens/store', ['as' => 'tokens.store', 'uses' => 'TokenController@save']);
        Route::get('/tokens/{id}/edit', ['as' => 'tokens.edit', 'uses' => 'TokenController@item']);
        Route::post('/tokens/{id}/update', ['as' => 'tokens.update', 'uses' => 'TokenController@save']);
        Route::get('/tokens/{id}/delete', ['as' => 'tokens.delete', 'uses' => 'TokenController@delete']);
        Route::post('/tokens/key/generate', ['as' => 'tokens.key.generate', 'uses' => 'TokenController@keyGenerate']);
        Route::post('/tokens/{id}/app_id/clear', ['as' => 'tokens.app_id.clear', 'uses' => 'TokenController@clearAppId']);
        Route::post('/tokens/status-update', ['as' => 'tokens.status.update', 'uses' => 'TokenController@statusUpdate']);
        Route::post('/tokens/select/delete', ['as' => 'tokens.select.delete', 'uses' => 'TokenController@tokensSelectDelete']);
        
        Route::get('/prokeys', ['as' => 'prokeys.index', 'uses' => 'ProkeyController@index']);
        Route::get('/prokeys/settings', ['as' => 'prokeys.settings', 'uses' => 'ProkeyController@settings']);
        Route::post('/prokeys/settings/save', ['as' => 'prokeys.settings.save', 'uses' => 'ProkeyController@settingsSave']);
        Route::get('/prokeys/add', ['as' => 'prokeys.add', 'uses' => 'ProkeyController@item']);
        Route::post('/prokeys/store', ['as' => 'prokeys.store', 'uses' => 'ProkeyController@save']);
        Route::get('/prokeys/{id}/edit', ['as' => 'prokeys.edit', 'uses' => 'ProkeyController@item']);
        Route::post('/prokeys/{id}/update', ['as' => 'prokeys.update', 'uses' => 'ProkeyController@save']);
        Route::get('/prokeys/{id}/delete', ['as' => 'prokeys.delete', 'uses' => 'ProkeyController@delete']);
        Route::post('/prokeys/status-update', ['as' => 'prokeys.status.update', 'uses' => 'ProkeyController@statusUpdate']);
        
        Route::get('/badip', ['as' => 'badip.index', 'uses' => 'MainController@badip']);
        Route::get('/badip/item', ['as' => 'badip.item', 'uses' => 'MainController@badipitem']);
        Route::post('/badip/store', ['as' => 'badip.store', 'uses' => 'MainController@badipsave']);
        Route::post('/badip/{id}/update', ['as' => 'badip.update', 'uses' => 'MainController@badipsave']);
        Route::get('/badip/{id}/delete', ['as' => 'badip.delete', 'uses' => 'MainController@badipdelete']);
        Route::post('/badip/change/status', ['as' => 'badip.change.status', 'uses' => 'MainController@changeStatus']);
        Route::get('/googletoken/{api_id}', ['as' => 'googletoken.index', 'uses' => 'MainController@googletoken']);
        Route::post('/googletoken/change/status', ['as' => 'googletoken.change.status', 'uses' => 'MainController@changeStatusGoogleToken']);
        Route::post('/googletoken/delete', ['as' => 'googletoken.delete', 'uses' => 'MainController@deleteGoogleToken']);

        Route::get('/servers/add', ['as' => 'servers.add', 'uses' => 'ServersController@add']);
        Route::get('/servers/edit/{server_id}', ['as' => 'servers.edit', 'uses' => 'ServersController@edit']);
        Route::post('/servers/save', ['as' => 'servers.save', 'uses' => 'ServersController@save']);
        Route::post('/servers/update/{server_id}', ['as' => 'servers.update', 'uses' => 'ServersController@update']);
        Route::get('/servers/destroy/{server_id}', ['as' => 'servers.destroy', 'uses' => 'ServersController@destroy']);
        Route::delete('/servers/deletecert/{server_id}', ['as' => 'servers.deletecert', 'uses' => 'ServersController@deletecert']);

        Route::get('apis/servers', ['as' => 'apis.servers', 'uses' => 'ServersController@index']);
        
        Route::get('apis/{api_id}/index', ['as' => 'apis.servers.index', 'uses' => 'ServersController@index']);
        Route::post('apis/index/save', ['as' => 'servers.index.save', 'uses' => 'ServersController@serversSave']);
        Route::post('apis/{api_id}/index/save', ['as' => 'apis.servers.index.save', 'uses' => 'ServersController@serversSave']);
        Route::get('apis/{api_id}/servers', ['as' => 'apis.servers.servers', 'uses' => 'ServersController@servers']);
        Route::get('apis/server-{server_id}/apis', ['as' => 'servers.apis', 'uses' => 'ServersController@serverApis']);
        Route::get('apis/{api_id}/server-{server_id}/apis', ['as' => 'apis.servers.apis', 'uses' => 'ServersController@serverApis']);
        Route::post('apis/server-{server_id}/apis/save', ['as' => 'servers.apis.save', 'uses' => 'ServersController@serverApisSave']);
        Route::post('apis/{api_id}/server-{server_id}/apis/save', ['as' => 'apis.servers.apis.save', 'uses' => 'ServersController@serverApisSave']);
        Route::get('apis/{api_id}/vpn', ['as' => 'apis.servers.vpn', 'uses' => 'ServersController@vpn']);
        Route::get('apis/{api_id}/ss', ['as' => 'apis.servers.ss', 'uses' => 'ServersController@ss']);
        Route::get('apis/{api_id}/servers/add', ['as' => 'apis.servers.add', 'uses' => 'ServersController@add']);
        Route::get('apis/{api_id}/servers/edit/{server_id}', ['as' => 'apis.servers.edit', 'uses' => 'ServersController@edit']);
        Route::post('apis/{api_id}/servers/save', ['as' => 'apis.servers.save', 'uses' => 'ServersController@save']);
        Route::post('apis/{api_id}/servers/update/{server_id}', ['as' => 'apis.servers.update', 'uses' => 'ServersController@update']);
        Route::get('apis/{api_id}/servers/destroy/{server_id}', ['as' => 'apis.servers.destroy', 'uses' => 'ServersController@destroy']);
        Route::delete('apis/{api_id}/servers/deletecert/{server_id}', ['as' => 'apis.servers.deletecert', 'uses' => 'ServersController@deletecert']);
        Route::post('apis/{api_id}/servers/copy/{server_id}', ['as' => 'apis.servers.copy', 'uses' => 'ServersController@copy']);
        Route::get('apis/{api_id}/servers/displace', ['as' => 'apis.servers.displace', 'uses' => 'ServersController@displace']);
        Route::get('apis/servers/setting', ['as' => 'servers.settings', 'uses' => 'SettingController@serversSettings']);
        Route::get('apis/{api_id}/servers/setting', ['as' => 'apis.servers.setting', 'uses' => 'SettingController@serversSettings']);
        Route::post('apis/{api_id}/servers/setting', ['as' => 'apis.setting.servers.save', 'uses' => 'SettingController@serversSettingsSave']);
        Route::get('apis/{api_id}/servers/api/destroy/{server_id}', ['as' => 'apis.servers.api.destroy', 'uses' => 'ServersController@destroyApi']);
        
        Route::post('/upload_flag', ['as' => 'servers.upload_flag', 'uses' => 'ServersController@upload_flag']); 
        Route::post('/upload_map', ['as' => 'servers.upload_map', 'uses' => 'ServersController@upload_map']);
        
        Route::post('/upload_flag/ajax-delete', ['as' => 'images.delete', 'uses' => 'ServersController@ajaxDeleteFlag']);
        Route::post('/upload_map/ajax-delete', ['as' => 'images.delete', 'uses' => 'ServersController@ajaxDeleteMap']);
        
        Route::post('/servers/update-id', ['as' => 'servers.update_id', 'uses' => 'ServersController@updateId']);
        
        Route::post('/servers/copy-select', ['as' => 'servers.copy_select', 'uses' => 'ServersController@copySelect']);
       
        Route::get('/setting', ['as' => 'setting.index', 'uses' => 'SettingController@index']);
        Route::post('/setting', ['as' => 'setting.save', 'uses' => 'SettingController@save']);
        Route::post('/minute_not_available/save', ['as' => 'setting.minute_not_available.save', 'uses' => 'SettingController@minuteNotAvailableSave']);
        Route::get('/servers/setting', ['as' => 'servers.setting', 'uses' => 'SettingController@serversSettings']);
        Route::post('/servers/setting/save', ['as' => 'setting.servers.save', 'uses' => 'SettingController@serversSettingsSave']);
        
        Route::get('apis/{api_id}/setting', ['as' => 'apis.setting.index', 'uses' => 'SettingController@index']);
        Route::post('apis/{api_id}/setting', ['as' => 'apis.setting.save', 'uses' => 'SettingController@save']);
        
        Route::get('/apis', ['as' => 'apis.index', 'uses' => 'ApiController@index']);
        
        Route::get('/apis/add', ['as' => 'apis.add', 'uses' => 'ApiController@add']);
        Route::post('/apis/save', ['as' => 'apis.save', 'uses' => 'ApiController@save']);
        Route::get('/apis/edit/{api}', ['as' => 'apis.edit', 'uses' => 'ApiController@edit']);
        Route::post('/apis/update/{api}', ['as' => 'apis.update', 'uses' => 'ApiController@update']);
        Route::get('apis/destroy/{api}', ['as' => 'apis.destroy', 'uses' => 'ApiController@destroy']);
        
        Route::post('/apis/servers/save_select_checked', ['as' => 'servers.save_select_checked', 'uses' => 'ServersController@saveSelectChecked']);
        Route::post('/apis/{api_id}/servers/save_select_checked', ['as' => 'servers.apis.save_select_checked', 'uses' => 'ServersController@saveSelectChecked']);
        Route::post('/apis/servers/displace_sort', ['as' => 'servers.displace_sort', 'uses' => 'ServersController@displaceSort']);
        Route::post('/apis/{api_id}/servers/displace_sort', ['as' => 'servers.apis.displace_sort', 'uses' => 'ServersController@displaceSortApi']);
    
        Route::get('apis/faqs', ['as' => 'faq.all', 'uses' => 'FaqController@all']);
        Route::get('apis/{api_id}/faq/all', ['as' => 'apis.faq.all', 'uses' => 'FaqController@all']);
        Route::get('apis/{api_id}/faq/index', ['as' => 'apis.faq.index', 'uses' => 'FaqController@index']);
        Route::get('apis/faq/add', ['as' => 'faq.add', 'uses' => 'FaqController@add']);
        Route::get('apis/{api_id}/faq/add', ['as' => 'apis.faq.add', 'uses' => 'FaqController@add']);
        Route::post('apis/faq/save', ['as' => 'faq.save', 'uses' => 'FaqController@save']);
        Route::post('apis/{api_id}/faq/save', ['as' => 'apis.faq.save', 'uses' => 'FaqController@save']);
        Route::get('apis/faq/edit/{faq_id}', ['as' => 'faq.edit', 'uses' => 'FaqController@edit']);
        Route::get('apis/{api_id}/faq/edit/{faq_id}', ['as' => 'apis.faq.edit', 'uses' => 'FaqController@edit']);
        Route::post('apis/faq/update/{faq_id}', ['as' => 'faq.update', 'uses' => 'FaqController@update']);
        Route::post('apis/{api_id}/faq/update/{faq_id}', ['as' => 'apis.faq.update', 'uses' => 'FaqController@update']);
        Route::delete('apis/faq/destroy/{faq_id}', ['as' => 'faq.destroy', 'uses' => 'FaqController@destroy']);
        Route::delete('apis/{api_id}/faq/destroy/{faq_id}', ['as' => 'apis.faq.destroy', 'uses' => 'FaqController@destroy']);
        Route::delete('apis/{api_id}/faq/destroyapi/{faq_id}', ['as' => 'apis.faq.destroyapi', 'uses' => 'FaqController@destroyApi']);
        Route::post('apis/faq/copy/{faq_id}', ['as' => 'faq.copy', 'uses' => 'FaqController@copy']);
        Route::post('apis/{api_id}/faq/copy/{faq_id}', ['as' => 'apis.faq.copy', 'uses' => 'FaqController@copy']);
        Route::post('/apis/faq/displace_sort', ['as' => 'faq.displace_sort', 'uses' => 'FaqController@displaceSort']);
        Route::post('/apis/{api_id}/faq/displace_sort', ['as' => 'apis.faq.displace_sort', 'uses' => 'FaqController@displaceSort']);
        Route::post('/faq/copy-select', ['as' => 'faq.copy_select', 'uses' => 'FaqController@copySelect']);
        Route::post('/apis/faq/save_select_checked', ['as' => 'faq.save_select_checked', 'uses' => 'FaqController@saveSelectChecked']);
        Route::post('/apis/{api_id}/faq/save_select_checked', ['as' => 'apis.faq.save_select_checked', 'uses' => 'FaqController@saveSelectChecked']);
        
        Route::get('apis/faq-{faq_id}/apis', ['as' => 'faq.apis', 'uses' => 'FaqController@faqApis']);
        Route::get('apis/{api_id}/faq-{faq_id}/apis', ['as' => 'apis.faq.apis', 'uses' => 'FaqController@faqApis']);
        Route::post('apis/faq-{faq_id}/apis/save', ['as' => 'faq.apis.save', 'uses' => 'FaqController@faqApisSave']);
        Route::post('apis/{api_id}/faq-{faq_id}/apis/save', ['as' => 'apis.faq.apis.save', 'uses' => 'FaqController@faqApisSave']);
        
        Route::get('apis/languages/list', ['as' => 'languages.index', 'uses' => 'LanguageController@index']);
        Route::get('apis/{api_id}/languages/list', ['as' => 'apis.languages.index', 'uses' => 'LanguageController@index']);
        Route::get('apis/languages/add', ['as' => 'languages.add', 'uses' => 'LanguageController@add']);
        Route::get('apis/{api_id}/languages/add', ['as' => 'apis.languages.add', 'uses' => 'LanguageController@add']);
        Route::post('apis/languages/save', ['as' => 'languages.save', 'uses' => 'LanguageController@save']);
        Route::post('apis/{api_id}/languages/save', ['as' => 'apis.languages.save', 'uses' => 'LanguageController@save']);
        Route::get('apis/languages/edit/{language_id}', ['as' => 'languages.edit', 'uses' => 'LanguageController@edit']);
        Route::get('apis/{api_id}/languages/edit/{language_id}', ['as' => 'apis.languages.edit', 'uses' => 'LanguageController@edit']);
        Route::post('apis/languages/update/{language_id}', ['as' => 'languages.update', 'uses' => 'LanguageController@update']);
        Route::post('apis/{api_id}/languages/update/{language_id}', ['as' => 'apis.languages.update', 'uses' => 'LanguageController@update']);
        Route::delete('apis/languages/destroy/{language_id}', ['as' => 'languages.destroy', 'uses' => 'LanguageController@destroy']);
        Route::delete('apis/{api_id}/languages/destroy/{language_id}', ['as' => 'apis.languages.destroy', 'uses' => 'LanguageController@destroy']);
        Route::post('/apis/languages/displace_sort', ['as' => 'languages.displace_sort', 'uses' => 'LanguageController@displaceSort']);
        Route::post('/apis/{api_id}/languages/displace_sort', ['as' => 'apis.languages.displace_sort', 'uses' => 'LanguageController@displaceSort']);
        
        Route::get('/connection_errors/index', ['as' => 'apis.connection_errors.index', 'uses' => 'ConnectionErrorController@index']);
        Route::get('/connection_errors/destroy/{connection_error_id}', ['as' => 'apis.connection_errors.destroy', 'uses' => 'ConnectionErrorController@destroy']);
        Route::get('/connection_errors/destroy_ip_country/{connection_error_id}', ['as' => 'apis.connection_errors.destroy_ip_country', 'uses' => 'ConnectionErrorController@destroyIpCountry']);
        Route::get('/connection_errors/ip/{ip}', ['as' => 'apis.connection_errors.ip', 'uses' => 'ConnectionErrorController@ip']);
        Route::post('/connection_errors/delete_ip', ['as' => 'apis.connection_errors.delete_ip', 'uses' => 'ConnectionErrorController@deleteIp']);
        Route::post('/connection_errors/delete_ip_country', ['as' => 'apis.connection_errors.delete_ip_country', 'uses' => 'ConnectionErrorController@deleteIpCountry']);
    });
    
    Route::get('/servers/save_open_vpn', ['as' => 'servers.save_open_vpn', 'uses' => 'ServersController@saveOpenVpn']);
    Route::get('/servers/save_open_vpn_all', ['as' => 'servers.save_open_vpn_all', 'uses' => 'ServersController@saveOpenVpnAll']);
    Route::get('/servers/badservers', ['as' => 'servers.update_count_connect', 'uses' => 'ServersController@badservers']);
    
    Route::get('/badip/sync', ['as' => 'badip.sync', 'uses' => 'MainController@badipSync']);
    
    Route::get('/token/clear', ['as' => 'googletoken.clear', 'uses' => 'MainController@googletokenClear']);
    Route::get('/ip/clear', ['as' => 'ip.clear', 'uses' => 'MainController@ipClear']);
    
    // Не запускается. Включен в googletoken.clear
    Route::get('/servers/auto_switch_on_date', ['as' => 'servers.auto_switch_on_date', 'uses' => 'MainController@autoSwitchOnDate']);
    
    // Не запускается. Включен в googletoken.clear
    Route::get('/prokeys/clear_on_date', ['as' => 'prokeys.clear_on_date', 'uses' => 'MainController@clearOnDate']);
    
    Route::get('/prokeys/check', ['as' => 'prokeys.check', 'uses' => 'ProkeyController@check']);
});

Route::group([], function()
{
    Route::get('/api/key/activate', ['as' => 'api.key.activate', 'uses' => 'TokenController@apiKeyActivate']);
    Route::post('/api/key/activate', ['as' => 'api.key.activate.post', 'uses' => 'TokenController@apiKeyActivate']);
    Route::post('/api/key/generate', ['as' => 'api.key.activate.generate', 'uses' => 'TokenController@apiKeyGenerate']);
    
    Route::get('/api/{api_key}-servers', ['as' => 'servers.api.servers', 'uses' => 'ServersController@apiServers']);
    Route::get('/api/{api_key}-ss_servers', ['as' => 'servers.api.servers.ss', 'uses' => 'ServersController@apiServersSs']);
    Route::get('/api/{api_key}-server', ['as' => 'servers.api.server', 'uses' => 'ServersController@apiServer']);
    Route::get('/api/{api_key}-ss_server', ['as' => 'servers.api.server', 'uses' => 'ServersController@apiServerSs']);
    Route::get('/api/{api_key}-settings', ['as' => 'settings.get', 'uses' => 'SettingController@apiGet']);
    
    Route::get('/api/{api_key}/faq', ['as' => 'api.faq.get', 'uses' => 'FaqController@apiGet']);
    
    Route::get('/api/{api_key}/faq/{lng}', ['as' => 'api.faq.get', 'uses' => 'FaqController@apiGet']);
    
    Route::post('/api/{api_key}/connection_error', ['as' => 'api.connection_error.add', 'uses' => 'ConnectionErrorController@apiAddError']);
    Route::get('/api/{api_key}/connection_error', ['as' => 'api.connection_error.add', 'uses' => 'ConnectionErrorController@apiAddError']);
    
    Route::get('/api/stat-all', ['as' => 'api.stat.all', 'uses' => 'MainController@apiStatAllGet']);
    Route::get('/api/stat/{api_key}', ['as' => 'api.stat', 'uses' => 'MainController@apiStatGet']);
    
    Route::get('api/get-pro', ['as' => 'prokeys.api.get', 'uses' => 'ProkeyController@apiGetPro']);
    Route::post('api/get-pro', ['as' => 'prokeys.api.get.post', 'uses' => 'ProkeyController@apiGetPro']);
    Route::get('api/check-pro', ['as' => 'prokeys.api.check', 'uses' => 'ProkeyController@apiCheckPro']);
    Route::post('api/check-pro', ['as' => 'prokeys.api.check.post', 'uses' => 'ProkeyController@apiCheckPro']);
});
