<?php

namespace App\GraphQL\Type;

use App\Database\Connection;
use App\GraphQL\Type\ProductType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use PDO;

class QueryType extends ObjectType
{
  public function __construct()
  {
    parent::__construct(
      [
        'name' => 'Query',
        'fields' => [
          'products' => [
            'type' => Type::listOf(new ProductType()),
            'resolve' => function () {
              $db = Connection::getInstance();
              $stmt = $db->query('SELECT * FROM products');
              return $stmt->fetchAll();
            }
          ],
          'categories' => [
            'type' => Type::listOf(Type::string()),
            'resolve' => function () {
              $db = Connection::getInstance();
              $stmt = $db->query('SELECT name FROM categories');
              return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
          ],
          'category_products' => [
            'type' => Type::listOf(new ProductType()),
            'args' => [
              'category' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args) {
              $db = Connection::getInstance();

              if ($args['category'] === 'all') {
                $stmt = $db->query('SELECT * FROM products');
              } else {
                $stmt = $db->prepare('SELECT * FROM products WHERE category_name = ?');
                $stmt->execute([$args['category']]);
              }

              return $stmt->fetchAll();
            }
          ],
        ],
      ]
    );
  }
}
