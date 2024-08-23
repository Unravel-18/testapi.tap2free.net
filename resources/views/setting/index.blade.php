@extends('layouts.app')

@section('title')
<title>Настройки</title>
@endsection

@section('head')
@endsection

@section('main')
<div class="row">
  <div class="col-sm-12">
    <div>
      <h2>
      Настройки Апи: {{ $api->name }}
    </h2>
    </div>
    
    <div style="margin-top: 32px;">
      <form class="form-horizontal" method="post">
        {{ csrf_field() }}
        
        <div class="form-group">
          <label for="app_api_auth" class="col-sm-2 control-label">Доступ только для определенного header(app_id)</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('app_api_auth:'.$api->id))checked=""@endif class="form-control" name="app_api_auth" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="google_tokens" class="col-sm-2 control-label">Google tokens</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('google_tokens:'.$api->id))checked=""@endif class="form-control" name="google_tokens" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="app_id" class="col-sm-2 control-label">Header(app_id)</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="app_id" value="{{ \App\Helpers\Setting::value('app_id:'.$api->id) }}" />
            <input type="text" class="form-control" name="app_id_2" value="{{ \App\Helpers\Setting::value('app_id_2:'.$api->id) }}" />
            <input type="text" class="form-control" name="app_id_3" value="{{ \App\Helpers\Setting::value('app_id_3:'.$api->id) }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="app_id" class="col-sm-2 control-label">Ключ на выходе</label>
          <div class="col-sm-9">
            <div>{{ \App\Helpers\Setting::value('app_encryption_app_id:' . $api->id) }}</div>
            <div>
              {{ \App\Helpers\Setting::value('app_encryption_app_id_2_2:' . $api->id) }}
            </div>
            <div>
              {{ \App\Helpers\Setting::value('app_encryption_app_id_3:' . $api->id) }}
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="app_api_auth_encryption_app_id" class="col-sm-2 control-label">Доступ только по шифр. app_id</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('app_api_auth_encryption_app_id:'.$api->id))checked=""@endif class="form-control" name="app_api_auth_encryption_app_id" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="app_api_auth_encryption_method" class="col-sm-2 control-label">Метод шифрования</label>
          <div class="col-sm-9">
            <select class="form-control" name="app_api_auth_encryption_method">
              <option value="1" {{ \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$api->id) == "1" ? 'selected=""' : "" }}>1</option>
              <option value="2" {{ \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$api->id) == "2" ? 'selected=""' : "" }}>2</option>
              <option value="3" {{ \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$api->id) == "3" ? 'selected=""' : "" }}>3</option>
              <option value="4" {{ \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$api->id) == "4" ? 'selected=""' : "" }}>4</option>
              <option value="5" {{ \App\Helpers\Setting::value('app_api_auth_encryption_method:'.$api->id) == "5" ? 'selected=""' : "" }}>5</option>
            </select>
          </div>
        </div>
        
        <!--
        <div class="form-group">
          <label for="app_api_auth_encryption_method" class="col-sm-2 control-label">Настройка шифрования</label>
          <div class="col-sm-9">
            <select style="width: 100px;float: left;" class="form-control" name="app_encryption_setting_1">
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_1:'.$api->id) == '1')selected=""@endif value="1">Метод 1</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_1:'.$api->id) == '2')selected=""@endif value="2">Метод 2</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_1:'.$api->id) == '3')selected=""@endif value="3">Метод 3</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_1:'.$api->id) == '4')selected=""@endif value="4">Метод 4</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_1:'.$api->id) == '5')selected=""@endif value="5">Метод 5</option>
            </select>
            <select style="width: 100px;float: left;margin-left: 3px;" class="form-control" name="app_encryption_setting_2">
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_2:'.$api->id) == '1')selected=""@endif value="1">Метод 1</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_2:'.$api->id) == '2')selected=""@endif value="2">Метод 2</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_2:'.$api->id) == '3')selected=""@endif value="3">Метод 3</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_2:'.$api->id) == '4')selected=""@endif value="4">Метод 4</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_2:'.$api->id) == '5')selected=""@endif value="5">Метод 5</option>
            </select>
            <select style="width: 100px;float: left;margin-left: 3px;" class="form-control" name="app_encryption_setting_3">
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_3:'.$api->id) == '1')selected=""@endif value="1">Метод 1</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_3:'.$api->id) == '2')selected=""@endif value="2">Метод 2</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_3:'.$api->id) == '3')selected=""@endif value="3">Метод 3</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_3:'.$api->id) == '4')selected=""@endif value="4">Метод 4</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_3:'.$api->id) == '5')selected=""@endif value="5">Метод 5</option>
            </select>
            <select style="width: 100px;float: left;margin-left: 3px;" class="form-control" name="app_encryption_setting_4">
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_4:'.$api->id) == '1')selected=""@endif value="1">Метод 1</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_4:'.$api->id) == '2')selected=""@endif value="2">Метод 2</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_4:'.$api->id) == '3')selected=""@endif value="3">Метод 3</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_4:'.$api->id) == '4')selected=""@endif value="4">Метод 4</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_4:'.$api->id) == '5')selected=""@endif value="5">Метод 5</option>
            </select>
            <select style="width: 100px;float: left;margin-left: 3px;" class="form-control" name="app_encryption_setting_5">
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_5:'.$api->id) == '1')selected=""@endif value="1">Метод 1</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_5:'.$api->id) == '2')selected=""@endif value="2">Метод 2</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_5:'.$api->id) == '3')selected=""@endif value="3">Метод 3</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_5:'.$api->id) == '4')selected=""@endif value="4">Метод 4</option>
              <option @if(\App\Helpers\Setting::value('app_encryption_setting_5:'.$api->id) == '5')selected=""@endif value="5">Метод 5</option>
            </select>  
          </div>
        </div>
        -->
        
        <div class="form-group">
          <label for="app_api_auth_encryption_key" class="col-sm-2 control-label">Доп. код шифрования</label>
          <div class="col-sm-9">
            <div class="input-group">
              <div class="input-group-addon">1</div>
              <div>
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_1" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_1:'.$api->id) }}" />
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_1_2" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_1_2:'.$api->id) }}" />
              </div>
            </div>
            <div class="input-group">
              <div class="input-group-addon">2</div>
              <div>
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_2" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_2:'.$api->id) }}" />
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_2_2" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_2_2:'.$api->id) }}" />
              </div>
            </div>
            <div class="input-group">
              <div class="input-group-addon">3</div>
              <div>
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_3" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_3:'.$api->id) }}" />
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_3_2" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_3_2:'.$api->id) }}" />
              </div>
            </div>
            <div class="input-group">
              <div class="input-group-addon">4</div>
              <div>
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_4" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_4:'.$api->id) }}" />
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_4_2" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_4_2:'.$api->id) }}" />
              </div>
            </div>
            <div class="input-group">
              <div class="input-group-addon">5</div>
              <div>
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_5" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_5:'.$api->id) }}" />
                <input style="width: 50%;" type="text" class="form-control" name="app_api_auth_encryption_key_5_2" value="{{ \App\Helpers\Setting::value('app_api_auth_encryption_key_5_2:'.$api->id) }}" />
              </div>
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="list_up_when_start" class="col-sm-2 control-label">Config encryption</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('conf_e-api:'.$api->id))checked=""@endif class="form-control" name="conf_e" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="list_up_when_start" class="col-sm-2 control-label">Do not encrypt the config with the first header</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('crypto_config_no_1-api:'.$api->id))checked=""@endif class="form-control" name="crypto_config_no_1" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="min_version" class="col-sm-2 control-label">Минимальная версия</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="min_version" value="{{ \App\Helpers\Setting::value('min_version:'.$api->id) }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="list_up_when_start" class="col-sm-2 control-label">Strict Update</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('strict_u-api:'.$api->id))checked=""@endif class="form-control" name="strict_u" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="start_server_rate" class="col-sm-2 control-label">Start server рейтинг</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('start_server_rate-api:'.$api->id))checked=""@endif class="form-control" name="start_server_rate" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="connect_ads" class="col-sm-2 control-label">Отображать рекламу</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('connect_ads-api:'.$api->id))checked=""@endif class="form-control" name="connect_ads" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="connect_ads_day" class="col-sm-2 control-label">День показа рекламы</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="connect_ads_day" value="{{ \App\Helpers\Setting::value('connect_ads_day-api:'.$api->id) }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="try_pro_always_on_startup" class="col-sm-2 control-label">Показывать банер Try_PRO при каждом запуске</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('try_pro_always_on_startup-api:'.$api->id))checked=""@endif class="form-control" name="try_pro_always_on_startup" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="day_try_pro" class="col-sm-2 control-label">День показа Try_PRO</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="day_try_pro" value="{{ \App\Helpers\Setting::value('day_try_pro:'.$api->id) }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="list_up_when_start" class="col-sm-2 control-label">Загружать сервер лист при каждом запуске 24h</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('list_up_when_start-api:'.$api->id))checked=""@endif class="form-control" name="list_up_when_start" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="ads_p" class="col-sm-2 control-label">Пауза рекламы</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="ads_p" value="{{ \App\Helpers\Setting::value('ads_p-api:'.$api->id) }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="list_up_when_start" class="col-sm-2 control-label">Реклама на:</label>
          <div class="col-sm-9">
            <label style="font-weight: normal;">
            <span style="float: left;">
            <input type="checkbox" @if(\App\Helpers\Setting::value('exit_ad-api:'.$api->id))checked=""@endif class="form-control" name="exit_ad" style="width: 22px; height: 22px;" />
            </span>
            <span style="float: left;padding: 6px 0px 0px 6px;">отключении</span>
            </label> 
            
            <label style="margin-left: 6px;font-weight: normal;">
            <span style="float: left;">
            <input type="checkbox" @if(\App\Helpers\Setting::value('sl_ad-api:'.$api->id))checked=""@endif class="form-control" name="sl_ad" style="width: 22px; height: 22px;" />
            </span>
            <span style="float: left;padding: 6px 0px 0px 6px;">сервер листе</span>
            </label> 
            
            <label style="margin-left: 6px;;font-weight: normal;">
            <span style="float: left;">
            <input type="checkbox" @if(\App\Helpers\Setting::value('con_ad-api:'.$api->id))checked=""@endif class="form-control" name="con_ad" style="width: 22px; height: 22px;" />
            </span>
            <span style="float: left;padding: 6px 0px 0px 6px;">подключении</span>
            </label> 
            
            <label style="margin-left: 6px;;font-weight: normal;">
            <span style="float: left;">
            <input type="checkbox" @if(\App\Helpers\Setting::value('strt_ad-api:'.$api->id))checked=""@endif class="form-control" name="strt_ad" style="width: 22px; height: 22px;" />
            </span>
            <span style="float: left;padding: 6px 0px 0px 6px;">старте</span>
            </label> 
          </div>
        </div>

        <div class="form-group">
          <label for="pro_for_pro" class="col-sm-2 control-label">PRO servers for PRO only</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('pro_for_pro-api:'.$api->id))checked=""@endif class="form-control" name="pro_for_pro" style="width: 22px; height: 22px;" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="start_ads" class="col-sm-2 control-label">Startup ads</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="start_ads" value="{{ \App\Helpers\Setting::value('start_ads-api:'.$api->id) }}" />
          </div>
        </div>
        
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-9">
            <input class="btn btn-success" type="submit" value="Сохранить" />
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
