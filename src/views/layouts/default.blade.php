<?php 
  use Illuminate\Support\Facades\Config;
?>
<html lang="en">
  <head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Laravel import export">
    <meta name="author" content="Jacopo Beschi">

    <title>{{(isset($title)) ? $title : Config::get('LaravelImportExport::baseconf.default_title')}}</title>

    {{-- loading css --}}
    <link href="{{asset('packages/jacopo/laravel-import-export/css/bootstrap.min.css')}}" media="all" type="text/css" rel="stylesheet">
    <link href="{{asset('packages/jacopo/laravel-import-export/css/default.css')}}" media="all" type="text/css" rel="stylesheet">

  </head>

  <body style="">

    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="https://github.com/intrip/laravel-import-export" target="_blank">Laravel Import Export</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li class="{{(isset($menu_index) && $menu_index==1) ? 'active' : ''}}"><a href="{{URL::action('Jacopo\LaravelImportExport\HomeController@getIndex')}}">Home</a></li>
            <li class="{{(isset($menu_index) && $menu_index==2) ? 'active' : ''}}"><a href="{{URL::action('Jacopo\LaravelImportExport\ImportController@getIndex')}}">Import</a></li>
            <li class="{{(isset($menu_index) && $menu_index==3) ? 'active' : ''}}"><a href="{{URL::action('Jacopo\LaravelImportExport\ExportController@getIndex')}}">Export</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">

    {{isset($content) ? $content : ''}}

    </div> <!-- /container -->


    {{-- loading javascript --}}
    {{ HTML::script(asset('packages/jacopo/laravel-import-export/js/jquery-1.10.2.min.js') ) }}
    {{ HTML::script(asset('packages/jacopo/laravel-import-export/js/bootstrap.js') ) }}

</body></html>