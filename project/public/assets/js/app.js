(function () {
  'use strict';

  const qs = (sel, ctx) => (ctx || document).querySelector(sel);
  const qsa = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

  function ajax(url, options = {}) {
    const headers = options.headers || {};
    if (!(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      options.body = options.body ? JSON.stringify(options.body) : null;
    }
    return fetch(url, {
      credentials: 'same-origin',
      ...options,
      headers,
    }).then((response) => {
      if (!response.ok) {
        throw new Error('Ошибка сети: ' + response.statusText);
      }
      const ct = response.headers.get('content-type') || '';
      if (ct.includes('application/json')) {
        return response.json();
      }
      return response.text();
    });
  }

  function initFlashAutoclose() {
    qsa('[data-flash]', document).forEach((el) => {
      setTimeout(() => {
        el.classList.add('fade-out');
      }, 5000);
    });
  }

  function initTabs(root) {
    const tabs = qsa('[data-tab]', root);
    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const id = tab.getAttribute('data-tab');
        tabs.forEach((btn) => btn.classList.toggle('active', btn === tab));
        qsa('[data-tab-panel]', root).forEach((panel) => {
          panel.classList.toggle('active', panel.getAttribute('data-tab-panel') === id);
        });
      });
    });
  }

  function initProfileModal() {
    const backdrop = qs('#profile-modal-backdrop');
    if (!backdrop) return;
    const closeBtn = qs('[data-modal-close]', backdrop);
    if (closeBtn) {
      closeBtn.addEventListener('click', () => hideModal());
    }
    backdrop.addEventListener('click', (event) => {
      if (event.target === backdrop) {
        hideModal();
      }
    });

    function hideModal() {
      backdrop.classList.remove('active');
      qs('#profile-modal-content').innerHTML = '';
    }

    qsa('[data-profile-id]').forEach((button) => {
      button.addEventListener('click', () => {
        const id = button.getAttribute('data-profile-id');
        backdrop.classList.add('active');
        qs('#profile-modal-content').innerHTML = '<div class="modal">Загрузка…</div>';
        fetch(`profile_modal.php?id=${encodeURIComponent(id)}`, {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
          .then((res) => res.text())
          .then((html) => {
            qs('#profile-modal-content').innerHTML = html;
            const form = qs('#profile-form');
            if (form) {
              initTabs(form);
              form.addEventListener('submit', (event) => {
                event.preventDefault();
                const data = new FormData(form);
                fetch('profile_save.php', {
                  method: 'POST',
                  body: data,
                  credentials: 'same-origin',
                })
                  .then((res) => res.json())
                  .then((json) => {
                    const status = qs('#profile-form-status');
                    if (status) {
                      status.textContent = json.message || 'Сохранено';
                      status.className = json.success ? 'alert alert-success mt-sm' : 'alert alert-error mt-sm';
                    }
                    if (json.success) {
                      setTimeout(() => window.location.reload(), 1200);
                    }
                  })
                  .catch((error) => {
                    console.error(error);
                  });
              });
            }
          })
          .catch((error) => {
            console.error(error);
            qs('#profile-modal-content').innerHTML = '<div class="modal">Не удалось загрузить данные.</div>';
          });
      });
    });
  }

  function initPagination() {
    qsa('[data-page]').forEach((item) => {
      item.addEventListener('click', (event) => {
        event.preventDefault();
        const url = new URL(window.location.href);
        url.searchParams.set('page', item.getAttribute('data-page'));
        window.location.href = url.toString();
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initFlashAutoclose();
    initProfileModal();
    initPagination();
    qsa('[data-tabs]').forEach(initTabs);
  });
})();
