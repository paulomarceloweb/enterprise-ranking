<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Enterprise Ranking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card shadow-sm" style="max-width: 380px; width: 100%;">
            <div class="card-body p-4">
                <h1 class="h4 mb-4 text-center">Enterprise Ranking</h1>
                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>
                <form method="POST" action="/login">
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>