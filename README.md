# Estoque System

Sistema simples em PHP 8.1 para gerenciamento de Locais e Inventário, com importação via Excel e impressão de etiquetas BOPP 100×60.

Principais features
- Autenticação com níveis (admin, editor, editorpro)
- Primeiro acesso forçando troca de senha
- Importação XLS/XLSX (col A = código, col B = nome, inicia na linha 7)
- Impressão de etiquetas compatível com Bematech/Elgin (100mm × 60mm)

Requisitos
- PHP 8.1+
- MySQL
- (opcional) Composer — necessário somente para recursos extras (importação XLSX, dotenv, PDF)

Quickstart (local)
1. Clone o repositório
2. Copie e ajuste variáveis: `cp .env.example .env` ou exporte as variáveis no sistema
3. Dependências:
   - O projeto agora inclui um `autoload` mínimo em `src/` e roda sem Composer para as funcionalidades principais.
   - Funcionalidades opcionais (Importação XLSX, geração de PDF, `phpdotenv`) requerem o diretório `vendor/` com as bibliotecas instaladas. Para restaurar essas funcionalidades execute:
     composer install
     composer require phpoffice/phpspreadsheet --no-interaction
     (opcional) composer require vlucas/phpdotenv dompdf/dompdf

4. Crie o banco e as tabelas:
   mysql -u root -p < sql/schema.sql
5. Crie o Admin inicial (opção rápida):
   php sql/seed_admin.php --email=admin@example.com --password=trocar123 --name="Administrador Inicial"
   # O script gera o hash e insere o Admin somente se ainda não existir. Altere a senha no primeiro login.
6. Inicie o servidor de desenvolvimento (raiz do projeto agora é o webroot):
   php -S localhost:8000 -t .
7. Acesse:
   - Página pública: http://localhost:8000/
   - Admin: http://localhost:8000/admin/locais (faça login)

Importação de Excel
- Rota administrativa: `Admin → Importar XLSX`
- Regras: somente colunas A (código) e B (nome) são lidas; leitura começa na linha 7; duplicatas atualizam o nome.
- Fallback CSV: se o servidor não tiver PhpSpreadsheet, carregue um `.csv` (col A = código, col B = nome) — o parser interno inicia na linha 7.

Impressão de etiquetas (BOPP 100×60)
- Use o botão "Imprimir Etiqueta" na listagem pública.
- O sistema gera uma página com `@page { size: 100mm 60mm }` — compatível com impressoras Bematech/Elgin ao imprimir direto do navegador.
- Para maior controle, é possível gerar PDF (Dompdf) — opcional.

Segurança
- Senhas com `password_hash()` e verificação com `password_verify()`.
- Sessões PHP usadas para autenticação; recomendamos TLS/HTTPS em produção.

Próximos passos sugeridos
- Criar `.env` e integrar `phpdotenv` para variáveis de ambiente
- Refatorar models para injeção de dependência PDO (testabilidade)
- Adicionar testes automatizados e CI

Se quiser, eu posso:
- (1) adicionar o seed do Admin já com hash gerado aqui; 
- (2) instalar as dependências via composer neste ambiente e testar a importação; 
- (3) refatorar modelos para receber `PDO` no construtor.

Escolha uma opção que eu execute a seguir.