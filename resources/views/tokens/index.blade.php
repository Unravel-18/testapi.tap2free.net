@extends('layouts.app')

@section('title')
<title>Api Keys</title>
@endsection

@section('head')
@endsection

@section('main')
<link href="/css/sumoselect.min.css" rel="stylesheet" />
<style>
.find_field[type=text], select.find_field {
    width: 100%;
    padding: 1px 2px;
    font-size: 13px;
    height: 25px;
    vertical-align: middle;
    min-width: 50px;
    font-weight: normal;
} 

.tbl-servers th, .tbl-servers td {
    vertical-align: middle!important;
    text-align: center;
}  
.tr-bg-active {
    background-color: grey!important;
}
</style>
@if (Session::has('message'))
    <div class="alert alert-info">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <p>{{ Session::get('message') }}</p>
    </div>
  @endif

<div class="row">
  <div class="col-sm-12">

<h2>Api Keys</h2>


<div style="padding-bottom: 24px;">
      <table>
      <tr>
      <td>
      <a class="btn btn-default btn-sm" href="{{ route('tokens.add', []) }}" style="float: left;">Add</a>
      </td>
      <td>
      <a class="btn btn-default btn-sm" href="{{ route('tokens.settings', []) }}" style="float: left;">Settings</a>
      </td>
      <td>
      <span class="btn btn-default btn-sm" onclick="deleteSelectTokens()" style="float: left;">Delete</span>
      </td>
      </tr>
      </table>
    </div>

    {{ $items->links() }}
    <div>
count all: {{ $countall }}
</div>

<style>
.SumoSelectStatus .SumoSelect {
    width: 140px;
}
</style>

