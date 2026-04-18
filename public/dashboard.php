<?php

require_once __DIR__ . '/../src/Security/auth_functions.php';
startSecureSession();

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$pwError = isset($_GET['pw_error']) && (string) $_GET['pw_error'] === '1';
$pwReason = isset($_GET['pw_reason']) ? (string) $_GET['pw_reason'] : '';
$updated = isset($_GET['updated']) && (string) $_GET['updated'] === '1';

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
$csrfToken = getCsrfToken();
$hl = htmlspecialchars(html_lang_attribute(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?php echo $hl; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars(t('dashboard.page_title'), ENT_QUOTES, 'UTF-8'); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <?php if ($mustResetPassword): ?>
              <h1 class="h4 mb-2"><?php echo htmlspecialchars(t('dashboard.first_access_heading'), ENT_QUOTES, 'UTF-8'); ?></h1>
              <p class="mb-3"><?php echo htmlspecialchars(t('dashboard.first_access_intro'), ENT_QUOTES, 'UTF-8'); ?></p>
              <form id="firstAccessForm" method="post" action="handle_first_access_password.php" class="text-start" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                <div class="mb-3">
                  <label for="password" class="form-label"><?php echo htmlspecialchars(t('dashboard.password_label'), ENT_QUOTES, 'UTF-8'); ?></label>
                  <div class="password-field">
                    <div class="password-field-track">
                      <input type="password" class="form-control" id="password" name="password" />
                      <button type="button" class="password-toggle" aria-label="<?php echo htmlspecialchars(t('common.password_show'), ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="password_confirm" class="form-label"><?php echo htmlspecialchars(t('dashboard.confirm_label'), ENT_QUOTES, 'UTF-8'); ?></label>
                  <div class="password-field">
                    <div class="password-field-track">
                      <input type="password" class="form-control" id="password_confirm" name="password_confirm" />
                      <button type="button" class="password-toggle" aria-label="<?php echo htmlspecialchars(t('common.password_show'), ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                      </button>
                    </div>
                  </div>
                  <div id="matchHelp" class="form-text text-secondary mt-2"><?php echo htmlspecialchars(t('dashboard.match_help_initial'), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <?php if ($pwError): ?>
                  <div class="alert alert-danger mb-3" role="alert">
                    <?php if ($pwReason === 'length'): ?>
                      <?php echo htmlspecialchars(t('dashboard.error_length'), ENT_QUOTES, 'UTF-8'); ?>
                    <?php elseif ($pwReason === 'mismatch'): ?>
                      <?php echo htmlspecialchars(t('dashboard.error_mismatch'), ENT_QUOTES, 'UTF-8'); ?>
                    <?php elseif ($pwReason === 'csrf'): ?>
                      <?php echo htmlspecialchars(t('dashboard.error_csrf'), ENT_QUOTES, 'UTF-8'); ?>
                    <?php elseif ($pwReason === 'empty'): ?>
                      <?php echo htmlspecialchars(t('dashboard.error_empty'), ENT_QUOTES, 'UTF-8'); ?>
                    <?php else: ?>
                      <?php echo htmlspecialchars(t('dashboard.error_generic'), ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <div class="d-grid gap-2 mt-3">
                  <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars(t('dashboard.submit_password'), ENT_QUOTES, 'UTF-8'); ?></button>
                </div>
              </form>
              <script>
                window.__PASSWORD_TOGGLE_LABELS = <?php echo json_encode(
                    ['show' => t('common.password_show'), 'hide' => t('common.password_hide')],
                    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
                ); ?>;
                window.__FIRST_ACCESS_I18N__ = <?php echo json_encode(
                    [
                        'match_initial' => t('dashboard.js.match_initial'),
                        'match_short' => t('dashboard.js.match_short'),
                        'match_mismatch' => t('dashboard.js.match_mismatch'),
                        'match_match' => t('dashboard.js.match_match'),
                    ],
                    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
                ); ?>;
              </script>
              <script src="assets/js/password_toggle.js"></script>
              <script src="assets/js/first_access_validation.js"></script>
            <?php else: ?>
              <h1 class="h4 mb-3"><?php echo htmlspecialchars(t('dashboard.logged_in_heading'), ENT_QUOTES, 'UTF-8'); ?></h1>
              <p class="mb-3"><?php echo htmlspecialchars(t('dashboard.logged_in_intro'), ENT_QUOTES, 'UTF-8'); ?></p>
              <?php if ($updated): ?>
                <div class="alert alert-success text-center" role="alert"><?php echo htmlspecialchars(t('dashboard.password_updated'), ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
              <form method="post" action="logout.php" class="d-grid">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                <button type="submit" class="btn btn-outline-danger"><?php echo htmlspecialchars(t('dashboard.logout'), ENT_QUOTES, 'UTF-8'); ?></button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
