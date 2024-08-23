<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <title>Ошибка 404</title>
    <link href="{{ asset('css/jquery-ui.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css?9') }}" />    
    <link rel="icon" href="/favicon.ico" />
  </head>
  <body> 
    <div class="container-fluid page_header gradient">
      <div class="container">
        <div class="row">
          <div class="col-sm-12">
            <div style="text-align: center; color: #B03060; font-size: 32px;">Ошибка 404</div>   
          </div>   
        </div>
      </div>
    </div>
    <div class="container page_body">
      <div class="row">
        <div class="col-xs-12 page_content" style="float: left;">
          <div class="container-fluid cols_table show_visited">
        <div class="container">
            <div class="content">
                <div class="title" style="text-align: center;">
                  Страница устарела, была удалена или не существовала вовсе.
                </div>
            </div>
        </div>
          </div> 
        </div>
      </div>
    </div>
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
    <script type="text/javascript" src="{{ asset('bootstrap/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/datepicker-ru.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/app.js?10') }}"></script>
  </body>
</html> 
