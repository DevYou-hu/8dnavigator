(function () {
  "use strict";

  // Mobile navigation
  var toggle = document.querySelector(".nav-toggle");
  var nav = document.getElementById("site-nav");
  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && nav.classList.contains("is-open")) {
        nav.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.focus();
      }
    });
  }

  // Footer year and back-to-top
  document.querySelectorAll("[data-year]").forEach(function (el) { el.textContent = new Date().getFullYear(); });
  document.querySelectorAll("[data-to-top]").forEach(function (btn) {
    btn.addEventListener("click", function () { window.scrollTo({ top: 0 }); });
  });

  // Copy buttons
  document.querySelectorAll("[data-copy]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var text = btn.getAttribute("data-copy");
      var done = function () { var t = btn.textContent; btn.textContent = "Copied"; setTimeout(function () { btn.textContent = t; }, 1600); };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done, function () { selectText(btn.previousElementSibling); });
      } else {
        selectText(btn.previousElementSibling);
      }
    });
  });
  function selectText(el) {
    if (!el) return;
    var r = document.createRange(); r.selectNodeContents(el);
    var s = window.getSelection(); s.removeAllRanges(); s.addRange(r);
  }

  // Resource filters
  var chips = document.querySelectorAll("[data-filter]");
  if (chips.length) {
    var groups = document.querySelectorAll("[data-group]");
    var apply = function (key) {
      chips.forEach(function (c) { c.setAttribute("aria-pressed", c.getAttribute("data-filter") === key ? "true" : "false"); });
      groups.forEach(function (g) { g.hidden = key !== "all" && g.getAttribute("data-group") !== key; });
    };
    chips.forEach(function (c) {
      c.addEventListener("click", function () { apply(c.getAttribute("data-filter")); });
    });
  }

  // Contact form
  var form = document.getElementById("contact-form");
  if (!form) return;

  function showStatus(kind, text) {
    var el = document.getElementById("form-status");
    el.hidden = false;
    el.className = "form-status " + kind;
    el.textContent = text;
    el.focus();
  }

  function setError(field, text) {
    var input = form.elements[field];
    var err = document.getElementById("err-" + field);
    if (!input || !err) return;
    if (text) {
      input.setAttribute("aria-invalid", "true");
      err.textContent = text; err.hidden = false;
    } else {
      input.removeAttribute("aria-invalid");
      err.textContent = ""; err.hidden = true;
    }
  }

  function validate() {
    var ok = true;
    var first = form.elements["first_name"].value.trim();
    var last = form.elements["last_name"].value.trim();
    var email = form.elements["email"].value.trim();
    var msg = form.elements["message"].value.trim();
    var consent = form.elements["consent"].checked;
    setError("first_name", first ? "" : "Enter your first name.");
    setError("last_name", last ? "" : "Enter your last name.");
    setError("email", /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) ? "" : "Enter a valid email address, for example name@company.com.");
    setError("message", msg.length >= 10 ? "" : "Write a short message (at least 10 characters).");
    setError("consent", consent ? "" : "Tick the box so we may use your details to reply.");
    [first, last, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email), msg.length >= 10, consent].forEach(function (v) { if (!v) ok = false; });
    return ok;
  }

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    if (!validate()) {
      var bad = form.querySelector('[aria-invalid="true"]');
      if (bad) bad.focus();
      return;
    }
    var btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    var label = btn.textContent;
    btn.textContent = "Sending…";
    fetch(form.getAttribute("action"), {
      method: "POST",
      body: new FormData(form),
      headers: { "Accept": "application/json" }
    })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .then(function (data) {
        if (data && data.ok) {
          form.reset();
          showStatus("ok", "Thank you. Your message has been sent. We will reply by email.");
        } else {
          showStatus("err", (data && data.error) || "The message could not be sent. Please email us directly at info@8dnavigator.com.");
        }
      })
      .catch(function () {
        showStatus("err", "The message could not be sent. Please email us directly at info@8dnavigator.com.");
      })
      .then(function () { btn.disabled = false; btn.textContent = label; });
  });
})();
