@extends('layouts.app')

@section('title')
<title>Обновление вопроса</title>
@endsection

@section('page_container_header')
<div class="container-fluid page_header">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <h1>Обновление вопроса</h1>
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
  <form class="form" id="event_form" action="{{ isset($api) ? route('apis.faq.update', ['api_id' => $api->id, 'faq_id' => $obj->id]) : route('faq.update', ['faq_id' => $obj->id]) }}" method="post" autocomplete="off" enctype="multipart/form-data">
    {{ csrf_field() }}
    <br />
    <button type="submit" class="btn btn-primary btn-block-xs submit">Сохранить</button>
    <br />
    <br />
    
    <input type="hidden" name="url_previous" value="{{ url()->previous() }}" />
    
    @foreach($languages as $key => $language)
    @if($key>0)
    <hr style="margin-top: 32px;" />
    @endif
    <div class="form-group">
      <label for="{{'questions_'.$language->id}}" class="control-label">Вопрос - Ответ. Язык: {{ $language->name }}</label>
      <input type="text" class="form-control" id="{{'questions_'.$language->id}}" name="questions[{{ $language->id }}]" value="{{ old('questions.'.$language->id, $obj->getQuestionByLanguage($language)) }}" />
      <div class="has-error" id="error_{{'questions_'.$language->id}}">
        @if($errors->has('questions.'.$language->id))
          @foreach($errors->get('questions.'.$language->id) as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    <div class="form-group">
      <textarea class="form-control" id="{{'answers_'.$language->id}}" name="answers[{{ $language->id }}]">{{ old('answers.'.$language->id, $obj->getAnswersByLanguage($language)) }}</textarea>
      <div class="has-error" id="error_{{'answers_'.$language->id}}">
        @if($errors->has('answers.'.$language->id))
          @foreach($errors->get('answers.'.$language->id) as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    @endforeach
    
    <button type="submit" class="btn btn-primary btn-block-xs submit">Сохранить</button>
  </form>
  
  <form id="upload_form" action="" method="POST" enctype="multipart/form-data" class="hidden"><input type="hidden" id="UPLOAD_IDENTIFIER" name="X-Progress-ID"><input type="hidden" name="source" value="" /></form>
  <br /><br /><br />
</div>
@endsection 