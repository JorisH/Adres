<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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