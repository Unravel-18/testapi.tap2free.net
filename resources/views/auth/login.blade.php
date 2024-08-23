@extends('layouts.app')

@section('title')
<title>Вход</title>
@endsection

@section('head')
<h1 style="text-align: center;">Вход</h1>
@endsection

@section('page_container_body')
<div class="container page_body">
<div class="row">
<div class="col-xs-12 page_content">
<div class="container-fluid cols_table show_visited content-block">
<div class="text_box">
  <div class="col-sm-12">
    <form class="form-horizontal" role="form" method="POST" action="{{ route('auth.auth') }}">
      {{ csrf_field() }}
      <div class="form-group{{ $errors->has('login') ? ' has-error' : '' }}">
        <label for="login" class="col-md-2 control-label">Логин</label>
        <div class="col-md-8">
          <input id="login" type="text" class="form-control" name="login" value="{{ old('login') }}" required='' autofocus='' />
          @if ($errors->has('login'))
          <span class="help-block">
          <strong>{{ $errors->first('login') }}</strong>
          </span>
          @endif
        </div>
      </div>
      <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
        <label for="password" class="col-md-2 control-label">Пароль</label>
        <div class="col-md-8">
          <input id="password" type="password" class="form-control" name="password" required='' />
          @if ($errors->has('password'))
          <span class="help-block">
          <strong>{{ $errors->first('password') }}</strong>
          </span>
          @endif
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-8 col-md-offset-2" style="text-align: center;">
          <button type="submit" class="btn btn-primary">
          Войти
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
</div>
</div>
</div>
</div>
@endsection 