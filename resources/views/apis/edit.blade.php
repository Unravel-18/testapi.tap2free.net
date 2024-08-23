@extends('layouts.app')

@section('title')
<title>Обновление Api</title>
@endsection

@section('page_container_header')
<div class="container-fluid page_header">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <h1>Обновление Api</h1>
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
  <form class="form" id="api_form" action="{{ route('apis.update', $api->id) }}" method="post" autocomplete="off" enctype="multipart/form-data">
    {{ csrf_field() }}
    <br />
    <button type="submit" class="btn btn-primary btn-block-xs submit">Сохранить</button>
    <br />
    <br />
    
    <input type="hidden" name="req_edit" value="1" />
    <input type="hidden" name="id" id="id" value="{{ $api->id }}" />
    
    <div class="form-group">
      <label for="name" class="control-label">Имя <sup>*</sup></label>
      <input type="text" class="form-control" id="name" name="name" value="{{ old('name', old('req_edit') ? null : $api->name) }}" />
      <div class="has-error" id="error_name">
        @if($errors->has('name'))
          @foreach($errors->get('name') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="package" class="control-label">Package <sup>*</sup></label>
      <input type="text" class="form-control" id="package" name="package" value="{{ old('package', old('req_edit') ? null : $api->package) }}" />
      <div class="has-error" id="error_name">
        @if($errors->has('package'))
          @foreach($errors->get('package') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="key" class="control-label">Ключ <sup>*</sup></label>
      <input type="text" class="form-control" id="key" name="key" value="{{ old('key', old('req_edit') ? null : $api->key) }}" />
      <div class="has-error" id="error_server">
        @if($errors->has('key'))
          @foreach($errors->get('key') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
  
    <div class="attachment file form-group">
      <div class="buttons" id="img">
        <div class="btn btn-default btn-file upload"><span class="glyphicon glyphicon-paperclip"></span>
          Добавить Иконку<input type="file" name="img" id="img" class="uploader" />
        </div>
      </div>
      <div class="has-error" id="error_img">
        @if($errors->has('img'))
          @foreach($errors->get('img') as $message)
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