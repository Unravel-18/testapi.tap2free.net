@extends('layouts.app')

@section('title')
<title>Обновление сервера</title>
@endsection

@section('page_container_header')
<div class="container-fluid page_header">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">
        <h1>Обновление сервера</h1>
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
  <form class="form" id="event_form" action="{{ isset($api) ? route('apis.servers.update', ['server_id' => $server->id, 'api_id' => $api->id]) : route('servers.update', ['server_id' => $server->id]) }}" method="post" autocomplete="off" enctype="multipart/form-data">
    {{ csrf_field() }}
    <br />
    <button type="submit" class="btn btn-primary btn-block-xs submit">Сохранить</button>
    
    <a style="float: right;" href="{{ isset($api) ? route('apis.servers.apis', ['server_id' => $server->id, 'api_id' => $api->id]) : route('servers.apis', ['server_id' => $server->id]) }}" class='btn btn-info btn-block-xs' type="button">Апи</a>
    
    <br />
    <br />
    
    <input type="hidden" name="url_previous" value="{{ url()->previous() }}" />
    
    <input type="hidden" name="req_edit" value="1" />
    <input type="hidden" name="id" id="id" value="{{ $server->id }}" />
    
    <div class="form-group">
      <label for="status" class="control-label">Active</label>
      <input type="checkbox" @if($server->status)checked=""@endif class="form-control" name="status" value="1" style="width: 22px; height: 22px;" />
      <div class="has-error" id="error_server">
        @if($errors->has('status'))
          @foreach($errors->get('status') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="server" class="control-label">Hoster <sup>*</sup></label>
      <input type="text" class="form-control" id="server" name="server" value="{{ old('server', old('req_edit') ? null : $server->server) }}" />
      <div class="has-error" id="error_server">
        @if($errors->has('server'))
          @foreach($errors->get('server') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="country" class="control-label">Страна <sup>*</sup></label>
      <input type="text" class="form-control" id="country" name="country" value="{{ old('country', old('req_edit') ? null : $server->country) }}" />
      <div class="has-error" id="error_server">
        @if($errors->has('country'))
          @foreach($errors->get('country') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
  
    <div class="attachment file form-group">
      <div class="buttons" id="img_flag_form">
        <div class="btn btn-default btn-file upload"><span class="glyphicon glyphicon-paperclip"></span>
          Add Flag 21х15<input type="file" name="img_flag" id="inpt_img_flag" class="uploader" />
        </div>
      </div>
      <input type="hidden" name="img_flag_f" id="img_flag_f" value="{{ old('img_flag_f') }}" />
    </div>
    
    <div class="form-group" id="upload_img_flag">
      @if($server->img_flag)
      <div class="block-img-upload" data-id="{{ $server->id }}"><div><span class="glyphicon glyphicon-remove" onclick="deleteImgFlag(this)"></span></div><a href="/images/{{ $server->img_flag }}"><img src="/images/{{ $server->img_flag }}" style="max-width:200px;" /></a></div>
      @endif
    </div>
    
    <div class="attachment file form-group">
      <div class="buttons" id="img_map_form">
        <div class="btn btn-default btn-file upload"><span class="glyphicon glyphicon-paperclip"></span>
          Add Map 360х156<input type="file" name="img_map" id="inpt_img_map" class="uploader" />
        </div>
      </div>
      <input type="hidden" name="img_map_f" id="img_map_f" value="{{ old('img_map_f') }}" />
    </div>
    
    <div class="form-group">
      <label for="name" class="control-label">Name <sup>*</sup></label>
      <input type="text" class="form-control" id="name" name="name" value="{{ old('name', old('req_edit') ? null : $server->name) }}" />
      <div class="has-error" id="error_name">
        @if($errors->has('name'))
          @foreach($errors->get('name') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="ip" class="control-label">IP <sup>*</sup></label>
      <input type="text" class="form-control" id="ip" name="ip" value="{{ old('ip', old('req_edit') ? null : $server->ip) }}" />
      <div class="has-error" id="error_ip">
        @if($errors->has('ip'))
          @foreach($errors->get('ip') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="attachment file form-group">
      <div class="buttons" id="img_map_form">
        <div class="btn btn-default btn-file upload"><span class="glyphicon glyphicon-paperclip"></span>
          Add OpenVPN <input name="files[]" type="file" id="files" multiple="multiple" />
        </div>
        <button class='btn btn-danger' onclick="return clickDeleteCert(this);" title="Удалить" type="button">Cert delete <span></span></button>
      </div>
      
      <div id="names_files" style="color: #0000CD;"></div>
    </div>
    
    <div class="form-group">
      <label for="ss_config" class="control-label">SS Conf</label>
      <input type="text" class="form-control" id="ss_config" name="ss_config" value="{{ old('ss_config', old('req_edit') ? null : $server->ss_config) }}" />
      <div class="has-error" id="error_name">
        @if($errors->has('ss_config'))
          @foreach($errors->get('ss_config') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="count_url" class="control-label">Count connection link</label>
      <input type="text" class="form-control" id="count_url" name="count_url" value="{{ old('count_url', old('req_edit') ? null : $server->count_url) }}" />
      <div class="has-error" id="error_server">
        @if($errors->has('count_url'))
          @foreach($errors->get('count_url') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="url_speed" class="control-label">Speed link</label>
      <input type="text" class="form-control" id="url_speed" name="url_speed" value="{{ old('url_speed', old('req_edit') ? null : $server->url_speed) }}" />
      <div class="has-error" id="error_server">
        @if($errors->has('url_speed'))
          @foreach($errors->get('url_speed') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="max_connection_rating" class="control-label">Max connection rating</label>
      <input type="text" class="form-control" id="max_connection_rating" name="max_connection_rating" value="{{ old('max_connection_rating', old('req_edit') ? null : $server->max_connection_rating) }}" />
      <div class="has-error" id="error_server">
        @if($errors->has('max_connection_rating'))
          @foreach($errors->get('max_connection_rating') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>
    
    <div class="form-group">
      <label for="auto_switch_on_date" class="control-label">Auto switch on date</label>
      <input type="number" class="form-control" id="auto_switch_on_date" name="auto_switch_on_date" value="{{ old('auto_switch_on_date', old('req_edit') ? null : $server->auto_switch_on_date) }}" />
      <div class="has-error" id="error_server">
        @if($errors->has('auto_switch_on_date'))
          @foreach($errors->get('auto_switch_on_date') as $message)
            <div class="help-block">{{ $message }}</div>
          @endforeach
        @endif
      </div>
    </div>

      <div class="form-group">
          <label for="services" class="control-label">Services</label>
          <input type="text" class="form-control" id="services" name="services" value="{{ old('services', old('req_edit') ? null : $server->services) }}" />
          <div class="has-error" id="error_server">
              @if($errors->has('services'))
                  @foreach($errors->get('services') as $message)
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

@push('scripts')
<script>
function clickDeleteCert(obj) {
    event.preventDefault();
    if (event.stopPropagation) {
        event.stopPropagation();
    } else {
        event.cancelBubble = true;
    }
    
    if (confirm('Дествительно хотите удалить сертификат?')) {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('servers.deletecert', ['server_id' => $server->id])}}",
		    	data: {
		    	 _method: 'DELETE',
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
			     $(obj).find('span').html('<i class="fa fa-spinner" aria-hidden="true"></i>');
		    	},
			    error: function (result) {
			     $(obj).find('span').html('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>');
			     //alert(JSON.stringify(result));
			    },
                success: function(result) {
                 $(obj).find('span').html('<i class="fa fa-check-circle" aria-hidden="true"></i>');
			     //alert(JSON.stringify(result));
		    	},
	    	});
    }
    
    return false;
}
</script>
@endpush
