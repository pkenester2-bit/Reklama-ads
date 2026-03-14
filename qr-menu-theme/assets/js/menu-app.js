(() => {
  const app = document.getElementById('qrApp');
  if (!app || typeof qrMenuData === 'undefined') return;

  const state = {
    lang: localStorage.getItem('qr_menu_lang') || app.dataset.lang || qrMenuData.lang || 'ru',
    table: app.dataset.table || '',
    cart: JSON.parse(localStorage.getItem('qr_menu_cart') || '[]'),
    categories: [],
    activeCategory: null,
    activeNav: 'home'
  };

  const el = {
    content: document.getElementById('menuContent'),
    skeleton: document.getElementById('menuSkeleton'),
    categoryBar: document.getElementById('categoryBar'),
    cartCounter: document.getElementById('cartCounter'),
    dishModal: document.getElementById('dishModal'),
    dishPanel: document.getElementById('dishModalPanel'),
    cartModal: document.getElementById('cartModal'),
    cartPanel: document.getElementById('cartModalPanel'),
    waiterModal: document.getElementById('waiterModal'),
    waiterPanel: document.getElementById('waiterModalPanel'),
    bookingModal: document.getElementById('bookingModal'),
    bookingPanel: document.getElementById('bookingModalPanel'),
    successModal: document.getElementById('successModal'),
    successPanel: document.getElementById('successModalPanel'),
    toast: document.getElementById('toast'),
    menuSheet: document.getElementById('menuSheet'),
    heroSlider: document.getElementById('heroSlider'),
    heroDots: document.getElementById('heroDots'),
    bottomNav: document.getElementById('bottomNav'),
    langToggleBtn: document.getElementById('langToggleBtn'),
    langMenu: document.getElementById('langMenu'),
    langSwapOption: document.getElementById('langSwapOption'),
    langToggleLabel: document.getElementById('langToggleLabel'),
    themeToggle: document.getElementById('themeToggle')
  };

  const t = key => (qrMenuData.translations?.[state.lang]?.[key] || qrMenuData.translations?.ru?.[key] || key);
  const money = n => `${Number(n || 0).toLocaleString('ru-RU')} ₸`;

  const getValidCategories = payload => {
    const categories = Array.isArray(payload?.categories) ? payload.categories : [];
    return categories.filter(cat => Array.isArray(cat.items) && cat.items.length > 0);
  };

  const saveCart = () => {
    localStorage.setItem('qr_menu_cart', JSON.stringify(state.cart));
  };

  const getTotals = () => {
    return state.cart.reduce((acc, row) => {
      acc.qty += row.qty;
      acc.total += row.qty * row.price;
      return acc;
    }, { qty: 0, total: 0 });
  };

  const showToast = msg => {
    if (!el.toast) return;
    el.toast.textContent = msg;
    el.toast.classList.add('visible');
    setTimeout(() => el.toast.classList.remove('visible'), 1300);
  };

  const applyStaticTranslations = () => {
    document.querySelectorAll('[data-i18n]').forEach(node => {
      const key = node.dataset.i18n;
      node.textContent = t(key);
    });
  };

  const setActiveBottomItem = nav => {
    if (!el.bottomNav) return;
    el.bottomNav.querySelectorAll('.qr-bottom-item').forEach(item => {
      item.classList.toggle('active', item.dataset.nav === nav);
    });
  };

  const closeAllPanels = () => {
    if (el.menuSheet) el.menuSheet.classList.remove('open');
    [el.cartModal, el.bookingModal, el.waiterModal, el.successModal, el.dishModal].forEach(modal => {
      if (modal) modal.classList.remove('open');
    });
  };

  const openNavPanel = nav => {
  const isMenuOpen = el.menuSheet?.classList.contains('open');

    if (nav === 'menu') {
      closeAllPanels();

      if (!isMenuOpen && el.menuSheet) {
        el.menuSheet.classList.add('open');
        state.activeNav = 'menu';
        setActiveBottomItem('menu');
      } else {
        state.activeNav = 'home';
        setActiveBottomItem('home');
    }
    return;
  }

    closeAllPanels();

    state.activeNav = nav;
    setActiveBottomItem(nav);

    if (nav === 'home') {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    if (nav === 'contacts') {
    openBookingModal();
    return;
    }

    if (nav === 'cart') {
      renderCart();
      if (el.cartModal) {
      el.cartModal.classList.add('open');
      }
      return;
    }
  };

  const updateCartWidget = () => {
    const totals = getTotals();
    if (el.cartCounter) el.cartCounter.textContent = totals.qty;
  };

  const getLocalizedDish = item => {
    const title = state.lang === 'kz' ? (item.title_kz || item.title || '') : (item.title_ru || item.title || '');
    const description = state.lang === 'kz' ? (item.description_kz || item.description || '') : (item.description_ru || item.description || '');
    const ingredients = state.lang === 'kz' ? (item.ingredients_kz || item.ingredients || '') : (item.ingredients_ru || item.ingredients || '');
    return { ...item, title, description, ingredients };
  };

  const addToCart = (item, modifiers = []) => {
    const localized = getLocalizedDish(item);
    const key = `${item.id}:${modifiers.join('|')}`;
    const existing = state.cart.find(c => c.key === key);

    if (existing) {
      existing.qty += 1;
    } else {
      state.cart.push({
        key,
        id: item.id,
        title: localized.title,
        price: item.price,
        qty: 1,
        modifiers
      });
    }

    saveCart();
    updateCartWidget();
    showToast(t('added'));
  };

  const openDishModal = item => {
    if (!el.dishPanel || !el.dishModal) return;

    const localized = getLocalizedDish(item);
    const modifierBlocks = (item.modifiers || []).map(group => `
      <div class="dish-mod-group">
        <strong>${group.name}</strong>
        <div>
          ${(group.options || []).map(opt => `<label><input type="checkbox" value="${opt}"> ${opt}</label>`).join('<br>')}
        </div>
      </div>
    `).join('');

    el.dishPanel.innerHTML = `
      <button class="qr-chip" data-close>✕</button>
      <div class="dish-modal-media">${item.image ? `<img src="${item.image}" alt="">` : ''}</div>
      <h2>${localized.title}</h2>
      <p class="qr-muted">${localized.ingredients || ''}</p>
      <p class="qr-muted">${localized.description || ''}</p>
      <p><strong>${money(item.price)}</strong>${item.weight ? ` · ${item.weight}` : ''}</p>
      ${modifierBlocks}
      <button class="btn-add" id="addDishBtn">${t('add')}</button>
    `;

    el.dishModal.classList.add('open');
    document.body.style.overflow = 'hidden';

    const closeBtn = el.dishPanel.querySelector('[data-close]');
    const addBtn = el.dishPanel.querySelector('#addDishBtn');

    if (closeBtn) closeBtn.onclick = () => el.dishModal.classList.remove('open');
    if (addBtn) {
      addBtn.onclick = () => {
        const modifiers = [...el.dishPanel.querySelectorAll('input[type="checkbox"]:checked')].map(i => i.value);
        addToCart(item, modifiers);
        el.dishModal.classList.remove('open');
      };
    }
  };

  const openWaiterList = () => {
    if (!el.waiterPanel || !el.waiterModal) return;

      if (!state.cart.length) {
        el.cartPanel.innerHTML = `
          <button class="qr-chip" data-close>✕</button>

          <div class="qr-empty">${t('empty_cart')}</div>

          <button class="btn-add" style="width:100%;margin-top:16px" id="goMenuBtn">
            ${t('menu')}
          </button>
        `;

        const closeBtn = el.cartPanel.querySelector('[data-close]');
        if (closeBtn) {
          closeBtn.onclick = () => {
            el.cartModal.classList.remove('open');
            document.body.style.overflow = '';
          };
        }

        const goMenuBtn = el.cartPanel.querySelector('#goMenuBtn');

        if (goMenuBtn) {
          goMenuBtn.onclick = () => {
            el.cartModal.classList.remove('open');
            document.body.style.overflow = '';
            openNavPanel('menu');
          };
        }

  return;
}

    const totals = getTotals();

    el.waiterPanel.innerHTML = `
      <button class="qr-chip" data-close>✕</button>
      <h2>${t('waiter_list_title')}</h2>
      <table class="qr-admin-like-table">
        <thead>
          <tr>
            <th>${t('menu')}</th>
            <th>${t('quantity')}</th>
            <th>${t('total')}</th>
          </tr>
        </thead>
        <tbody>
          ${state.cart.map(row => `
            <tr>
              <td>${row.title}</td>
              <td>${row.qty}</td>
              <td>${money(row.qty * row.price)}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
      <p><strong>${t('total')}: ${money(totals.total)}</strong></p>
    `;

    el.waiterModal.classList.add('open');
    const closeBtn = el.waiterPanel.querySelector('[data-close]');
    if (closeBtn) closeBtn.onclick = () => el.waiterModal.classList.remove('open');
  };

  const openBookingModal = () => {
    if (!el.bookingPanel || !el.bookingModal) return;

    const phone = qrMenuData.bookingPhone || '';
    const phoneHref = phone.replace(/[^+\d]/g, '');

    el.bookingPanel.innerHTML = `
      <button class="qr-chip" data-close>✕</button>
      <h2>${t('booking_title')}</h2>
      <p class="qr-muted">${t('booking_phone')}</p>
      <p><strong>${phone || t('no_booking_phone')}</strong></p>
      ${phone ? `<a class="btn-add" style="display:inline-block;" href="tel:${phoneHref}">${t('call_now')}</a>` : ''}
    `;

    el.bookingModal.classList.add('open');
    const closeBtn = el.bookingPanel.querySelector('[data-close]');
    if (closeBtn) closeBtn.onclick = () => el.bookingModal.classList.remove('open');
  };

  const renderCategories = () => {
    if (!el.categoryBar) return;

    el.categoryBar.innerHTML = state.categories.map(cat => `
      <button class="qr-chip ${state.activeCategory === cat.slug ? 'active' : ''}" data-cat="${cat.slug}">
        ${cat.name}
      </button>
    `).join('');

    el.categoryBar.querySelectorAll('[data-cat]').forEach(btn => {
      btn.onclick = () => {
        const slug = btn.dataset.cat;
        state.activeCategory = slug;
        renderCategories();

        const section = document.getElementById(`cat-${slug}`);
        if (section) {
          section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        if (el.menuSheet) el.menuSheet.classList.remove('open');
        state.activeNav = 'home';
        setActiveBottomItem('home');
      };
    });
  };

  const renderMenu = () => {
    if (!el.content) return;

    if (!state.categories.length) {
      el.content.innerHTML = `<div class="qr-empty">${t('empty_results')}</div>`;
      return;
    }

    const html = state.categories.map(cat => `
      <section class="qr-category-section" id="cat-${cat.slug}">
        <h2 class="qr-section-title">${cat.name}</h2>
        <div class="qr-grid">
          ${cat.items.map(item => {
            const localized = getLocalizedDish(item);
            return `
              <article class="menu-item-card" data-id="${item.id}" data-unavailable="${item.available ? 0 : 1}">
                ${item.image ? `<img src="${item.image}" alt="">` : '<div style="background:#eee;border-radius:12px"></div>'}
                <div>
                  <strong>${localized.title}</strong>
                  <p class="qr-muted">${localized.description || ''}</p>
                  <div class="menu-badges">
                    ${(item.badges || []).map(b => `<span class="menu-badge ${b}">${b.toUpperCase()}</span>`).join('')}
                  </div>
                  <div class="menu-price-row">
                    <span class="menu-price">${money(item.price)}</span>
                    <button class="btn-add" data-open="${item.id}" ${item.available ? '' : 'disabled'}>
                      ${item.available ? t('add') : t('unavailable')}
                    </button>
                  </div>
                </div>
              </article>
            `;
          }).join('')}
        </div>
      </section>
    `).join('');

    el.content.innerHTML = html;

    const items = state.categories.flatMap(c => c.items);

    el.content.querySelectorAll('[data-open]').forEach(btn => {
      const id = Number(btn.dataset.open);
      const item = items.find(i => i.id === id);
      if (item) {
        btn.onclick = e => {
          e.stopPropagation();
          openDishModal(item);
        };
      }
    });

    el.content.querySelectorAll('.menu-item-card').forEach(card => {
      card.onclick = () => {
        const id = Number(card.dataset.id);
        const item = items.find(i => i.id === id);
        if (item) openDishModal(item);
      };
    });
  };

  const renderCart = () => {
    if (!el.cartPanel || !el.cartModal) return;

    if (!state.cart.length) {
      el.cartPanel.innerHTML = `
        <button class="qr-chip" data-close>✕</button>

        <div class="qr-empty">
          ${t('empty_cart')}
        </div>

        <button class="btn-add" style="width:100%;margin-top:16px" id="goMenuBtn">
          ${t('menu')}
        </button>
      `;
      const closeBtn = el.cartPanel.querySelector('[data-close]');
      if (closeBtn) closeBtn.onclick = () => el.cartModal.classList.remove('open');
      return;
    }

    const totals = getTotals();

    el.cartPanel.innerHTML = `
      <button class="qr-chip" data-close>✕</button>
      <h2>${t('your_order')}</h2>
      ${state.cart.map((row, i) => `
        <div class="cart-row">
          <div>
            <strong>${row.title}</strong>
            <div class="qr-muted">${row.modifiers.join(', ') || t('no_modifiers')}</div>
          </div>
          <div>
            <div class="qty-controls">
              <button class="qty-btn" data-dec="${i}">−</button>
              <span>${row.qty}</span>
              <button class="qty-btn" data-inc="${i}">+</button>
            </div>
            <div>${money(row.qty * row.price)}</div>
          </div>
        </div>
      `).join('')}
      <p><strong>${t('total')}: ${money(totals.total)}</strong></p>
      <textarea id="orderComment" class="qr-search" placeholder="${t('order_comment')}"></textarea>
      <input id="orderTable" class="qr-search" placeholder="${t('table_number')}" value="${state.table}">
      <input id="orderName" class="qr-search" placeholder="${state.lang === 'kz' ? 'Аты' : 'Имя'}">
      <input id="orderPhone" class="qr-search" placeholder="${state.lang === 'kz' ? 'Телефон' : 'Телефон'}">
      <select id="orderType" class="qr-search">
        <option value="dine-in">${t('order_type_hall')}</option>
        <option value="takeaway">${t('order_type_takeaway')}</option>
        <option value="delivery">${t('order_type_delivery')}</option>
      </select>
      <div class="qr-cart-actions">
        <button class="btn-waiter" id="showWaiterBtn" type="button">${t('show_waiter')}</button>
        <button class="btn-add" id="checkoutBtn" style="width:100%;padding:12px;">${t('checkout')}</button>
      </div>
    `;

    const closeBtn = el.cartPanel.querySelector('[data-close]');
    if (closeBtn) closeBtn.onclick = () => el.cartModal.classList.remove('open');

    const waiterBtn = el.cartPanel.querySelector('#showWaiterBtn');
    if (waiterBtn) waiterBtn.onclick = openWaiterList;

    el.cartPanel.querySelectorAll('[data-inc]').forEach(b => {
      b.onclick = () => {

        const index = Number(b.dataset.inc);

        state.cart[index].qty += 1;

        saveCart();
        renderCart();
        updateCartWidget();
      };
    });



    el.cartPanel.querySelectorAll('[data-dec]').forEach(b => {
      b.onclick = () => {
        const index = Number(b.dataset.dec);
        const row = state.cart[index];
        row.qty -= 1;
        if (row.qty <= 0) state.cart.splice(Number(b.dataset.dec), 1);
        saveCart();
        renderCart();
        updateCartWidget();
      };
    });
  };

    const checkoutBtn = el.cartPanel.querySelector('#checkoutBtn');

    if (checkoutBtn) {
      checkoutBtn.onclick = () => {

        const order = {
          items: state.cart,
          comment: document.getElementById('orderComment')?.value || '',
          table: document.getElementById('orderTable')?.value || '',
          name: document.getElementById('orderName')?.value || '',
          phone: document.getElementById('orderPhone')?.value || '',
          type: document.getElementById('orderType')?.value || 'dine-in'
        };

        console.log('ORDER:', order);

        state.cart = [];
        saveCart();
        updateCartWidget();

        el.cartModal.classList.remove('open');

        if (el.successModal) {
          el.successModal.classList.add('open');
        }

        showToast('Заказ отправлен');
      };
    }

  const renderHero = () => {
    if (!el.heroSlider || !el.heroDots) return;

    const slides = [...el.heroSlider.querySelectorAll('.qr-hero-slide')];
    if (!slides.length) return;

    let active = 0;

    el.heroDots.innerHTML = slides.map((_, i) => `
      <button class="hero-dot ${i === 0 ? 'active' : ''}" data-hero-dot="${i}"></button>
    `).join('');

    const setActive = index => {
      active = index;
      slides.forEach((s, i) => s.classList.toggle('active', i === index));
      el.heroDots.querySelectorAll('.hero-dot').forEach((d, i) => d.classList.toggle('active', i === index));
    };

    el.heroDots.querySelectorAll('.hero-dot').forEach(dot => {
      dot.onclick = () => setActive(Number(dot.dataset.heroDot));
    });

    setInterval(() => {
      setActive((active + 1) % slides.length);
    }, 4500);
  };

  const applyTheme = theme => {
    const isDark = theme === 'dark';
    document.body.classList.toggle('qr-theme-dark', isDark);
    if (el.themeToggle) {
      el.themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    }
  };

  const syncLangDropdown = () => {
    if (!el.langToggleLabel || !el.langSwapOption) return;
    el.langToggleLabel.textContent = state.lang === 'kz' ? 'KZ' : 'RU';
    el.langSwapOption.textContent = state.lang === 'kz' ? 'RU' : 'KZ';
  };

  const setLanguage = lang => {
    state.lang = lang;
    localStorage.setItem('qr_menu_lang', state.lang);
    applyStaticTranslations();
    syncLangDropdown();

    const preloaded = getValidCategories(qrMenuData.preloadedMenu);
    if (preloaded.length) {
      state.categories = preloaded;
      state.activeCategory = preloaded[0]?.slug || null;
      renderCategories();
      renderMenu();
    }

    fetchMenu();
  };

  const fetchMenu = async () => {
    try {
      const res = await fetch(`${qrMenuData.restUrl}/menu?lang=${state.lang}`, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('menu_load_failed');

      const data = await res.json();
      const fresh = getValidCategories(data);

      if (fresh.length) {
        state.categories = fresh;
        if (!state.activeCategory || !fresh.some(cat => cat.slug === state.activeCategory)) {
          state.activeCategory = fresh[0].slug;
        }
        renderCategories();
        renderMenu();
      } else if (!state.categories.length) {
        el.content.innerHTML = `<div class="qr-empty">${t('empty_results')}</div>`;
      }
    } catch (e) {
      if (!state.categories.length && el.content) {
        el.content.innerHTML = `<div class="qr-empty">${t('empty_results')}</div>`;
      }
    } finally {
      if (el.skeleton) el.skeleton.style.display = 'none';
    }
  };

  if (el.langToggleBtn) {
    el.langToggleBtn.onclick = e => {
      e.stopPropagation();
      el.langMenu?.classList.toggle('open');
      el.langToggleBtn.setAttribute(
        'aria-expanded',
        el.langMenu?.classList.contains('open') ? 'true' : 'false'
      );
    };
  }

  if (el.langSwapOption) {
    el.langSwapOption.onclick = () => {
      const nextLang = state.lang === 'ru' ? 'kz' : 'ru';
      setLanguage(nextLang);
      el.langMenu?.classList.remove('open');
      el.langToggleBtn?.setAttribute('aria-expanded', 'false');
    };
  }

  document.addEventListener('click', e => {
    const target = e.target;
    if (el.langMenu && target instanceof Element && !target.closest('.qr-lang-dropdown')) {
      el.langMenu.classList.remove('open');
      el.langToggleBtn?.setAttribute('aria-expanded', 'false');
    }
  });

  if (el.themeToggle) {
    const savedTheme = localStorage.getItem('qr_menu_theme') || 'light';
    applyTheme(savedTheme);

    el.themeToggle.onclick = () => {
      const next = document.body.classList.contains('qr-theme-dark') ? 'light' : 'dark';
      localStorage.setItem('qr_menu_theme', next);
      applyTheme(next);
    };
  }

  if (el.bottomNav) {
    el.bottomNav.querySelectorAll('[data-nav]').forEach(btn => {
      btn.onclick = () => {

        // барлық модалдарды жабу
        [el.cartModal, el.dishModal, el.successModal, el.waiterModal, el.bookingModal]
          .forEach(m => m && m.classList.remove('open'));

        document.body.style.overflow = '';

        // навигация ашу
        openNavPanel(btn.dataset.nav);
      };
    });
  }

  [el.cartModal, el.dishModal, el.successModal, el.waiterModal, el.bookingModal].forEach(modal => {
    if (!modal) return;
    modal.addEventListener('click', e => {
      if (e.target === modal) {
        modal.classList.remove('open');
        state.activeNav = 'home';
        setActiveBottomItem('home');
      }
    });
  });

  const preloaded = getValidCategories(qrMenuData.preloadedMenu);
  if (preloaded.length) {
    state.categories = preloaded;
    state.activeCategory = preloaded[0]?.slug || null;
    renderCategories();
    renderMenu();
    if (el.skeleton) el.skeleton.style.display = 'none';
  }

applyStaticTranslations();
syncLangDropdown();
updateCartWidget();
renderHero();
setActiveBottomItem('home');
fetchMenu();


// CATEGORY SCROLL ACTIVE FIX
window.addEventListener('scroll', () => {

  const sections = document.querySelectorAll('.qr-category-section');

  let current = null;

  sections.forEach(section => {
    const rect = section.getBoundingClientRect();
    if (rect.top <= 120) {
      current = section.id.replace('cat-','');
    }
  });

  if (current && current !== state.activeCategory) {
    state.activeCategory = current;
    renderCategories();
  }

});


})();