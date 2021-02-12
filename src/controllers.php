<?php

namespace Tactics;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** @var Application $app */
$app->get('/gemeentes', 'Tactics\\Adres::gemeentes');
$app->get('/straten', 'Tactics\\Adres::straten');

class Adres
{
  public function gemeentes(Request $request, Application $app)
  {
    $q = $request->get('q');
    $q = preg_replace('$[,.;/ ]+$', ' ', $q);
    // replace ipad/iphone curly single quotes by straight quotes
    $q = str_replace(['‘', '’'], "'", html_entity_decode($q));
    $terms = explode(' ', $q);
    if (empty($terms)) return new JsonResponse();

    $querybuilder = $this->getQueryBuilder($app)
      ->select('code', 'naam as gemeente')
      ->from('gemeente');

    foreach ($terms as $index => $value) {
      $querybuilder
        ->andWhere(
          $querybuilder->expr()->orX(
            $querybuilder->expr()->like('code', ":q$index"),
            $querybuilder->expr()->like('naam', ":q$index")
          ))
        ->setParameter(":q$index", is_numeric($value) ? $value . '%' : '%' . $value . '%');
    }

    $result = $querybuilder->execute()->fetchAll();

    return new JsonResponse($result);
  }

  public function straten(Request $request, Application $app)
  {
    $q = $request->get('q');
    // replace ipad/iphone curly single quotes by straight quotes
    $q = str_replace(['‘', '’'], "'", html_entity_decode($q));
    if (!$q) return new JsonResponse();

    $queryBuilder = $this->getQueryBuilder($app);
    $result = $queryBuilder
      ->select('naam as straat')
      ->from('straat')
      ->where(
        $queryBuilder->expr()->andX(
          $queryBuilder->expr()->eq('postcode', ":postcode"),
          $queryBuilder->expr()->like('naam', ":q")
        ))
      ->setParameters([
          'postcode' => $request->get('postcode'),
          'q' => "%$q%"
        ])
      ->execute()
      ->fetchAll();

    return new JsonResponse($result);
  }

  /**
   * @param Application $app
   * @return \Doctrine\DBAL\Query\QueryBuilder
   */
  private function getQueryBuilder(Application $app)
  {
    /** @var \Doctrine\DBAL\Connection $conn */
    $conn = $app['db'];

    return $conn->createQueryBuilder();
  }
}
