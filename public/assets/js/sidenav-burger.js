// Mobile drawer and persistent desktop collapse use the same trigger.
(function () {
  var sidenav = document.querySelector("[sidenav-main]");
  var trigger = document.querySelector("[sidenav-trigger]");
  var closeButton = document.querySelector("[sidenav-close]");
  var backdrop = document.querySelector("[sidenav-backdrop]");
  if (!sidenav || !trigger) return;

  var desktop = window.matchMedia("(min-width: 1200px)");
  var mobileOpen = false;

  function sync() {
    var expanded = desktop.matches
      ? !document.body.classList.contains("sidenav-collapsed")
      : mobileOpen;
    sidenav.setAttribute("aria-expanded", String(expanded));
    trigger.setAttribute("aria-expanded", String(expanded));
    trigger.setAttribute("aria-label", expanded ? "Tutup menu navigasi" : "Buka menu navigasi");
    sidenav.inert = !expanded;
    sidenav.classList.toggle("translate-x-0", !desktop.matches && mobileOpen);
    sidenav.classList.toggle("ml-4", !desktop.matches && mobileOpen);
    if (backdrop) backdrop.classList.toggle("hidden", desktop.matches || !mobileOpen);
    document.body.classList.toggle("sidenav-open", !desktop.matches && mobileOpen);
  }

  function closeMobile(restoreFocus) {
    mobileOpen = false;
    sync();
    if (restoreFocus) trigger.focus();
  }

  trigger.addEventListener("click", function () {
    if (desktop.matches) {
      var collapsed = document.body.classList.toggle("sidenav-collapsed");
      try {
        localStorage.setItem("argon-sidenav", collapsed ? "collapsed" : "expanded");
      } catch (_) {}
      sync();
      return;
    }
    mobileOpen = !mobileOpen;
    sync();
    if (mobileOpen) (closeButton || sidenav).focus();
  });

  if (closeButton) closeButton.addEventListener("click", function () { closeMobile(true); });
  if (backdrop) backdrop.addEventListener("click", function () { closeMobile(true); });

  document.addEventListener("keydown", function (event) {
    if (desktop.matches || !mobileOpen) return;
    if (event.key === "Escape") {
      event.preventDefault();
      closeMobile(true);
      return;
    }
    if (event.key !== "Tab") return;
    var focusable = Array.from(sidenav.querySelectorAll("a[href], button, summary, [tabindex='0']"))
      .filter(function (element) { return element.getClientRects().length && !element.disabled; });
    var first = focusable[0];
    var last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  desktop.addEventListener("change", function () { closeMobile(false); });
  sync();
})();
