<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage')
;

$app->get('/autocomplete', function (Request $request) use ($app) {
  $postcode = $request->get('postcode');
  $straatnaam = $request->get('straat');
  if (!$postcode && !$straatnaam) return new JsonResponse();

  if ($postcode && $straatnaam){
    $sql = "SELECT * FROM straat WHERE postcode LIKE :postcode AND naam LIKE :straatnaam";
    $result = $app['db']->fetchAll($sql, [
      'postcode' => $postcode.'%',
      'straatnaam' => $straatnaam.'%'
    ]);
  } else if ($postcode) {
    $sql = "SELECT * FROM gemeente WHERE code LIKE :postcode";
    $result = $app['db']->fetchAll($sql, [
      'postcode' => $postcode."%",
    ]);
  } else {
    $sql = "SELECT * FROM straat WHERE naam LIKE :straatnaam";
    $result = $app['db']->fetchAll($sql, [
      'straatnaam' => $straatnaam.'%'
    ]);
  }

  return new JsonResponse($result);
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
