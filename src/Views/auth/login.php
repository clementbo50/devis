<?php 
$title = 'Connexion - Générateur de Devis';

ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header text-white" style="background-color: #1d3557;">
                <h4 class="mb-0"><i class="bi bi-box-arrow-in-right"></i> Connexion</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/login">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?? '' ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn text-white" style="background-color: #1d3557;">Se connecter</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Pas encore de compte ? <a href="/register" style="color: #457b9d;">Créer un compte</a></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

include __DIR__ . '/../layout.php';
