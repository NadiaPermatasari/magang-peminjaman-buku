/*!

=========================================================
* Argon Dashboard Tailwind - v1.0.1 (Laravel starter edition)
=========================================================

* Product Page: https://www.creative-tim.com/product/argon-dashboard-tailwind
* Copyright 2022 Creative Tim (https://www.creative-tim.com)

* Coded by www.creative-tim.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

*/

// The current page key is set by the Blade layout on <body data-page="...">.
// It replaces the original ".html" file name detection.
var page = document.body.getAttribute("data-page") || "dashboard";

// Absolute base URL of the /public/assets folder, injected by the layout
// (window.ARGON_ASSETS). Falls back to /assets/ for plain setups.
var to_build = window.ARGON_ASSETS || "/assets/";

loadStylesheet(to_build + "css/perfect-scrollbar.css");
loadJS(to_build + "js/perfect-scrollbar.js", true);

if (document.querySelector("[slider]")) {
  loadJS(to_build + "js/carousel.js", true);
}

if (document.querySelector("nav [navbar-trigger]")) {
  loadJS(to_build + "js/navbar-collapse.js", true);
}

if (document.querySelector("[data-target='tooltip']")) {
  loadJS(to_build + "js/tooltips.js", true);
  loadStylesheet(to_build + "css/tooltips.css");
}

if (document.querySelector("[nav-pills]")) {
  loadJS(to_build + "js/nav-pills.js", true);
}

if (document.querySelector("[dropdown-trigger]")) {
  loadJS(to_build + "js/dropdown.js", true);
}

if (document.querySelector("[fixed-plugin]")) {
  loadJS(to_build + "js/fixed-plugin.js", true);
}

if (document.querySelector("[navbar-main]") || document.querySelector("[navbar-profile]")) {
  if (document.querySelector("[navbar-main]")) {
    loadJS(to_build + "js/navbar-sticky.js", true);
  }
  if (document.querySelector("aside")) {
    loadJS(to_build + "js/sidenav-burger.js", true);
  }
}

if (document.querySelector("canvas")) {
  loadJS(to_build + "js/charts.js", true);
}

function loadJS(FILE_URL, async) {
  let dynamicScript = document.createElement("script");

  dynamicScript.setAttribute("src", FILE_URL);
  dynamicScript.setAttribute("type", "text/javascript");
  dynamicScript.setAttribute("async", async);

  document.head.appendChild(dynamicScript);
}

function loadStylesheet(FILE_URL) {
  let dynamicStylesheet = document.createElement("link");

  dynamicStylesheet.setAttribute("href", FILE_URL);
  dynamicStylesheet.setAttribute("type", "text/css");
  dynamicStylesheet.setAttribute("rel", "stylesheet");

  document.head.appendChild(dynamicStylesheet);
}
