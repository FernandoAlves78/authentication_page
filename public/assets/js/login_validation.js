const loginForm = document.getElementById("loginForm");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const emailError = document.getElementById("emailError");
const passwordError = document.getElementById("passwordError");

const MSG = window.__LOGIN_VALIDATION__ || {};

function isValidEmail(value) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(value);
}

function validateEmail() {
  if (!emailInput || !emailError) return true;
  const value = emailInput.value.trim();
  if (!value) {
    emailInput.classList.add("is-invalid");
    emailError.textContent = MSG.emailRequired ?? "";
    return false;
  }
  if (!isValidEmail(value)) {
    emailInput.classList.add("is-invalid");
    emailError.textContent = MSG.emailInvalid;
    return false;
  }
  emailInput.classList.remove("is-invalid");
  return true;
}

function validatePassword() {
  if (!passwordInput || !passwordError) return true;
  const value = passwordInput.value;
  if (!value) {
    passwordInput.classList.add("is-invalid");
    passwordError.textContent = MSG.passwordRequired ?? "";
    return false;
  }
  if (value.length < 8) {
    passwordInput.classList.add("is-invalid");
    passwordError.textContent = MSG.passwordMin ?? "";
    return false;
  }
  passwordInput.classList.remove("is-invalid");
  return true;
}

if (emailInput) emailInput.addEventListener("input", validateEmail);
if (passwordInput) passwordInput.addEventListener("input", validatePassword);
if (loginForm) {
  loginForm.addEventListener("submit", (event) => {
    if (!(validateEmail() && validatePassword())) {
      event.preventDefault();
    }
  });
}
