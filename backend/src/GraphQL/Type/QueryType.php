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
          'type' => Type::nonNull(Type::listOf(Type::nonNull(new ObjectType([
            'name' => 'CartItem',
            'fields' => [
              'id' => Type::nonNull(Type::id()),
              'quantity' => Type::nonNull(Type::int()),
              'product' => Type::nonNull(new ObjectType([
                'name' => 'CartProduct',
                'fields' => [
                  'id' => Type::nonNull(Type::string()),
                  'name' => Type::nonNull(Type::string()),
                  'brand' => Type::string(),
                  'category' => Type::string(),
                  'description' => Type::string(),
                  'attributes' => Type::listOf(new ObjectType([
                    'name' => 'CartItemAttribute',
                    'fields' => [
                      'name' => Type::nonNull(Type::string()),
                      'type' => Type::nonNull(Type::string()),
                      'selectedValue' => Type::nonNull(Type::string())
                    ]
                  ]))
                ]
              ]))
            ]
          ])))),
          'args' => [
            'userId' => Type::nonNull(Type::int())
          ],
          'resolve' => function ($root, $args) {
            $stmt = $this->db->prepare('
              SELECT 
                  ci.id as cart_item_id,
                  ci.quantity,
                  ci.product_id,
                  p.name as product_name,
                  p.brand,
                  p.category_name,
                  p.description,
                  cia.attribute_set_id,
                  cia.selected_value,
                  ast.name as attribute_name,
                  ast.type as attribute_type
              FROM cart_items ci
              JOIN products p ON ci.product_id = p.id
              LEFT JOIN cart_items_attributes cia ON ci.id = cia.order_id
              LEFT JOIN attribute_sets ast ON cia.attribute_set_id = ast.id
              WHERE ci.user_id = :user_id AND ci.is_order = false
            ');

            $stmt->execute([
              ':user_id' => $args['userId']
            ]);
            $cart = $stmt->fetchAll();

            return array_reduce($cart, function ($items, $row) {
              $itemId = $row['cart_item_id'];
              if (!isset($items[$itemId])) {
                $items[$itemId] = [
                  'id' => $itemId,
                  'quantity' => $row['quantity'],
                  'product' => [
                    'id' => $row['product_id'],
                    'name' => $row['product_name'],
                    'brand' => $row['brand'],
                    'category' => $row['category_name'],
                    'description' => $row['description'],
                    'attributes' => []
                  ]
                ];
              }

              if ($row['attribute_set_id']) {
                $items[$itemId]['product']['attributes'][] = [
                  'name' => $row['attribute_name'],
                  'type' => $row['attribute_type'],
                  'selectedValue' => $row['selected_value']
                ];
              }

              return $items;
            }, []);
          }
        ]
      ],
    ]);
  }
}
