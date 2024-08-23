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
  
  <form class="row g-3 needs-validation" method="post" action="{{ $item ? route('badip.update', ['id' => $item->id]) : route('badip.store') }}" enctype="multipart/form-data">
     {{ csrf_field() }}
     <input type="hidden" name="req_edit" value="1" />
     @if ($item)
     <input type="hidden" name="id" id="id" value="{{ $item->id }}" />
     @endif  
     
     <div class="form-group">
      <label for="ip" class="control-label">Ip <sup>*</sup></label>
      @if ($item)
      <input disabled="" type="text" class="form-control" id="ip" name="dataitem[ip]" value="{{ old('req_edit') ? old('dataitem.ip') : ($item ? $item->ip : '') }}" />
      @else
      <input type="text" class="form-control" id="ip" name="dataitem[ip]" value="{{ old('req_edit') ? old('dataitem.ip') : ($item ? $item->ip : '') }}" />
      @endif
      
      <div class="has-error" id="error_ip">
        @if($errors->has('ip'))
          @foreach($errors->get('ip') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    @if ($item)
    <button type="submit" class="btn btn-primary btn-block-xs submit">Save</button>
    @else
    <button type="submit" class="btn btn-primary btn-block-xs submit">Add</button>
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