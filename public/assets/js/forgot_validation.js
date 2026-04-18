const forgotForm = document.getElementById("forgotForm");
const emailInput = document.getElementById("email");
const emailError = document.getElementById("emailError");

const MSG = window.__FORGOT_VALIDATION__ || {};

function isValidEmail(value) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(value);
}

function validateEmail() {
  if (!emailInput || !emailError) return true;
  const value = emailInput.value.trim();
  if (!value) {
    emailInput.classList.add("is-invalid");
    emailError.textContent = MSG.emailRequired || "";
    return false;
  }
  if (!isValidEmail(value)) {
    emailInput.classList.add("is-invalid");
    emailError.textContent = MSG.emailInvalid || "";
    return false;
  }
  emailInput.classList.remove("is-invalid");
  return true;
}

if (emailInput) emailInput.addEventListener("input", validateEmail);
if (forgotForm) {
  forgotForm.addEventListener("submit", (event) => {
    if (!validateEmail()) {
      event.preventDefault();
    }
  });
}
