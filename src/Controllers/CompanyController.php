<?php

namespace App\Controllers;

use App\Models\Company;

class CompanyController
{
    private const UPLOAD_DIR = '/var/www/html/public/assets/uploads/';
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    private const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    private const PUBLIC_URL = '/assets/uploads/';
    
    public function edit(): void
    {
        AuthController::requireAuth();
        
        $userId = AuthController::getUserId();
        $company = Company::findByUserId($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($userId, $company ? $company->id : null);
            return;
        }
        
        include __DIR__ . '/../Views/company/edit.php';
    }
    
    private function update(int $userId, ?int $companyId): void
    {
        $data = [
            'name' => $_POST['name'] ?? 'Mon Entreprise',
            'legal_status' => $_POST['legal_status'] ?? null,
            'representative_name' => $_POST['representative_name'] ?? null,
            'representative_firstname' => $_POST['representative_firstname'] ?? null,
            'address' => $_POST['address'] ?? null,
            'siret' => $_POST['siret'] ?? null,
            'tva_number' => $_POST['tva_number'] ?? null,
            'tva_exempt' => isset($_POST['tva_exempt']) ? true : false,
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
        ];
        
        $existingCompany = $companyId ? Company::findById($companyId) : null;
        $currentLogoPath = $existingCompany->logo_path ?? null;
        
        // Handle logo removal
        if (isset($_POST['remove_logo']) && $currentLogoPath) {
            $this->deleteLogoFile($currentLogoPath);
            $data['logo_path'] = null;
        }
        
        // Handle new logo upload
        if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleLogoUpload($_FILES['logo'], $currentLogoPath);
            if ($uploadResult['success']) {
                $data['logo_path'] = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['error'];
                header('Location: /company/edit');
                exit;
            }
        } else {
            $data['logo_path'] = $currentLogoPath;
            if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (max 2 Mo)',
                    UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux',
                    UPLOAD_ERR_PARTIAL => 'Fichier partiellement uploadé',
                    UPLOAD_ERR_NO_TMP_DIR => 'Erreur temporaire',
                ];
                $errorMsg = $uploadErrors[$_FILES['logo']['error']] ?? 'Erreur d\'upload';
                $_SESSION['error'] = $errorMsg;
                header('Location: /company/edit');
                exit;
            }
        }
        
        if ($companyId) {
            $company = Company::findById($companyId);
            $company->update($data);
            $_SESSION['success'] = 'Entreprise mise à jour avec succès';
        } else {
            $data['user_id'] = $userId;
            Company::create($data);
            $_SESSION['success'] = 'Entreprise créée avec succès';
        }
        header('Location: /company/edit');
        exit;
    }
    
    private function handleLogoUpload(array $file, ?string $currentPath): array
    {
        $uploadDir = self::UPLOAD_DIR;
        
        // Check directory is writable
        if (!is_writable($uploadDir)) {
            return ['success' => false, 'error' => 'Répertoire non accessible en écriture'];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = $uploadDir . $filename;
        
        // Delete old logo if exists
        if ($currentPath) {
            $this->deleteLogoFile($currentPath);
        }
        
        // Move uploaded file - try copy first as fallback
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'path' => self::PUBLIC_URL . $filename];
        }
        
        // Fallback: use copy if move_uploaded_file fails (e.g., different filesystem)
        if (copy($file['tmp_name'], $destination)) {
            return ['success' => true, 'path' => self::PUBLIC_URL . $filename];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de l\'upload du fichier.'];
    }
    
    private function deleteLogoFile(?string $logoPath): void
    {
        if ($logoPath) {
            $filePath = $logoPath;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
