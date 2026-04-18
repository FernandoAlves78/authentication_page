<?php

declare(strict_types=1);

$langOpts = ['pt' => 'PT', 'en' => 'EN', 'es' => 'ES', 'it' => 'IT'];
$locKey = array_key_exists($loc, $langOpts) ? $loc : 'pt';
$currentLabel = $langOpts[$locKey];

?>
<div class="d-flex justify-content-end mb-3">
  <div class="auth-lang-dropdown" id="authLangRoot">
    <button
      type="button"
      class="auth-lang-trigger"
      id="authLangBtn"
      aria-haspopup="listbox"
      aria-expanded="false"
      aria-label="<?php echo htmlspecialchars(t('login.lang_label'), ENT_QUOTES, 'UTF-8'); ?>"
    >
      <span class="auth-lang-current"><?php echo htmlspecialchars($currentLabel, ENT_QUOTES, 'UTF-8'); ?></span>
      <span class="auth-lang-chevron" aria-hidden="true"></span>
    </button>
    <ul class="auth-lang-menu" id="authLangMenu" role="listbox" hidden>
<?php foreach ($langOpts as $code => $label): ?>
<?php $selected = $locKey === $code; ?>
      <li
        role="option"
        class="auth-lang-option<?php echo $selected ? ' is-active' : ''; ?>"
        data-value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
        aria-selected="<?php echo $selected ? 'true' : 'false'; ?>"
      ><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></li>
<?php endforeach; ?>
    </ul>
  </div>
</div>
