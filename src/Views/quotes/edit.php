<?php 
$title = 'Modifier le devis - ' . ($quote->quote_number ?? '') . ' - Générateur de Devis';

$today = date('Y-m-d');
$validUntil = date('Y-m-d', strtotime('+30 days'));

ob_start();

$isExempt = ($company->tva_exempt ?? 0) == 1 || ($quote->tva_rate ?? 0) == 0;
?>

<!-- Hidden fields for PDF generation -->
<input type="hidden" id="quote_number" value="<?= htmlspecialchars($quote->quote_number ?? '') ?>">
<input type="hidden" id="quote_date" value="<?= $quote->quote_date ?? '' ?>">
<input type="hidden" id="client_id" value="<?= $quote->client_id ?? '' ?>">
<input type="hidden" id="valid_until" value="<?= $quote->valid_until ?? '' ?>">
<input type="hidden" id="note" value="<?= htmlspecialchars($quote->note ?? '') ?>">
<input type="hidden" id="conditions" value="<?= htmlspecialchars($quote->conditions ?? '') ?>">

<input type="hidden" id="pdf_totalHT" value="<?= number_format($quote->total_ht ?? 0, 2, '.', '') ?>">
<input type="hidden" id="pdf_totalTVA" value="<?= number_format($quote->total_tva ?? 0, 2, '.', '') ?>">
<input type="hidden" id="pdf_totalTTC" value="<?= number_format($quote->total_ttc ?? 0, 2, '.', '') ?>">
<input type="hidden" id="pdf_tvaLabel" value="<?= $quote->tva_rate ?? 20 ?>%">
<input type="hidden" id="pdf_toggle_date_field" value="1">
<input type="hidden" id="tvaExempt" value="<?= $isExempt ? 1 : 0 ?>">
<input type="hidden" id="pdf_quote_number" value="<?= htmlspecialchars($quote->quote_number ?? '') ?>">
<input type="hidden" id="pdf_quote_date" value="<?= $quote->quote_date ?? '' ?>">
<input type="hidden" id="pdf_valid_until" value="<?= $quote->valid_until ?? '' ?>">
<input type="hidden" id="pdf_note" value="<?= htmlspecialchars($quote->note ?? '') ?>">
<input type="hidden" id="pdf_conditions" value="<?= htmlspecialchars($quote->conditions ?? '') ?>">
<form method="POST" action="/quotes/edit?id=<?= $quote->id ?>" id="quoteForm">
    <div class="row">
        <div class="col-lg-8">
            <!-- En-tête du devis -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> En-tête du devis</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="quote_number" class="form-label">Numéro de devis</label>
                            <input type="text" class="form-control" id="quote_number" name="quote_number" value="<?= htmlspecialchars($quote->quote_number) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quote_date" class="form-label">Date d'émission</label>
                            <input type="date" class="form-control" id="quote_date" name="quote_date" value="<?= $quote->quote_date ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="valid_until" class="form-label">Valide jusqu'à</label>
                            <input type="date" class="form-control" id="valid_until" name="valid_until" value="<?= $quote->valid_until ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations client -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Informations du client</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($clients)): ?>
                    <div class="mb-3">
                        <label for="client_selector" class="form-label">Sélectionner un client <span class="text-danger">*</span></label>
                        <select class="form-select" id="client_selector" name="client_id" required>
                            <option value="">-- Choisir un client --</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?= $c->id ?>" 
                                    data-name="<?= htmlspecialchars($c->name) ?>"
                                    data-type="<?= htmlspecialchars($c->type ?? 'particulier') ?>"
                                    data-address="<?= htmlspecialchars($c->address ?? '') ?>"
                                    data-siret="<?= htmlspecialchars($c->siret ?? '') ?>"
                                    data-code-ape="<?= htmlspecialchars($c->code_ape ?? '') ?>"
                                    data-service="<?= htmlspecialchars($c->service ?? '') ?>"
                                    <?= ($client->id ?? '') == $c->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" id="client_name" value="<?= htmlspecialchars($client->name ?? '') ?>">
                    <input type="hidden" id="client_type" value="<?= htmlspecialchars($client->type ?? 'particulier') ?>">
                    <input type="hidden" id="client_address" value="<?= htmlspecialchars($client->address ?? '') ?>">
                    <input type="hidden" id="client_siret" value="<?= htmlspecialchars($client->siret ?? '') ?>">
                    <input type="hidden" id="client_code_ape" value="<?= htmlspecialchars($client->code_ape ?? '') ?>">
                    <input type="hidden" id="client_service" value="<?= htmlspecialchars($client->service ?? '') ?>">
                    <?php else: ?>
                    <div class="alert alert-warning">
                        Aucun client disponible. <a href="/clients/create">Créer un client</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tableau des prestations -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-check"></i> Prestations</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="toggle_date_field" checked>
                            <label class="form-check-label text-white" for="toggle_date_field">Activer date</label>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="itemsTable">
                            <thead style="background-color: #457b9d; color: white;">
                                <tr>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 12%;">Date</th>
                                    <th style="width: 8%;">Qté</th>
                                    <th style="width: 12%;">Unité</th>
                                    <th style="width: 15%;">Prix unit.</th>
                                    <th style="width: 13%;">Total</th>
                                    <th style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <?php if (!empty($items)): ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr class="item-row">
                                            <td><input type="text" class="form-control" name="item_description[]" value="<?= htmlspecialchars($item->description) ?>" required></td>
                                            <td><input type="date" class="form-control" name="item_date[]" value="<?= $item->item_date ?>"></td>
                                            <td><input type="number" class="form-control" name="item_quantity[]" value="<?= $item->quantity ?>" min="0.01" step="0.01" required></td>
                                            <td><input type="text" class="form-control" name="item_unit[]" value="<?= htmlspecialchars($item->unit ?? '') ?>"></td>
                                            <td><input type="number" class="form-control" name="item_unit_price[]" value="<?= $item->unit_price ?>" min="0" step="0.01" required></td>
                                            <td class="text-end item-total"><?= number_format($item->total, 2, ',', ' ') ?> €</td>
                                            <td><button type="button" class="btn btn-sm btn-danger btn-delete-row"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="item-row">
                                        <td><input type="text" class="form-control" name="item_description[]" placeholder="Description" required></td>
                                        <td><input type="date" class="form-control" name="item_date[]"></td>
                                        <td><input type="number" class="form-control" name="item_quantity[]" value="1" min="0.01" step="0.01" required></td>
                                        <td><input type="text" class="form-control" name="item_unit[]" placeholder="Unité"></td>
                                        <td><input type="number" class="form-control" name="item_unit_price[]" value="0" min="0" step="0.01" required></td>
                                        <td class="text-end item-total">0.00 €</td>
                                        <td><button type="button" class="btn btn-sm btn-danger btn-delete-row"><i class="bi bi-trash"></i></button></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-secondary" id="addRowBtn">
                            <i class="bi bi-plus"></i> Ajouter une ligne
                        </button>
                    </div>
                </div>
            </div>

            <!-- Conditions et notes -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-card-text"></i> Conditions et notes</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="note" class="form-label">Note / Objet</label>
                        <textarea class="form-control" id="note" name="note" rows="2"><?= htmlspecialchars($quote->note ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="conditions" class="form-label">Conditions générales</label>
                        <textarea class="form-control" id="conditions" name="conditions" rows="3"><?= htmlspecialchars($quote->conditions ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Entreprise -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Mon entreprise</h5>
                </div>
                <div class="card-body">
                    <?php if ($company): ?>
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
                        <p><strong><?= htmlspecialchars($company->name) ?></strong></p>
                        <?php if ($company->legal_status): ?>
                            <p class="mb-1"><small class="text-muted">Statut:</small> <?= htmlspecialchars($company->legal_status) ?></p>
                        <?php endif; ?>
                        <?php if ($company->address): ?>
                            <p class="mb-1"><small class="text-muted">Adresse:</small><br><?= nl2br(htmlspecialchars($company->address)) ?></p>
                        <?php endif; ?>
                        <?php if ($company->tva_exempt == 1): ?>
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Entreprise exonérée de TVA</strong> (art. 293B CGI)
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TVA -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-percent"></i> Taux de TVA</h5>
                </div>
                <div class="card-body">
                    <?php if ($tvaExempt): ?>
                        <input type="hidden" id="tva_rate_hidden" name="tva_rate" value="0">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Votre entreprise est exonérée de TVA. Le taux est automatiquement fixé à 0%.
                        </div>
                    <?php else: ?>
                    <div class="mb-3">
                        <label for="tva_rate" class="form-label">Taux de TVA</label>
                        <?php $isCustom = !in_array($quote->tva_rate, [20, 10, 5.5, 0]); ?>
                        <select class="form-select" id="tva_rate" name="tva_rate">
                            <option value="20" <?= $quote->tva_rate == 20 && !$isCustom ? 'selected' : '' ?>>TVA 20%</option>
                            <option value="10" <?= $quote->tva_rate == 10 ? 'selected' : '' ?>>TVA 10%</option>
                            <option value="5.5" <?= $quote->tva_rate == 5.5 ? 'selected' : '' ?>>TVA 5,5%</option>
                            <option value="0" <?= $quote->tva_rate == 0 ? 'selected' : '' ?>>TVA non applicable</option>
                            <option value="custom" <?= $isCustom ? 'selected' : '' ?>>Autre taux</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customTvaGroup" style="display: <?= $isCustom ? 'block' : 'none' ?>;">
                        <label for="tva_custom_rate" class="form-label">Taux personnalisé (%)</label>
                        <input type="number" class="form-control" id="tva_custom_rate" name="tva_custom_rate" min="0" max="100" step="0.01" value="<?= $isCustom ? $quote->tva_rate : 0 ?>">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Totaux -->
            <div class="card shadow-sm mb-4" style="background-color: #f1faee;">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Totaux</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total HT:</span>
                        <strong id="totalHT"><?= number_format($quote->total_ht, 2, ',', ' ') ?> €</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>TVA (<span id="tvaLabel"><?= number_format($quote->tva_rate, 2, ',', ' ') ?>%</span>):</span>
                        <strong id="totalTVA"><?= number_format($quote->total_tva, 2, ',', ' ') ?> €</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Total TTC:</strong></span>
                        <strong id="totalTTC" style="color: #1d3557; font-size: 1.2em;"><?= number_format($quote->total_ttc, 2, ',', ' ') ?> €</strong>
                    </div>
                    <div id="tvaMention" class="text-muted small mt-2" style="display: <?= $quote->tva_rate == 0 ? 'block' : 'none' ?>;">TVA non applicable, art. 293B du CGI</div>
                </div>
            </div>

            <!-- Statut -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: #1d3557; color: white;">
                    <h5 class="mb-0"><i class="bi bi-flag"></i> Statut</h5>
                </div>
                <div class="card-body">
                    <select class="form-select" name="status">
                        <option value="draft" <?= $quote->status === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="sent" <?= $quote->status === 'sent' ? 'selected' : '' ?>>Envoyé</option>
                        <option value="accepted" <?= $quote->status === 'accepted' ? 'selected' : '' ?>>Accepté</option>
                        <option value="rejected" <?= $quote->status === 'rejected' ? 'selected' : '' ?>>Refusé</option>
                    </select>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn text-white" style="background-color: #1d3557;">
                    <i class="bi bi-check-lg"></i> Mettre à jour
                </button>
                <button type="button" id="generatePdfBtn" class="btn text-white" style="background-color: #e63946;">
                    <i class="bi bi-file-earmark-pdf"></i> Générer le PDF
                </button>
                <a href="/quotes" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </div>
    </div>
</form>
<?php
$content = ob_get_clean();

include __DIR__ . '/../layout.php';
