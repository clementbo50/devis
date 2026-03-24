<?php 
$title = 'Modifier Client - Générateur de Devis';

$client = $client ?? null;

ob_start();
?>
<form method="POST" action="/clients/edit?id=<?= $client->id ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Informations du client</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nom / Raison sociale *</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($client->name ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type de client</label>
                            <select class="form-select" id="type" name="type">
                                <option value="particulier" <?= ($client->type ?? '') === 'particulier' ? 'selected' : '' ?>>Particulier</option>
                                <option value="entreprise" <?= ($client->type ?? '') === 'entreprise' ? 'selected' : '' ?>>Entreprise</option>
                                <option value="public" <?= ($client->type ?? '') === 'public' ? 'selected' : '' ?>>Entité publique</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($client->address ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="siret" class="form-label">SIRET</label>
                            <input type="text" class="form-control" id="siret" name="siret" value="<?= htmlspecialchars($client->siret ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="code_ape" class="form-label">Code APE</label>
                            <input type="text" class="form-control" id="code_ape" name="code_ape" value="<?= htmlspecialchars($client->code_ape ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="service" class="form-label">Service / Département</label>
                        <input type="text" class="form-control" id="service" name="service" value="<?= htmlspecialchars($client->service ?? '') ?>">
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
                        <a href="/clients" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();

include __DIR__ . '/../layout.php';