<form id="formList">
	<table class="table table-striped table-bordered tbl-servers">
		<thead>
			<tr>
                <th><input type="checkbox" id="select_servers_all" /></th>
                <th style="vertical-align: middle;"><input value="{{ urldecode(request('search.short_code')) }}" name="search[short_code]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
                <th style="vertical-align: middle;font-size: 13px;width: 150px!important;" class="SumoSelectStatus">
                  <select size="width: 150px!important;" multiple="" name="search[status]" class="find_field multipleSelect" onchange="changeFindField(this)">
                    <option value="all" @if(stripos(urldecode(request('search.status')), 'all') !== false) selected="" @endif>All</option>
                    <option value="active" @if(stripos(urldecode(request('search.status')), 'active') !== false) selected="" @endif>Active</option>
                    <option value="not activated" @if(stripos(urldecode(request('search.status')), 'not activated') !== false) selected="" @endif>Not Activated</option>
                    <option value="expired" @if(stripos(urldecode(request('search.status')), 'expired') !== false) selected="" @endif>Expired</option>
                    <option value="banned" @if(stripos(urldecode(request('search.status')), 'banned') !== false) selected="" @endif>Banned</option>
                  </select>
                </th>
                <th style="vertical-align: middle;">
                  <select name="search[subscribe]" class="find_field" onchange="changeFindField(this)">
                    <option value="">All</option>
                    <option value="1" @if(request('search.subscribe') == '1') selected="" @endif>yes</option>
                    <option value="0" @if(urldecode(request('search.subscribe')) == '0') selected="" @endif>no</option>
                  </select>
                </th>
                <th style="vertical-align: middle;"><input value="{{ urldecode(request('search.days')) }}" name="search[days]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.email')) }}" name="search[email]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.app_id')) }}" name="search[app_id]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.created_at')) }}" name="search[created_at]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.activated_at')) }}" name="search[activated_at]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"></th>
				<th colspan="3"></th>
		 	</tr>
		</thead>
		<thead>
			<tr>
				<th style="vertical-align: middle;"></th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'short_code' ? '-' : '' }}short_code" href="?sort={{ $sort == 'short_code' ? '-' : '' }}short_code">Short code</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'status' ? '-' : '' }}status" href="?sort={{ $sort == 'status' ? '-' : '' }}status">Status</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'subscribe' ? '-' : '' }}subscribe" href="?sort={{ $sort == 'subscribe' ? '-' : '' }}subscribe">Subscribe</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'days' ? '-' : '' }}days" href="?sort={{ $sort == 'days' ? '-' : '' }}days">Days</a></th>
				<th style="vertical-align: middle;">Left</th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'email' ? '-' : '' }}email" href="?sort={{ $sort == 'email' ? '-' : '' }}email">Email</a></th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'app_id' ? '-' : '' }}app_id" href="?sort={{ $sort == 'app_id' ? '-' : '' }}app_id">App-id</a></th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'created_at' ? '-' : '' }}created_at" href="?sort={{ $sort == 'created_at' ? '-' : '' }}created_at">Created</a></th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'activated_at' ? '-' : '' }}activated_at" href="?sort={{ $sort == 'activated_at' ? '-' : '' }}activated_at">Activated</a></th>
                <th style="vertical-align: middle;">Requests</th>
			    <th colspan="3"></th>
		 	</tr>
		</thead>

		<tbody>
			@foreach ($items as $item)
				<tr class="tr-server" data-id="{{{ $item->id }}}" id="trItem_{{{ $item->id }}}">
					<td><input type="checkbox" name="select_items[]" class="select_servers" data-id="{{{ $item->id }}}" value="{{{ $item->id }}}" /></td>
                    <td>{{{ $item->short_code }}}</td>
					<td style="width: 115px;">
                      <select style="font-size: 12px!important;font-weight: bold!important;" class="" onchange="changeItemStatus(this)">
                         <option value="active" @if($item->status == 'active') selected="" @endif>Active</option>
                         <option value="not activated" @if($item->status == 'not activated') selected="" @endif>Not Activated</option>
                         <option value="expired" @if($item->status == 'expired') selected="" @endif>Expired</option>
                         <option value="banned" @if($item->status == 'banned') selected="" @endif>Banned</option>
                       </select>
                    </td>
					<td>
                    @if($item->subscribe == "1")
                    <i class="fa fa-check" aria-hidden="true"></i>
                    @else
                    @endif
                    </td>
					<td>{{{ $item->days }}}</td>
					<td style="white-space: nowrap;">
                      <div>{{{ $item->getLeft() }}}</div>
                      @if($item->subscribe == "1")
                      @if($item->subscribe_time)
                      <div>{{ date("d.m.Y H:m", $item->subscribe_time) }}</div>
                      @endif
                      @endif
                    </td>
					<td>{{{ $item->email }}}</td>
					<td class="td_app_id">{{{ $item->app_id }}}</td>
					<td>{{{ date_create($item->created_at)->format("Y-m-d H:i") }}}</td>
					<td>{{{ $item->activated_at ? date_create($item->activated_at)->format("Y-m-d H:i") : '' }}}</td>
					<td>
                      <div style="white-space: nowrap;">{{{ $item->app_hour_count }}}({{{ $item->app_24hour_count }}})</div>
                    </td>

                    <td style="text-align: center;"><a class="btn btn-info btn-sm glyphicon glyphicon-cog" title="Изменить" href="{{ route('tokens.edit', ['id' => $item->id]) }}"></a></td>
                    <td style="text-align: center;"><button onclick="clickAppIdClear(this)" data-href="{{ route('tokens.app_id.clear', ['id' => $item->id]) }}" class='btn btn-sm btn-warning' title="Очистить app-id" type="button"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                    <td style="text-align: center;"><button data-href="{{ route('tokens.delete', ['id' => $item->id]) }}" class='btn btn-sm btn-danger glyphicon glyphicon-trash btn-click-confirm' title="Удалить" type="button"></button></td>
				</tr>
			@endforeach
		</tbody>
	</table>
</form>
  </div>
</div>
@endsection

@push('scripts')
<script src="/js/jquery.sumoselect.min.js"></script>

<script>
$(".chosen-select").chosen();

