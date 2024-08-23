@extends('layouts.app')

@section('title')
<title>Вопрос</title>
@endsection

@section('head')
@endsection

@section('main')
<div class="row" style="margin: 0px 12px 12px 12px;">
  <div class="col-sm-12">
    <h2>
      Апи вопроса: 
      {{ $faq->question }}
    </h2>
  </div>
</div>
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
</style>
<div class="row" style="margin: 12px;">
    @if (Session::has('message'))
  <div class="col-sm-12">
    <div class="alert alert-info">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <p>{{ Session::get('message') }}</p>
    </div>
  </div>
  @endif
  <div class="col-sm-12">
    <form class="form" id="event_form" method="post" action="{{ isset($api) ? route('apis.faq.apis.save', ['faq_id' => $faq->id, 'api_id' => $api->id]) : route('faq.apis.save', ['faq_id' => $faq->id]) }}">
    {{ csrf_field() }}
    <button type="submit" class="btn btn-primary btn-block-xs submit">Сохранить</button>
    
    <table class="table table-striped table-bordered tbl-servers" style="margin-top: 22px;">
		<thead>
			<tr>
                <th style="vertical-align: middle;">name</th>
                <th style="vertical-align: middle;"><label>status<br /><input onchange="selectChkRow(this, 2)" type="checkbox" /></label></th>
			</tr>
		</thead>

		<tbody>
			@foreach ($apis as $itemapi)
				<tr style="@if($itemapi->status == '0')background-color: gray;@endif" class="tr-server" data-id="{{{ $itemapi->id }}}">
					<td>
                      @if($itemapi->img)
                      <img width="20" src="/images-api/{{ $itemapi->img }}" />
                      @endif
                      {{ $itemapi->name }}
                    </td>
                    <td><input type="checkbox" name="apis[{{ $itemapi->id }}][status_faq]" @if($itemapi->status_faq == '1') checked="" @endif class="selectChkRow2" value="1" /></td>
                </tr>
			@endforeach
		</tbody>
	</table>
    
    <button type="submit" class="btn btn-primary btn-block-xs submit">Сохранить</button>
    <br /><br />
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function selectChkRow(obj, num) {
    if ($(obj).prop('checked')) {
        $(".selectChkRow"+num).prop('checked', true);
    } else {
        $(".selectChkRow"+num).prop('checked', false);
    }
}

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
    
    var faq_id = $('.select_servers:checked:first').attr('data-id');
    
    if (faq_id) {
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('faq.save_select_checked', [])}}",
		    	data: {
		    	    faq_id: faq_id,          
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
    var $objToCheck = $('.select_servers:checked:first');
    
    var objTo = null;
    
    if ($objToCheck.length) {
        objTo = $objToCheck[0].parentNode.parentNode;
        var faq_to_id = $(objTo).attr('data-id');
    } else {
        faq_to_id = 0;
    }
    
    $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "{{route('faq.displace_sort', [])}}",
		    	data: {
		    	    faq_id: $(obj).attr('data-id'),  
		    	    faq_to_id: faq_to_id,  
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
</script>
@endpush
