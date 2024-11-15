<?php

class DatabaseImporter
{
    private PDO $db;
    private array $statements = [];
    private array $data;

    public function __construct(string $jsonPath)
    {
        $this->loadJson($jsonPath);
        $this->connect();
        $this->prepareStatements();
    }

    private function loadJson(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception("JSON file not found: $path");
        }

        $jsonFile = file_get_contents($path);
        $this->data = json_decode($jsonFile, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }
    }

    private function connect(): void
    {
        $this->db = new PDO(
            // Replace with your database credentials
            'mysql:host=DB_HOST;dbname=DB_NAME;charset=utf8mb4',
            'DB_USERNAME',
            'DB_PASS',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    private function prepareStatements(): void
    {
        $this->statements = [
            'category' => $this->db->prepare(
                'INSERT IGNORE INTO categories (name) VALUES (?)'
            ),
            'product' => $this->db->prepare(
                'REPLACE INTO products (id, name, description, category_name, in_stock, brand) 
                 VALUES (?, ?, ?, ?, ?, ?)'
            ),
            'image' => $this->db->prepare(
                'INSERT INTO product_images (product_id, image_url) 
                 VALUES (?, ?)'
            ),
            'attributeSet' => $this->db->prepare(
                'INSERT IGNORE INTO attribute_sets (id, name, type) 
                 VALUES (?, ?, ?)'
            ),
            'attributeItem' => $this->db->prepare(
                'INSERT IGNORE INTO attribute_items 
                 (id, attribute_set_id, product_id, display_value, value)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE display_value = VALUES(display_value)'
            ),
            'currency' => $this->db->prepare(
                'INSERT IGNORE INTO currencies (label, symbol) 
                 VALUES (?, ?)'
            ),
            'price' => $this->db->prepare(
                'INSERT INTO prices (product_id, amount, currency_label) 
                 VALUES (?, ?, ?)'
            ),
            'deleteImages' => $this->db->prepare(
                'DELETE FROM product_images WHERE product_id = ?'
            ),
            'deleteAttributes' => $this->db->prepare(
                'DELETE FROM attribute_items WHERE product_id = ?'
            ),
            'deletePrices' => $this->db->prepare(
                'DELETE FROM prices WHERE product_id = ?'
            )
        ];
    }

    public function import(): void
    {
        try {
            $this->db->beginTransaction();

            $this->importCategories();
            $this->importProducts();

            $this->db->commit();
            echo "Import completed successfully\n";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Import failed: ' . $e->getMessage());
        }
    }

    private function importCategories(): void
    {
        foreach ($this->data['data']['categories'] as $category) {
            $this->statements['category']->execute([$category['name']]);
        }
    }

    private function importProducts(): void
    {
        foreach ($this->data['data']['products'] as $product) {
            $this->statements['deleteImages']->execute([$product['id']]);
            $this->statements['deleteAttributes']->execute([$product['id']]);
            $this->statements['deletePrices']->execute([$product['id']]);

            $this->statements['product']->execute([
                $product['id'],
                $product['name'],
                $product['description'],
                $product['category'],
                isset($product['inStock']) ? (int) $product['inStock'] : 1,
                $product['brand'] ?? null
            ]);

            $this->importImages($product);
            $this->importAttributes($product);
            $this->importPrices($product);
        }
    }

    private function importImages(array $product): void
    {
        if (isset($product['gallery'])) {
            foreach ($product['gallery'] as $imageUrl) {
                $this->statements['image']->execute([
                    $product['id'],
                    $imageUrl,
                ]);
            }
        }
    }

    private function importAttributes(array $product): void
    {
        if (isset($product['attributes'])) {
            foreach ($product['attributes'] as $attribute) {
                $this->statements['attributeSet']->execute([
                    $attribute['id'],
                    $attribute['name'],
                    $attribute['type']
                ]);

                foreach ($attribute['items'] as $item) {
                    $this->statements['attributeItem']->execute([
                        $item['id'],
                        $attribute['id'],
                        $product['id'],
                        $item['displayValue'],
                        $item['value']
                    ]);
                }
            }
        }
    }

    private function importPrices(array $product): void
    {
        if (isset($product['prices'])) {
            foreach ($product['prices'] as $price) {
                $this->statements['currency']->execute([
                    $price['currency']['label'],
                    $price['currency']['symbol']
                ]);

                $this->statements['price']->execute([
                    $product['id'],
                    $price['amount'],
                    $price['currency']['label']
                ]);
            }
        }
    }
}

try {
    $importer = new DatabaseImporter('data.json');
    $importer->import();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
