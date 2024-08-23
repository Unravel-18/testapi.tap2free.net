@extends('layouts.app')

@section('title')
<title>Api Key</title>
@endsection

@section('title_header')
Api Key
@endsection

@section('page_container_header')
<div class="container-fluid page_header">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <h3>
@if($item)
{{ $item->name }}
@else
New Api Key
@endif
        </h3>
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
  
  <form class="row g-3 needs-validation" method="post" action="{{ $item ? route('tokens.update', ['id' => $item->id]) : route('tokens.store') }}" enctype="multipart/form-data">
     {{ csrf_field() }}
     <input type="hidden" name="req_edit" value="1" />
     @if ($item)
     <input type="hidden" name="id" id="id" value="{{ $item->id }}" />
     @endif  
     
     <div class="form-group">
      <label for="short_code" class="control-label">Shortcode <sup>*</sup></label>
      @if ($item)
      <input readonly="" type="text" class="form-control" id="short_code" name="dataitem[short_code]" value="{{ old('req_edit') ? old('dataitem.short_code') : ($item ? $item->short_code : '') }}" />
      @else
      <div class="input-group">
        <input type="text" class="form-control" id="short_code" name="dataitem[short_code]" value="{{ old('req_edit') ? old('dataitem.short_code') : ($item ? $item->short_code : '') }}" />
        <span onclick="clickGenerateKey()" style="cursor: pointer;" class="input-group-addon">Generate</span>
      </div>
      @endif
      
      <div class="has-error" id="error_short_code">
        @if($errors->has('short_code'))
          @foreach($errors->get('short_code') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
     
    <div class="form-group">
      <label for="days" class="control-label">Days <sup>*</sup></label>
      <input type="text" class="form-control" id="days" name="dataitem[days]" value="{{ old('req_edit') ? old('dataitem.days') : ($item ? $item->days : '') }}" />
      
      <div class="has-error" id="error_days">
        @if($errors->has('days'))
          @foreach($errors->get('days') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
     
    <div class="form-group">
      <label for="email" class="control-label">Email <sup>*</sup></label>
      <input type="email" class="form-control" id="email" name="dataitem[email]" value="{{ old('req_edit') ? old('dataitem.email') : ($item ? $item->email : '') }}" />
      
      <div class="has-error" id="error_email">
        @if($errors->has('email'))
          @foreach($errors->get('email') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    @if ($item)
    <button type="submit" class="btn btn-primary btn-block-xs submit">Save shortcode</button>
    @else
    <button type="submit" class="btn btn-primary btn-block-xs submit">Add shortcode</button>
    @endif
  </form>
  
  <form id="upload_form" action="" method="POST" enctype="multipart/form-data" class="hidden"><input type="hidden" id="UPLOAD_IDENTIFIER" name="X-Progress-ID"><input type="hidden" name="source" value="" /></form>
  <br /><br /><br />
</div>
@endsection

@push('styles')
<style>
</style>
@endpush

@push('scripts')
<script>
function clickGenerateKey() {
    $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{ route("tokens.key.generate") }}",
		    	data: {
                },
                type: 'POST',
                dataType: 'json',
		    	success: function(result) {
			        if(result.status == 1){
			            $("#short_code").val(result.key);
			        } 
		    	},
			    error: function (result) {
			    }
   	});
}

function deleteCategoryIcon(obj){
    var obj_block = $(obj).parent();
    
    if(confirm('Вы действительно хотите удалить?')) {
        if(obj_block.attr('data-id')) {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/admin/categories/delete_icon",
		    	data: {
		    	   id: obj_block.attr('data-id')
                },
                type: 'POST',
                dataType: 'json',
		    	success: function(result) {
			        if(result.status == 1){
			            obj_block.remove();
			        } 
		    	},
			    error: function (result) {
			    }
	    	});
      }
    }
}
</script>
@endpush