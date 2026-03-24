<?php

namespace App\Controllers;

use App\Models\Quote;
use App\Models\Client;
use App\Models\QuoteItem;
use App\Models\Company;

class QuoteController
{
    public function index(): void
    {
        AuthController::requireAuth();
        
        $userId = AuthController::getUserId();
        $quotes = Quote::findAllByUserId($userId);
        
        include __DIR__ . '/../Views/quotes/index.php';
    }

    public function create(): void
    {
        AuthController::requireAuth();
        
        $userId = AuthController::getUserId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveQuote($userId);
            return;
        }
        
        $company = Company::findByUserId($userId);
        $clients = Client::findAllByUserId($userId);
        $quoteNumber = Quote::generateQuoteNumber($userId);
        $tvaExempt = $company && $company->tva_exempt;
        
        include __DIR__ . '/../Views/quotes/create.php';
    }

    public function edit(): void
    {
        AuthController::requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /quotes');
            exit;
        }
        
        $quote = Quote::findById($id);
        if (!$quote || $quote->user_id !== AuthController::getUserId()) {
            header('Location: /quotes');
            exit;
        }
        
        $userId = AuthController::getUserId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateQuote($id);
            return;
        }
        
        $company = Company::findByUserId($userId);
        $clients = Client::findAllByUserId($userId);
        $client = $quote->getClient();
        $items = $quote->getItems();
        $tvaExempt = $company && $company->tva_exempt;
        
        include __DIR__ . '/../Views/quotes/edit.php';
    }

    public function show(): void
    {
        AuthController::requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /quotes');
            exit;
        }
        
        $quote = Quote::findById($id);
        if (!$quote || $quote->user_id !== AuthController::getUserId()) {
            header('Location: /quotes');
            exit;
        }
        
        $company = $quote->getCompany();
        $client = $quote->getClient();
        $items = $quote->getItems();
        
        include __DIR__ . '/../Views/quotes/show.php';
    }

    public function delete(): void
    {
        AuthController::requireAuth();
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: /quotes');
            exit;
        }
        
        $quote = Quote::findById($id);
        if ($quote && $quote->user_id === AuthController::getUserId()) {
            QuoteItem::deleteAllByQuoteId($id);
            $quote->delete();
        }
        
        header('Location: /quotes');
        exit;
    }

    public function updateStatus(): void
    {
        AuthController::requireAuth();
        
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (!$id || !$status) {
            header('Location: /quotes');
            exit;
        }
        
        $quote = Quote::findById($id);
        if ($quote && $quote->user_id === AuthController::getUserId()) {
            $quote->update(['status' => $status]);
        }
        
        header('Location: /quotes/show?id=' . $id);
        exit;
    }

    private function saveQuote(int $userId): void
    {
        $company = Company::findByUserId($userId);
        
        // Use client selected from dropdown (required field)
        if (empty($_POST['client_id'])) {
            throw new \Exception('Veuillez sélectionner un client');
        }
        
        $client = Client::findById($_POST['client_id']);
        
        if (!$client) {
            throw new \Exception('Client sélectionné invalide');
        }
        
        $isCompanyExempt = $company && $company->tva_exempt;
        
        if ($isCompanyExempt) {
            $tvaRate = 0;
        } else {
            $tvaRate = $_POST['tva_rate'] === 'custom' 
                ? floatval($_POST['tva_custom_rate'] ?? 0) 
                : floatval($_POST['tva_rate'] ?? 20);
        }
        
        $quote = Quote::create([
            'user_id' => $userId,
            'company_id' => $company->id,
            'client_id' => $client->id,
            'quote_number' => $_POST['quote_number'],
            'quote_date' => $_POST['quote_date'],
            'valid_until' => $_POST['valid_until'],
            'note' => $_POST['note'] ?? null,
            'conditions' => $_POST['conditions'] ?? null,
            'tva_rate' => $tvaRate,
            'status' => $_POST['status'] ?? 'draft'
        ]);
        
        $this->saveQuoteItems($quote->id);
        
        header('Location: /quotes/show?id=' . $quote->id);
        exit;
    }

    private function updateQuote(int $id): void
    {
        $quote = Quote::findById($id);
        $company = Company::findById($quote->company_id);
        
        // Use client selected from dropdown (required field)
        if (empty($_POST['client_id'])) {
            throw new \Exception('Veuillez sélectionner un client');
        }
        
        $client = Client::findById($_POST['client_id']);
        
        if (!$client) {
            throw new \Exception('Client sélectionné invalide');
        }
        
        $isCompanyExempt = $company && $company->tva_exempt;
        
        if ($isCompanyExempt) {
            $tvaRate = 0;
        } else {
            $tvaRate = $_POST['tva_rate'] === 'custom' 
                ? floatval($_POST['tva_custom_rate'] ?? 0) 
                : floatval($_POST['tva_rate'] ?? 20);
        }
        
        $quoteData = [
            'quote_number' => $_POST['quote_number'],
            'quote_date' => $_POST['quote_date'],
            'valid_until' => $_POST['valid_until'],
            'note' => $_POST['note'] ?? null,
            'conditions' => $_POST['conditions'] ?? null,
            'tva_rate' => $tvaRate,
            'status' => $_POST['status'] ?? 'draft'
        ];
        
        // Update client_id if changed
        if (!empty($_POST['client_id'])) {
            $quoteData['client_id'] = $_POST['client_id'];
        }
        
        $quote->update($quoteData);
        
        QuoteItem::deleteAllByQuoteId($id);
        $this->saveQuoteItems($id);
        
        header('Location: /quotes/show?id=' . $id);
        exit;
    }

    private function saveQuoteItems(int $quoteId): void
    {
        $descriptions = $_POST['item_description'] ?? [];
        $itemDates = $_POST['item_date'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
        $units = $_POST['item_unit'] ?? [];
        $unitPrices = $_POST['item_unit_price'] ?? [];
        
        $itemsData = [];
        
        foreach ($descriptions as $index => $description) {
            if (empty($description)) continue;
            
            $quantity = floatval($quantities[$index] ?? 1);
            $unitPrice = floatval($unitPrices[$index] ?? 0);
            
            QuoteItem::create([
                'quote_id' => $quoteId,
                'description' => $description,
                'item_date' => !empty($itemDates[$index]) ? $itemDates[$index] : null,
                'quantity' => $quantity,
                'unit' => $units[$index] ?? null,
                'unit_price' => $unitPrice,
                'sort_order' => $index
            ]);
        }
        
        $this->recalculateTotals($quoteId);
    }

    private function recalculateTotals(int $quoteId): void
    {
        $quote = Quote::findById($quoteId);
        $items = $quote->getItems();
        
        $totalHT = 0;
        foreach ($items as $item) {
            $totalHT += $item->total;
        }
        
        $tvaRate = $quote->tva_rate;
        $totalTVA = $tvaRate > 0 ? $totalHT * $tvaRate / 100 : 0;
        $totalTTC = $totalHT + $totalTVA;
        
        $quote->update([
            'total_ht' => $totalHT,
            'total_tva' => $totalTVA,
            'total_ttc' => $totalTTC
        ]);
    }
}
