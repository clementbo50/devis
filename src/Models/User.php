<?php

namespace App\Models;

class User
{
    public int $id;
    public string $email;
    public string $password_hash;
    public string $name;
    public string $role;
    public string $created_at;
    public string $updated_at;

    public static function findByEmail(string $email): ?self
    {
        $db = \App\Database\Database::getInstance();
        $result = $db->fetchOne(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
        
        if (!$result) {
            return null;
        }
        
        $user = new self();
        foreach ($result as $key => $value) {
            $user->$key = $value;
        }
        return $user;
    }

    public static function findById(int $id): ?self
    {
        $db = \App\Database\Database::getInstance();
        $result = $db->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
        
        if (!$result) {
            return null;
        }
        
        $user = new self();
        foreach ($result as $key => $value) {
            $user->$key = $value;
        }
        return $user;
    }

    public static function create(array $data): self
    {
        $db = \App\Database\Database::getInstance();
        $db->query(
            "INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, ?)",
            [$data['email'], $data['password_hash'], $data['name'], $data['role'] ?? 'user']
        );
        
        return self::findById($db->lastInsertId());
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }
}
