window.addEventListener('load', function() {
    // 二重生成防止
    if (document.querySelector('.sidebar-toggle-btn')) return;

    // 設定パラメータの取得（フォールバック付き）
    const params = window.luxSidebarToggleParams || {
        textHideSidebar: 'サイドバー非表示',
        textShowSidebar: 'サイドバー表示',
        targetSelector: '#wp-footer',
        keepState: '1',
        defaultState: 'open',
        scrollDisplay: 'always',
        scrollThreshold: 0,
        opacity: 0.5
    };


    const keepState = params.keepState === '1';
    const defaultState = params.defaultState || 'open';
    const storageKey = 'nhk_sidebar_closed';

    // 初期開閉状態の判定
    let isClosed = false;
    if (keepState) {
        const savedState = localStorage.getItem(storageKey);
        if (savedState !== null) {
            isClosed = savedState === 'true';
        } else {
            isClosed = defaultState === 'closed';
        }
    } else {
        // 状態保持が無効な場合は保存データを消去し、デフォルト状態を適用
        localStorage.removeItem(storageKey);
        isClosed = defaultState === 'closed';
    }

    // トグルボタンの動的生成
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'sidebar-toggle-btn';

    if (isClosed) {
        document.body.classList.add('is-sidebar-closed');
        toggleBtn.textContent = params.textShowSidebar;
    } else {
        document.body.classList.remove('is-sidebar-closed');
        toggleBtn.textContent = params.textHideSidebar;
    }

    // 挿入ターゲット要素の決定と安全挿入
    let targetEl = null;
    if (params.targetSelector) {
        targetEl = document.querySelector(params.targetSelector);
    }
    const footerEl = targetEl || document.getElementById('wp-footer') || document.body;
    footerEl.appendChild(toggleBtn);

    // スクロール連動表示の処理 (#page-top ボタンと同様の挙動)
    function updateScrollVisibility() {
        if (params.scrollDisplay === 'scroll_only') {
            const threshold = (params.scrollThreshold !== undefined && params.scrollThreshold !== null) ? parseInt(params.scrollThreshold, 10) : 0;
            if (window.scrollY > threshold) {
                toggleBtn.style.opacity = params.opacity;
                toggleBtn.style.visibility = 'visible';
            } else {
                toggleBtn.style.opacity = '0';
                toggleBtn.style.visibility = 'hidden';
            }
        }
    }

    if (params.scrollDisplay === 'scroll_only') {
        window.addEventListener('scroll', updateScrollVisibility);
        updateScrollVisibility();
    }

    // クリックイベントの設定
    toggleBtn.addEventListener('click', function() {
        document.body.classList.toggle('is-sidebar-closed');
        const closedNow = document.body.classList.contains('is-sidebar-closed');
        toggleBtn.textContent = closedNow ? params.textShowSidebar : params.textHideSidebar;

        if (keepState) {
            localStorage.setItem(storageKey, closedNow ? 'true' : 'false');
        }

        // クリック後のフォーカス残りを解除（半透明状態の復元）
        toggleBtn.blur();

        // レイアウト補正用イベント発火（目次等の位置ズレ防止）
        window.dispatchEvent(new Event('resize'));
    });
});
