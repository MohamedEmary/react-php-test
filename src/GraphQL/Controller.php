<?php

namespace App\GraphQL;

use App\Database\Connection;
use App\GraphQL\Type\MutationType;
use App\GraphQL\Type\QueryType;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use GraphQL\GraphQL as GraphQLBase;
use RuntimeException;
use Throwable;

class Controller
{
    public static function handle()
    {
        try {
            $db = Connection::getInstance();
            $schema = new Schema(
                (new SchemaConfig())
                    ->setQuery(new QueryType($db))
                    ->setMutation(new MutationType($db))
            );

            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);
            $query = $input['query'];
            $variableValues = $input['variables'] ?? null;

            $result = GraphQLBase::executeQuery(
                $schema,
                $query,
                [],
                null,
                $variableValues
            );
            $output = $result->toArray();
        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($output);
    }
}
