@extends('layouts.app')

@section('title')
<title>APIS</title>
@endsection

@section('head')

@endsection

@section('main')
<div class="row">
  <div class="col-sm-12">
    <a class="btn btn-default" href="{{ route('apis.add') }}">Добавить API</a>
  </div>
</div>

<div class="row">
    @foreach($apis as $ap)
    <div class="col-sm-4" style="margin-top: 22px;">
      <div>
      
      @if($ap->img)
      <img width="40" src="/images-api/{{ $ap->img }}" />
      @endif
      
      <div style="display: inline-block;position: relative;top: 9px;">
        <div>{{ $ap->name }}  ({{ $ap->key }})</div>
        <div style="font-size: 13px;">{{ $ap->package }}&nbsp;</div>
      </div>
      
      <a href="{{ route('apis.edit', $ap->id) }}" class="glyphicon glyphicon glyphicon-cog"></a>
      <span data-href="{{ route('apis.destroy', $ap->id) }}" class="glyphicon glyphicon glyphicon-trash btn-click-confirm openMessageModal"></span>
      </div>
      
      <a href="{{ route('apis.servers.servers', ['api_id' => $ap->id]) }}" style="margin-right: 12px;">Серверы</a>
      <a href="{{ route('apis.faq.index', ['api_id' => $ap->id]) }}">FAQ</a>
      <br />
      <a href="{{ route('apis.setting.index', ['api_id' => $ap->id]) }}" style="margin-right: 12px;">Настройки</a>
      <a href="{{ route('googletoken.index', ['api_id' => $ap->id]) }}">Tokens</a>
    </div>
    @endforeach
</div>
<br /><br /><br />
@endsection

@push('scripts')
@endpush
