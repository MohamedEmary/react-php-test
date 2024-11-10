<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use PDO;

class QueryType extends ObjectType
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
    parent::__construct([
      'name' => 'Query',
      'fields' => [
        'products' => [
          'type' => Type::listOf(new ProductType($db)),
          'resolve' => function () {
            $stmt = $this->db->query('SELECT * FROM products');
            return $stmt->fetchAll();
          }
        ],
        'categories' => [
          'type' => Type::listOf(Type::string()),
          'resolve' => function () {
            $stmt = $this->db->query('SELECT DISTINCT category_name FROM products');
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
          }
        ],
        'category_products' => [
          'type' => Type::listOf(new ProductType($db)),
          'args' => [
            'category' => Type::nonNull(Type::string())
          ],
          'resolve' => function ($root, $args) {
            if ($args['category'] === 'all') {
              $stmt = $this->db->query('SELECT * FROM products');
            } else {
              $stmt = $this->db->prepare('SELECT * FROM products WHERE category_name = ?');
              $stmt->execute([$args['category']]);
            }

            return $stmt->fetchAll();
          }
        ],
      ],
    ]);
  }
}
