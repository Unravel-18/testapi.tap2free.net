@extends('layouts.app')

@section('title')
<title>FAQ</title>
@endsection

@section('head')
<div class="row" style="margin: 0px 6px 6px 6px;">
  <div class="col-sm-12">
    <h2>Все вопросы</h2>
  </div>
</div>
@endsection

@section('main')
<div class="row">
  <div class="col-sm-12">

    <div style="padding-bottom: 24px;">
      <a onclick="clickBtnAddServer(this)" class="btn btn-default btn-sm" href="{{ isset($api) ? route('apis.faq.add', ['api_id' => $api->id]) : route('faq.add', []) }}" style="float: left;">Добавить</a>
      <a class="btn btn-default btn-sm" href="{{ isset($api) ? route('apis.languages.index', ['api_id' => $api->id]) : route('languages.index', []) }}" style="float: left;">Языки</a>
    </div>

    @if ($items->count())

    {{ $items->links() }}

	<table class="table table-striped table-bordered tbl-servers">
		<thead>
			<tr>
				<th><input type="checkbox" id="select_all" /></th>
                <th></th>
				<th>Вопрос</th>
				<th>Ответ</th>
				<th colspan="3"></th>
			</tr>
		</thead>

		<tbody>
			@foreach ($items as $item)
				<tr class="tr-server" data-id="{{{ $item->id }}}">
					<td><input type="checkbox" name="select_rows[]" class="select_rows" data-id="{{{ $item->id }}}" /></td>
                    <td style="white-space: nowrap;">
                      <span data-id="{{{ $item->id }}}" data-type="up" onclick="clickDisplaceServer(this)" style="cursor: pointer;">
                        &nbsp;<i class="fa fa-long-arrow-up" aria-hidden="true"></i>&nbsp;
                      </span>
                      <span>
                        &nbsp;
                      </span>
                      <span data-id="{{{ $item->id }}}" data-type="down"  onclick="clickDisplaceServer(this)" style="cursor: pointer;">
                        &nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>&nbsp;
                      </span>
                    </td>
                    <td><a style="color: black;" title="Перейти" href="{{ isset($api) ? route('apis.faq.edit', ['faq_id' => $item->id, 'api_id' => $api->id]) : route('faq.edit', ['faq_id' => $item->id]) }}">{{{ $item->question }}}</a></td>
                    <td>{{{ str_limit($item->answer, 100) }}}</td>
					
                    <td style="text-align: center;"><a class="btn btn-info btn-sm glyphicon glyphicon-cog" title="Изменить" href="{{ isset($api) ? route('apis.faq.edit', ['faq_id' => $item->id, 'api_id' => $api->id]) : route('faq.edit', ['faq_id' => $item->id]) }}"></a></td>
					<td style="text-align: center;"><a class="btn btn-success btn-sm glyphicon glyphicon-list" title="Апи" href="{{ isset($api) ? route('apis.faq.apis', ['faq_id' => $item->id, 'api_id' => $api->id]) : route('faq.apis', ['faq_id' => $item->id]) }}"></a></td>
                    <td style="text-align: center;">
						<form method="POST" action="{{ isset($api) ? route('apis.faq.destroy', ['faq_id' => $item->id, 'api_id' => $api->id]) : route('faq.destroy', ['faq_id' => $item->id]) }}">
                            {{ csrf_field() }}
                            <input name="_method" type="hidden" value="DELETE" />                        
                            <button class='btn btn-sm btn-danger glyphicon glyphicon-trash btn-delete' title="Удалить" type="button"></button>
                        </form>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>

    {{ $items->links() }}
@else
<div class="row">
  <div class="col-xs-12">
    Список пуст
  </div>
</div>
@endif

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
    event.preventDefault();
    if (event.stopPropagation) {
        event.stopPropagation()
    } else {
        event.cancelBubble = true
    }
    
    var select_id = $('.select_rows:checked:first').attr('data-id');
    
    if (select_id) {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{ route('faq.save_select_checked', [])}}",
		    	data: {
		    	    select_id: select_id,          
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
    } else {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('faq.save_select_checked', [])}}",
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
		    	url: "{{route('faq.displace_sort', [])}}",
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

$("#btn_copy_sel").click(function(){
        var objs_id = [];
        
        $(".select_rows").each(function() {
            if($(this).prop('checked')){
                objs_id.push($(this).attr('data-id'));
            }
        });
        
        if(objs_id.length && confirm('Подтвердите действие')){
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/faq/copy-select",
		    	data: {
		    	    objs_id: objs_id,
                    new_api_id: $("#new_api_id_sel").val(),              
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	    $("#status_before_sel").show();
		    	    $("#status_success_sel").hide();
		    	},
			    error: function (result) {
			     //alert(JSON.stringify(result));
                 $('body').html(result['responseText']);
			    },
                success: function(result) {
		    	    $("#status_before_sel").hide();
		    	    $("#status_success_sel").show();
                    window.location.reload();
		    	},
	    	});
         }
    });
    
$("#select_all").click(function(){
        if($("#select_all").prop('checked')){
            $(".select_rows").prop('checked', true);
        } else {
            $(".select_rows").prop('checked', false);
        }
    });
</script>
@endpush
