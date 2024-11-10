<?php

namespace App\GraphQL\Type;

use App\Database\Connection;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class PriceType extends ObjectType
{
  public function __construct()
  {
    parent::__construct([
      'name' => 'Price',
      'fields' => [
        'id' => Type::nonNull(Type::int()),
        'amount' => Type::nonNull(Type::float()),
        'currency' => [
          'type' => new CurrencyType(),
          'resolve' => function ($price) {
            $db = Connection::getInstance();
            $stmt = $db->prepare('
              SELECT *
              FROM currencies 
              WHERE label = ?
            ');
            $stmt->execute([$price['currency_label']]);
            return $stmt->fetch();
          }
        ]
      ]
    ]);
  }
}
