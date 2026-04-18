<?php
require_once __DIR__ . '/../src/Security/auth_functions.php';
$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Entrar</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous"
  />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="login-body">
  <div class="container min-vh-100 d-flex justify-content-center align-items-center">
    <div class="row w-100 justify-content-center">
      <div class="col-12 col-sm-10 col-md-8 col-lg-5">
        <div class="card login-card shadow-lg border-0">
          <div class="card-body p-4 p-md-5">
            <h1 class="h3 mb-4 text-center fw-semibold">Entrar</h1>
            <form id="loginForm" method="post" action="login.php" novalidate>
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
              <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="seuemail@exemplo.com" required />
                <div class="invalid-feedback" id="emailError">Informe um e-mail valido.</div>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-field">
                  <div class="password-field-track">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Digite sua senha" minlength="8" required />
                    <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-pressed="false">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                  </div>
                </div>
                <div class="invalid-feedback" id="passwordError">A senha deve ter pelo menos 8 caracteres.</div>
              </div>

              <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Entrar</button>
              </div>

              <div class="text-center mt-3">
                <a href="forgot_password.php" class="forgot-link">Esqueci minha senha</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="assets/js/password_toggle.js"></script>
  <script src="assets/js/login_validation.js"></script>
</body>
</html>

