<?php

namespace App\GraphQL\Type;

use App\Database\Connection;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MutationType extends ObjectType
{
  public function __construct()
  {
    parent::__construct(
      [
        'name' => 'Mutation',
        'fields' => [
          'createUser' => [
            'type' => Type::int(),
            'resolve' => function () {
              $db = Connection::getInstance();
              $stmt = $db->prepare('INSERT INTO users () VALUES ()');
              $stmt->execute();
              return $db->lastInsertId();
            }
          ],
          'createOrder' => [
            'type' => Type::int(),
            'args' => [
              'userId' => Type::nonNull(Type::int()),
              'productId' => Type::nonNull(Type::string()),
              'quantity' => Type::int(),
              'attributes' => Type::listOf(new InputObjectType([
                'name' => 'AttributeInput',
                'fields' => [
                  'name' => Type::nonNull(Type::string()),
                  'value' => Type::nonNull(Type::string())
                ]
              ]))
            ],
            'resolve' => function ($root, $args) {
              $db = Connection::getInstance();

              $stmt = $db->prepare('SELECT in_stock FROM products WHERE id = ?');
              $stmt->execute([$args['productId']]);
              $product = $stmt->fetch();

              if (!$product || !$product['in_stock']) {
                throw new UserError('Product is out of stock');
              }

              $stmt = $db->prepare('
                  SELECT DISTINCT 
                      attr_set.id as set_id,
                      attr_set.name,
                      GROUP_CONCAT(attr_it.value) as allowed_values
                  FROM attribute_sets attr_set
                  JOIN attribute_items attr_it 
                      ON attr_it.attribute_set_id = attr_set.id
                  WHERE attr_it.product_id = ?
                  GROUP BY attr_set.id, attr_set.name
              ');
              $stmt->execute([$args['productId']]);
              $requiredAttributes = $stmt->fetchAll();

              if (count($requiredAttributes) > 0) {
                if (empty($args['attributes'])) {
                  throw new UserError('Product attributes are required');
                }

                $validAttributeNames = array_column($requiredAttributes, 'name');

                foreach ($args['attributes'] as $attr) {
                  if (!in_array($attr['name'], $validAttributeNames)) {
                    throw new UserError("Invalid attribute: {$attr['name']} is not a valid attribute for this product");
                  }
                }

                $providedAttrs = [];
                foreach ($args['attributes'] as $attr) {
                  $providedAttrs[$attr['name']] = $attr['value'];
                }

                foreach ($requiredAttributes as $required) {
                  if (!isset($providedAttrs[$required['name']])) {
                    throw new UserError("Missing required attribute: {$required['name']}");
                  }

                  $allowedValues = explode(',', $required['allowed_values']);
                  if (!in_array($providedAttrs[$required['name']], $allowedValues)) {
                    throw new UserError("Invalid value for {$required['name']}");
                  }
                }
              }

              $stmt = $db->prepare('INSERT INTO orders (user_id, product_id, quantity) VALUES (?, ?, ?)');
              $stmt->execute([$args['userId'], $args['productId'], $args['quantity'] ?? 1]);

              $orderId = $db->lastInsertId();

              if (!empty($args['attributes'])) {
                $stmt = $db->prepare('INSERT INTO order_attributes (order_id, attribute_set_id, selected_value) VALUES (?, ?, ?)');
                foreach ($args['attributes'] as $attribute) {
                  $stmt->execute([$orderId, $attribute['name'], $attribute['value']]);
                }
              }

              return $orderId;
            }
          ]
        ],
      ]
    );
  }
}
