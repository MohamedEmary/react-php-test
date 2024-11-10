<?php

namespace App\GraphQL\Type;

use App\Database\Connection;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use PDO;

class PriceType extends ObjectType
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
    parent::__construct([
      'name' => 'Price',
      'fields' => [
        'id' => Type::nonNull(Type::int()),
        'amount' => Type::nonNull(Type::float()),
        'currency' => [
          'type' => new CurrencyType(),
          'resolve' => function ($price) {
            $stmt = $this->db->prepare('
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
