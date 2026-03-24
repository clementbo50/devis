<?php 
$title = 'Devis - ' . $quote->quote_number . ' - Générateur de Devis';

$statusLabels = [
    'draft' => ['Brouillon', 'secondary'],
    'sent' => ['Envoyé', 'info'],
    'accepted' => ['Accepté', 'success'],
    'rejected' => ['Refusé', 'danger']
];

$isExempt = ($company->tva_exempt == 1) || $quote->tva_rate == 0;

ob_start();
?>

<!-- Hidden fields for PDF generation -->
<input type="hidden" id="quote_number" value="<?= htmlspecialchars($quote->quote_number) ?>">
<input type="hidden" id="quote_date" value="<?= $quote->quote_date ?>">
<input type="hidden" id="valid_until" value="<?= $quote->valid_until ?>">
<input type="hidden" id="companyName" value="<?= htmlspecialchars($company->name ?? '') ?>">
<input type="hidden" id="companyLegalStatus" value="<?= htmlspecialchars($company->legal_status ?? '') ?>">
<input type="hidden" id="companyAddress" value="<?= htmlspecialchars($company->address ?? '') ?>">
<input type="hidden" id="companySiret" value="<?= htmlspecialchars($company->siret ?? '') ?>">
<input type="hidden" id="companyTva" value="<?= htmlspecialchars($company->tva_number ?? '') ?>">
<input type="hidden" id="companyEmail" value="<?= htmlspecialchars($company->email ?? '') ?>">
<input type="hidden" id="companyPhone" value="<?= htmlspecialchars($company->phone ?? '') ?>">
<input type="hidden" id="companyFirstname" value="<?= htmlspecialchars($company->representative_firstname ?? '') ?>">
<input type="hidden" id="companyLastname" value="<?= htmlspecialchars($company->representative_name ?? '') ?>">
<input type="hidden" id="companyTvaExempt" value="<?= $company->tva_exempt ?? 0 ?>">
<input type="hidden" id="companyLogoPath" value="<?= htmlspecialchars($company->logo_path ?? '') ?>">
<input type="hidden" id="client_name" value="<?= htmlspecialchars($client->name ?? '') ?>">
<input type="hidden" id="client_type" value="<?= htmlspecialchars($client->type ?? 'particulier') ?>">
<input type="hidden" id="client_address" value="<?= htmlspecialchars($client->address ?? '') ?>">
<input type="hidden" id="client_siret" value="<?= htmlspecialchars($client->siret ?? '') ?>">
<input type="hidden" id="client_code_ape" value="<?= htmlspecialchars($client->code_ape ?? '') ?>">
<input type="hidden" id="client_service" value="<?= htmlspecialchars($client->service ?? '') ?>">
<input type="hidden" id="note" value="<?= htmlspecialchars($quote->note ?? '') ?>">
<input type="hidden" id="conditions" value="<?= htmlspecialchars($quote->conditions ?? '') ?>">
<input type="hidden" id="tva_rate" value="<?= $quote->tva_rate ?>">
<input type="hidden" id="totalHT" value="<?= number_format($quote->total_ht, 2, '.', '') ?>">
<input type="hidden" id="totalTVA" value="<?= number_format($quote->total_tva, 2, '.', '') ?>">
<input type="hidden" id="totalTTC" value="<?= number_format($quote->total_ttc, 2, '.', '') ?>">
<input type="hidden" id="tvaLabel" value="<?= $quote->tva_rate ?>%">
<input type="hidden" id="toggle_date_field" value="1">
<input type="hidden" id="tvaExempt" value="<?= $isExempt ? 1 : 0 ?>">
<div class="row">
    <div class="col-lg-8">
        <!-- En-tête -->
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background-color: #1d3557; color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Devis n° <?= htmlspecialchars($quote->quote_number) ?></h5>
                    <span class="badge bg-<?= $statusLabels[$quote->status][1] ?>"><?= $statusLabels[$quote->status][0] ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><small class="text-muted">Date d'émission:</small><br><strong><?= date('d/m/Y', strtotime($quote->quote_date)) ?></strong></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><small class="text-muted">Valide jusqu'à:</small><br><strong><?= date('d/m/Y', strtotime($quote->valid_until)) ?></strong></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><small class="text-muted">Taux TVA:</small><br><strong><?= $quote->tva_rate ?>%</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Entreprise et Client -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header" style="background-color: #457b9d; color: white;">
                        <h6 class="mb-0"><i class="bi bi-building"></i> Émetteur</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong><?= htmlspecialchars($company->name) ?></strong></p>
                        <?php if ($company->legal_status): ?>
                            <p class="mb-1 text-muted"><?= htmlspecialchars($company->legal_status) ?></p>
                        <?php endif; ?>
                        <?php if ($company->address): ?>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($company->address)) ?></p>
                        <?php endif; ?>
                        <?php if ($company->siret): ?>
                            <p class="mb-1"><small class="text-muted">SIRET:</small> <?= htmlspecialchars($company->siret) ?></p>
                        <?php endif; ?>
                        <?php if ($company->tva_exempt == 1): ?>
                            <p class="mb-1"><small class="text-muted">TVA:</small> <span class="badge bg-warning">Exonérée (art. 293B CGI)</span></p>
                        <?php elseif ($company->tva_number): ?>
                            <p class="mb-1"><small class="text-muted">TVA:</small> <?= htmlspecialchars($company->tva_number) ?></p>
                        <?php endif; ?>
                        <?php if ($company->email): ?>
                            <p class="mb-1"><?= htmlspecialchars($company->email) ?></p>
                        <?php endif; ?>
                        <?php if ($company->phone): ?>
                            <p class="mb-1"><?= htmlspecialchars($company->phone) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header" style="background-color: #457b9d; color: white;">
                        <h6 class="mb-0"><i class="bi bi-person"></i> Client</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong><?= htmlspecialchars($client->name) ?></strong> <span class="badge bg-secondary"><?= ucfirst($client->type) ?></span></p>
                        <?php if ($client->address): ?>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($client->address)) ?></p>
                        <?php endif; ?>
                        <?php if ($client->siret): ?>
                            <p class="mb-1"><small class="text-muted">SIRET:</small> <?= htmlspecialchars($client->siret) ?></p>
                        <?php endif; ?>
                        <?php if ($client->code_ape): ?>
                            <p class="mb-1"><small class="text-muted">Code APE:</small> <?= htmlspecialchars($client->code_ape) ?></p>
                        <?php endif; ?>
                        <?php if ($client->service): ?>
                            <p class="mb-1"><small class="text-muted">Service:</small> <?= htmlspecialchars($client->service) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Note -->
        <?php if (!empty($quote->note)): ?>
        <div class="card shadow-sm mb-4" style="background-color: #f1faee;">
            <div class="card-body">
                <p class="mb-1"><small class="text-muted">Objet / Note:</small></p>
                <p class="mb-0"><?= nl2br(htmlspecialchars($quote->note)) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tableau des prestations -->
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background-color: #1d3557; color: white;">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Prestations</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead style="background-color: #457b9d; color: white;">
                            <tr>
                                <th>Description</th>
                                <th>Date</th>
                                <th class="text-center">Qté</th>
                                <th>Unité</th>
                                <th class="text-end">Prix unit.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <?php foreach ($items as $item): ?>
                            <tr class="item-row">
                                <td><?= htmlspecialchars($item->description) ?></td>
                                <td><?= $item->item_date ? date('Y-m-d', strtotime($item->item_date)) : '-' ?></td>
                                <td class="text-center"><?= number_format($item->quantity, 2, ',', ' ') ?></td>
                                <td><?= htmlspecialchars($item->unit ?? '-') ?></td>
                                <td class="text-end"><?= number_format($item->unit_price, 2, ',', ' ') ?> €</td>
                                <td class="text-end"><strong><?= number_format($item->total, 2, ',', ' ') ?> €</strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Conditions -->
        <?php if (!empty($quote->conditions)): ?>
        <div class="card shadow-sm mb-4" style="background-color: #f1faee;">
            <div class="card-body">
                <p class="mb-1"><small class="text-muted">Conditions et remarques:</small></p>
                <p class="mb-0"><?= nl2br(htmlspecialchars($quote->conditions)) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <!-- Totaux -->
        <div class="card shadow-sm mb-4" style="background-color: #a8dadc;">
            <div class="card-body">
                <h6 class="text-dark mb-3">Totaux</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total HT:</span>
                    <strong><?= number_format($quote->total_ht, 2, ',', ' ') ?> €</strong>
                </div>
                <?php $isExempt = ($company->tva_exempt == 1) || $quote->tva_rate == 0; ?>
                <?php if (!$isExempt): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>TVA (<?= $quote->tva_rate ?>%):</span>
                    <strong><?= number_format($quote->total_tva, 2, ',', ' ') ?> €</strong>
                </div>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between">
                    <span><strong>Total TTC:</strong></span>
                    <strong style="color: #1d3557; font-size: 1.3em;"><?= number_format($quote->total_ttc, 2, ',', ' ') ?> €</strong>
                </div>
                <?php if ($isExempt): ?>
                    <p class="text-muted small mt-2 mb-0">TVA non applicable, art. 293B du CGI</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h6 class="mb-3">Actions</h6>
                <div class="d-grid gap-2">
                    <button type="button" id="generatePdfBtn" class="btn text-white" style="background-color: #e63946;">
                        <i class="bi bi-file-earmark-pdf"></i> Télécharger PDF
                    </button>
                    <a href="/quotes/edit?id=<?= $quote->id ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                </div>
            </div>
        </div>

        <!-- Changer le statut -->
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background-color: #1d3557; color: white;">
                <h6 class="mb-0">Statut</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="/quotes/update-status">
                    <input type="hidden" name="id" value="<?= $quote->id ?>">
                    <div class="mb-2">
                        <select class="form-select form-select-sm" name="status">
                            <option value="draft" <?= $quote->status === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                            <option value="sent" <?= $quote->status === 'sent' ? 'selected' : '' ?>>Envoyé</option>
                            <option value="accepted" <?= $quote->status === 'accepted' ? 'selected' : '' ?>>Accepté</option>
                            <option value="rejected" <?= $quote->status === 'rejected' ? 'selected' : '' ?>>Refusé</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Mettre à jour</button>
                </form>
            </div>
        </div>

        <a href="/quotes" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>
<?php
$content = ob_get_clean();

include __DIR__ . '/../layout.php';
