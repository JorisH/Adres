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

$app->get('/gemeentes', function (Request $request) use ($app) {
  $q = $request->get('q');
  $q = preg_replace('$[,.;/ ]+$', ' ', $q);
  $terms = explode(' ', $q);
  if (empty($terms)) return new JsonResponse();

  $qIndexedTerms = array_combine(
    array_map(function($index){
      return "q$index";
    }, array_keys($terms)),
    array_map(function($term) {
      return is_numeric($term) ? $term.'%' : '%'.$term.'%';
    }, $terms)
  );

  $sqlWhereClause = implode(' AND ', array_map(function($qIndex){
    return "(code LIKE :$qIndex OR naam LIKE :$qIndex)";
  }, array_keys($qIndexedTerms)));

  $sql = 'SELECT code, naam as gemeente FROM gemeente WHERE ' . $sqlWhereClause;

  $result = $app['db']->fetchAll($sql, $qIndexedTerms);

  return new JsonResponse($result);
});

$app->get('/straten', function (Request $request) use ($app) {
  $q = $request->get('q');
  if (!$q) return new JsonResponse();

  $postcode = $request->get('postcode');

  $sql = "SELECT naam AS straat FROM straat WHERE postcode = :postcode AND naam LIKE :q";

  $result = $app['db']->fetchAll($sql, [
    'postcode' => $postcode,
    'q' => "%$q%"
  ]);

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
