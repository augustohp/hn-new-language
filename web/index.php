<?php

require __DIR__.'/../vendor/autoload.php';
define('APP_ENV', getenv('APP_ENV') ?: 'prod');
define('APP_DIR_TEMPLATES', realpath(__DIR__.'/../templates'));
define('APP_URL_RSS', 'http://news.ycombinator.com/rss');

// Bootstrap
call_user_func(function() {
    $default_env_config = array(
        'error_reporting' => -1,
        'display_errors' => 0,
        'date.dafault_timezone' => 'UTC'
    );
    $env_config = array();
    switch (APP_ENV) {
        case 'dev':
            $env_config = array(
                'display_errors' => 1
            );
            break;
    }
    foreach (array_merge($default_env_config, $env_config) as $parameter=>$value) {
        ini_set($parameter, $value);
    }
});

$searchFor = array('language');
$loader = new Twig_Loader_Filesystem(APP_DIR_TEMPLATES);
$twig = new Twig_Environment($loader);
$template = $twig->loadTemplate('layout.twig.html');

$rss = Feed::loadRss(APP_URL_RSS);
$message = 'No! \o/';
$title = 'All good!';
foreach ($rss->item as $suspectEntry) {
    if (strpos($suspectEntry->title, 'language') === false) {
        continue;
    }

    $title = 'No good';
    $message = 'Yes. T.T';
    break;
}

echo $template->render(array(
    'title' => $title,
    'message' => $message,
));
