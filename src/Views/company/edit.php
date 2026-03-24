<?php 
$title = 'Mon Entreprise - Générateur de Devis';

if (!defined('DOC_ROOT')) {
    define('DOC_ROOT', '/var/www/html');
}

$company = $company ?? null;
$logoPath = $company->logo_path ?? null;

ob_start();
?>
<form method="POST" action="/company/edit" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-image"></i> Logo</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-3">
                            <?php 
                            $logoFullPath = DOC_ROOT . $logoPath;
                            if ($logoPath && file_exists($logoFullPath)): ?>
                                <img src="<?= $logoPath ?>" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                            <?php elseif (!empty($company->name)): ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 100px; width: 100px;">
                                    <i class="bi bi-building text-muted" style="font-size: 2rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label for="logo" class="form-label">Charger un logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WebP. Taille max: 2 Mo.</small>
                            <?php if ($logoPath): ?>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                    <label class="form-check-label" for="remove_logo">Supprimer le logo</label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Informations de l'entreprise</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nom de l'entreprise *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($company->name ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="legal_status" class="form-label">Statut juridique</label>
                            <select class="form-select" id="legal_status" name="legal_status">
                                <option value="">Sélectionner...</option>
                                <option value="EI" <?= ($company->legal_status ?? '') === 'EI' ? 'selected' : '' ?>>EI (Entreprise Individuelle)</option>
                                <option value="SARL" <?= ($company->legal_status ?? '') === 'SARL' ? 'selected' : '' ?>>SARL</option>
                                <option value="SAS" <?= ($company->legal_status ?? '') === 'SAS' ? 'selected' : '' ?>>SAS</option>
                                <option value="SA" <?= ($company->legal_status ?? '') === 'SA' ? 'selected' : '' ?>>SA</option>
                                <option value="EURL" <?= ($company->legal_status ?? '') === 'EURL' ? 'selected' : '' ?>>EURL</option>
                                <option value="Micro-entreprise" <?= ($company->legal_status ?? '') === 'Micro-entreprise' ? 'selected' : '' ?>>Micro-entreprise</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="representative_name" class="form-label">Nom du représentant</label>
                            <input type="text" class="form-control" id="representative_name" name="representative_name" value="<?= htmlspecialchars($company->representative_name ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="representative_firstname" class="form-label">Prénom du représentant</label>
                            <input type="text" class="form-control" id="representative_firstname" name="representative_firstname" value="<?= htmlspecialchars($company->representative_firstname ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($company->address ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Informations fiscales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="siret" class="form-label">SIRET</label>
                            <input type="text" class="form-control" id="siret" name="siret" value="<?= htmlspecialchars($company->siret ?? '') ?>" placeholder="123 456 789 00012">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tva_number" class="form-label">Numéro de TVA intracommunautaire</label>
                            <input type="text" class="form-control" id="tva_number" name="tva_number" value="<?= htmlspecialchars($company->tva_number ?? '') ?>" placeholder="FR12345678901">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tva_exempt" name="tva_exempt" <?= ($company->tva_exempt ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="tva_exempt">
                                Exonéré de TVA (art. 293 B du CGI) - Auto-entrepreneur
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-envelope"></i> Contact</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($company->email ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($company->phone ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4" style="background-color: #f1faee;">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Actions</h6>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn text-white" style="background-color: #1d3557;">
                            <i class="bi bi-check-lg"></i> Enregistrer
                        </button>
                        <a href="/quotes" class="btn btn-outline-secondary">Retour aux devis</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();

include __DIR__ . '/../layout.php';
