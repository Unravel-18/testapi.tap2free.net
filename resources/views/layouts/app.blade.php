<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    @section('title')
    <title>Администрирование сайта</title>
    @show
    <link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}" />
    
    <link rel="stylesheet" href="{{ asset('css/chosen.min.css') }}" />     
    <link rel="stylesheet" href="{{ asset('font-awesome-4.7.0/css/font-awesome.min.css') }}" />    
    
    <link rel="stylesheet" href="{{ asset('css/app.css?9') }}" />    
    <link rel="icon" href="/favicon.ico" />
    @yield('styles')
    <script>window.Laravel = <?= json_encode(['csrfToken' => csrf_token()]) ?></script>
    <style>
    .jsgrid-cell {
        overflow: hidden!important;
    }
    </style>
  </head>
  <body>
    @yield('body_top')
    <div class="navbar navbar-default navbar-static-top top_navbar" role="navigation">
      <div class="container">
        <div class="navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="{{ route('servers.index') }}">Api</a></li>
            @if(isset($api))
            <li><a href="{{ route('apis.servers', []) }}">Все сервера</a></li>
            <li><a href="{{ route('apis.servers.servers', ['api_id' => $api->id]) }}">Сервера АПИ</a></li>
            @else
            <li><a href="{{ route('apis.servers') }}">Все сервера</a></li>
            @if(Session::get('this_api'))
            <li><a href="{{ route('apis.servers.servers', ['api_id' => Session::get('this_api')->id]) }}">Сервера АПИ</a></li>
            @endif  
            @if(Session::get('this_api'))
            <li><a href="{{ route('apis.setting.index', ['api_id' => Session::get('this_api')->id]) }}">Настройки АПИ</a></li>
            @endif
            @endif     
            @if(isset($api))    
            <li><a href="{{ route('apis.setting.index', ['api_id' => $api->id]) }}">Настройки АПИ</a></li>
            @endif
            @if(isset($api))    
            <li><a href="{{ route('faq.all', []) }}">FAQ ALL</a></li>
            <li><a href="{{ route('apis.faq.index', ['api_id' => $api->id]) }}">FAQ API</a></li>
            @else
            <li><a href="{{ route('faq.all', []) }}">FAQ ALL</a></li>
            @if(Session::get('this_api'))
            <li><a href="{{ route('apis.faq.index', ['api_id' => Session::get('this_api')->id]) }}">FAQ API</a></li>
            @endif
            @endif
            <li><a href="{{ route('apis.connection_errors.index', []) }}">Connection error</a></li>
            <li><a href="{{ route('badip.index') }}">BadIp</a></li>
            @if(isset($api))    
            <li><a href="{{ route('googletoken.index', ['api_id' => $api->id]) }}">Tokens</a></li>
            @endif
            
            <li><a href="{{ route('tokens.index', ['search[status]' => implode(',', ['active','not activated','banned'])]) }}">ApiKeys</a></li>
            <li><a href="{{ route('prokeys.index', []) }}">ProKeys</a></li>
            
            <li></li>
            @if(isset($api)) 
            <li><a style="color: #20B2AA;">{{ strtoupper($api->name) }}</a></li>
            @else
            @if(Session::get('this_api'))
            <li><a style="color: #20B2AA;">{{ strtoupper(Session::get('this_api')->name) }}</a></li>
            @endif
            @endif
            @if(env('APP_DEBUG'))
            <li><a style="color: #20B2AA;">Debag TRUE</a></li>
            @endif
            @if(!env('ACCESS_IP'))
            <li><a style="color: #20B2AA;">IP not blocked</a></li>
            @endif
            @if(isset ($count_connections_404) && $count_connections_404 > 0)
            <li><a style="color: #20B2AA;">Need Check: {{ $count_connections_404 }}</a></li>
            @endif
            @if(isset($open_api) && !empty($open_api))
            <li><a style="color: #20B2AA;">Open api: {{ str_limit($open_api, (isset($api) ? 10 : 70), '...') }}</a></li>
            @endif
            
            @yield('navbar_header')
          </ul>
          <ul class="nav navbar-nav navbar-right">
            @if(Session::get('auth'))
            <li><a href="{{ route('auth.logout') }}">Выйти</a></li>
            @endif
            @yield('navbar_header_right')
          </ul>
        </div>
      </div>
    </div>
    @section('page_container_header')
    <div class="container-fluid page_header gradient">
      <div class="container">
        <div class="row">
          <div class="col-sm-12">
            @section('head')
            <div align="center">
              @if (Session::has('message'))
              <div class="flash alert">
                <p>{{ Session::get('message') }}</p>
              </div>
			  @endif
            </div>
            @show    
          </div>   
        </div>
      </div>
    </div>
    @show  
    @section('page_container_body')
    <div class="container page_body">
      <div class="row">
        <div class="col-xs-12 page_content" style="float: left;">
          @section('content')
          <div class="container-fluid cols_table show_visited">
            @yield('main')
          </div>
          @show   
        </div>
      </div>
    </div>
    @show 
    <footer class="page_footer">
      <div class="container">
        <div class="row">
          <div class="col-xs-6 col-sm-4">
          </div>
          <div class="col-xs-6 col-sm-4 text-right">
          </div>
        </div>
      </div>
    </footer>
    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/chosen.jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/datepicker-ru.js') }}"></script>
    
    <script type="text/javascript" src="{{ asset('js/app.js?36') }}<?= rand(10000000, 100000000) ?>"></script>
    @stack('scripts')
  </body>
</html> 