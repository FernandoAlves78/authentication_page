(function () {
  document.querySelectorAll(".password-field").forEach(function (wrap) {
    var input = wrap.querySelector("input");
    var btn = wrap.querySelector(".password-toggle");
    var icon = btn && btn.querySelector("i");
    if (!input || !btn) {
      return;
    }

    btn.addEventListener("click", function () {
      var willShowPlain = input.type === "password";
      input.type = willShowPlain ? "text" : "password";
      btn.setAttribute(
        "aria-label",
        willShowPlain ? "Ocultar senha" : "Mostrar senha"
      );
      btn.setAttribute("aria-pressed", willShowPlain ? "true" : "false");
      if (icon) {
        icon.className = willShowPlain ? "bi bi-eye-slash" : "bi bi-eye";
      }
    });
  });
})();
