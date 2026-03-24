<?php

namespace App\Controllers;

use App\Models\Client;

class ClientController
{
    public function index(): void
    {
        AuthController::requireAuth();
        
        $userId = AuthController::getUserId();
        $clients = Client::findAllByUserId($userId);
        
        include __DIR__ . '/../Views/clients/index.php';
    }
    
    public function create(): void
    {
        AuthController::requireAuth();
        
        $userId = AuthController::getUserId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save($userId);
            return;
        }
        
        include __DIR__ . '/../Views/clients/create.php';
    }
    
    public function edit(): void
    {
        AuthController::requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /clients');
            exit;
        }
        
        $client = Client::findById($id);
        if (!$client || $client->user_id !== AuthController::getUserId()) {
            header('Location: /clients');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }
        
        include __DIR__ . '/../Views/clients/edit.php';
    }
    
    public function delete(): void
    {
        AuthController::requireAuth();
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: /clients');
            exit;
        }
        
        $client = Client::findById($id);
        if ($client && $client->user_id === AuthController::getUserId()) {
            Client::delete($id);
        }
        
        header('Location: /clients');
        exit;
    }
    
    private function save(int $userId): void
    {
        $client = Client::create([
            'user_id' => $userId,
            'type' => $_POST['type'] ?? 'particulier',
            'name' => $_POST['name'],
            'address' => $_POST['address'] ?? null,
            'siret' => $_POST['siret'] ?? null,
            'code_ape' => $_POST['code_ape'] ?? null,
            'service' => $_POST['service'] ?? null
        ]);
        
        $_SESSION['success'] = 'Client créé avec succès';
        header('Location: /clients');
        exit;
    }
    
    private function update(int $id): void
    {
        $client = Client::findById($id);
        $client->update([
            'type' => $_POST['type'] ?? $client->type,
            'name' => $_POST['name'] ?? $client->name,
            'address' => $_POST['address'] ?? $client->address,
            'siret' => $_POST['siret'] ?? $client->siret,
            'code_ape' => $_POST['code_ape'] ?? $client->code_ape,
            'service' => $_POST['service'] ?? $client->service
        ]);
        
        $_SESSION['success'] = 'Client mis à jour avec succès';
        header('Location: /clients');
        exit;
    }
}
