(function () {
  var root = document.getElementById("authLangRoot");
  var btn = document.getElementById("authLangBtn");
  var menu = document.getElementById("authLangMenu");
  if (!root || !btn || !menu) return;

  function close() {
    menu.hidden = true;
    btn.setAttribute("aria-expanded", "false");
  }

  function open() {
    menu.hidden = false;
    btn.setAttribute("aria-expanded", "true");
  }

  function toggle() {
    if (menu.hidden) open();
    else close();
  }

  btn.addEventListener("click", function (e) {
    e.stopPropagation();
    toggle();
  });

  document.addEventListener("click", function () {
    close();
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") close();
  });

  menu.addEventListener("click", function (e) {
    var opt = e.target.closest(".auth-lang-option");
    if (!opt) return;
    e.stopPropagation();
    var v = opt.getAttribute("data-value");
    if (!v) return;
    document.cookie =
      "auth_lang=" +
      encodeURIComponent(v) +
      ";path=/;max-age=31536000;SameSite=Lax";
    location.reload();
  });
})();
