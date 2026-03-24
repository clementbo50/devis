<?php

namespace App\Models;

class Quote
{
    public int $id;
    public int $user_id;
    public int $company_id;
    public int $client_id;
    public string $quote_number;
    public string $quote_date;
    public string $valid_until;
    public ?string $note;
    public ?string $conditions;
    public float $tva_rate;
    public ?float $tva_custom_rate;
    public float $total_ht;
    public float $total_tva;
    public float $total_ttc;
    public string $status;
    public string $created_at;
    public string $updated_at;

    private ?Company $company = null;
    private ?Client $client = null;
    private ?array $items = null;

    public static function findById(int $id): ?self
    {
        $db = \App\Database\Database::getInstance();
        $result = $db->fetchOne("SELECT * FROM quotes WHERE id = ?", [$id]);
        
        if (!$result) {
            return null;
        }
        
        return self::hydrate($result);
    }

    public static function findAllByUserId(int $userId): array
    {
        $db = \App\Database\Database::getInstance();
        $results = $db->fetchAll(
            "SELECT * FROM quotes WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
        
        return array_map(fn($r) => self::hydrate($r), $results);
    }

    public static function create(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        $db->query(
            "INSERT INTO quotes (user_id, company_id, client_id, quote_number, quote_date, valid_until, 
             note, conditions, tva_rate, tva_custom_rate, total_ht, total_tva, total_ttc, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'],
                $data['company_id'],
                $data['client_id'],
                $data['quote_number'],
                $data['quote_date'],
                $data['valid_until'],
                $data['note'] ?? null,
                $data['conditions'] ?? null,
                $data['tva_rate'] ?? 20.00,
                $data['tva_custom_rate'] ?? null,
                $data['total_ht'] ?? 0,
                $data['total_tva'] ?? 0,
                $data['total_ttc'] ?? 0,
                $data['status'] ?? 'draft'
            ]
        );
        
        return self::findById($db->lastInsertId());
    }

    public function update(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        $db->query(
            "UPDATE quotes SET quote_number = ?, quote_date = ?, valid_until = ?, note = ?, conditions = ?,
             tva_rate = ?, tva_custom_rate = ?, total_ht = ?, total_tva = ?, total_ttc = ?, status = ?
             WHERE id = ?",
            [
                $data['quote_number'] ?? $this->quote_number,
                $data['quote_date'] ?? $this->quote_date,
                $data['valid_until'] ?? $this->valid_until,
                $data['note'] ?? $this->note,
                $data['conditions'] ?? $this->conditions,
                $data['tva_rate'] ?? $this->tva_rate,
                $data['tva_custom_rate'] ?? $this->tva_custom_rate,
                $data['total_ht'] ?? $this->total_ht,
                $data['total_tva'] ?? $this->total_tva,
                $data['total_ttc'] ?? $this->total_ttc,
                $data['status'] ?? $this->status,
                $this->id
            ]
        );
        
        return self::findById($this->id);
    }

    public function delete(): bool
    {
        $db = \App\Database\Database::getInstance();
        $db->query("DELETE FROM quotes WHERE id = ?", [$this->id]);
        return true;
    }

    public function getCompany(): ?Company
    {
        if ($this->company === null) {
            $this->company = Company::findById($this->company_id);
        }
        return $this->company;
    }

    public function getClient(): ?Client
    {
        if ($this->client === null) {
            $this->client = Client::findById($this->client_id);
        }
        return $this->client;
    }

    public function getItems(): array
    {
        if ($this->items === null) {
            $this->items = QuoteItem::findAllByQuoteId($this->id);
        }
        return $this->items;
    }

    public static function generateQuoteNumber(int $userId): string
    {
        $db = \App\Database\Database::getInstance();
        $year = date('Y');
        $result = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM quotes WHERE user_id = ? AND quote_number LIKE ?",
            [$userId, "DEVIS-$year-%"]
        );
        
        $nextNum = ($result['cnt'] ?? 0) + 1;
        return sprintf("DEVIS-%s-%04d", $year, $nextNum);
    }

    private static function hydrate(array $data): self
    {
        $quote = new self();
        foreach ($data as $key => $value) {
            if (property_exists($quote, $key)) {
                $quote->$key = $value;
            }
        }
        return $quote;
    }
}
