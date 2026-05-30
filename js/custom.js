/**
 * Sample GNU Board Base Template — custom.js
 *
 * - 순수 JavaScript (Vanilla JS) 기준
 * - jQuery·그누보드 common.js 와 네임스페이스 분리 (window.G5Template)
 * - 요소가 없으면 해당 기능만 건너뜀 (에러 없음)
 */
(function (window, document) {
  'use strict';

  var G5Template = window.G5Template || {};

  /* --------------------------------------------------------------------------
   * Config
   * -------------------------------------------------------------------------- */
  G5Template.config = {
    debug: false,
    /** true 시 #siteMain 에 snap-enabled 클래스 (PC만 CSS 적용) */
    scrollSnapEnabled: false,
    /** 헤더 스크롤 class 전환 기준(px) */
    headerScrollOffset: 10,
    /** 상단 이동 버튼 표시 기준(px) */
    goTopVisibleOffset: 300,
    /** 앵커 스크롤 시 고정 헤더 보정(px) */
    anchorHeaderOffset: 72,
    /** reveal IntersectionObserver threshold */
    revealThreshold: 0.12
  };

  /* --------------------------------------------------------------------------
   * Utilities
   * -------------------------------------------------------------------------- */

  /**
   * DOM 준비 후 1회 실행
   * @param {Function} fn
   */
  G5Template.ready = function (fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  };

  /**
   * 단일 요소 조회
   * @param {string} selector
   * @param {ParentNode=} context
   * @returns {Element|null}
   */
  G5Template.qs = function (selector, context) {
    return (context || document).querySelector(selector);
  };

  /**
   * 여러 요소 조회
   * @param {string} selector
   * @param {ParentNode=} context
   * @returns {Element[]}
   */
  G5Template.qsa = function (selector, context) {
    return Array.prototype.slice.call((context || document).querySelectorAll(selector));
  };

  /**
   * 스크롤 Y 위치 (크로스 브라우저)
   * @returns {number}
   */
  G5Template.getScrollY = function () {
    return window.pageYOffset || document.documentElement.scrollTop || 0;
  };

  /**
   * 부드러운 스크롤 지원 여부
   * @returns {boolean}
   */
  G5Template.supportsSmoothScroll = function () {
    return 'scrollBehavior' in document.documentElement.style;
  };

  /**
   * 대상 위치로 스크롤 (고정 헤더 높이 보정)
   * @param {Element} target
   */
  G5Template.scrollToElement = function (target) {
    if (!target || typeof target.getBoundingClientRect !== 'function') {
      return;
    }
    var offset = G5Template.config.anchorHeaderOffset;
    var top = target.getBoundingClientRect().top + G5Template.getScrollY() - offset;

    if (G5Template.supportsSmoothScroll()) {
      window.scrollTo({ top: top, left: 0, behavior: 'smooth' });
    } else {
      window.scrollTo(0, top);
    }
  };

  /**
   * body 스크롤 잠금 (모달·모바일 메뉴)
   * @param {boolean} lock
   */
  G5Template.lockBodyScroll = function (lock) {
    document.body.style.overflow = lock ? 'hidden' : '';
  };

  /* --------------------------------------------------------------------------
   * 1~3. 모바일 메뉴 (열기/닫기/바깥클릭/ESC)
   *
   * 지원 선택자 (빌더·기존 head.php 호환):
   * - 열기: .mobile-menu-btn, .site-header__menu-btn
   * - 메뉴: .mobile-menu, #siteMobileNav
   * - 닫기: .mobile-menu-close, .site-header__mobile-close
   * - 오버레이: .mobile-menu-overlay, .site-header__overlay
   * - 열림 상태 class: is-open (메뉴·오버레이)
   * -------------------------------------------------------------------------- */
  G5Template.initMobileMenu = function () {
    var openBtns = G5Template.qsa('.mobile-menu-btn, .site-header__menu-btn');
    var menus = G5Template.qsa('.mobile-menu, #siteMobileNav');
    var closeBtns = G5Template.qsa('.mobile-menu-close, .site-header__mobile-close');
    var overlays = G5Template.qsa('.mobile-menu-overlay, .site-header__overlay');

    if (!openBtns.length || !menus.length) {
      return;
    }

    var menu = menus[0];
    var overlay = overlays.length ? overlays[0] : null;
    var isOpen = false;

    /**
     * @param {boolean} open
     */
    var setOpen = function (open) {
      isOpen = !!open;
      menu.classList.toggle('is-open', isOpen);
      if (overlay) {
        overlay.classList.toggle('is-open', isOpen);
      }
      openBtns.forEach(function (btn) {
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
      menu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      G5Template.lockBodyScroll(isOpen);
    };

    openBtns.forEach(function (btn) {
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        setOpen(!menu.classList.contains('is-open'));
      });
    });

    closeBtns.forEach(function (btn) {
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        setOpen(false);
      });
    });

    if (overlay) {
      overlay.addEventListener('click', function () {
        setOpen(false);
      });
    }

    /* 메뉴 바깥 클릭 시 닫기 */
    document.addEventListener('click', function (event) {
      if (!isOpen) {
        return;
      }
      var target = event.target;
      if (!(target instanceof Element)) {
        return;
      }
      var clickedInsideMenu = menu.contains(target);
      var clickedOpenBtn = openBtns.some(function (btn) {
        return btn.contains(target);
      });
      if (!clickedInsideMenu && !clickedOpenBtn) {
        setOpen(false);
      }
    });

    /* ESC 키로 닫기 */
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && isOpen) {
        setOpen(false);
      }
    });
  };

  /* --------------------------------------------------------------------------
   * 4. 스크롤 시 헤더 class 변경
   *
   * 대상: #siteHeader
   * 추가 class: is-scrolled (스크롤 시)
   * -------------------------------------------------------------------------- */
  G5Template.initHeaderScroll = function () {
    var header = document.getElementById('siteHeader');
    if (!header) {
      return;
    }

    var threshold = G5Template.config.headerScrollOffset;
    var ticking = false;

    var update = function () {
      if (G5Template.getScrollY() > threshold) {
        header.classList.add('is-scrolled');
      } else {
        header.classList.remove('is-scrolled');
      }
      ticking = false;
    };

    window.addEventListener('scroll', function () {
      if (!ticking) {
        window.requestAnimationFrame(update);
        ticking = true;
      }
    }, { passive: true });

    update();
  };

  /* --------------------------------------------------------------------------
   * 5. FAQ 아코디언
   *
   * HTML 예시:
   * <div class="faq-item">
   *   <button type="button" class="faq-question">질문</button>
   *   <div class="faq-answer">답변</div>
   * </div>
   *
   * 열림 class: .faq-item.is-open
   *
   * 아코디언 모드 (.faq-list):
   * - data-accordion-mode="multiple" (기본) — 여러 FAQ 동시에 열림 (faq-accordion 게시판)
   * - data-accordion-mode="single" — 한 번에 하나만 열림 (랜딩 section/faq.php 등)
   * -------------------------------------------------------------------------- */
  G5Template.initFaqAccordion = function () {
    var items = G5Template.qsa('.faq-item');
    if (!items.length) {
      return;
    }

    items.forEach(function (item) {
      var question = G5Template.qs('.faq-question', item);
      if (!question) {
        return;
      }

      question.addEventListener('click', function (event) {
        event.preventDefault();
        var willOpen = !item.classList.contains('is-open');

        /* single 모드일 때만 형제 FAQ 닫기 */
        var parent = item.parentElement;
        if (parent && parent.classList.contains('faq-list') && parent.getAttribute('data-accordion-mode') === 'single') {
          G5Template.qsa('.faq-item', parent).forEach(function (sibling) {
            if (sibling !== item) {
              sibling.classList.remove('is-open');
              var q = G5Template.qs('.faq-question', sibling);
              if (q) {
                q.setAttribute('aria-expanded', 'false');
              }
            }
          });
        }

        item.classList.toggle('is-open', willOpen);
        question.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      });

      if (!question.hasAttribute('aria-expanded')) {
        question.setAttribute('aria-expanded', item.classList.contains('is-open') ? 'true' : 'false');
      }
    });
  };

  /* --------------------------------------------------------------------------
   * 6. 상담문의 모달 열기/닫기
   *
   * - 열기: .consult-modal-open (data-target="#modalId" 선택 가능)
   * - 닫기: .consult-modal-close, .consult-modal-overlay
   * - 모달: .consult-modal.is-open
   * -------------------------------------------------------------------------- */
  G5Template.initConsultModal = function () {
    var modals = G5Template.qsa('.consult-modal');
    if (!modals.length) {
      return;
    }

    var openModal = function (modal) {
      if (!modal) {
        return;
      }
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      G5Template.lockBodyScroll(true);
    };

    var closeModal = function (modal) {
      if (!modal) {
        return;
      }
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      G5Template.lockBodyScroll(false);
    };

    var closeAll = function () {
      modals.forEach(function (modal) {
        closeModal(modal);
      });
    };

    modals.forEach(function (modal) {
      if (!modal.hasAttribute('aria-hidden')) {
        modal.setAttribute('aria-hidden', 'true');
      }

      var overlay = G5Template.qs('.consult-modal-overlay', modal);
      if (overlay) {
        overlay.addEventListener('click', function () {
          closeModal(modal);
        });
      }

      G5Template.qsa('.consult-modal-close', modal).forEach(function (btn) {
        btn.addEventListener('click', function (event) {
          event.preventDefault();
          closeModal(modal);
        });
      });
    });

    G5Template.qsa('.consult-modal-open').forEach(function (trigger) {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        var targetSelector = trigger.getAttribute('data-target');
        var modal = targetSelector
          ? G5Template.qs(targetSelector)
          : modals[0];
        openModal(modal);
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeAll();
      }
    });
  };

  /* --------------------------------------------------------------------------
   * 6-1. 상담 문의 폼 제출 (inquiry 게시판 + 이메일 알림)
   * -------------------------------------------------------------------------- */
  G5Template.initConsultForm = function () {
    var forms = G5Template.qsa('.cmp-consult-form');
    if (!forms.length) {
      return;
    }

    forms.forEach(function (form) {
      var statusEl = G5Template.qs('.cmp-consult-form__status', form);
      var submitBtn = G5Template.qs('.cmp-consult-form__submit', form);

      var showStatus = function (message, isError) {
        if (!statusEl) {
          return;
        }
        statusEl.textContent = message;
        statusEl.hidden = false;
        statusEl.classList.toggle('is-error', !!isError);
        statusEl.classList.toggle('is-success', !isError);
      };

      form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        if (submitBtn) {
          submitBtn.disabled = true;
        }

        var formData = new FormData(form);

        fetch(form.getAttribute('action') || '', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json'
          }
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (data && data.success) {
              if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
              }
              showStatus(data.message || '문의가 접수되었습니다.', false);
              form.reset();
              var modal = form.closest('.consult-modal');
              if (modal) {
                window.setTimeout(function () {
                  modal.classList.remove('is-open');
                  modal.setAttribute('aria-hidden', 'true');
                  G5Template.lockBodyScroll(false);
                }, 2000);
              }
            } else {
              showStatus((data && data.message) || '접수에 실패했습니다. 다시 시도해 주세요.', true);
            }
          })
          .catch(function () {
            showStatus('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.', true);
          })
          .finally(function () {
            if (submitBtn) {
              submitBtn.disabled = false;
            }
          });
      });
    });
  };

  /* --------------------------------------------------------------------------
   * 7. 상단 이동 버튼
   *
   * 대상: .go-top, #top_btn, .site-dock__btn--top
   * 표시 class: is-visible (스크롤 후)
   * -------------------------------------------------------------------------- */
  G5Template.initGoTop = function () {
    var buttons = G5Template.qsa('.go-top, #top_btn, .site-dock__btn--top');
    if (!buttons.length) {
      return;
    }

    var threshold = G5Template.config.goTopVisibleOffset;

    var updateVisibility = function () {
      var show = G5Template.getScrollY() > threshold;
      buttons.forEach(function (btn) {
        btn.classList.toggle('is-visible', show);
      });
    };

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        if (G5Template.supportsSmoothScroll()) {
          window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
        } else {
          window.scrollTo(0, 0);
        }
      });
    });

    window.addEventListener('scroll', updateVisibility, { passive: true });
    updateVisibility();
  };

  /* --------------------------------------------------------------------------
   * 8. 앵커 링크 부드러운 스크롤
   *
   * - 같은 페이지 내 href="#id" 링크
   * - 빌더·섹션 앵커 (#section-contact 등)
   * - javascript:, mailto:, tel: 제외
   * -------------------------------------------------------------------------- */
  G5Template.initSmoothAnchor = function () {
    var scope = G5Template.qs('.site-main, #siteMain, .page-template, #siteFooter, #siteHeader');
    var root = scope || document;

    G5Template.qsa('a[href^="#"]', root).forEach(function (link) {
      var href = link.getAttribute('href');
      if (!href || href === '#' || href.indexOf('#') !== 0) {
        return;
      }

      link.addEventListener('click', function (event) {
        var id = href.slice(1);
        if (!id) {
          return;
        }
        var target = document.getElementById(id);
        if (!target) {
          return;
        }
        event.preventDefault();
        G5Template.scrollToElement(target);
      });
    });
  };

  /* --------------------------------------------------------------------------
   * 9. 스크롤 등장 애니메이션 (.reveal)
   *
   * - 뷰포트 진입 시 .is-visible 추가
   * - CSS에서 .reveal / .reveal.is-visible 스타일 정의
   * -------------------------------------------------------------------------- */
  G5Template.initReveal = function () {
    var elements = G5Template.qsa('.reveal');
    if (!elements.length) {
      return;
    }

    var onReveal = function (el) {
      el.classList.add('is-visible');
    };

    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(
        function (entries, obs) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting && entry.target instanceof Element) {
              onReveal(entry.target);
              obs.unobserve(entry.target);
            }
          });
        },
        {
          threshold: G5Template.config.revealThreshold,
          rootMargin: '0px 0px -40px 0px'
        }
      );

      elements.forEach(function (el) {
        observer.observe(el);
      });
    } else {
      /* 구형 브라우저: 즉시 표시 */
      elements.forEach(onReveal);
    }
  };

  /* --------------------------------------------------------------------------
   * 팝업 배너 (.cmp-popup)
   * - 닫기: .cmp-popup__close, .cmp-popup__close-btn, .cmp-popup__backdrop
   * - 오늘 하루 보지 않기: localStorage cmp_popup_hide_until
   * -------------------------------------------------------------------------- */
  G5Template.initPopupBanner = function () {
    var popups = G5Template.qsa('.cmp-popup');
    if (!popups.length) {
      return;
    }

    var storageKey = 'cmp_popup_hide_until';

    var shouldHideToday = function () {
      try {
        var until = localStorage.getItem(storageKey);
        if (!until) {
          return false;
        }
        return Date.now() < parseInt(until, 10);
      } catch (e) {
        return false;
      }
    };

    var closePopup = function (popup, rememberToday) {
      if (!popup) {
        return;
      }
      popup.classList.remove('is-open');
      popup.setAttribute('aria-hidden', 'true');
      popup.setAttribute('hidden', 'hidden');
      if (rememberToday) {
        try {
          var end = new Date();
          end.setHours(23, 59, 59, 999);
          localStorage.setItem(storageKey, String(end.getTime()));
        } catch (e) { /* ignore */ }
      }
    };

    var openPopup = function (popup) {
      if (!popup || shouldHideToday()) {
        return;
      }
      popup.removeAttribute('hidden');
      popup.classList.add('is-open');
      popup.setAttribute('aria-hidden', 'false');
    };

    popups.forEach(function (popup) {
      var backdrop = G5Template.qs('.cmp-popup__backdrop', popup);
      var closeBtns = G5Template.qsa('.cmp-popup__close, .cmp-popup__close-btn', popup);
      var todayChk = G5Template.qs('#cmpPopupToday', popup);

      closeBtns.forEach(function (btn) {
        btn.addEventListener('click', function (event) {
          event.preventDefault();
          var remember = todayChk && todayChk.checked;
          closePopup(popup, remember);
        });
      });

      if (backdrop) {
        backdrop.addEventListener('click', function () {
          closePopup(popup, todayChk && todayChk.checked);
        });
      }

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && popup.classList.contains('is-open')) {
          closePopup(popup, false);
        }
      });
    });

    /* 메인에서만 자동 표시 (선택 — #cmpPopupBanner 존재 시) */
    var auto = document.getElementById('cmpPopupBanner');
    if (auto && document.documentElement.classList.contains('page-index')) {
      window.setTimeout(function () {
        openPopup(auto);
      }, 800);
    }
  };

  /* --------------------------------------------------------------------------
   * Scroll Snap (선택)
   * -------------------------------------------------------------------------- */
  G5Template.initScrollSnap = function () {
    var main = document.getElementById('siteMain');
    if (!main) {
      return;
    }
    if (G5Template.config.scrollSnapEnabled) {
      main.classList.add('snap-enabled');
    } else {
      main.classList.remove('snap-enabled');
    }
  };

  /* --------------------------------------------------------------------------
   * Master init
   * -------------------------------------------------------------------------- */
  G5Template.init = function () {
    if (G5Template.config.debug) {
      console.log('[G5Template] init');
    }

    G5Template.initMobileMenu();
    G5Template.initHeaderScroll();
    G5Template.initFaqAccordion();
    G5Template.initConsultModal();
    G5Template.initConsultForm();
    G5Template.initPopupBanner();
    G5Template.initGoTop();
    G5Template.initSmoothAnchor();
    G5Template.initReveal();
    G5Template.initScrollSnap();
  };

  G5Template.ready(function () {
    G5Template.init();
  });

  window.G5Template = G5Template;
})(window, document);
