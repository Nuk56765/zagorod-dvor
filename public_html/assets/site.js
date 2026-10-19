// Поведение страниц сайта: меню, всплывающие формы, поиск по каталогу, цена с опциями.
(function () {
  "use strict";

  function onEscape(handler) {
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") handler();
    });
  }

  // Меню на телефоне
  document.querySelectorAll("[data-nav]").forEach(function (wrap) {
    var button = wrap.querySelector("[data-nav-toggle]");
    if (!button) return;
    button.addEventListener("click", function () {
      var open = !wrap.classList.contains("open");
      wrap.classList.toggle("open", open);
      button.setAttribute("aria-expanded", String(open));
      button.textContent = open ? "Закрыть меню" : "Меню";
    });
  });

  // Заявка в шапке: открывается при наведении и по клику
  document.querySelectorAll("[data-order-widget]").forEach(function (root) {
    var button = root.querySelector("button");
    var timer = null;
    var pinned = root.classList.contains("is-open");
    function set(open) {
      root.classList.toggle("is-open", open);
      button.setAttribute("aria-expanded", String(open));
    }
    root.addEventListener("mouseenter", function () { clearTimeout(timer); set(true); });
    root.addEventListener("mouseleave", function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        if (!pinned && !root.contains(document.activeElement)) set(false);
      }, 180);
    });
    root.addEventListener("focusin", function () { set(true); });
    button.addEventListener("click", function () {
      pinned = !root.classList.contains("is-open") || !pinned;
      set(pinned);
    });
    onEscape(function () { pinned = false; set(false); });
  });

  // «Остались вопросы?»
  document.querySelectorAll("[data-ask-widget]").forEach(function (root) {
    var panel = root.querySelector("form");
    var toggle = root.querySelector("[data-ask-toggle]");
    function set(open) {
      panel.hidden = !open;
      toggle.setAttribute("aria-expanded", String(open));
      if (open) {
        var field = panel.querySelector("input[name=name]");
        if (field) field.focus();
      }
    }
    toggle.addEventListener("click", function () { set(panel.hidden); });
    root.querySelector("[data-ask-close]").addEventListener("click", function () { set(false); });
    onEscape(function () { set(false); });
  });

  // Карта области на телефоне листается: сразу показываем восток, где Муром
  document.querySelectorAll(".map-scroll").forEach(function (box) {
    if (box.scrollWidth > box.clientWidth) box.scrollLeft = box.scrollWidth;
  });

  // Цели Яндекс Метрики: заявка и звонок
  function goal(name) {
    if (window.ym && window.zdMetrikaId) window.ym(window.zdMetrikaId, "reachGoal", name);
  }
  document.addEventListener("click", function (event) {
    var link = event.target.closest ? event.target.closest("a[href^='tel:']") : null;
    if (link) goal("phone");
  });

  // Кнопка отправки: защита от двойного нажатия
  document.addEventListener("submit", function (event) {
    var form = event.target;
    if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== "post") return;
    if (form.getAttribute("action") === "/form/lead") goal("lead");
    if (form.getAttribute("action") === "/form/review") goal("review");
    var button = form.querySelector("[data-submit]");
    if (!button) return;
    setTimeout(function () {
      button.disabled = true;
      button.textContent = "Отправляем…";
    }, 0);
  });

  // Цена с опциями на карточке товара
  document.querySelectorAll("[data-configurator]").forEach(function (box) {
    var base = Number(box.getAttribute("data-base")) || 0;
    var priceEl = box.querySelector("[data-price]");
    var extraRow = box.querySelector("[data-extra]");
    var extraValue = box.querySelector("[data-extra-value]");
    var format = function (value) {
      return new Intl.NumberFormat("ru-RU").format(value).replace(/\s/g, " ") + " ₽";
    };
    function update() {
      var extra = 0;
      box.querySelectorAll("input[data-price]").forEach(function (input) {
        if (input.checked) extra += Number(input.getAttribute("data-price")) || 0;
      });
      priceEl.textContent = format(base + extra);
      if (extraRow) {
        extraRow.hidden = extra === 0;
        extraValue.textContent = format(extra);
      }
    }
    box.addEventListener("change", update);
    update();
  });

  // Поиск на главной: подсказки по разделам и товарам, Enter — каталог с результатами
  document.querySelectorAll("[data-site-search]").forEach(function (form) {
    var input = form.querySelector("input[name=q]");
    var list = form.querySelector(".search-suggest");
    var dataEl = form.querySelector("[data-search-index]");
    var index = dataEl ? JSON.parse(dataEl.textContent) : [];
    var shown = [];
    var active = -1;
    var fold = function (v) { return v.toLowerCase().replace(/ё/g, "е").replace(/[×хx]/g, "x").replace(/\s+/g, " ").trim(); };
    var match = function (s, q) {
      var tight = s.replace(/ /g, "");
      return q.split(" ").every(function (w) { return s.indexOf(w) >= 0 || tight.indexOf(w) >= 0; });
    };
    function close() { list.hidden = true; input.setAttribute("aria-expanded", "false"); }
    function render() {
      var q = fold(input.value);
      list.innerHTML = "";
      if (!q) { shown = []; close(); return; }
      var found = index.filter(function (item) { return match(item.s, q); });
      shown = found.filter(function (i) { return i.k === "cat"; }).concat(found.filter(function (i) { return i.k !== "cat"; })).slice(0, 8);
      if (!shown.length) {
        var empty = document.createElement("li");
        empty.className = "search-empty";
        empty.textContent = "Ничего не нашли. Нажмите «Найти» или оставьте заявку на свой размер.";
        list.appendChild(empty);
      }
      shown.forEach(function (item, i) {
        var li = document.createElement("li");
        li.setAttribute("role", "presentation");
        var a = document.createElement("a");
        a.href = item.u;
        a.setAttribute("role", "option");
        a.setAttribute("aria-selected", String(i === active));
        a.className = (i === active ? "is-active " : "") + (item.k === "cat" ? "is-category" : "");
        var t = document.createElement("span"); t.textContent = item.t;
        var p = document.createElement("strong"); p.textContent = item.p;
        var c = document.createElement("span"); c.className = "muted"; c.textContent = item.c;
        a.appendChild(t); a.appendChild(p); a.appendChild(c);
        a.addEventListener("mouseenter", function () { active = i; });
        li.appendChild(a);
        list.appendChild(li);
      });
      list.hidden = false;
      input.setAttribute("aria-expanded", "true");
    }
    input.addEventListener("input", function () { active = -1; render(); });
    input.addEventListener("focus", render);
    input.addEventListener("keydown", function (event) {
      if (event.key === "Escape") { close(); return; }
      if (!shown.length) return;
      if (event.key === "ArrowDown") { event.preventDefault(); active = (active + 1) % shown.length; render(); }
      if (event.key === "ArrowUp") { event.preventDefault(); active = active <= 0 ? shown.length - 1 : active - 1; render(); }
    });
    form.addEventListener("submit", function (event) {
      if (shown[active]) { event.preventDefault(); location.href = shown[active].u; return; }
      if (!input.value.trim()) { event.preventDefault(); location.href = "/katalog"; }
    });
    document.addEventListener("pointerdown", function (event) { if (!form.contains(event.target)) close(); });
  });

  // Поиск по каталогу: фильтр карточек и подсказки
  var searchForm = document.querySelector("[data-catalog-search]");
  if (searchForm) {
    var input = searchForm.querySelector("input[name=q]");
    var list = searchForm.querySelector(".search-suggest");
    var grid = document.querySelector("[data-catalog-grid]");
    var empty = document.querySelector("[data-catalog-empty]");
    var dataEl = document.getElementById("catalog-data");
    var items = dataEl ? JSON.parse(dataEl.textContent) : [];
    var active = -1;
    var shown = [];

    var fold = function (value) {
      return value.toLowerCase().replace(/ё/g, "е").replace(/[×хx]/g, "x").replace(/\s+/g, " ").trim();
    };
    var matches = function (haystack, query) {
      var tight = haystack.replace(/ /g, "");
      return query.split(" ").every(function (word) {
        return haystack.indexOf(word) >= 0 || tight.indexOf(word) >= 0;
      });
    };

    function render() {
      var query = fold(input.value);
      var count = 0;
      grid.querySelectorAll("[data-search]").forEach(function (card) {
        var ok = !query || matches(card.getAttribute("data-search"), query);
        card.hidden = !ok;
        if (ok) count++;
      });
      empty.hidden = count > 0;
      shown = query ? items.filter(function (item) { return matches(item.s, query); }).slice(0, 8) : [];
      list.innerHTML = "";
      if (!query || document.activeElement !== input) {
        list.hidden = true;
        input.setAttribute("aria-expanded", "false");
        return;
      }
      if (!shown.length) {
        var li = document.createElement("li");
        li.className = "search-empty";
        li.textContent = "Ничего не нашли. Посмотрите раздел целиком или оставьте свой размер.";
        list.appendChild(li);
      }
      shown.forEach(function (item, index) {
        var li = document.createElement("li");
        li.setAttribute("role", "presentation");
        var a = document.createElement("a");
        a.href = item.u;
        a.id = "catalog-opt-" + index;
        a.setAttribute("role", "option");
        a.setAttribute("aria-selected", String(index === active));
        if (index === active) a.className = "is-active";
        var title = document.createElement("span");
        title.textContent = item.t;
        var price = document.createElement("strong");
        price.textContent = item.p;
        var meta = document.createElement("span");
        meta.className = "muted";
        meta.textContent = item.c;
        a.appendChild(title);
        a.appendChild(price);
        a.appendChild(meta);
        a.addEventListener("mouseenter", function () { active = index; });
        li.appendChild(a);
        list.appendChild(li);
      });
      list.hidden = false;
      input.setAttribute("aria-expanded", "true");
    }

    input.addEventListener("input", function () { active = -1; render(); });
    input.addEventListener("focus", render);
    input.addEventListener("keydown", function (event) {
      if (event.key === "Escape") { list.hidden = true; return; }
      if (!shown.length) return;
      if (event.key === "ArrowDown") { event.preventDefault(); active = (active + 1) % shown.length; render(); }
      if (event.key === "ArrowUp") { event.preventDefault(); active = active <= 0 ? shown.length - 1 : active - 1; render(); }
    });
    searchForm.addEventListener("submit", function (event) {
      if (shown[active]) { event.preventDefault(); location.href = shown[active].u; return; }
      event.preventDefault();
      list.hidden = true;
      var url = new URL(location.href);
      if (input.value.trim()) url.searchParams.set("q", input.value.trim());
      else url.searchParams.delete("q");
      history.replaceState(null, "", url);
    });
    document.addEventListener("pointerdown", function (event) {
      if (!searchForm.contains(event.target)) list.hidden = true;
    });

    // Подсказка в поле: названия моделей по очереди
    if (!input.value && items.length && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      var i = 0;
      setInterval(function () {
        if (input.value) return;
        i = (i + 1) % items.length;
        input.placeholder = items[i].t;
      }, 2600);
    }
  }
})();
