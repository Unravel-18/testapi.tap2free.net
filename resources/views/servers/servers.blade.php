@extends('layouts.app')

@section('title')
<title>Сервера</title>
@endsection

@section('head')
@endsection

@section('page_container_body')
<div class="row" style="margin: 0px 12px 12px 12px;">
  <div class="col-sm-12">
    <h2>Сервера Апи: <span style="color: #20B2AA;">{{ strtoupper($api->name) }}</span></h2>
  </div>
</div>

<div onclick="clickUpPage()" style="position: fixed;bottom: 10px; left: 180px;z-index: 999;color: black;font-size: 32px;cursor: pointer;">
<i class="fa fa-arrow-circle-up" aria-hidden="true"></i>
</div>
<div onclick="clickDownPage()" style="position: fixed;bottom: 10px; left: 220px;z-index: 999;color: black;font-size: 32px;cursor: pointer;">
<i class="fa fa-arrow-circle-down" aria-hidden="true"></i>
</div>


<div class="row" style="margin: 12px;">
  <div class="col-sm-12">

    
    
    <div style="padding-bottom: 24px;">
      <button class="btn btn-default btn-sm" style="" id="btnServersSave" onclick="clickServersSave()">Save <span></span></button>
    </div>
    
    <div id="message">
    </div>
    
    {{ $servers->links() }}

	
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
    <form id="formServersSave" onsubmit="return false;" action="{{ route('apis.servers.index.save', ['api_id' => $api->id]) }}" method="post">
    
    {{ csrf_field() }}
    
    <div>
    Серверов всего: <b>{{ $count_all }}</b> Активно: <b>{{ $count_active }}</b> Неактивно: <b>{{ $count_noactive }}</b>
    </div>
    
    <table class="table table-striped table-bordered tbl-servers">
        <thead>
			<tr>
                <th><input type="checkbox" id="select_servers_all" /></th>
                <th style="vertical-align: middle;"></th>
                <th style="vertical-align: middle;">
                  <select name="search[status]" class="find_field" onchange="changeFindField(this)">
                    <option value="1" @if(request('search.status') == '1') selected="" @endif>активен</option>
                    <option value="0" @if(request('search.status') == '0') selected="" @endif>не активен</option>
                  </select>
                </th>
                <th style="vertical-align: middle;"><input value="{{ urldecode(request('search.server')) }}" name="search[server]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
                <th style="vertical-align: middle;"><input value="{{ urldecode(request('search.country')) }}" name="search[country]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.name')) }}" name="search[name]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.ip')) }}" name="search[ip]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;">
                  <select style="width: 40px;min-width: 40px;" name="search[status_ss_vpn]" class="find_field" onchange="changeFindField(this)">
                    <option value=""></option>
                    <option value="1" @if(request('search.status_ss_vpn') == '1') selected="" @endif>VPN</option>
                    <option value="2" @if(request('search.status_ss_vpn') == '2') selected="" @endif>SS</option>
                  </select>
                </th>
				<th style="vertical-align: middle;">
                  <select style="width: 40px;min-width: 40px;" name="search[status_pro]" class="find_field" onchange="changeFindField(this)">
                    <option value=""></option>
                    <option value="1" @if(request('search.status_pro') == '1') selected="" @endif>PRO</option>
                    <option value="0" @if(request('search.status_pro') == '0') selected="" @endif>FREE</option>
                  </select>
                </th>
				<th style="vertical-align: middle;">
                  <select style="width: 30px;min-width: 30px;" name="search[status_local]" class="find_field" onchange="changeFindField(this)">
                    <option value=""></option>
                    <option value="1" @if(request('search.status_local') == '1') selected="" @endif>да</option>
                    <option value="0" @if(request('search.status_local') == '0') selected="" @endif>нет</option>
                  </select>
                </th>
				<th style="vertical-align: middle;">
                  <select style="width: 30px;min-width: 30px;" name="search[status_fake]" class="find_field" onchange="changeFindField(this)">
                    <option value=""></option>
                    <option value="1" @if(request('search.status_fake') == '1') selected="" @endif>да</option>
                    <option value="0" @if(request('search.status_fake') == '0') selected="" @endif>нет</option>
                  </select>
                </th>
				<th style="vertical-align: middle;">
                  <select style="width: 30px;min-width: 30px;" name="search[status_not_rating]" class="find_field" onchange="changeFindField(this)">
                    <option value=""></option>
                    <option value="1" @if(request('search.status_not_rating') == '1') selected="" @endif>да</option>
                    <option value="0" @if(request('search.status_not_rating') == '0') selected="" @endif>нет</option>
                  </select>
                </th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.count_connections')) }}" name="search[count_connections]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.speed_mbps')) }}" name="search[speed_mbps]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.rate')) }}" name="search[rate]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;"></th>
				<th style="vertical-align: middle;"><input value="{{ urldecode(request('search.auto_switch_on_date')) }}" name="search[auto_switch_on_date]" onchange="changeFindField(this)" class="find_field" type="text" /></th>
				<th style="vertical-align: middle;" colspan="3"></th>
			</tr>
		</thead>
		<thead>
			<tr>
                <th style="vertical-align: middle;"></th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'sort' ? '-' : '' }}sort" href="?sort={{ $sort == 'sort' ? '-' : '' }}sort">sort</a></th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'status' ? '-' : '' }}status" href="?sort={{ $sort == 'status' ? '-' : '' }}status">Статус</a></th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'server' ? '-' : '' }}server" href="?sort={{ $sort == 'server' ? '-' : '' }}server">Hoster</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'country' ? '-' : '' }}country" href="?sort={{ $sort == 'country' ? '-' : '' }}country">Страна</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'name' ? '-' : '' }}name" href="?sort={{ $sort == 'name' ? '-' : '' }}name">Имя</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'ip' ? '-' : '' }}ip" href="?sort={{ $sort == 'ip' ? '-' : '' }}ip">Ip</a></th>
                <th style="vertical-align: middle;">type</th>
                <th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'status_pro' ? '-' : '' }}status_pro" href="?sort={{ $sort == 'status_pro' ? '-' : '' }}status_pro">Тип</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'status_local' ? '-' : '' }}status_local" href="?sort={{ $sort == 'status_local' ? '-' : '' }}status_local">Local</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'status_fake' ? '-' : '' }}status_fake" href="?sort={{ $sort == 'status_fake' ? '-' : '' }}status_fake">Fake</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'status_not_rating' ? '-' : '' }}status_not_rating" href="?sort={{ $sort == 'status_not_rating' ? '-' : '' }}status_not_rating">Not Rating</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'count_connections' ? '-' : '' }}count_connections" href="?sort={{ $sort == 'count_connections' ? '-' : '' }}count_connections">Connected</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'speed_mbps' ? '-' : '' }}speed_mbps" href="?sort={{ $sort == 'speed_mbps' ? '-' : '' }}speed_mbps">Speed mbps</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'rate' ? '-' : '' }}rate" href="?sort={{ $sort == 'rate' ? '-' : '' }}rate">Rate</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'not_available_at' ? '-' : '' }}not_available_at" href="?sort={{ $sort == 'not_available_at' ? '-' : '' }}not_available_at">Minutes</a></th>
				<th style="vertical-align: middle;"><a onclick="changeSortField(this)" data-name="sort" data-value="{{ $sort == 'auto_switch_on_date' ? '-' : '' }}auto_switch_on_date" href="?sort={{ $sort == 'auto_switch_on_date' ? '-' : '' }}auto_switch_on_date">Auto<br />witch<br />date</a></th>
				<th style="vertical-align: middle;" colspan="3"></th>
			</tr>
		</thead>

		<tbody>
			@foreach ($servers as $server)
				<input type="hidden" name="servers[{{ $server->id }}]" value="{{{ $server->id }}}" />
                <tr class="tr-server @if($server->status == '0') tr-bg-active @endif" data-api_server_id="{{ $server->api_server_id }}" data-id="{{{ $server->id }}}" data-sort="{{{ $server->sort }}}" data-api_servers_sort="{{{ $server->api_servers_sort }}}">
					<td><input type="checkbox" name="select_servers[]" class="select_servers" data-id="{{{ $server->id }}}" /></td>
                    <td style="white-space: nowrap;" >
                      <span data-api_server_id="{{ $server->api_server_id }}" data-type="up" onclick="clickDisplaceServer(this)" style="cursor: pointer;">
                        &nbsp;<i class="fa fa-long-arrow-up" aria-hidden="true"></i>&nbsp;
                      </span>
                      <span>
                        &nbsp;
                      </span>
                      <span data-api_server_id="{{ $server->api_server_id }}" data-id="{{ $server->id }}" data-type="down"  onclick="clickDisplaceServer(this)" style="cursor: pointer;">
                        &nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>&nbsp;
                      </span>
                    </td>
                    <td><input type="checkbox" name="chk_servers[{{ $server->id }}]" value="{{ $server->id }}" @if($server->status == '1') checked="" @endif class="chk_servers" data-id="{{{ $server->id }}}" /></td>
                    <td>
                    {{{ $server->server }}}</td>
                    <td>
                      @if($server->img_flag)
                      <img src="/images/{{ $server->img_flag }}" style="max-width:20px;" />
                      @endif
                      {{{ $server->country }}}
                    </td>
					<td>{{{ $server->name }}}</td>
					<td>{{{ $server->ip }}}</td>
					<td>{{{ ($server->ca_crt && $server->ss_config ? 'VPN/SS' : ($server->ca_crt ? 'VPN' : ($server->ss_config ? 'SS' : ('')))) }}}</td>
					<td style="color: {{ $server->status_pro == '1' ? 'red' : 'green' }};">{{ $server->status_pro == '1' ? 'PRO' : 'FREE' }}</td>
					<td>@if ($server->status_local == '1')<i class="fa fa-check" aria-hidden="true"></i>@endif</td>
					<td>@if ($server->status_fake == '1')<i class="fa fa-check" aria-hidden="true"></i>@endif</td>
					<td>@if ($server->status_not_rating == '1')<i class="fa fa-check" aria-hidden="true"></i>@endif</td>
					<td>{{{ $server->count_connections }}}</td>
                    <td>{{{ $server->speed_mbps }}}</td>
                    <td>{{{ $server->rate }}}</td>
                    <td>{{{ $server->minutes_not_available }}}</td>
                    <td>{{{ $server->auto_switch_on_date }}}</td>
					
                    <td style="text-align: center;"><a class="btn btn-info btn-sm glyphicon glyphicon-cog" title="Изменить" href="{{ isset($api) ? route('apis.servers.edit', ['server_id' => $server->id, 'api_id' => $api->id]) : route('apis.servers.edit', ['server_id' => $server->id, 'api_id' => $api->id]) }}"></a></td>
                    <td style="text-align: center;"><a class="btn btn-success btn-sm glyphicon glyphicon-list" title="Апи" href="{{  isset($api) ? route('apis.servers.apis', ['server_id' => $server->id, 'api_id' => $api->id]) : route('apis.servers.apis', ['server_id' => $server->id, 'api_id' => $api->id]) }}"></a></td>
                    <td style="text-align: center;"><button data-href="{{ isset($api) ? route('apis.servers.api.destroy', ['server_id' => $server->id, 'api_id' => $api->id]) : route('apis.servers.destroy', ['server_id' => $server->id, 'api_id' => $api->id]) }}" class='btn btn-sm btn-danger glyphicon glyphicon-remove btn-click-confirm' title="Удалить" type="button"></button></td>
                </tr>
			@endforeach
		</tbody>
	</table>
    </form>

    {{ $servers->links() }}

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

function clickUpPage() {
    $('html, body').animate({scrollTop: 0}, 300);
}

function clickDownPage() {
    $('html, body').animate({scrollTop: $('html, body').height()}, 300);
}

function clickServersSave() {
    $("#btnServersSave span").html('');
    
    if (confirm('Дествительно хотите сохранить?')) {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{ route('apis.servers.index.save', ['api_id' => $api->id]) }}",
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
		    	url: "{{route('servers.save_select_checked', ['api_id' => $api->id])}}",
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
		    	url: "{{route('servers.save_select_checked', ['api_id' => $api->id])}}",
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
        var server_to_id = $(objTo).attr('data-api_server_id');
    } else {
        server_to_id = 0;
    }
    
    $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('servers.apis.displace_sort', ['api_id' => $api->id])}}",
		    	data: {
		    	    api_server_id: $(obj).attr('data-api_server_id'),  
		    	    api_server_to_id: server_to_id,  
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
                            
                            if (!thisElem && $(obj).attr('data-api_server_id') == $(this).attr('data-api_server_id')) {
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
                        
                            if (!thisElem && $(obj).attr('data-api_server_id') == $(this).attr('data-api_server_id')) {
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
</script>
@endpush
