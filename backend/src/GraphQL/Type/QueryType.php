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
        'GetAllProducts' => [
          'type' => Type::listOf(new ProductType($db)),
          'resolve' => function () {
            $stmt = $this->db->query('SELECT * FROM products');
            return $stmt->fetchAll();
          }
        ],
        'GetCategories' => [
          'type' => Type::listOf(Type::string()),
          'resolve' => function () {
            $stmt = $this->db->query('SELECT DISTINCT category_name FROM products');
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
          }
        ],
        'GetCategoryProducts' => [
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
        'GetProductWithId' => [
          'type' => Type::listOf(new ProductType($db)),
          'args' => [
            'id' => Type::nonNull(Type::string())
          ],
          'resolve' => function ($root, $args) {
            $stmt = $this->db->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$args['id']]);
            return $stmt->fetchAll();
          }
        ],
        'GetUserCart' => [
          'type' => Type::listOf(new ObjectType([
            'name' => 'CartItem',
            'fields' => [
              'id' => Type::nonNull(Type::int()),
              'quantity' => Type::nonNull(Type::int()),
              'product' => Type::nonNull(new ProductType($this->db)),
              'selectedAttributes' => Type::listOf(new ObjectType([
                'name' => 'SelectedAttribute',
                'fields' => [
                  'name' => Type::nonNull(Type::string()),
                  'value' => Type::nonNull(Type::string())
                ]
              ]))
            ]
          ])),
          'args' => [
            'userId' => Type::nonNull(Type::int())
          ],
          'resolve' => function ($root, $args) {
            $stmt = $this->db->prepare('
                    SELECT ci.id, ci.quantity, ci.product_id,
                          p.* 
                    FROM cart_items ci
                    JOIN products p ON p.id = ci.product_id 
                    WHERE ci.user_id = ? AND ci.is_order = FALSE
                  ');
            $stmt->execute([$args['userId']]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($cartItems)) {
              return [];
            }

            $result = [];
            foreach ($cartItems as $item) {
              $attrStmt = $this->db->prepare('
                      SELECT cia.attribute_set_id as name, cia.selected_value as value
                      FROM cart_items_attributes cia
                      WHERE cia.order_id = ?
                    ');
              $attrStmt->execute([$item['id']]);
              $attributes = $attrStmt->fetchAll(PDO::FETCH_ASSOC);

              $result[] = [
                'id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
                'product' => $item,
                'selectedAttributes' => $attributes
              ];
            }

            return $result;
          }
        ]
      ],
    ]);
  }
}