document.body.onkeydown = document.body.onkeyup = document.body.onkeypress = handleCklick;

var isPressShift = false;
var checkboxStartPressShift = null;
var checkboxEndPressShift = null;

function deleteSelectTokens () {
    var ids = [];
    
    $(".select_servers:checked").each(function () {
        ids.push($(this).val());
    });
    
    if (ids.length > 0) {
        if (confirm("Вы действительно хотите удалить выбранное?")) {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{ route("tokens.select.delete") }}",
		    	data: {
		    	   ids: ids,
		    	},
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {
			    },
                success: function(result) {
                    if (result.status == 1) {
                        for (var i in ids) {
                            $("#trItem_"+ids[i]).remove();
                        }
                    }
                },
   	        });
        }
    } else {
        alert("Ничего не выбрано!!!");
    }
    
    
}

function changeItemStatus(obj) {
    $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{ route("tokens.status.update") }}",
		    	data: {
		    	   id: $(obj).parent().parent().attr("data-id"),
		    	   status: $(obj).val(),
		    	},
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {
			    },
                success: function(result) {
                    
                },
   	});
    
    event.preventDefault();
                if (event.stopPropagation) {
                    event.stopPropagation()
                } else {
                    event.cancelBubble = true
                }
        
        return false;
}

function clickAppIdClear(obj) {
    if (confirm("Вы действительно хотите удалить?")) {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: $(obj).attr("data-href"),
		    	data: {},
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	   $(obj).html('<i class="fa fa-spinner" aria-hidden="true"></i>');
		    	},
			    error: function (result) {
			       $(obj).html('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>');
			    },
                success: function(result) {
                    if (result.status) {
                        $(obj).html('<i class="fa fa-check" aria-hidden="true"></i>');
                   
                        $(obj).parent().parent().find(".td_app_id").empty();
                    }
                },
   	});
    }
}

function del_selects(){
    if (confirm("Вы действительно хотите удалить?")) {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('googletoken.delete', [])}}",
		    	data: $("#formList").serialize(),
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {
			    },
                success: function(result) {
                    location.reload();
                },
   	});
    }
}

function handleCklick(e) {
    /*
    let text = e.type +
        ' key=' + e.key +
        ' code=' + e.code +
        (e.shiftKey ? ' shiftKey' : '') +
        (e.ctrlKey ? ' ctrlKey' : '') +
        (e.altKey ? ' altKey' : '') +
        (e.metaKey ? ' metaKey' : '') +
        (e.repeat ? ' (repeat)' : '') +
        "\n";
    */    
    switch (e.type) {
        case 'keydown':
           switch (e.key) {
              case 'Shift':
                  if (!isPressShift) {
                      // Нажата клавиша
                      isPressShift = true;
                  }
                  break;
           }
           break;
        case 'keyup':
           switch (e.key) {
              case 'Shift':
                  if (isPressShift) {
                      // Отжата клавиша
                      isPressShift = false;
                      checkboxStartPressShift = null;
                      checkboxEndPressShift = null;
                  }
                  break;
           }
           break;
    }
}

$('.select_rows').change(function () {
    if (isPressShift) {
        if (checkboxStartPressShift) {
            checkboxEndPressShift = this;
        } else {
            checkboxStartPressShift = this;
        }
        
        if (checkboxStartPressShift && checkboxEndPressShift) {
            selectServerRange(checkboxStartPressShift, checkboxEndPressShift);
        }
    }
});

function selectServerRange(checkboxStart, checkboxEnd) {
    var isswap = null;
    
    $(".select_rows").each(function() {
        if (isswap === null) {
            if (checkboxStart == this) {
                isswap = false;
            } else if (checkboxEnd == this) {
                isswap = true;
            }
        }
    });
    
    if (isswap) {
        var objStart = checkboxEnd;
        var objEnd = checkboxStart;
    } else {
        var objStart = checkboxStart;
        var objEnd = checkboxEnd;
    }
    
    var isstart = false;
    var issend = false;
    
    if ($(objStart).prop('checked') && $(objEnd).prop('checked')) {
        var ischecked = true;
    } else {
        var ischecked = false;
    }
    
    $(".select_rows").each(function() {
        if (objEnd == this) {
            issend = true;
        }
        
        if (isstart && !issend) {
            $(this).prop('checked', ischecked);
        }
        
        if (objStart == this) {
            isstart = true;
        }
    });
}

