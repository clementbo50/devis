<?php

namespace App\Models;

class Client
{
    public int $id;
    public int $user_id;
    public string $type;
    public string $name;
    public ?string $address;
    public ?string $siret;
    public ?string $code_ape;
    public ?string $service;
    public string $created_at;
    public string $updated_at;

    public static function findById(int $id): ?self
    {
        $db = \App\Database\Database::getInstance();
        $result = $db->fetchOne("SELECT * FROM clients WHERE id = ?", [$id]);
        
        if (!$result) {
            return null;
        }
        
        return self::hydrate($result);
    }

    public static function findAllByUserId(int $userId): array
    {
        $db = \App\Database\Database::getInstance();
        $results = $db->fetchAll("SELECT * FROM clients WHERE user_id = ? ORDER BY name", [$userId]);
        
        return array_map(fn($r) => self::hydrate($r), $results);
    }

    public static function create(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        $db->query(
            "INSERT INTO clients (user_id, type, name, address, siret, code_ape, service) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'],
                $data['type'] ?? 'particulier',
                $data['name'],
                $data['address'] ?? null,
                $data['siret'] ?? null,
                $data['code_ape'] ?? null,
                $data['service'] ?? null
            ]
        );
        
        return self::findById($db->lastInsertId());
    }

    public function update(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        $db->query(
            "UPDATE clients SET type = ?, name = ?, address = ?, siret = ?, code_ape = ?, service = ? WHERE id = ?",
            [
                $data['type'] ?? $this->type,
                $data['name'] ?? $this->name,
                $data['address'] ?? $this->address,
                $data['siret'] ?? $this->siret,
                $data['code_ape'] ?? $this->code_ape,
                $data['service'] ?? $this->service,
                $this->id
            ]
        );
        
        return self::findById($this->id);
    }
    
    public static function delete(int $id): void
    {
        $db = \App\Database\Database::getInstance();
        $db->query("DELETE FROM clients WHERE id = ?", [$id]);
    }
    
    private static function hydrate(array $data): self
    {
        $client = new self();
        foreach ($data as $key => $value) {
            if (property_exists($client, $key)) {
                $client->$key = $value;
            }
        }
        return $client;
    }
}
