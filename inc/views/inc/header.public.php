<?php
$translator = new Translator();

echo <<<EOT
<!DOCTYPE html>
<html>
<head>
	<title>{$translator->translate('Sign In')}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="Watson, Thoth" />
	<meta name="description" content="A system that as a component of data source for the conversational application, as well as an independent asset center application for any organizations." />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	<meta name="apple-mobile-web-app-title" content="Thoth" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" href="/views/css/bootstrap.min.css" />
  <link rel="stylesheet" href="/views/css/bootstrap-datepicker.css" />
	<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<link rel="stylesheet" href="/views/css/dashboard.css" />
  <link rel="stylesheet" href="/views/css/style.css" />
  <script src="/views/js/jquery-3.1.1.min.js"></script>
  <script src="/views/js/bootstrap.min.js"></script>
</head>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">{$translator->translate('Asset Center')}</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a><div class="ui-loading">
                  <span class="indicator">Loading...</span>
              </div></a>
            </li>
            <li data-source="Home" class="active"><a href="/">{$translator->translate('Sign In')}</a></li>
            
          </ul>

        </div>
      </div>
	</nav>
	<div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-md-2 sidebar">
                <ul class="nav nav-sidebar">
                    <li data-source="Home" class="active"><a href="/">{$translator->translate('Sign In')}</a></li>
                    
                </ul>

            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
EOT;
