<?php

namespace App\Models;

class QuoteItem
{
    public int $id;
    public int $quote_id;
    public string $description;
    public ?string $item_date;
    public float $quantity;
    public ?string $unit;
    public float $unit_price;
    public float $total;
    public int $sort_order;

    public static function findById(int $id): ?self
    {
        $db = \App\Database\Database::getInstance();
        $result = $db->fetchOne("SELECT * FROM quote_items WHERE id = ?", [$id]);
        
        if (!$result) {
            return null;
        }
        
        return self::hydrate($result);
    }

    public static function findAllByQuoteId(int $quoteId): array
    {
        $db = \App\Database\Database::getInstance();
        $results = $db->fetchAll(
            "SELECT * FROM quote_items WHERE quote_id = ? ORDER BY sort_order",
            [$quoteId]
        );
        
        return array_map(fn($r) => self::hydrate($r), $results);
    }

    public static function create(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        
        $quantity = $data['quantity'] ?? 1;
        $unitPrice = $data['unit_price'] ?? 0;
        $total = $quantity * $unitPrice;
        
        $db->query(
            "INSERT INTO quote_items (quote_id, description, item_date, quantity, unit, unit_price, total, sort_order) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['quote_id'],
                $data['description'],
                $data['item_date'] ?? null,
                $quantity,
                $data['unit'] ?? null,
                $unitPrice,
                $total,
                $data['sort_order'] ?? 0
            ]
        );
        
        return self::findById($db->lastInsertId());
    }

    public function update(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        
        $quantity = $data['quantity'] ?? $this->quantity;
        $unitPrice = $data['unit_price'] ?? $this->unit_price;
        $total = $quantity * $unitPrice;
        
        $db->query(
            "UPDATE quote_items SET description = ?, item_date = ?, quantity = ?, unit = ?, 
             unit_price = ?, total = ?, sort_order = ? WHERE id = ?",
            [
                $data['description'] ?? $this->description,
                $data['item_date'] ?? $this->item_date,
                $quantity,
                $data['unit'] ?? $this->unit,
                $unitPrice,
                $total,
                $data['sort_order'] ?? $this->sort_order,
                $this->id
            ]
        );
        
        return self::findById($this->id);
    }

    public function delete(): bool
    {
        $db = \App\Database\Database::getInstance();
        $db->query("DELETE FROM quote_items WHERE id = ?", [$this->id]);
        return true;
    }

    public static function deleteAllByQuoteId(int $quoteId): bool
    {
        $db = \App\Database\Database::getInstance();
        $db->query("DELETE FROM quote_items WHERE quote_id = ?", [$quoteId]);
        return true;
    }

    private static function hydrate(array $data): self
    {
        $item = new self();
        foreach ($data as $key => $value) {
            if (property_exists($item, $key)) {
                $item->$key = $value;
            }
        }
        return $item;
    }
}
