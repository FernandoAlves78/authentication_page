<?php
require_once __DIR__ . '/../src/Security/auth_functions.php';
startSecureSession();
$csrfToken = getCsrfToken();
$loc = current_locale();

$token = isset($_GET['token']) ? trim((string) $_GET['token']) : '';
$token = preg_replace('/\s+/', '', $token);
$reset = null;
if ($token !== '') {
    $reset = findValidPasswordResetToken($token);
}

if (!$token || !$reset) {
    $hl = htmlspecialchars(html_lang_attribute(), ENT_QUOTES, 'UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $hl; ?>">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?php echo htmlspecialchars(t('reset.invalid.page_title'), ENT_QUOTES, 'UTF-8'); ?></title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    </head>
    <body class="bg-light d-flex align-items-center min-vh-100">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
              <div class="card-body text-center">
                <h1 class="h4 mb-3"><?php echo htmlspecialchars(t('reset.invalid.heading'), ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="mb-3 text-start small"><?php echo htmlspecialchars(t('reset.invalid.help'), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-3"><?php echo htmlspecialchars(t('reset.invalid.web_public_hint'), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-3"><?php echo htmlspecialchars(t('reset.invalid.request_new'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="forgot_password.php" class="btn btn-primary"><?php echo htmlspecialchars(t('reset.invalid.back_forgot'), ENT_QUOTES, 'UTF-8'); ?></a>
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
$hl = htmlspecialchars(html_lang_attribute(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?php echo $hl; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars(t('reset.form.page_title'), ENT_QUOTES, 'UTF-8'); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <?php include __DIR__ . '/partials/auth_lang_dropdown.php'; ?>
            <h1 class="h4 mb-3 text-center"><?php echo htmlspecialchars(t('reset.form.heading'), ENT_QUOTES, 'UTF-8'); ?></h1>
            <form id="resetPasswordForm" method="post" action="handle_reset_password.php" novalidate>
              <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="mb-3">
                <label for="password" class="form-label"><?php echo htmlspecialchars(t('reset.form.new_password'), ENT_QUOTES, 'UTF-8'); ?></label>
                <div class="password-field">
                  <div class="password-field-track">
                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" />
                    <button type="button" class="password-toggle" aria-label="<?php echo htmlspecialchars(t('common.password_show'), ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="false">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                  </div>
                </div>
                <div class="invalid-feedback" id="passwordError"><?php echo htmlspecialchars(t('validation.password_min'), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div class="mb-3">
                <label for="password_confirm" class="form-label"><?php echo htmlspecialchars(t('reset.form.confirm_password'), ENT_QUOTES, 'UTF-8'); ?></label>
                <div class="password-field">
                  <div class="password-field-track">
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" />
                    <button type="button" class="password-toggle" aria-label="<?php echo htmlspecialchars(t('common.password_show'), ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="false">
                      <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                  </div>
                </div>
                <div class="invalid-feedback" id="confirmPasswordError"><?php echo htmlspecialchars(t('validation.password_confirm_required'), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars(t('reset.form.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/auth_lang_dropdown.js"></script>
  <script>
    window.__PASSWORD_TOGGLE_LABELS = <?php echo json_encode(
        ['show' => t('common.password_show'), 'hide' => t('common.password_hide')],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
    ); ?>;
    window.__RESET_PASSWORD_I18N__ = <?php echo json_encode(
        [
            'passwordRequired' => t('validation.password_required'),
            'passwordMin' => t('validation.password_min'),
            'confirmRequired' => t('validation.password_confirm_required'),
            'mismatch' => t('dashboard.error_mismatch'),
        ],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
    ); ?>;
  </script>
  <script src="assets/js/password_toggle.js"></script>
  <script src="assets/js/reset_password_validation.js"></script>
</body>
</html>
