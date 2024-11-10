<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ProductImageType extends ObjectType
{
  public function __construct()
  {
    parent::__construct([
      'name' => 'ProductImage',
      'fields' => [
        'id' => Type::nonNull(Type::int()),
        'image_url' => Type::nonNull(Type::string()),
      ]
    ]);
  }
}
