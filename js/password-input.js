(function () {
  'use strict';

  const ICONS = {
    hidden: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12S5 5 12 5s11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
    visible: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.88 21.88 0 0 1 5.06-6.94"></path><path d="M1 1l22 22"></path><path d="M9.88 9.88a3 3 0 0 0 4.24 4.24"></path><path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path></svg>'
  };

  class PasswordInput {
    constructor(input) {
      this.input = input;
      this.inputType = 'password';
      this.button = null;
      this.isVisible = false;
      this.init();
    }

    init() {
      if (!this.input || this.input.dataset.passwordInitialized) return;

      this.input.dataset.passwordInitialized = 'true';
      this.input.setAttribute('autocomplete', this.input.autocomplete || 'current-password');
      this.input.classList.add('password-toggle-input');
      this.wrapInput();
      this.createToggle();
      this.updateToggle();
      this.attachEvents();
    }

    wrapInput() {
      const wrapper = document.createElement('div');
      wrapper.className = 'password-input-wrapper';
      this.input.parentNode.insertBefore(wrapper, this.input);
      wrapper.appendChild(this.input);
    }

    createToggle() {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'password-toggle';
      button.title = 'Show password';
      button.setAttribute('aria-pressed', 'false');
      button.setAttribute('aria-label', 'Show password');
      button.innerHTML = '<span class="password-toggle__icon"></span>';
      this.button = button;
      this.input.parentNode.appendChild(button);
    }

    attachEvents() {
      this.button.addEventListener('click', this.toggleVisibility.bind(this));
      this.input.addEventListener('input', this.syncState.bind(this));
    }

    updateToggle() {
      const iconElement = this.button.querySelector('.password-toggle__icon');
      iconElement.innerHTML = this.isVisible ? ICONS.visible : ICONS.hidden;
      this.button.setAttribute('aria-pressed', String(this.isVisible));
      this.button.setAttribute('aria-label', this.isVisible ? 'Hide password' : 'Show password');
      this.button.title = this.isVisible ? 'Hide password' : 'Show password';
      this.input.type = this.isVisible ? 'text' : 'password';
    }

    toggleVisibility() {
      this.isVisible = !this.isVisible;
      this.updateToggle();
    }

    syncState() {
      if (this.input.type === 'text' && !this.isVisible) {
        this.isVisible = false;
        this.updateToggle();
      }
    }
  }

  function initPasswordInputs(root = document) {
    const inputs = Array.from(root.querySelectorAll('input[type="password"][data-password-toggle]:not([data-password-initialized])'));
    inputs.forEach((input) => new PasswordInput(input));
  }

  document.addEventListener('DOMContentLoaded', function () {
    initPasswordInputs(document);
  });

  window.PasswordInput = PasswordInput;
  window.initPasswordInputs = initPasswordInputs;
})();
