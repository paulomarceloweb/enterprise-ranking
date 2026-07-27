<?php
use App\Core\Auth;
use App\Models\Configuracao;
$configuracao = Configuracao::obter();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? $configuracao['nome_sistema']) ?></title>
    <?php if (!empty($configuracao['favicon'])): ?>
        <link rel="icon" href="/<?= htmlspecialchars($configuracao['favicon']) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --cor-primaria: <?= htmlspecialchars($configuracao['cor_primaria']) ?>;
            --cor-barra-lateral: <?= htmlspecialchars($configuracao['cor_barra_lateral']) ?>;
        }
        .btn-primary { background-color: var(--cor-primaria); border-color: var(--cor-primaria); }
        .btn-primary:hover, .btn-primary:focus { background-color: var(--cor-primaria); border-color: var(--cor-primaria); filter: brightness(0.9); }
        .btn-outline-primary { color: var(--cor-primaria); border-color: var(--cor-primaria); }
        .btn-outline-primary:hover { background-color: var(--cor-primaria); border-color: var(--cor-primaria); }
        .text-primary { color: var(--cor-primaria) !important; }
        .bg-primary { background-color: var(--cor-primaria) !important; }
        a { color: var(--cor-primaria); }
        .sidebar-personalizada { background-color: var(--cor-barra-lateral) !important; }
        .sidebar-personalizada .nav-pills .nav-link.active { background-color: var(--cor-primaria); }
    </style>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar-personalizada text-white p-3" style="width: 240px; min-width: 240px; flex-shrink: 0; min-height: 100vh;">
        <?php if (!empty($configuracao['logo'])): ?>
            <img src="/<?= htmlspecialchars($configuracao['logo']) ?>" alt="<?= htmlspecialchars($configuracao['nome_sistema']) ?>" class="mb-4" style="max-height: 44px; max-width: 100%;">
        <?php else: ?>
            <h5 class="mb-4"><?= htmlspecialchars($configuracao['nome_sistema']) ?></h5>
        <?php endif; ?>
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item"><a class="nav-link text-white" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/colaboradores"><i class="bi bi-people"></i> Colaboradores</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/ranking"><i class="bi bi-trophy"></i> Ranking</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/aniversariantes/novo"><i class="bi bi-balloon"></i> Aniversariantes</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/relatorios"><i class="bi bi-bar-chart"></i> Relatórios</a></li>
            <?php if (Auth::isSuperAdmin()): ?>
            <li class="nav-item mt-3"><small class="text-white-50 text-uppercase">Administração</small></li>
            <li class="nav-item"><a class="nav-link text-white" href="/regionais"><i class="bi bi-diagram-3"></i> Regionais</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/cidades"><i class="bi bi-geo-alt"></i> Cidades</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/departamentos"><i class="bi bi-building"></i> Departamentos</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/tipos-ocorrencia"><i class="bi bi-exclamation-triangle"></i> Tipos de Ocorrência</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/usuarios"><i class="bi bi-person-gear"></i> Usuários</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/configuracoes"><i class="bi bi-palette"></i> Personalização</a></li>
            <?php endif; ?>
        </ul>
        <hr class="text-white-50">
        <div class="small">
            Logado como <strong><?= htmlspecialchars(Auth::nome() ?? '') ?></strong><br>
            <a href="/logout" class="text-white-50">Sair</a>
        </div>
    </nav>
    <main class="flex-fill p-4" style="min-width: 0; overflow-x: auto;">