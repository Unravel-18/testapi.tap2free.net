@extends('layouts.app')

@section('title')
<title>Настройки Pro Keys</title>
@endsection

@section('head')
@endsection

@section('main')
<div class="row">
  <div class="col-sm-12">
    <div>
      <h2>Настройки Pro Keys</h2>
    </div>
    
    <div style="margin-top: 32px;">
      <form class="form-horizontal" action="{{ route('prokeys.settings.save') }}" method="post">
        {{ csrf_field() }}
        
        <div class="form-group">
          <label for="get_pro_limit_24h" class="col-sm-2 control-label">get-pro limit 24h:</label>
          <div class="col-sm-9">
            <input id="get_pro_limit_24h" type="number" class="form-control" name="get_pro_limit_24h" value="{{ \App\Helpers\Setting::value('get_pro_limit_24h') }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="pro_id_limit_1h" class="col-sm-2 control-label">pro-id limit 1h:</label>
          <div class="col-sm-9">
            <input id="pro_id_limit_1h" type="number" class="form-control" name="pro_id_limit_1h" value="{{ \App\Helpers\Setting::value('pro_id_limit_1h') }}" />
          </div>
        </div>
        
        <div class="form-group">
          <label for="pro_id_limit_24h" class="col-sm-2 control-label">pro-id limit 24h:</label>
          <div class="col-sm-9">
            <input id="pro_id_limit_24h" type="number" class="form-control" name="pro_id_limit_24h" value="{{ \App\Helpers\Setting::value('pro_id_limit_24h') }}" />
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
