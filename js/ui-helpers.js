/**
 * UI-only helpers: toasts and modal alert/confirm/prompt (replaces native dialogs).
 */
(function (global) {
  'use strict';

  function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function escapeAttr(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;');
  }

  function ensureToastHost() {
    let host = document.getElementById('ui-toast-host');
    if (!host) {
      host = document.createElement('div');
      host.id = 'ui-toast-host';
      host.setAttribute('aria-live', 'polite');
      document.body.appendChild(host);
    }
    return host;
  }

  function showToast(message, type) {
    type = type || 'info';
    const host = ensureToastHost();
    const el = document.createElement('div');
    el.className = 'ui-toast ui-toast--' + type;
    el.setAttribute('role', 'status');
    el.textContent = message;
    host.appendChild(el);
    requestAnimationFrame(function () {
      el.classList.add('ui-toast--show');
    });
    setTimeout(function () {
      el.classList.remove('ui-toast--show');
      setTimeout(function () {
        if (el.parentNode) el.remove();
      }, 280);
    }, 3200);
  }

  function uiAlert(message) {
    return new Promise(function (resolve) {
      const backdrop = document.createElement('div');
      backdrop.className = 'ui-modal-backdrop';
      backdrop.setAttribute('role', 'presentation');
      backdrop.innerHTML =
        '<div class="ui-modal card" role="alertdialog" aria-modal="true">' +
        '<p class="ui-modal__body">' +
        escapeHtml(message).replace(/\n/g, '<br>') +
        '</p>' +
        '<div class="ui-modal__actions"><button type="button" class="btn btn-primary ui-modal__ok">OK</button></div>' +
        '</div>';
      document.body.appendChild(backdrop);
      function close() {
        backdrop.remove();
        resolve();
      }
      backdrop.querySelector('.ui-modal__ok').addEventListener('click', close);
      backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) close();
      });
    });
  }

  function uiConfirm(message) {
    return new Promise(function (resolve) {
      const backdrop = document.createElement('div');
      backdrop.className = 'ui-modal-backdrop';
      backdrop.innerHTML =
        '<div class="ui-modal card" role="dialog" aria-modal="true">' +
        '<p class="ui-modal__body">' +
        escapeHtml(message).replace(/\n/g, '<br>') +
        '</p>' +
        '<div class="ui-modal__actions">' +
        '<button type="button" class="btn btn-secondary" data-cancel>Cancel</button>' +
        '<button type="button" class="btn btn-primary" data-ok>OK</button>' +
        '</div></div>';
      document.body.appendChild(backdrop);
      function finish(v) {
        backdrop.remove();
        resolve(v);
      }
      backdrop.querySelector('[data-cancel]').addEventListener('click', function () {
        finish(false);
      });
      backdrop.querySelector('[data-ok]').addEventListener('click', function () {
        finish(true);
      });
      backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) finish(false);
      });
    });
  }

  function uiPrompt(message, defaultValue, title) {
    defaultValue = defaultValue == null ? '' : String(defaultValue);
    return new Promise(function (resolve) {
      const backdrop = document.createElement('div');
      backdrop.className = 'ui-modal-backdrop';
      const titleHtml = title
        ? '<h3 class="ui-modal__title">' + escapeHtml(title) + '</h3>'
        : '';
      backdrop.innerHTML =
        '<div class="ui-modal card ui-modal--prompt" role="dialog" aria-modal="true">' +
        titleHtml +
        '<p class="ui-modal__body">' +
        escapeHtml(message).replace(/\n/g, '<br>') +
        '</p>' +
        '<input type="text" class="input ui-modal__input" value="' +
        escapeAttr(defaultValue) +
        '" />' +
        '<div class="ui-modal__actions">' +
        '<button type="button" class="btn btn-secondary" data-cancel>Cancel</button>' +
        '<button type="button" class="btn btn-primary" data-ok>OK</button>' +
        '</div></div>';
      document.body.appendChild(backdrop);
      var input = backdrop.querySelector('.ui-modal__input');
      input.focus();
      input.select();
      function finish(val) {
        backdrop.remove();
        resolve(val);
      }
      backdrop.querySelector('[data-cancel]').addEventListener('click', function () {
        finish(null);
      });
      backdrop.querySelector('[data-ok]').addEventListener('click', function () {
        finish(input.value);
      });
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') finish(input.value);
        if (e.key === 'Escape') finish(null);
      });
      backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) finish(null);
      });
    });
  }

  global.showToast = showToast;
  global.uiAlert = uiAlert;
  global.uiConfirm = uiConfirm;
  global.uiPrompt = uiPrompt;
})(typeof window !== 'undefined' ? window : globalThis);
