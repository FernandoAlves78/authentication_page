(function () {
  var MSG =
    window.__FIRST_ACCESS_I18N__ || {
      match_initial: "",
      match_short: "",
      match_mismatch: "",
      match_match: "",
    };

  var firstAccessForm = document.getElementById("firstAccessForm");
  var passwordInput = document.getElementById("password");
  var confirmInput = document.getElementById("password_confirm");
  var matchHelp = document.getElementById("matchHelp");

  if (!firstAccessForm || !passwordInput || !confirmInput || !matchHelp) {
    return;
  }

  function updateMatchHelp() {
    if (!passwordInput.value || !confirmInput.value) {
      matchHelp.classList.remove("text-danger", "text-success");
      matchHelp.classList.add("text-secondary");
      matchHelp.textContent = MSG.match_initial;
      return;
    }

    if (passwordInput.value.length < 8) {
      matchHelp.classList.remove("text-success");
      matchHelp.classList.add("text-danger");
      matchHelp.textContent = MSG.match_short;
      return;
    }

    if (passwordInput.value !== confirmInput.value) {
      matchHelp.classList.remove("text-success");
      matchHelp.classList.add("text-danger");
      matchHelp.textContent = MSG.match_mismatch;
    } else {
      matchHelp.classList.remove("text-danger");
      matchHelp.classList.add("text-success");
      matchHelp.textContent = MSG.match_match;
    }
  }

  passwordInput.addEventListener("input", updateMatchHelp);
  confirmInput.addEventListener("input", updateMatchHelp);
  firstAccessForm.addEventListener("submit", function (e) {
    if (
      passwordInput.value.length < 8 ||
      passwordInput.value !== confirmInput.value
    ) {
      e.preventDefault();
      updateMatchHelp();
    }
  });
})();
