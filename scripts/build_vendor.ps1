<# DEPRECATED
   Este script foi deixado como rastro histórico: o repositório foi convertido para rodar sem Composer em tempo de execução
   e o diretório `vendor/` foi removido conforme solicitado pelo mantenedor.

   Para restaurar o fluxo anterior (instalar dependências e gerar `vendor/`) execute na raiz do projeto:
     composer install --no-dev --optimize-autoloader
   ou copie um `vendor/` pronto para a árvore do projeto.
#>
Write-Host "build_vendor.ps1: script obsoleto — o projeto foi convertido para não depender do Composer em tempo de execução." -ForegroundColor Yellow
Write-Host "Para restaurar dependências: execute 'composer install' em um ambiente com Composer e copie/atualize 'vendor/' no projeto." -ForegroundColor Cyan
exit 0

