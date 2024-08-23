@extends('layouts.app')

@section('title')
<title>Connection error</title>
@endsection

@section('head')
@endsection

@section('page_container_body')
<div class="row" style="margin: 0px 12px 12px 12px;">
  <div class="col-sm-12">
    <h2>Connection error</h2>
  </div>
</div>

<div class="row" style="margin: 12px;">
  <div class="col-sm-12">

    <div style="padding-bottom: 24px;">
      <table>
      <tr>
      <td>
      <a class="btn btn-default btn-sm" style="float: left;" href="{{ route('servers.settings', []) }}">Settings</a>
      </td>
      <td>
      <button class="btn btn-default btn-sm" onclick="deleteItems(this)" id="" style="float: left;">
        Удалить
        <span class="glyphicon glyphicon-refresh" id="status_before_sel" style="display: none;"></span>
        <span class="glyphicon glyphicon-ok" id="status_success_sel" style="display: none;"></span>
      </button>
      </td>
      </tr>
      </table>
    </div>
    
    <div id="message">
    </div>
    
    {{ $connection_errors->links() }}
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
    <form id="formServersSave" onsubmit="return false;" action="{{ route('servers.save', []) }}" method="post">
    
    {{ csrf_field() }}
    
    <table class="table table-striped table-bordered tbl-servers">
        <thead>
			<tr>
                <th><input type="checkbox" id="select_servers_all" /></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.ip')) }}" name="search[ip]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
                <th style="vertical-align: middle;"><input value="{{ urldecode(request('search.country')) }}" name="search[country]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
                <th style="vertical-align: middle;"><input value="{{ urldecode(request('search.count_errors')) }}" name="search[count_errors]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
                <th colspan="2" style="vertical-align: middle;"><input value="{{ urldecode(request('search.error_at')) }}" name="search[error_at]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
            </tr>
		</thead>
        <thead>
			<tr>
                <th style="vertical-align: middle;"></th>
                <th style="vertical-align: middle;">Ip</th>
				<th style="vertical-align: middle;">Country</th>
				<th style="vertical-align: middle;">Count Errors (Last Hour)</th>
				<th style="vertical-align: middle;">First Error</th>
				<th style="vertical-align: middle;">Last Error</th>
				<th style="vertical-align: middle;" colspan="2"></th>
			</tr>
		</thead>

		<tbody>
			@foreach ($connection_errors as $connection_error)
				<input type="hidden" name="servers[{{ $connection_error->id }}]" value="{{{ $connection_error->id }}}" />
                <tr class="tr-server @if($connection_error->status == '0') tr-bg-active @endif" data-id="{{{ $connection_error->id }}}">
					<td><input type="checkbox" name="select_servers[]" class="select_servers" data-id="{{{ $connection_error->id }}}" /></td>
                    <td><a href="{{ route('apis.servers', ['search[ip]' => $connection_error->ip]) }}">{{ $connection_error->ip }}</a></td>
                    <td>{{{ $connection_error->country }}}</td>
                    <td>{{ $connection_error->count_errors }}({{ $connection_error->count_errors_last_hour }})</td>
                    <td>{{{ $connection_error->min_error_at }}}</td>
                    <td>{{{ $connection_error->max_error_at }}}</td>
					
                    <td style="text-align: center;"><a class="btn btn-info btn-sm fa fa-external-link" title="Перейти" href="{{ route('apis.connection_errors.ip', ['ip' => $connection_error->ip]) }}"></a></td>
                    <td style="text-align: center;"><button data-href="{{ route('apis.connection_errors.destroy', ['connection_error_id' => $connection_error->id]) }}" class='btn btn-sm btn-danger glyphicon glyphicon-trash btn-click-confirm' title="Удалить" type="button"></button></td>
                </tr>
			@endforeach
		</tbody>
	</table>
    </form>

    {{ $connection_errors->links() }}

  </div>
</div>
@endsection

@push('scripts')
<script>
$(".chosen-select").chosen();

document.body.onkeydown = document.body.onkeyup = document.body.onkeypress = handleCklick;

var isPressShift = false;
var checkboxStartPressShift = null;
var checkboxEndPressShift = null;

function clickServersSave() {
    $("#btnServersSave span").html('');
    
    if (confirm('Дествительно хотите сохранить?')) {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{ route('servers.index.save') }}",
		    	data: $("#formServersSave").serialize(),
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	    $("#btnServersSave span").html('<i class="fa fa-refresh" aria-hidden="true"></i>');
		    	},
			    error: function (result) {
		    	    $("#btnServersSave span").html('<i class="fa fa-times" aria-hidden="true"></i>');
			    },
                success: function (result) {
		    	    $("#btnServersSave span").html('<i class="fa fa-check" aria-hidden="true"></i>');
		    	},
                complete: function (result) {
                    $('.tr-server').each(function () {
                        if ($(this).find('.chk_servers').prop('checked')) {
                            $(this).removeClass('tr-bg-active');
                        } else {
                            $(this).addClass('tr-bg-active');
                        }
                    });
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

$('.select_servers').change(function () {
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
    
    $(".select_servers").each(function() {
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
    
    $(".select_servers").each(function() {
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
    event.preventDefault();
    if (event.stopPropagation) {
        event.stopPropagation()
    } else {
        event.cancelBubble = true
    }
    
    var server_id = $('.select_servers:checked:first').attr('data-id');
    
    if (server_id) {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('servers.save_select_checked')}}",
		    	data: {
		    	    server_id: server_id,          
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {
			     //alert(JSON.stringify(result));
			    },
                success: function(result) {
		    	    location.href = $(obj).attr('href')
		    	},
	    	});
    } else {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('servers.save_select_checked')}}",
		    	data: {         
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {
			     //alert(JSON.stringify(result));
			    },
                success: function(result) {
		    	},
                complete: function(result) {
                    location.href = $(obj).attr('href')
		    	},
	    	});
    }
    
    return false;
}

function clickDisplaceServer(obj) {
    var $objToCheck = $('.select_servers:checked:first');
    
    var objTo = null;
    
    if ($objToCheck.length) {
        objTo = $objToCheck[0].parentNode.parentNode;
        var server_to_id = $(objTo).attr('data-id');
    } else {
        server_to_id = 0;
    }
    
    $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('servers.displace_sort')}}",
		    	data: {
		    	    server_id: $(obj).attr('data-id'),  
		    	    server_to_id: server_to_id,  
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

function changeFindField (obj) {
    event.preventDefault();
    if (event.stopPropagation) {
        event.stopPropagation()
    } else {
        event.cancelBubble = true
    }
    
    changeGetValueByPage($(obj).attr('name'), $(obj).val());
    
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

function deleteItems(obj) {
    var items_id = [];
        
    $(".select_servers").each(function() {
            if($(this).prop('checked')){
                items_id.push($(this).attr('data-id'));
            }
    });
    
    if(items_id.length && confirm('Вы действительно хотите удалить выбранные Читы ?')){
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{ route('apis.connection_errors.delete_ip') }}",
		    	data: {
		    	    items_id: items_id,
                    new_api_id: $("#new_api_id_sel").val(),        
                    new_platform_id: $("#new_api_id_platform").val(),          
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {
			     //alert(JSON.stringify(result));
                 $('body').html(result['responseText']);
			    },
                success: function(result) {
                    window.location.reload();
		    	},
	    	});
    }
}
</script>
@endpush
