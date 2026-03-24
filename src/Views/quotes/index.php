<?php 
$title = 'Mes Devis - Générateur de Devis';

$statusLabels = [
    'draft' => ['Brouillon', 'secondary'],
    'sent' => ['Envoyé', 'info'],
    'accepted' => ['Accepté', 'success'],
    'rejected' => ['Refusé', 'danger']
];

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color: #1d3557;">Mes Devis</h2>
    <a href="/quotes/create" class="btn text-white" style="background-color: #457b9d;">
        <i class="bi bi-plus-circle"></i> Nouveau devis
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1d3557; color: white;">
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Validité</th>
                        <th>Total TTC</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quotes)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Aucun devis pour le moment</p>
                                <a href="/quotes/create" class="btn btn-sm" style="background-color: #457b9d; color: white;">Créer votre premier devis</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quotes as $quote): ?>
                            <?php $client = \App\Models\Client::findById($quote->client_id); ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($quote->quote_number) ?></strong></td>
                                <td><?= htmlspecialchars($client->name ?? 'N/A') ?></td>
                                <td><?= date('d/m/Y', strtotime($quote->quote_date)) ?></td>
                                <td><?= date('d/m/Y', strtotime($quote->valid_until)) ?></td>
                                <td><strong><?= number_format($quote->total_ttc, 2, ',', ' ') ?> €</strong></td>
                                <td>
                                    <?php $status = $statusLabels[$quote->status] ?? ['Inconnu', 'secondary']; ?>
                                    <span class="badge bg-<?= $status[1] ?>"><?= $status[0] ?></span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/quotes/show?id=<?= $quote->id ?>" class="btn btn-sm btn-outline-primary" title="Voir / PDF">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/quotes/edit?id=<?= $quote->id ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="/quotes/delete" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $quote->id ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce devis ?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

include __DIR__ . '/../layout.php';
