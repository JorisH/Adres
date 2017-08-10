<?php

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});
$app->register(new DoctrineServiceProvider(), array(
  'db.options' => include __DIR__.'/../config/db.php'
));
$app->register(new CorsServiceProvider(), [
  "cors.allowOrigin" => "*",
]);
$app["cors-enabled"]($app);

return $app;
