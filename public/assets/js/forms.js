/*
 * Form helpers for the Argon Laravel starter (no jQuery).
 *
 *  [data-tom-select]        select2-style select (search, multiple, tags via data-create="true")
 *  [data-file-input]        single file input with image preview + file name
 *  [data-dropzone]          drag & drop multi-file upload with previews and remove buttons
 *  [data-password-toggle]   eye button that shows / hides a password field
 *  [data-range-output]      live value label for <input type="range">
 *  [data-count-for]         live character counter for textareas
 */
(function () {
  "use strict";

  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  function formatBytes(bytes) {
    if (bytes === 0) return "0 B";
    var units = ["B", "KB", "MB", "GB"];
    var i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0) + " " + units[i];
  }

  /* ---------------------------------------------------------------- */
  /* Tom Select                                                        */
  /* ---------------------------------------------------------------- */
  function initTomSelect(root) {
    if (typeof TomSelect === "undefined") return;

    root.querySelectorAll("select[data-tom-select]").forEach(function (el) {
      if (el.tomselect) return;

      var multiple = el.multiple;
      var options = {
        create: el.dataset.create === "true",
        maxItems: multiple ? (el.dataset.maxItems ? parseInt(el.dataset.maxItems, 10) : null) : 1,
        maxOptions: 500,
        allowEmptyOption: !multiple,
        hidePlaceholder: multiple,
        placeholder: el.dataset.placeholder || el.getAttribute("placeholder") || (multiple ? "Select..." : undefined),
        plugins: {},
        onInitialize: function () {
          this.wrapper.classList.add("argon-select");
          if (el.classList.contains("is-invalid")) this.wrapper.classList.add("is-invalid");
        },
      };

      if (multiple) {
        options.plugins.remove_button = { title: "Remove" };
      } else if (el.dataset.search !== "false") {
        options.plugins.dropdown_input = {};
      }
      if (el.dataset.clear === "true") {
        options.plugins.clear_button = { title: "Clear" };
      }
      if (el.dataset.createOnBlur === "true") {
        options.createOnBlur = true;
      }

      new TomSelect(el, options);
    });
  }

  /* ---------------------------------------------------------------- */
  /* Single file input with preview                                    */
  /* ---------------------------------------------------------------- */
  function initFileInputs(root) {
    root.querySelectorAll("[data-file-input]").forEach(function (wrapper) {
      var input = wrapper.querySelector('input[type="file"]');
      var nameEl = wrapper.querySelector("[data-file-name]");
      var previewEl = wrapper.querySelector("[data-file-preview]");
      var clearBtn = wrapper.querySelector("[data-file-clear]");
      if (!input) return;

      function reset() {
        input.value = "";
        if (nameEl) nameEl.textContent = nameEl.dataset.placeholder || "No file chosen";
        if (previewEl) {
          previewEl.src = previewEl.dataset.placeholder || "";
          previewEl.classList.toggle("hidden", !previewEl.dataset.placeholder);
        }
        if (clearBtn) clearBtn.classList.add("hidden");
      }

      input.addEventListener("change", function () {
        var file = input.files && input.files[0];
        if (!file) return reset();
        if (nameEl) nameEl.textContent = file.name + " (" + formatBytes(file.size) + ")";
        if (previewEl) {
          if (file.type.indexOf("image/") === 0) {
            previewEl.src = URL.createObjectURL(file);
            previewEl.classList.remove("hidden");
          } else {
            previewEl.classList.add("hidden");
          }
        }
        if (clearBtn) clearBtn.classList.remove("hidden");
      });

      if (clearBtn) clearBtn.addEventListener("click", reset);
    });
  }

  /* ---------------------------------------------------------------- */
  /* Drag & drop multi upload                                          */
  /* ---------------------------------------------------------------- */
  function initDropzones(root) {
    root.querySelectorAll("[data-dropzone]").forEach(function (zone) {
      var input = zone.querySelector('input[type="file"]');
      var list = zone.querySelector("[data-dropzone-list]");
      var counter = zone.querySelector("[data-dropzone-count]");
      var browse = zone.querySelector("[data-dropzone-browse]");
      if (!input) return;

      var maxFiles = parseInt(zone.dataset.maxFiles || "0", 10);
      var maxSize = parseInt(zone.dataset.maxSize || "0", 10); // bytes
      var files = [];

      function sync() {
        // Rebuild the input's FileList from our array so the form submits exactly what is shown.
        var dt = new DataTransfer();
        files.forEach(function (f) {
          dt.items.add(f);
        });
        input.files = dt.files;
        render();
      }

      function render() {
        if (!list) return;
        list.innerHTML = "";
        files.forEach(function (file, index) {
          var item = document.createElement("div");
          item.className = "flex items-center p-2 mb-2 border border-solid rounded-lg border-gray-200 dark:border-white/20 bg-white dark:bg-slate-850";
          var thumb;
          if (file.type.indexOf("image/") === 0) {
            thumb = document.createElement("img");
            thumb.src = URL.createObjectURL(file);
            thumb.className = "object-cover w-10 h-10 mr-3 rounded-lg";
          } else {
            thumb = document.createElement("div");
            thumb.className = "flex items-center justify-center w-10 h-10 mr-3 text-white rounded-lg bg-gradient-to-tl from-slate-600 to-slate-300";
            thumb.innerHTML = '<i class="fas fa-file"></i>';
          }
          var text = document.createElement("div");
          text.className = "flex-1 min-w-0";
          text.innerHTML =
            '<p class="mb-0 text-sm font-semibold truncate text-slate-700 dark:text-white">' +
            escapeHtml(file.name) +
            '</p><p class="mb-0 text-xs text-slate-400">' +
            formatBytes(file.size) +
            "</p>";
          var remove = document.createElement("button");
          remove.type = "button";
          remove.className = "p-0 ml-3 text-sm bg-transparent border-0 cursor-pointer text-slate-400 hover:text-red-600";
          remove.innerHTML = '<i class="fas fa-times"></i>';
          remove.addEventListener("click", function () {
            files.splice(index, 1);
            sync();
          });
          item.appendChild(thumb);
          item.appendChild(text);
          item.appendChild(remove);
          list.appendChild(item);
        });
        if (counter) {
          counter.textContent = files.length
            ? files.length + " file" + (files.length > 1 ? "s" : "") + " selected"
            : counter.dataset.placeholder || "";
        }
      }

      function addFiles(fileList) {
        var errors = [];
        Array.prototype.forEach.call(fileList, function (file) {
          if (maxFiles && files.length >= maxFiles) {
            errors.push("Maximum " + maxFiles + " files.");
            return;
          }
          if (maxSize && file.size > maxSize) {
            errors.push(file.name + " is larger than " + formatBytes(maxSize) + ".");
            return;
          }
          var duplicate = files.some(function (f) {
            return f.name === file.name && f.size === file.size;
          });
          if (!duplicate) files.push(file);
        });
        sync();
        var errorEl = zone.querySelector("[data-dropzone-error]");
        if (errorEl) {
          errorEl.textContent = errors.filter(function (v, i, a) {
            return a.indexOf(v) === i;
          }).join(" ");
          errorEl.classList.toggle("hidden", errors.length === 0);
        }
      }

      input.addEventListener("change", function () {
        addFiles(input.files);
      });

      ["dragenter", "dragover"].forEach(function (evt) {
        zone.addEventListener(evt, function (e) {
          e.preventDefault();
          zone.classList.add("is-dragover");
        });
      });
      ["dragleave", "drop"].forEach(function (evt) {
        zone.addEventListener(evt, function (e) {
          e.preventDefault();
          zone.classList.remove("is-dragover");
        });
      });
      zone.addEventListener("drop", function (e) {
        if (e.dataTransfer && e.dataTransfer.files.length) addFiles(e.dataTransfer.files);
      });
      if (browse) {
        browse.addEventListener("click", function (e) {
          e.preventDefault();
          input.click();
        });
      }
    });
  }

  /* ---------------------------------------------------------------- */
  /* Small helpers                                                     */
  /* ---------------------------------------------------------------- */
  function initPasswordToggles(root) {
    root.querySelectorAll("[data-password-toggle]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var target = document.getElementById(btn.dataset.passwordToggle);
        if (!target) return;
        var show = target.type === "password";
        target.type = show ? "text" : "password";
        var icon = btn.querySelector("i");
        if (icon) {
          icon.classList.toggle("fa-eye", !show);
          icon.classList.toggle("fa-eye-slash", show);
        }
      });
    });
  }

  function initRangeOutputs(root) {
    root.querySelectorAll("[data-range-output]").forEach(function (range) {
      var out = document.getElementById(range.dataset.rangeOutput);
      if (!out) return;
      var update = function () {
        out.textContent = range.value + (range.dataset.suffix || "");
      };
      range.addEventListener("input", update);
      update();
    });
  }

  function initCounters(root) {
    root.querySelectorAll("[data-count-for]").forEach(function (el) {
      var field = document.getElementById(el.dataset.countFor);
      if (!field) return;
      var max = field.getAttribute("maxlength");
      var update = function () {
        el.textContent = field.value.length + (max ? " / " + max : "");
      };
      field.addEventListener("input", update);
      update();
    });
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  window.argonForms = {
    init: function (root) {
      root = root || document;
      initTomSelect(root);
      initFileInputs(root);
      initDropzones(root);
      initPasswordToggles(root);
      initRangeOutputs(root);
      initCounters(root);
    },
  };

  ready(function () {
    window.argonForms.init(document);
  });
})();
