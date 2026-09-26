// Perfect Scrollbar wrapper (Windows only, like the original template).
// Waits until the async-loaded PerfectScrollbar plugin is available.
(function () {
  var isWindows = navigator.platform.indexOf("Win") > -1;
  if (!isWindows) return;

  function init() {
    if (typeof PerfectScrollbar === "undefined") {
      return setTimeout(init, 50);
    }

    var instances = [];

    if (document.querySelector("main")) {
      instances.push(new PerfectScrollbar(document.querySelector("main")));
    }

    document.querySelectorAll(".overflow-auto, .overflow-y-auto, .overflow-x-auto").forEach(function (element) {
      instances.push(new PerfectScrollbar(element));
    });

    window.argonScrollbars = instances;
  }

  init();
})();
