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
      <h2>Общие настройки</h2>
    </div>
    
    <div style="margin-top: 32px;">
      <form class="form-horizontal" action="{{ route('setting.servers.save') }}" method="post">
        {{ csrf_field() }}
        
        <div class="form-group">
          <label for="app_id" class="col-sm-2 control-label">Время оповещения не доступных серверов в минутах:</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="minute_not_available" value="{{ \App\Helpers\Setting::value('minute_not_available') }}" />
          </div>
        </div>
        
        <!--
        <div class="form-group">
          <label for="app_api_auth_encryption_app_id" class="col-sm-2 control-label">Шифрование по настройкам</label>
          <div class="col-sm-9">
            <input type="checkbox" @if(\App\Helpers\Setting::value('app_encryption_setting'))checked=""@endif class="form-control" name="app_encryption_setting" style="width: 22px; height: 22px;" />
          </div>
        </div>
        -->
        
        <div class="form-group">
          <label for="app_id" class="col-sm-2 control-label">Время оповещения Connection error(минуты):</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="time_connection_error" value="{{ \App\Helpers\Setting::value('time_connection_error') }}" />
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