function confirmClick () {
    if (!confirm('Дествительно хотите сместить?')) {
        event.preventDefault();
        if (event.stopPropagation) {
            event.stopPropagation()
        } else {
            event.cancelBubble = true
        }
        return false;
    }
}

function clickBtnAddServer(obj) {
    return true;
}

function clickDisplaceServer(obj) {
    var $objToCheck = $('.select_rows:checked:first');
    
    var objTo = null;
    
    if ($objToCheck.length) {
        objTo = $objToCheck[0].parentNode.parentNode;
        var obj_to_id = $(objTo).attr('data-id');
    } else {
        var obj_to_id = 0;
    }
    
    $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('languages.displace_sort', [])}}",
		    	data: {
		    	    obj_id: $(obj).attr('data-id'),  
		    	    obj_to_id: obj_to_id,  
		    	    type: $(obj).attr('data-type'),          
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {
			        alert(JSON.stringify(result));
			    },
                success: function(result) {
                    if (objTo) {
                        var prevElem = null;
                        var thisElem = null;
                        var nextElem = null;
                        
                        $('.tr-server').each(function () {
                            if (prevElem && !nextElem) {
                                nextElem = this;
                            }
                            
                            if (!thisElem && $(obj).attr('data-id') == $(this).attr('data-id')) {
                                thisElem = this;
                            }
                        
                            if (!prevElem && objTo == this) {
                                prevElem = this;
                            }
                        });
                        
                        if (thisElem && nextElem && thisElem != nextElem) {
                            thisElem.parentNode.insertBefore(thisElem, nextElem);
                        }
                    } else {
                        var prevElem = null;
                        var thisElem = null;
                        var nextElem = null;
                    
                        $('.tr-server').each(function () {
                            if (thisElem && !nextElem) {
                                nextElem = this;
                            }
                        
                            if (!thisElem && $(obj).attr('data-id') == $(this).attr('data-id')) {
                                thisElem = this;
                            }
                        
                            if (!prevElem || !thisElem) {
                                prevElem = this;
                            }
                        });
                    
                        switch ($(obj).attr('data-type')) {
                            case 'up':
                                if (thisElem && prevElem && thisElem != prevElem) {
                                    thisElem.parentNode.insertBefore(thisElem, prevElem);
                                }
                                break;
                            case 'down':
                                if (thisElem && nextElem && thisElem != nextElem) {
                                    thisElem.parentNode.insertBefore(nextElem, thisElem);
                                }
                                break;
                        }
                    }
		    	},
   	});
}
$("#select_all").click(function(){
        if($("#select_all").prop('checked')){
            $(".select_rows").prop('checked', true);
        } else {
            $(".select_rows").prop('checked', false);
        }
    });
    
function changeFindField (obj) {
    event.preventDefault();
    if (event.stopPropagation) {
        event.stopPropagation()
    } else {
        event.cancelBubble = true
    }
    
    if (typeof $(obj).val() != "string") {
        changeGetValueByPage($(obj).attr('name'), $(obj).val().join(","));
    } else {
        changeGetValueByPage($(obj).attr('name'), $(obj).val());
    }
    
    return false;
}

function changeSortField (obj) {
    event.preventDefault();
    if (event.stopPropagation) {
        event.stopPropagation()
    } else {
        event.cancelBubble = true
    }
    
    changeGetValueByPage($(obj).attr('data-name'), $(obj).attr('data-value'));
    
    return false;
}

