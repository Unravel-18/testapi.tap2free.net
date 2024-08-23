@extends('layouts.app')

@section('title')
<title>Настройки Ключей</title>
@endsection

@section('head')
@endsection

@section('main')
<div class="row">
  <div class="col-sm-12">
    <div>
      <h2>Настройки Ключей</h2>
    </div>
    
    <div style="margin-top: 32px;">
      <form class="form-horizontal" action="{{ route('tokens.settings.save') }}" method="post">
        {{ csrf_field() }}
        
        <div class="form-group">
          <label for="short_key_retry_error_24h" class="col-sm-2 control-label">Short key retry error 24h:</label>
          <div class="col-sm-9">
            <input id="short_key_retry_error_24h" type="number" class="form-control" name="short_key_retry_error_24h" value="{{ \App\Helpers\Setting::value('short_key_retry_error_24h') }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="short_key_reset_limit_24h" class="col-sm-2 control-label">Short key reset limit 24h:</label>
          <div class="col-sm-9">
            <input id="short_key_reset_limit_24h" type="number" class="form-control" name="short_key_reset_limit_24h" value="{{ \App\Helpers\Setting::value('short_key_reset_limit_24h') }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="app_id_limit_1h" class="col-sm-2 control-label">App-id limit 1h:</label>
          <div class="col-sm-9">
            <input id="app_id_limit_1h" type="number" class="form-control" name="app_id_limit_1h" value="{{ \App\Helpers\Setting::value('app_id_limit_1h') }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="app_id_limit_24h" class="col-sm-2 control-label">App-id limit 24h:</label>
          <div class="col-sm-9">
            <input id="app_id_limit_24h" type="number" class="form-control" name="app_id_limit_24h" value="{{ \App\Helpers\Setting::value('app_id_limit_24h') }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="app_id_limit_24h" class="col-sm-2 control-label">Secret Key:</label>
          <div class="col-sm-9">
            <input id="app_id_limit_24h" type="text" class="form-control" name="tokens_secret_key" value="{{ \App\Helpers\Setting::value('tokens_secret_key') }}" />
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
