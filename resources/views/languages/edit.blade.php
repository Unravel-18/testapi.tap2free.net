@extends('layouts.app')

@section('title')
<title>Обновление языка</title>
@endsection

@section('page_container_header')
<div class="container-fluid page_header">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <h1>Обновление языка</h1>
      </div>
    </div>
  </div>
</div>
@endsection   
 
@section('content')
<div class="text_box">
  @if (Session::has('message'))
    <div class="alert alert-info">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <p>{{ Session::get('message') }}</p>
    </div>
  @endif
  @if (count($errors) > 0)
    <div class="alert alert-danger">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
        <ul>
            @foreach (array_slice($errors->all(), 0, 4) as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
  @endif
  <form class="form" id="event_form" action="{{ isset($api) ? route('apis.languages.update', ['api_id' => $api->id, 'language_id' => $obj->id]) : route('languages.update', ['language_id' => $obj->id]) }}" method="post" autocomplete="off" enctype="multipart/form-data">
    {{ csrf_field() }}
    <br />
    <button type="submit" class="btn btn-primary btn-block-xs submit">Сохранить</button>
    <br />
    <br />
    
    <input type="hidden" name="url_previous" value="{{ url()->previous() }}" />
    
    <input type="hidden" name="req_edit" value="1" />
    <input type="hidden" name="id" id="id" value="{{ $obj->id }}" />
    
    <div class="form-group">
      <label for="name" class="control-label">Наименование <sup>*</sup></label>
      <input type="text" class="form-control" id="name" name="name" value="{{ old('name', old('req_edit') ? null : $obj->name) }}" />
      <div class="has-error" id="error_name">
        @if($errors->has('name'))
          @foreach($errors->get('name') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="code" class="control-label">Код <sup>*</sup></label>
      <input type="text" class="form-control" id="code" name="code" value="{{ old('code', old('req_edit') ? null : $obj->code) }}" />
      <div class="has-error" id="error_code">
        @if($errors->has('code'))
          @foreach($errors->get('code') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <button type="submit" class="btn btn-primary btn-block-xs submit">Сохранить</button>
  </form>
  
  <form id="upload_form" action="" method="POST" enctype="multipart/form-data" class="hidden"><input type="hidden" id="UPLOAD_IDENTIFIER" name="X-Progress-ID"><input type="hidden" name="source" value="" /></form>
  <br /><br /><br />
</div>
@endsection 