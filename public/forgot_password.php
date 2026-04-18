<?php
require_once __DIR__ . '/../src/Security/auth_functions.php';
$csrfToken = getCsrfToken();
$loc = current_locale();
$csrfFlash = isset($_GET['error']) && (string) $_GET['error'] === 'csrf';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attribute(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars(t('forgot.page_title'), ENT_QUOTES, 'UTF-8'); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <?php include __DIR__ . '/partials/auth_lang_dropdown.php'; ?>
            <h1 class="h4 mb-3 text-center"><?php echo htmlspecialchars(t('forgot.heading'), ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="mb-3"><?php echo htmlspecialchars(t('forgot.intro'), ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if ($csrfFlash): ?>
              <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars(t('forgot.error_csrf'), ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form id="forgotForm" method="post" action="handle_forgot_password.php" novalidate>
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
              <div class="mb-3">
                <label for="email" class="form-label"><?php echo htmlspecialchars(t('forgot.email_label'), ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="email" class="form-control" id="email" name="email" autocomplete="email" />
                <div class="invalid-feedback" id="emailError"><?php echo htmlspecialchars(t('validation.email_invalid'), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars(t('forgot.submit'), ENT_QUOTES, 'UTF-8'); ?></button>
              </div>
            </form>
            <div class="text-center mt-3">
              <a href="index.php"><?php echo htmlspecialchars(t('forgot.back_login'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/auth_lang_dropdown.js"></script>
  <script>
    window.__FORGOT_VALIDATION__ = <?php echo json_encode(
        [
            'emailRequired' => t('validation.email_required'),
            'emailInvalid' => t('validation.email_invalid'),
        ],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
    ); ?>;
  </script>
  <script src="assets/js/forgot_validation.js"></script>
</body>
</html>
