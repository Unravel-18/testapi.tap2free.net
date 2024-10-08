@extends('layouts.app')

@section('title')
<title>BadIp</title>
@endsection

@section('head')
@endsection

@section('main')

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

<h2>BadIp</h2>

<div style="padding-bottom: 24px;">
      <table>
      <tr>
      <td>
      <a class="btn btn-default btn-sm" href="{{ route('badip.item', []) }}" style="float: left;">Add</a>
      </td>
      </tr>
      </table>
    </div>
    
    {{ $items->links() }}

	<table class="table table-striped table-bordered tbl-servers">
		<thead>
			<tr>
                <th style="vertical-align: middle;"><input value="{{ urldecode(request('search.ip')) }}" name="search[ip]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;">
                  <select name="search[status]" class="find_field" onchange="changeFindField(this)">
                    <option value=""></option>
                    <option value="1" @if(request('search.status') == '1') selected="" @endif>активен</option>
                    <option value="0" @if(request('search.status') == '0') selected="" @endif>не активен</option>
                  </select>
                </th>
		 	</tr>
		</thead>
		<thead>
			<tr>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'ip' ? '-' : '' }}ip" href="?sort={{ $sort == 'ip' ? '-' : '' }}ip">Ip</a></th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'status' ? '-' : '' }}status" href="?sort={{ $sort == 'status' ? '-' : '' }}status">Статус</a></th>
			</tr>
		</thead>

		<tbody>
			@foreach ($items as $item)
				<tr class="tr-server" data-id="{{{ $item->id }}}">
					<td>{{{ $item->ip }}}</td>
					
                    <td style="text-align: center;">
                      <input onchange="changeBadIpStatus(this)" data-id="{{{ $item->id }}}" {{ $item->status == "1" ? 'checked=""' : '' }}  type="checkbox" value="1" />
                    </td>
                    <td style="text-align: center;"><button data-href="{{ route('badip.delete', ['id' => $item->id]) }}" class='btn btn-sm btn-danger glyphicon glyphicon-trash btn-click-confirm' title="Удалить" type="button"></button></td>
				</tr>
			@endforeach
		</tbody>
	</table>

    {{ $items->links() }}


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

function changeBadIpStatus(obj) {
    $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('badip.change.status', [])}}",
		    	data: {
		    	    id: $(obj).attr('data-id'),  
		    	    status: $(obj).prop('checked') ? "1" : "0",         
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
</script>
@endpush
