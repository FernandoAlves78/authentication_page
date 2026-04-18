<?php
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <?php
          require_once __DIR__ . '/../src/Security/auth_functions.php';
          startSecureSession();

          $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
          $pwError = isset($_GET['pw_error']) && (string) $_GET['pw_error'] === '1';
          $pwReason = isset($_GET['pw_reason']) ? (string) $_GET['pw_reason'] : '';
          $updated = isset($_GET['updated']) && (string) $_GET['updated'] === '1';
          $csrfToken = getCsrfToken();

          if ($userId <= 0) {
              header('Location: index.php');
              exit;
          }

          $pdo = getPdo();
          $stmt = $pdo->prepare('SELECT must_reset_password FROM users WHERE id = :user_id');
          $stmt->execute([':user_id' => $userId]);
          $user = $stmt->fetch();

          if (!$user) {
              header('Location: index.php');
              exit;
          }

          $mustResetPassword = ((int) $user['must_reset_password'] === 1);
          ?>

          <div class="card-body text-center">
            <?php if ($mustResetPassword): ?>
              <h1 class="h4 mb-2">Primeiro acesso</h1>
              <p class="mb-3">Voce precisa cadastrar uma nova senha.</p>
              <form id="firstAccessForm" method="post" action="handle_first_access_password.php" class="text-start" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                <div class="mb-3">
                  <label for="password" class="form-label">Senha</label>
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
                  <label for="password_confirm" class="form-label">Confirma senha</label>
                  <div class="password-field">
                    <div class="password-field-track">
                      <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" required />
                      <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                      </button>
                    </div>
                  </div>
                  <div id="matchHelp" class="form-text text-secondary mt-2">Digite a mesma senha para confirmar.</div>
                </div>

                <?php if ($pwError): ?>
                  <div class="alert alert-danger mb-3" role="alert">
                    <?php if ($pwReason === 'length'): ?>
                      A senha deve ter pelo menos 8 caracteres.
                    <?php elseif ($pwReason === 'mismatch'): ?>
                      As senhas nao conferem. Verifique e tente novamente.
                    <?php elseif ($pwReason === 'csrf'): ?>
                      Sua sessao expirou. Atualize a pagina e tente novamente.
                    <?php else: ?>
                      Nao foi possivel cadastrar a nova senha. Verifique os campos e tente novamente.
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <div class="d-grid gap-2 mt-3">
                  <button type="submit" class="btn btn-primary">Cadastrar nova senha</button>
                </div>
              </form>
            <?php else: ?>
              <h1 class="h4 mb-3">Login efetuado com sucesso</h1>
              <p class="mb-3">Voce esta autenticado corretamente.</p>
              <?php if ($updated): ?>
                <div class="alert alert-success text-center" role="alert">Senha atualizada com sucesso.</div>
              <?php endif; ?>
              <form method="post" action="logout.php" class="d-grid">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                <button type="submit" class="btn btn-outline-danger">Sair</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const firstAccessForm = document.getElementById("firstAccessForm");
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("password_confirm");
    const matchHelp = document.getElementById("matchHelp");

    if (firstAccessForm && passwordInput && confirmInput && matchHelp) {
      function updateMatchHelp() {
        if (!passwordInput.value || !confirmInput.value) {
          matchHelp.classList.remove("text-danger", "text-success");
          matchHelp.classList.add("text-secondary");
          matchHelp.textContent = "Digite a mesma senha para confirmar.";
          return;
        }

        if (passwordInput.value.length < 8) {
          matchHelp.classList.remove("text-success");
          matchHelp.classList.add("text-danger");
          matchHelp.textContent = "A senha deve ter pelo menos 8 caracteres.";
          return;
        }

        if (passwordInput.value !== confirmInput.value) {
          matchHelp.classList.remove("text-success");
          matchHelp.classList.add("text-danger");
          matchHelp.textContent = "As senhas nao conferem.";
        } else {
          matchHelp.classList.remove("text-danger");
          matchHelp.classList.add("text-success");
          matchHelp.textContent = "Senhas conferem.";
        }
      }

      passwordInput.addEventListener("input", updateMatchHelp);
      confirmInput.addEventListener("input", updateMatchHelp);
      firstAccessForm.addEventListener("submit", function (e) {
        if (passwordInput.value.length < 8 || passwordInput.value !== confirmInput.value) {
          e.preventDefault();
          updateMatchHelp();
        }
      });
    }
  </script>
  <script src="assets/js/password_toggle.js"></script>
</body>
</html>