function changeGetValueByPage(name, value) {
    var data = {};

    var link = location.href;

    var purl = parse_url(link);

    if (purl && purl['query']) {
        var pstr = parse_str(purl['query']);

        if (pstr[name]) {
            delete pstr[name];
        }

        for (var i in pstr) {
            if (pstr[i] != '')
                data[i] = pstr[i];
        }
    }

    if (value != '')
        data[name] = encodeURIComponent(value.replace(/[!'()*]/g, escape));

    var query = http_build_query(data);
    
    link = link.replace(/\?.*$/, '');
    link = link.replace(/#.*$/, '');

    if (query) {
        link += '?' + query;
    }

    if (location.href != link) {
        //alert(link);

        location.href = link;
    }
}

function parse_str(str, array){	// Parses the string into variables
	// 
	// +   original by: Cagri Ekin
	// +   improved by: Michael White (http://crestidg.com)

	var glue1 = '=';
	var glue2 = '&';

	var array2 = str.split(glue2);
	var array3 = [];
	for(var x=0; x<array2.length; x++){
		var tmp = array2[x].split(glue1);
		array3[unescape(tmp[0])] = unescape(tmp[1]).replace(/[+]/g, ' ');
	}

	if(array){
		array = array3;
	} else{
		return array3;
	}
}



var parse_url = function (str, component) {
    // example 1: parse_url('http://username:password@hostname/path?arg=value#anchor');
    // returns 1: {scheme: 'http', host: 'hostname', user: 'username', pass: 'password', path: '/path', query: 'arg=value', fragment: 'anchor'}

    var query, key = [
            'source', 'scheme', 'authority', 'userInfo',
            'user', 'pass', 'host', 'port', 'relative', 'path',
            'directory', 'file', 'query', 'fragment'
        ],
        ini = (this.php_js && this.php_js.ini) || {},
        mode = (ini['phpjs.parse_url.mode'] &&
            ini['phpjs.parse_url.mode'].local_value) || 'php',
        parser = {
            php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
            strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
            loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
        };

    var m = parser[mode].exec(str),
        uri = {},
        i = 14;
    while (i--) {
        if (m[i]) {
            uri[key[i]] = m[i];
        }
    }

    if (component) {
        return uri[component.replace('PHP_URL_', '').toLowerCase()];
    }
    if (mode !== 'php') {
        var name = (ini['phpjs.parse_url.queryKey'] &&
            ini['phpjs.parse_url.queryKey'].local_value) || 'queryKey';
        parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
        uri[name] = {};
        query = uri[key[12]] || '';
        query.replace(parser, function ($0, $1, $2) {
            if ($1) {
                uri[name][$1] = $2;
            }
        });
    }
    delete uri.source;
    return uri;
}

var http_build_query = function (obj, num_prefix, temp_key) {

    var output_string = []

    Object.keys(obj).forEach(function (val) {

        var key = val;

        num_prefix && !isNaN(key) ? key = num_prefix + key : ''

        var key = encodeURIComponent(key.replace(/[!'()*]/g, escape));
        temp_key ? key = temp_key + '[' + key + ']' : ''

        if (typeof obj[val] === 'object') {
            var query = http_build_query(obj[val], null, key)
            output_string.push(query)
        } else {
            var value = String(obj[val]);

            value = encodeURIComponent(value.replace(/[!'()*]/g, escape));

            output_string.push(key + '=' + value)
        }

    })

    return output_string.join('&')

}

var stripos = function (f_haystack, f_needle, f_offset) { // Find position of first occurrence of a case-insensitive string
    //
    // +	 original by: Martijn Wieringa

    var haystack = f_haystack.toLowerCase();
    var needle = f_needle.toLowerCase();
    var index = 0;

    if (f_offset == undefined) {
        f_offset = 0;
    }

    if ((index = haystack.indexOf(needle, f_offset)) > -1) {
        return index;
    }

    return false;
}

$(document).ready(function() {
  $('.multipleSelect').SumoSelect();
});
</script>
@endpush
