<?php
$instance = INSTANCE;
// $language = LANGUAGE;
$translator = new Translator();

echo <<<EOT
<!DOCTYPE html>
<html>
<head>
	<title>{$app->getName()}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="Watson, Thoth" />
	<meta name="description" content="A system that as a component of data source for the conversational application, as well as an independent asset center application for any organizations." />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	<meta name="apple-mobile-web-app-title" content="{$app->getName()}" />
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
<body class="download">
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
            <li data-source="Home"><a href="/">{$translator->translate('Home')}</a></li>          
          </ul>

        </div>
      </div>
	</nav>
	<div class="container-fluid">
        <div class="row">
            <div class="main">
EOT;

$isLatest = FALSE;
$count = count($builds);
foreach($builds as $key => $val) {
    $build = new BuildModel($val['idbuild'], $val['idapp'], $val['uid'], $val['display'], $val['platform'], $val['version'], $val['notes'], $val['time']);
    $icon = $build->getPlatform() === BuildModel::IOS ? '<i class="glyphicon glyphicon-apple glyphicon-app-big"></i>' : '<i class="glyphicon glyphicon-text-color glyphicon-app-big"></i>';    
    $downloadUrl = DistributionManager::getDownloadUrl($build->getBuildId());
    $installUrl = DistributionManager::getInstallUrl($build->getBuildId(), $build->getPlatform());

    if($isLatest) {

        echo <<<EOT
        <li class="list-group-item">
            <a href="{$installUrl}" class="name ui-modal-button">Install v{$build->getVersion()} for {$build->getPlatform()}</a>
        </li>
EOT;
    }
    else {
        $isLatest = TRUE;
    
        echo <<<EOT
        <div id="t-wrapper" class="download">
            <div class="form-group">
                <h1>{$build->getDisplay()} (v{$build->getVersion()})</h1>
                <p class="ui-note">Install for {$build->getPlatform()}</p>
            </div>

            <div class="form-group ui-form-group-image-container">
                {$icon}
                <a href="/api/v1/build/code/{$app->getId()}"><img src="/api/v1/build/code/{$app->getId()}" class="ui-qr-code" /></a>
            </div>

            <div class="form-group">
                <p class="ui-notice ui-notice-date">{$build->getTime()}</p>
            </div>

            <div class="form-group">
                <a class="btn btn-success btn-download btn-install" href="{$installUrl}">{$translator->translate('Install to mobile device now')}</a>
            </div>

            <div class="form-group">
                <a class="btn btn-info btn-download" href="{$downloadUrl}">{$translator->translate('Download to PC and install later')}</a>
            </div>

            <div class="form-group ui-release-notes">
                {$build->getNotesHTML()}
            </div>
        </div>
EOT;
        if($count > 1)
            echo <<<EOT
    <div class="form-group">
        <label>{$translator->translate('Other previous versions')}</label>
    </div>
EOT;
    }
}
?>

            </div>
        </div>
    </div>
    <script src="/views/js/main.js"></script>
</body>
</html>