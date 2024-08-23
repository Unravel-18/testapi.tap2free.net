@extends('layouts.app')

@section('title')
<title>Pro Key</title>
@endsection

@section('title_header')
Pro Key
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
New Pro Key
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
  
  <form class="row g-3 needs-validation" method="post" action="{{ $item ? route('prokeys.update', ['id' => $item->id]) : route('prokeys.store') }}" enctype="multipart/form-data">
     {{ csrf_field() }}
     <input type="hidden" name="req_edit" value="1" />
     @if ($item)
     <input type="hidden" name="id" id="id" value="{{ $item->id }}" />
     @endif
     
    <div class="form-group">
      <label for="status" class="control-label">status</label>
      <select class="form-control" id="status" name="dataitem[status]">
        <option value="1" @if(old('req_edit') ? old('dataitem.status') : ($item ? $item->status : '') == '1') selected="" @endif>Active</option>
        <option value="2" @if(old('req_edit') ? old('dataitem.status') : ($item ? $item->status : '') == '2') selected="" @endif>Banned</option>
      </select>
      
      <div class="has-error" id="error_status">
        @if($errors->has('status'))
          @foreach($errors->get('status') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
     
    <div class="form-group">
      <label for="package_name" class="control-label">packageName</label>
      <input type="text" class="form-control" id="package_name" name="dataitem[package_name]" value="{{ old('req_edit') ? old('dataitem.package_name') : ($item ? $item->package_name : '') }}" />
      
      <div class="has-error" id="error_package_name">
        @if($errors->has('package_name'))
          @foreach($errors->get('package_name') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
     
    <div class="form-group">
      <label for="subscription_id" class="control-label">subscriptionId</label>
      <input type="text" class="form-control" id="subscription_id" name="dataitem[subscription_id]" value="{{ old('req_edit') ? old('dataitem.subscription_id') : ($item ? $item->subscription_id : '') }}" />
      
      <div class="has-error" id="error_subscription_id">
        @if($errors->has('subscription_id'))
          @foreach($errors->get('subscription_id') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
     
    <div class="form-group">
      <label for="token" class="control-label">token</label>
      <input type="text" class="form-control" id="token" name="dataitem[token]" value="{{ old('req_edit') ? old('dataitem.token') : ($item ? $item->token : '') }}" />
      
      <div class="has-error" id="error_token">
        @if($errors->has('token'))
          @foreach($errors->get('token') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
     
    <div class="form-group">
      <label for="payment_state" class="control-label">paymentState</label>
      <input type="text" class="form-control" id="payment_state" name="dataitem[payment_state]" value="{{ old('req_edit') ? old('dataitem.payment_state') : ($item ? $item->payment_state : '') }}" />
      
      <div class="has-error" id="error_payment_state">
        @if($errors->has('payment_state'))
          @foreach($errors->get('payment_state') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
     
    <div class="form-group">
      <label for="pro_id" class="control-label">proId</label>
      <input type="text" class="form-control" id="pro_id" name="dataitem[pro_id]" value="{{ old('req_edit') ? old('dataitem.pro_id') : ($item ? $item->pro_id : '') }}" />
      
      <div class="has-error" id="error_pro_id">
        @if($errors->has('pro_id'))
          @foreach($errors->get('pro_id') as $message)
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