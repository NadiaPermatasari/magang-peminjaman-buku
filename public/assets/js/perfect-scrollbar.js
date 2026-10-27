// Perfect Scrollbar wrapper (Windows only, like the original template).
// Waits until the async-loaded PerfectScrollbar plugin is available.
(function () {
  var isWindows = navigator.platform.indexOf("Win") > -1;
  if (!isWindows || window.matchMedia("(pointer: coarse)").matches) return;

  function init() {
    if (typeof PerfectScrollbar === "undefined") {
      return setTimeout(init, 50);
    }

    var instances = [];

    // Keep page/table scrolling native; .ps otherwise clips their content.
    document.querySelectorAll("[sidenav-main] .overflow-y-auto").forEach(function (element) {
      instances.push(new PerfectScrollbar(element));
    });

    window.argonScrollbars = instances;
  }

  init();
})();
