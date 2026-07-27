<h1 class="h3 mb-4">Bem-vindo, <?= htmlspecialchars(\App\Core\Auth::nome()) ?>!</h1>
<p class="text-muted">Nível de acesso: <?= htmlspecialchars(\App\Core\Auth::nivel()) ?></p>