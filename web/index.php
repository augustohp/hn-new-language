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

function is_there_a_new_language_out_there() {
    $rss = Feed::loadRss(APP_URL_RSS);
    foreach ($rss->item as $suspectEntry) {
        if (strpos($suspectEntry->title, 'language') === false) {
            continue;
        }

        return true;
    }

    return false;
}

$app = new Silex\Application();
$app['debug'] = (APP_ENV == 'dev');
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path'=>APP_DIR_TEMPLATES));
$app->get('/', function() use ($app) {
    $cache_age = 1 * 60 * 60; // <days> * minutes * seconds
    $message = 'No new language today!';
    $title = 'All good!';
    if (is_there_a_new_language_out_there()) {
        $title = 'No good';
        $message = 'Be careful, a new language has appeared.';
    }
    header('ETag: '.md5($title.strtotime('today')));
    header('Cache-Control: public, max-age='.$cache_age);
    $templateVars = array(
        'title' => $title,
        'message' => $message,
    );
    return $app['twig']->render('layout.twig.html', $templateVars);
});
$app->run();
