<?php
require_once __DIR__ . '/../src/Support/i18n.php';

$reason = isset($_GET['reason']) ? (string) $_GET['reason'] : '';
if ($reason === 'csrf') {
    $failMessage = t('login_failed.message_csrf');
} elseif ($reason === 'rate_limit') {
    $failMessage = t('login_failed.message_rate_limit');
} elseif ($reason === 'validation') {
    $failMessage = t('login_failed.message_validation');
} else {
    $failMessage = t('login_failed.message');
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(html_lang_attribute(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars(t('login_failed.page_title'), ENT_QUOTES, 'UTF-8'); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h1 class="h4 mb-3"><?php echo htmlspecialchars(t('login_failed.heading'), ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="mb-3"><?php echo htmlspecialchars($failMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="index.php" class="btn btn-primary"><?php echo htmlspecialchars(t('login_failed.retry'), ENT_QUOTES, 'UTF-8'); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
