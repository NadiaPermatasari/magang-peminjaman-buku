// Sidenav burger button
//  - mobile / tablet (< xl): slides the sidenav in and out (Argon behaviour)
//  - desktop (>= xl): collapses / expands the sidenav and widens the content,
//    remembered in localStorage (restored early by the layout to avoid flicker)
// `page` is defined globally by argon-dashboard-tailwind.js from <body data-page="...">

var sidenav = document.querySelector("aside");
var sidenav_trigger = document.querySelector("[sidenav-trigger]");
var sidenav_close_button = document.querySelector("[sidenav-close]");
var XL_BREAKPOINT = 1200;

function isDesktop() {
  return window.innerWidth >= XL_BREAKPOINT;
}

if (sidenav && sidenav_trigger) {
  var burger = sidenav_trigger.firstElementChild;
  var top_bread = burger.firstElementChild;
  var bottom_bread = burger.lastElementChild;

  sidenav_trigger.addEventListener("click", function () {
    if (isDesktop() && page !== "virtual-reality") {
      var collapsed = document.body.classList.toggle("sidenav-collapsed");
      try {
        localStorage.setItem("argon-sidenav", collapsed ? "collapsed" : "expanded");
      } catch (e) {}
      return;
    }

    if (page == "virtual-reality") {
      sidenav.classList.toggle("xl:left-[18%]");
    }
    if (sidenav.getAttribute("aria-expanded") == "false") {
      sidenav.setAttribute("aria-expanded", "true");
    } else {
      sidenav.setAttribute("aria-expanded", "false");
    }
    sidenav.classList.toggle("translate-x-0");
    sidenav.classList.toggle("ml-6");
    sidenav.classList.toggle("shadow-xl");
    if (page == "rtl") {
      top_bread.classList.toggle("-translate-x-[5px]");
      bottom_bread.classList.toggle("-translate-x-[5px]");
    } else {
      top_bread.classList.toggle("translate-x-[5px]");
      bottom_bread.classList.toggle("translate-x-[5px]");
    }
  });

  if (sidenav_close_button) {
    sidenav_close_button.addEventListener("click", function () {
      sidenav_trigger.click();
    });
  }

  window.addEventListener("click", function (e) {
    if (!sidenav.contains(e.target) && !sidenav_trigger.contains(e.target)) {
      if (sidenav.getAttribute("aria-expanded") == "true") {
        sidenav_trigger.click();
      }
    }
  });

  // Leaving a mobile-open sidenav when the window grows to desktop size
  window.addEventListener("resize", function () {
    if (isDesktop() && sidenav.getAttribute("aria-expanded") == "true") {
      sidenav.setAttribute("aria-expanded", "false");
      sidenav.classList.remove("translate-x-0", "ml-6");
      sidenav.classList.add("shadow-xl");
      top_bread.classList.remove("translate-x-[5px]", "-translate-x-[5px]");
      bottom_bread.classList.remove("translate-x-[5px]", "-translate-x-[5px]");
    }
  });
}
