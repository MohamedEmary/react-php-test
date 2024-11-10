<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class AttributeSetGroupType extends ObjectType
{
  public function __construct()
  {
    parent::__construct([
      'name' => 'AttributeSetGroup',
      'fields' => [
        'name' => Type::nonNull(Type::string()),
        'type' => Type::nonNull(Type::string()),
        'values' => Type::listOf(Type::string())
      ]
    ]);
  }
}
