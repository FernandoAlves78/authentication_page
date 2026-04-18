# Authentication Portfolio Project

Projeto de autenticacao em PHP para demonstrar boas praticas de backend, seguranca e organizacao de codigo em um fluxo real de login.

## O que este projeto demonstra

- Login com validacao de credenciais e controle de sessao.
- Fluxo de primeiro acesso com troca obrigatoria de senha.
- Recuperacao de senha com token de uso unico e expiracao.
- Protecoes basicas de seguranca:
  - CSRF em formularios sensiveis.
  - Hardening de sessao (`HttpOnly`, `SameSite`, `Secure` em HTTPS, `session_regenerate_id`).
  - Token de reset salvo como hash no banco.
  - Rate limiting basico para login e recuperacao.

## Estrutura

- `public/`: paginas e handlers HTTP.
- `public/assets/`: CSS e JavaScript.
- `src/Security/auth_functions.php`: orquestracao de auth e seguranca.
- `src/Support/connection.php`: conexao PDO compartilhada.
- `src/Repositories/`: acesso a dados.
- `src/Services/`: regras de negocio.
- `config/`: configuracao da aplicacao.
- `database/schema.sql`: schema inicial.
- `database/seeds/create_demo_user.php`: seed de usuario demo.
- `scripts/install_mailhog_laragon.ps1` / `scripts/start_mailhog.bat`: MailHog local (ver secao abaixo).
- `tests/run_tests.php`: testes automatizados de fluxo critico.

## Setup local

1. Copie `config/config.example.php` para `config/config.php` e ajuste credenciais.
2. Importe `database/schema.sql` no MySQL.
3. (Opcional) Execute `php database/seeds/create_demo_user.php`.
4. Suba o projeto no Laragon e acesse `http://localhost/authentication_page/public`.

## MailHog (e-mails de recuperacao em desenvolvimento)

A recuperacao de senha envia por SMTP para **127.0.0.1:1025** quando `mail_smtp_enabled` esta ativo em `config/config.php`. O MailHog captura esses e-mails localmente.

**Se o Laragon nao iniciar o MailHog sozinho:**

1. Tente no Laragon: **Menu → Tools → MailHog → Start** (se existir).
2. Ou arranque manualmente:
   - executavel padrao: **`C:\laragon\bin\mailhog\MailHog.exe`** (duplo clique), **ou**
   - **`scripts\start_mailhog.bat`** (usa `C:\laragon\bin\mailhog\MailHog.exe`).
3. Se a pasta `bin\mailhog` nao existir, instale uma vez:
   ```powershell
   cd scripts
   powershell -ExecutionPolicy Bypass -File .\install_mailhog_laragon.ps1
   ```
   (Use `-LaragonRoot "D:\laragon"` se o Laragon nao estiver em `C:\laragon`.)

**Interface web (caixa de entrada de teste):** abra no navegador **http://localhost:8025** — ai aparecem os e-mails “Esqueci minha senha” com o link de redefinicao.

**Opcional:** no Laragon, **Menu → Laragon → Procfile**, adicione uma linha para o MailHog subir com o ambiente, por exemplo `MailHog: autorun C:\laragon\bin\mailhog\MailHog.exe`, e reinicie o Laragon.

Em producao, desative SMTP local (`mail_smtp_enabled = false`) e configure envio real.

## Como testar

- Teste automatizado:
  - `php tests/run_tests.php`
- Teste manual recomendado:
  - Login valido e invalido.
  - Primeiro acesso com senha temporaria.
  - Recuperacao de senha e tentativa de reuso de token.

## Decisoes tecnicas

- **PDO unificado** para reduzir inconsistencias de acesso a dados.
- **Camada de servico/repositorio** para separar regra de negocio de persistencia.
- **Mensagens de erro genericas** para nao expor detalhes internos ao usuario.

## Melhorias futuras

- Migrar configuracao para variaveis de ambiente (`.env`).
- Adicionar logs estruturados.
- Introduzir PHPUnit e pipeline CI para execucao de testes.

