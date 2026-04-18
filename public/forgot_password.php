<?php
require_once __DIR__ . '/../src/Security/auth_functions.php';
$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar senha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h1 class="h4 mb-3 text-center">Recuperar senha</h1>
            <p class="mb-3">Informe seu e-mail e, se ele estiver cadastrado, voce recebera um link para redefinir sua senha.</p>
            <form method="post" action="handle_forgot_password.php">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
              <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required />
              </div>
              <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Enviar link de redefinicao</button>
              </div>
            </form>
            <div class="text-center mt-3">
              <a href="index.php">Voltar ao login</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

