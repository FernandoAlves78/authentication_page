<?php
require_once __DIR__ . '/../src/Security/auth_functions.php';
startSecureSession();
$csrfToken = getCsrfToken();

$token = isset($_GET['token']) ? trim((string) $_GET['token']) : '';
$token = preg_replace('/\s+/', '', $token);
$reset = null;
if ($token !== '') {
    $reset = findValidPasswordResetToken($token);
}

if (!$token || !$reset) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Link invalido</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    </head>
    <body class="bg-light d-flex align-items-center min-vh-100">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
              <div class="card-body text-center">
                <h1 class="h4 mb-3">Link de redefinicao invalido ou expirado</h1>
                <p class="mb-3 text-start small">Possiveis causas: link antigo (passou mais de 30 minutos), novo pedido de recuperacao (só o ultimo e-mail vale), ou endereco incompleto ao copiar da mensagem.</p>
                <p class="mb-3">Se o site usa uma pasta na URL (ex.: <code>/authentication_page/public/</code>), confira em <code>config/config.php</code> a constante <code>web_public_path</code>.</p>
                <p class="mb-3">Solicite uma nova redefinicao de senha.</p>
                <a href="forgot_password.php" class="btn btn-primary">Voltar para recuperar senha</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redefinir senha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="h4 mb-3 text-center">Redefinir sua senha</h1>
            <form method="post" action="handle_reset_password.php">
              <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="mb-3">
                <label for="password" class="form-label">Nova senha</label>
                <div class="password-field">
                  <div class="password-field-track">
                    <input type="password" class="form-control" id="password" name="password" minlength="8" required />
                    <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-pressed="false">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirmar nova senha</label>
                <div class="password-field">
                  <div class="password-field-track">
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" required />
                    <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-pressed="false">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Salvar nova senha</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/password_toggle.js"></script>
</body>
</html>

