# enterprise-ranking

Sistema interno de gestão de colaboradores e geração automática de artes (ranking, boas-vindas, promoção, aniversário) para a Mottanet.

Enterprise Ranking

Sistema interno da Mottanet para gestão de colaboradores e geração automática de artes internas (ranking mensal, boas-vindas, promoção e aniversariantes), substituindo o processo manual feito hoje no Photoshop.

Repositório privado — contém lógica de negócio e, eventualmente, dados de colaboradores da empresa.

Sobre o projeto

Hoje o R.H. envia uma planilha para o marketing, que edita cada arte manualmente no Photoshop (foto, nome, cidade, departamento, colocação) e reenvia tudo por WhatsApp. Esse sistema automatiza esse fluxo ponta a ponta: cadastro centralizado de colaboradores, geração automática das artes a partir do cadastro/planilha, e histórico consultável de tudo o que já foi gerado.

Funcionalidades
Colaboradores: cadastro completo (foto, sexo, cidade, departamento, cargo, contato, redes sociais, datas de admissão/nascimento), histórico de eventos (promoção, transferência, desligamento, readmissão), ocorrências/documentos anexáveis (PMO, PMC, advertência etc.), cadastro em massa via planilha ou individual.
Estrutura organizacional: Diretor → Regional (agrupamento de cidades) → Cidade → Departamento/Cargo, com filtros e breadcrumb.
Templates de arte (todos com identidade visual configurável pelo Super Admin — cor, logo, fonte):
Ranking mensal: geração em lote a partir de planilha ou individual, com colocação 1º/2º/3º (crachá dourado/prata/bronze) por setor.
Boas-vindas: gerado ao cadastrar um colaborador, com variante de texto por gênero.
Promoção: cargo anterior → cargo novo, disparado por evento no histórico do colaborador.
Aniversariantes: em grupo (até 6 por imagem, com paginação automática) ou individual, gerável sob demanda para qualquer mês.
Histórico e download: toda arte gerada fica registrada (quem, quando, de quem), com download individual ou em lote (.zip).
Usuários e permissões: Super Admin (controle total) e R.H. (múltiplos usuários, operação do dia a dia), com log de ações.
Autenticação: login com recuperação de senha e convite por e-mail para novos usuários.
Stack
PHP 8.3 (orientado a objetos, PDO com prepared statements)
MySQL
Composer (autoload e dependências — leitura de .xlsx, envio de e-mail)
GD (geração de imagem)
Bootstrap + JavaScript (fetch/AJAX) no front-end
Ambiente local: WampServer
Como rodar localmente
bash
git clone https://github.com/SEU_USUARIO/enterprise-ranking.git
cd enterprise-ranking
composer install
cp .env.example .env # e preencher com as credenciais locais do MySQL

Importar o schema do banco (database/schema.sql) via phpMyAdmin ou linha de comando, e apontar o VirtualHost/pasta do WAMP para public/.

Roadmap

v1 (em construção) Cadastro de colaboradores, geração dos 4 templates, histórico, usuários com 2 níveis, estrutura Diretor/Regional/Cidade.

v2 (planejado) Importação de rankings históricos, painel de "display" (TV/telão), organograma pessoa a pessoa (quem responde para quem), login individual para todos os colaboradores, possível evolução para produto white-label.

Licença

Uso interno — Mottanet. Não distribuído publicamente.
