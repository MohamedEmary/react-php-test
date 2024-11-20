<?php

namespace App\GraphQL\Type;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use PDO;

class MutationType extends ObjectType
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
    parent::__construct([
      'name' => 'Mutation',
      'fields' => [
        'createUser' => [
          'type' => Type::int(),
          'resolve' => function () {
            $stmt = $this->db->prepare('INSERT INTO users () VALUES ()');
            $stmt->execute();
            return $this->db->lastInsertId();
          }
        ],
        'addToCart' => [
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
            // Check product stock
            $stmt = $this->db->prepare('SELECT in_stock FROM products WHERE id = ?');
            $stmt->execute([$args['productId']]);
            $product = $stmt->fetch();

            if (!$product || !$product['in_stock']) {
              throw new UserError('Product is out of stock');
            }

            $stmt = $this->db->prepare('
                          SELECT p.name, pr.amount, c.symbol as currency_symbol
                          FROM products p
                          JOIN prices pr ON pr.product_id = p.id 
                          JOIN currencies c ON c.label = pr.currency_label
                          WHERE p.id = ? AND pr.currency_label = "USD"
                      ');
            $stmt->execute([$args['productId']]);
            $productData = $stmt->fetch();

            // Get first product image
            $stmt = $this->db->prepare('
                          SELECT image_url 
                          FROM product_images 
                          WHERE product_id = ? 
                          LIMIT 1
                      ');
            $stmt->execute([$args['productId']]);
            $imageData = $stmt->fetch();

            // Get required attributes
            $stmt = $this->db->prepare('
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

            // Validate attributes
            if (count($requiredAttributes) > 0) {
              if (empty($args['attributes'])) {
                throw new UserError('Product attributes are required');
              }

              $validAttributeNames = array_column($requiredAttributes, 'name');

              foreach ($args['attributes'] as $attr) {
                if (!in_array($attr['name'], $validAttributeNames)) {
                  throw new UserError("Invalid attribute: {$attr['name']} is not valid for this product");
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

            // Check for existing cart item
            $stmt = $this->db->prepare('
                        SELECT id, quantity 
                        FROM cart_items 
                        WHERE user_id = ? AND product_id = ? AND is_order = FALSE
                    ');
            $stmt->execute([$args['userId'], $args['productId']]);
            $existingItem = $stmt->fetch();

            $quantity = $args['quantity'] ?? 1;

            if ($existingItem) {
              // Update existing cart item quantity
              $stmt = $this->db->prepare('
                            UPDATE cart_items 
                            SET quantity = quantity + ? 
                            WHERE id = ?
                        ');
              $stmt->execute([$quantity, $existingItem['id']]);
              $itemId = $existingItem['id'];
            } else {
              // Create new cart item
              $stmt = $this->db->prepare('
            INSERT INTO cart_items (
                user_id, 
                product_id, 
                quantity,
                unit_price,
                product_name,
                product_image,
                currency_symbol
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
              $stmt->execute([
                $args['userId'],
                $args['productId'],
                $quantity,
                $productData['amount'],
                $productData['name'],
                $imageData['image_url'],
                $productData['currency_symbol']
              ]);
              $itemId = $this->db->lastInsertId();

              // Add attributes for new item
              if (!empty($args['attributes'])) {
                $stmt = $this->db->prepare('
                                INSERT INTO cart_items_attributes 
                                (order_id, attribute_set_id, selected_value) 
                                VALUES (?, ?, ?)
                            ');
                foreach ($args['attributes'] as $attribute) {
                  $stmt->execute([$itemId, $attribute['name'], $attribute['value']]);
                }
              }
            }

            return $itemId;
          }
        ],
        'addOrder' => [
          'type' => Type::nonNull(Type::string()),
          'args' => [
            'userId' => Type::nonNull(Type::int())
          ],
          'resolve' => function ($root, $args) {
            $stmt = $this->db->prepare('
                              SELECT ci.id, ci.product_id 
                              FROM cart_items ci
                              WHERE ci.user_id = ? AND ci.is_order = FALSE
                            ');
            $stmt->execute([$args['userId']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($items)) {
              $cartItemIds = array_column($items, 'id');
              $productIds = array_column($items, 'product_id');

              $stmt = $this->db->prepare('
                                UPDATE cart_items 
                                SET is_order = TRUE 
                                WHERE id IN (' . str_repeat('?,', count($cartItemIds) - 1) . '?)
                              ');
              $stmt->execute($cartItemIds);
              return 'Successfully added order with products: ' . implode(', ', $productIds);
            } else {
              return 'No items in cart';
            }
          }
        ],
        'IncreaseCartItemQuantity' => [
          'type' => new ObjectType([
            'name' => 'CartItemUpdate',
            'fields' => [
              'id' => Type::nonNull(Type::id()),
              'quantity' => Type::nonNull(Type::int())
            ]
          ]),
          'args' => [
            'cartItemId' => Type::nonNull(Type::id())
          ],
          'resolve' => function ($root, $args) {
            $stmt = $this->db->prepare('
            UPDATE cart_items 
            SET quantity = quantity + 1 
            WHERE id = :cart_item_id
        ');
            $stmt->execute([':cart_item_id' => $args['cartItemId']]);

            $stmt = $this->db->prepare('
            SELECT id, quantity 
            FROM cart_items 
            WHERE id = :cart_item_id
        ');
            $stmt->execute([':cart_item_id' => $args['cartItemId']]);
            return $stmt->fetch();
          }
        ],
        'DecreaseCartItemQuantity' => [
          'type' => new ObjectType([
            'name' => 'CartItemUpdate',
            'fields' => [
              'id' => Type::nonNull(Type::id()),
              'quantity' => Type::nonNull(Type::int())
            ]
          ]),
          'args' => [
            'cartItemId' => Type::nonNull(Type::id())
          ],
          'resolve' => function ($root, $args) {
            $stmt = $this->db->prepare('
            UPDATE cart_items 
            SET quantity = GREATEST(quantity - 1, 1)
            WHERE id = :cart_item_id
        ');
            $stmt->execute([':cart_item_id' => $args['cartItemId']]);

            $stmt = $this->db->prepare('
            SELECT id, quantity 
            FROM cart_items 
            WHERE id = :cart_item_id
        ');
            $stmt->execute([':cart_item_id' => $args['cartItemId']]);
            return $stmt->fetch();
          }
        ]
      ],
    ]);
  }
}
