// Keep navbar menus inside the viewport, including narrow mobile screens.
(function () {
  var entries = [];

  function close(entry, restoreFocus) {
    entry.trigger.setAttribute("aria-expanded", "false");
    entry.menu.classList.add("hidden");
    if (restoreFocus) entry.trigger.focus();
  }

  function position(entry) {
    var anchor = entry.trigger.parentElement.getBoundingClientRect();
    var width = entry.menu.offsetWidth;
    var left = Math.max(16, Math.min(anchor.right - width, window.innerWidth - width - 16));
    entry.menu.style.left = (left - anchor.left) + "px";
    entry.menu.style.right = "auto";
    entry.menu.style.maxHeight = Math.max(120, window.innerHeight - anchor.bottom - 24) + "px";
  }

  document.querySelectorAll("[dropdown-trigger]").forEach(function (trigger) {
    var menu = trigger.parentElement.querySelector("[dropdown-menu]");
    if (!menu) return;
    var entry = { trigger: trigger, menu: menu };
    entries.push(entry);

    trigger.addEventListener("click", function () {
      var open = trigger.getAttribute("aria-expanded") !== "true";
      entries.forEach(function (other) { close(other, false); });
      if (!open) return;
      menu.classList.remove("hidden");
      trigger.setAttribute("aria-expanded", "true");
      position(entry);
    });
  });

  document.addEventListener("click", function (event) {
    entries.forEach(function (entry) {
      if (!entry.trigger.parentElement.contains(event.target)) close(entry, false);
    });
  });

  document.addEventListener("keydown", function (event) {
    if (event.key !== "Escape") return;
    entries.forEach(function (entry) {
      if (entry.trigger.getAttribute("aria-expanded") === "true") close(entry, true);
    });
  });

  window.addEventListener("resize", function () {
    entries.forEach(function (entry) { close(entry, false); });
  });

  window.addEventListener("scroll", function () {
    entries.forEach(function (entry) { close(entry, false); });
  }, { passive: true });
})();
