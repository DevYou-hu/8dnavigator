(function () {
  "use strict";

  // Repeatable lists: add, remove, move, collapse
  var uid = 0;
  function updateCount(list) {
    var c = list.querySelector("[data-count]");
    if (c) c.textContent = list.querySelector("[data-items]").children.length;
  }
  function markDirty(el) {
    var form = el.closest("[data-dirty-guard]");
    if (form) form.dispatchEvent(new Event("input", { bubbles: true }));
  }
  function setOpen(item, open) {
    item.classList.toggle("is-open", open);
    var t = item.querySelector("[data-toggle]");
    if (t) t.setAttribute("aria-expanded", open ? "true" : "false");
  }

  document.addEventListener("click", function (e) {
    var btn = e.target.closest("button");
    if (!btn) return;
    var item = btn.closest("[data-item]");
    var list = btn.closest("[data-list]");

    if (btn.hasAttribute("data-toggle")) {
      setOpen(item, !item.classList.contains("is-open"));
    } else if (btn.hasAttribute("data-up") && item.previousElementSibling) {
      item.parentNode.insertBefore(item, item.previousElementSibling);
      btn.focus(); markDirty(item);
    } else if (btn.hasAttribute("data-down") && item.nextElementSibling) {
      item.parentNode.insertBefore(item.nextElementSibling, item);
      btn.focus(); markDirty(item);
    } else if (btn.hasAttribute("data-remove")) {
      var title = item.querySelector("[data-title]").textContent;
      if (window.confirm("Biztosan törlöd ezt az elemet: „" + title + "”?\nA törlés a Mentés gomb megnyomása után lesz végleges.")) {
        markDirty(item);
        item.remove();
        updateCount(list);
      }
    } else if (btn.hasAttribute("data-add")) {
      var tpl = list.querySelector("template[data-template]");
      var html = tpl.innerHTML.split("__i__").join("n" + Date.now() + (uid++));
      var wrap = document.createElement("div");
      wrap.innerHTML = html;
      // give cloned fields unique ids
      wrap.querySelectorAll("[id]").forEach(function (el) {
        var old = el.id, nu = old + "-" + Date.now() + (uid++);
        el.id = nu;
        var lab = wrap.querySelector('label[for="' + old + '"]');
        if (lab) lab.setAttribute("for", nu);
      });
      var node = wrap.firstElementChild;
      list.querySelector("[data-items]").appendChild(node);
      setOpen(node, true);
      updateCount(list);
      markDirty(node);
      var first = node.querySelector("input[type=text], textarea, select");
      if (first) first.focus();
    } else if (btn.hasAttribute("data-expand-all")) {
      var items = list.querySelectorAll("[data-item]");
      var anyClosed = Array.prototype.some.call(items, function (i) { return !i.classList.contains("is-open"); });
      items.forEach(function (i) { setOpen(i, anyClosed); });
      btn.textContent = anyClosed ? "Mind becsuk" : "Mind kinyit";
    }
  });

  // Live item titles
  document.addEventListener("input", function (e) {
    if (e.target.hasAttribute && e.target.hasAttribute("data-item-title")) {
      var item = e.target.closest("[data-item]");
      if (item) item.querySelector("[data-title]").textContent = e.target.value.trim() || "Új elem";
    }
  });

  // Unsaved-changes guard
  document.querySelectorAll("[data-dirty-guard]").forEach(function (form) {
    var dirty = false;
    var note = form.querySelector("[data-dirty-note]");
    function set() { dirty = true; if (note) note.hidden = false; }
    form.addEventListener("input", set);
    form.addEventListener("change", set);
    form.addEventListener("submit", function () { dirty = false; });
    window.addEventListener("beforeunload", function (e) {
      if (dirty) { e.preventDefault(); e.returnValue = ""; }
    });
    // Ctrl+S / Cmd+S saves
    document.addEventListener("keydown", function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "s") {
        e.preventDefault();
        dirty = false;
        form.requestSubmit ? form.requestSubmit() : form.submit();
      }
    });
  });

  // Confirmation for destructive forms
  document.querySelectorAll("form[data-confirm]").forEach(function (f) {
    f.addEventListener("submit", function (e) {
      if (!window.confirm(f.getAttribute("data-confirm"))) e.preventDefault();
    });
  });

  // Auto-hide success messages
  setTimeout(function () {
    document.querySelectorAll(".a-flash--ok").forEach(function (el) { el.classList.add("is-fading"); });
  }, 5000);
})();
