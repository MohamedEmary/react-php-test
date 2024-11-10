<?php

namespace App\GraphQL\Type;

use App\Database\Connection;
use App\GraphQL\Type\AttributeSetGroupType;
use App\GraphQL\Type\PriceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ProductType extends ObjectType
{
  public function __construct()
  {
    parent::__construct([
      'name' => 'Product',
      'description' => 'A product in the catalog',
      'fields' => [
        'id' => Type::nonNull(Type::string()),
        'name' => Type::nonNull(Type::string()),
        'description' => Type::string(),
        'brand' => Type::string(),
        'category_name' => Type::string(),
        'in_stock' => Type::boolean(),
        'images' => [
          'type' => Type::listOf(new ProductImageType()),
          'resolve' => function ($product) {
            $db = Connection::getInstance();
            $stmt = $db->prepare('
                  SELECT id, image_url
                  FROM product_images 
                  WHERE product_id = ? 
              ');
            $stmt->execute([$product['id']]);
            return $stmt->fetchAll();
          }
        ],
        'attributes' => [
          'type' => Type::listOf(new AttributeSetGroupType()),
          'resolve' => function ($product) {
            $db = Connection::getInstance();
            $stmt = $db->prepare('
                  SELECT DISTINCT attr_set.id, 
                        attr_set.name, 
                        attr_set.type,
                        GROUP_CONCAT(attr_it.display_value) as value_list
                  FROM attribute_sets attr_set
                  JOIN attribute_items attr_it ON attr_it.attribute_set_id = attr_set.id 
                  WHERE attr_it.product_id = ?
                  GROUP BY attr_set.id, attr_set.name, attr_set.type
            ');
            $stmt->execute([$product['id']]);
            return array_map(function ($row) {
              return [
                'id' => $row['id'],
                'name' => $row['name'],
                'type' => $row['type'],
                'values' => explode(',', $row['value_list'])
              ];
            }, $stmt->fetchAll());
          }
        ],
        'prices' => [
          'type' => Type::listOf(new PriceType()),
          'resolve' => function ($product) {
            $db = Connection::getInstance();
            $stmt = $db->prepare('
                  SELECT *
                  FROM prices
                  WHERE product_id = ?
              ');
            $stmt->execute([$product['id']]);
            return $stmt->fetchAll();
          }
        ]
      ]
    ]);
  }
}
