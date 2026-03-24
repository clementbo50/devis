<?php

namespace App\Models;

class Company
{
    public int $id;
    public int $user_id;
    public string $name;
    public ?string $legal_status;
    public ?string $representative_name;
    public ?string $representative_firstname;
    public ?string $address;
    public ?string $siret;
    public ?string $tva_number;
    public bool $tva_exempt;
    public ?string $email;
    public ?string $phone;
    public ?string $logo_path;
    public string $created_at;
    public string $updated_at;

    public static function findById(int $id): ?self
    {
        $db = \App\Database\Database::getInstance();
        $result = $db->fetchOne("SELECT * FROM companies WHERE id = ?", [$id]);
        
        if (!$result) {
            return null;
        }
        
        return self::hydrate($result);
    }

    public static function findByUserId(int $userId): ?self
    {
        $db = \App\Database\Database::getInstance();
        $result = $db->fetchOne("SELECT * FROM companies WHERE user_id = ?", [$userId]);
        
        if (!$result) {
            return null;
        }
        
        return self::hydrate($result);
    }

    public static function create(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        $db->query(
            "INSERT INTO companies (user_id, name, legal_status, representative_name, representative_firstname, 
             address, siret, tva_number, tva_exempt, email, phone, logo_path) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'],
                $data['name'],
                $data['legal_status'] ?? null,
                $data['representative_name'] ?? null,
                $data['representative_firstname'] ?? null,
                $data['address'] ?? null,
                $data['siret'] ?? null,
                $data['tva_number'] ?? null,
                (int) ($data['tva_exempt'] ?? false),
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['logo_path'] ?? null
            ]
        );
        
        return self::findById($db->lastInsertId());
    }

    public function update(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        
        $logoPath = $data['logo_path'] ?? $this->logo_path;
        
        $db->query(
            "UPDATE companies SET name = ?, legal_status = ?, representative_name = ?, representative_firstname = ?,
             address = ?, siret = ?, tva_number = ?, tva_exempt = ?, email = ?, phone = ?, logo_path = ?
             WHERE id = ?",
            [
                $data['name'],
                $data['legal_status'] ?? null,
                $data['representative_name'] ?? null,
                $data['representative_firstname'] ?? null,
                $data['address'] ?? null,
                $data['siret'] ?? null,
                $data['tva_number'] ?? null,
                (int) ($data['tva_exempt'] ?? false),
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $logoPath,
                $this->id
            ]
        );
        
        return self::findById($this->id);
    }

    private static function hydrate(array $data): self
    {
        $company = new self();
        foreach ($data as $key => $value) {
            if (property_exists($company, $key)) {
                $company->$key = $value;
            }
        }
        return $company;
    }
}
