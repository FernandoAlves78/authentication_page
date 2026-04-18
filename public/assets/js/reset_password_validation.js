const resetForm = document.getElementById("resetPasswordForm");
const passwordInput = document.getElementById("password");
const confirmInput = document.getElementById("password_confirm");
const passwordError = document.getElementById("passwordError");
const confirmError = document.getElementById("confirmPasswordError");

const MSG = window.__RESET_PASSWORD_I18N__ || {};

function validateAll() {
  if (!passwordInput || !confirmInput || !passwordError || !confirmError) {
    return true;
  }

  const pw = passwordInput.value;
  const cf = confirmInput.value;

  passwordInput.classList.remove("is-invalid");
  confirmInput.classList.remove("is-invalid");

  let ok = true;

  if (!pw) {
    passwordInput.classList.add("is-invalid");
    passwordError.textContent = MSG.passwordRequired ?? "";
    ok = false;
  } else if (pw.length < 8) {
    passwordInput.classList.add("is-invalid");
    passwordError.textContent = MSG.passwordMin ?? "";
    ok = false;
  }

  if (!cf) {
    confirmInput.classList.add("is-invalid");
    confirmError.textContent = MSG.confirmRequired ?? "";
    ok = false;
  } else if (pw.length >= 8 && pw !== cf) {
    confirmInput.classList.add("is-invalid");
    confirmError.textContent = MSG.mismatch ?? "";
    ok = false;
  }

  return ok;
}

function syncWhileTyping() {
  if (!passwordInput || !confirmInput || !passwordError || !confirmError) return;
  const pw = passwordInput.value;
  const cf = confirmInput.value;

  if (pw && pw.length < 8) {
    passwordInput.classList.add("is-invalid");
    passwordError.textContent = MSG.passwordMin ?? "";
  } else if (pw) {
    passwordInput.classList.remove("is-invalid");
  }

  if (cf && pw.length >= 8 && pw !== cf) {
    confirmInput.classList.add("is-invalid");
    confirmError.textContent = MSG.mismatch ?? "";
  } else if (cf && pw === cf && pw.length >= 8) {
    confirmInput.classList.remove("is-invalid");
  }
}

if (passwordInput) passwordInput.addEventListener("input", syncWhileTyping);
if (confirmInput) confirmInput.addEventListener("input", syncWhileTyping);

if (resetForm) {
  resetForm.addEventListener("submit", (event) => {
    if (!validateAll()) {
      event.preventDefault();
    }
  });
}
