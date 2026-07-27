<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\RegionalController;
use App\Controllers\CidadeController;
use App\Controllers\DepartamentoController;
use App\Controllers\TipoOcorrenciaController;
use App\Controllers\ColaboradorController;
use App\Controllers\RankingController;
use App\Controllers\BoasVindasController;
use App\Controllers\PromocaoController;
use App\Controllers\AniversariantesController;
use App\Controllers\OcorrenciaController;
use App\Controllers\RelatorioController;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$router = new Router();

$router->get('/', [AuthController::class, 'loginForm']);
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/regionais', [RegionalController::class, 'index']);
$router->post('/regionais', [RegionalController::class, 'store']);
$router->get('/regionais/editar', [RegionalController::class, 'edit']);
$router->post('/regionais/atualizar', [RegionalController::class, 'update']);
$router->post('/regionais/excluir', [RegionalController::class, 'destroy']);

$router->get('/cidades', [CidadeController::class, 'index']);
$router->post('/cidades', [CidadeController::class, 'store']);
$router->get('/cidades/editar', [CidadeController::class, 'edit']);
$router->post('/cidades/atualizar', [CidadeController::class, 'update']);
$router->post('/cidades/excluir', [CidadeController::class, 'destroy']);

$router->get('/departamentos', [DepartamentoController::class, 'index']);
$router->post('/departamentos', [DepartamentoController::class, 'store']);
$router->get('/departamentos/editar', [DepartamentoController::class, 'edit']);
$router->post('/departamentos/atualizar', [DepartamentoController::class, 'update']);
$router->post('/departamentos/excluir', [DepartamentoController::class, 'destroy']);

$router->get('/tipos-ocorrencia', [TipoOcorrenciaController::class, 'index']);
$router->post('/tipos-ocorrencia', [TipoOcorrenciaController::class, 'store']);
$router->get('/tipos-ocorrencia/editar', [TipoOcorrenciaController::class, 'edit']);
$router->post('/tipos-ocorrencia/atualizar', [TipoOcorrenciaController::class, 'update']);
$router->post('/tipos-ocorrencia/excluir', [TipoOcorrenciaController::class, 'destroy']);

$router->get('/colaboradores', [ColaboradorController::class, 'index']);
$router->get('/colaboradores/exportar', [ColaboradorController::class, 'exportar']);
$router->get('/colaboradores/novo', [ColaboradorController::class, 'create']);
$router->post('/colaboradores', [ColaboradorController::class, 'store']);
$router->get('/colaboradores/editar', [ColaboradorController::class, 'edit']);
$router->post('/colaboradores/atualizar', [ColaboradorController::class, 'update']);
$router->post('/colaboradores/excluir', [ColaboradorController::class, 'destroy']);

$router->get('/ranking', [RankingController::class, 'index']);
$router->get('/ranking/novo', [RankingController::class, 'create']);
$router->post('/ranking', [RankingController::class, 'store']);
$router->get('/ranking/detalhe', [RankingController::class, 'show']);
$router->get('/ranking/zip', [RankingController::class, 'baixarZip']);
$router->get('/ranking/exportar-xlsx', [RankingController::class, 'exportarXlsx']);
$router->post('/ranking/regenerar-arte', [RankingController::class, 'regenerarArte']);
$router->post('/ranking/marcar-enviado', [RankingController::class, 'marcarEnviado']);
$router->post('/ranking/desmarcar-enviado', [RankingController::class, 'desmarcarEnviado']);
$router->post('/ranking/excluir', [RankingController::class, 'destroy']);

$router->get('/ranking/manual/novo', [RankingController::class, 'manualForm']);
$router->post('/ranking/manual/gerar', [RankingController::class, 'manualGerar']);

$router->get('/ranking/lote', [RankingController::class, 'loteForm']);
$router->post('/ranking/lote/upload', [RankingController::class, 'loteUpload']);
$router->post('/ranking/lote/processar', [RankingController::class, 'loteProcessar']);
$router->get('/ranking/lote/resultado', [RankingController::class, 'loteResultado']);

$router->get('/ranking/planilhas', [RankingController::class, 'planilhasIndex']);
$router->get('/ranking/planilhas/baixar', [RankingController::class, 'baixarPlanilha']);

$router->post('/boas-vindas/gerar', [BoasVindasController::class, 'store']);
$router->get('/boas-vindas/detalhe', [BoasVindasController::class, 'show']);

$router->get('/promocoes/nova', [PromocaoController::class, 'form']);
$router->post('/promocoes', [PromocaoController::class, 'store']);
$router->get('/promocoes/detalhe', [PromocaoController::class, 'show']);

$router->get('/aniversariantes/novo', [AniversariantesController::class, 'form']);
$router->get('/aniversariantes/pesquisar', [AniversariantesController::class, 'pesquisar']);
$router->post('/aniversariantes', [AniversariantesController::class, 'store']);
$router->get('/aniversariantes/resultado', [AniversariantesController::class, 'resultado']);

$router->get('/ocorrencias/nova', [OcorrenciaController::class, 'form']);
$router->post('/ocorrencias', [OcorrenciaController::class, 'store']);
$router->get('/ocorrencias/anexo', [OcorrenciaController::class, 'baixarAnexo']);
$router->post('/ocorrencias/excluir', [OcorrenciaController::class, 'destroy']);

$router->get('/relatorios', [RelatorioController::class, 'index']);
$router->get('/relatorios/demografico', [RelatorioController::class, 'demografico']);
$router->get('/relatorios/aniversariantes', [RelatorioController::class, 'aniversariantes']);
$router->get('/relatorios/promocoes', [RelatorioController::class, 'promocoes']);
$router->get('/relatorios/ocorrencias', [RelatorioController::class, 'ocorrencias']);
$router->get('/relatorios/turnover', [RelatorioController::class, 'turnover']);
$router->get('/relatorios/ranking', [RelatorioController::class, 'ranking']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);