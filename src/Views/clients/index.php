<?php 
$title = 'Mes Clients - Générateur de Devis';

$typeLabels = [
    'particulier' => ['Particulier', 'secondary'],
    'entreprise' => ['Entreprise', 'primary'],
    'public' => ['Public', 'info']
];

ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color: #1d3557;">Mes Clients</h2>
    <a href="/clients/create" class="btn text-white" style="background-color: #457b9d;">
        <i class="bi bi-plus-circle"></i> Nouveau client
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1d3557; color: white;">
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-people fs-1"></i>
                                <p class="mt-2">Aucun client pour le moment</p>
                                <a href="/clients/create" class="btn btn-sm" style="background-color: #457b9d; color: white;">Ajouter votre premier client</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($client->name) ?></strong></td>
                                <td>
                                    <?php $type = $typeLabels[$client->type] ?? ['Inconnu', 'secondary']; ?>
                                    <span class="badge bg-<?= $type[1] ?>"><?= $type[0] ?></span>
                                </td>
                                <td><?= htmlspecialchars($client->address ?? 'N/A') ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/clients/edit?id=<?= $client->id ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="/clients/delete" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $client->id ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')">
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
