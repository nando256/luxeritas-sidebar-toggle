window.addEventListener('load', function() {
    // 二重生成防止
    if (document.querySelector('.sidebar-toggle-btn')) return;

    // トグルボタンの動的生成
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'sidebar-toggle-btn';

    // 初期状態の復元 (localStorage)
    const isClosed = localStorage.getItem('nhk_sidebar_closed') === 'true';
    if (isClosed) {
        document.body.classList.add('is-sidebar-closed');
        toggleBtn.textContent = 'サイドバー表示';
    } else {
        toggleBtn.textContent = 'サイドバー非表示';
    }

    // #wp-footer の配下に安全挿入
    const footerEl = document.getElementById('wp-footer') || document.body;
    footerEl.appendChild(toggleBtn);

    // クリックイベントの設定
    toggleBtn.addEventListener('click', function() {
        document.body.classList.toggle('is-sidebar-closed');
        const closedNow = document.body.classList.contains('is-sidebar-closed');
        toggleBtn.textContent = closedNow ? 'サイドバー表示' : 'サイドバー非表示';
        localStorage.setItem('nhk_sidebar_closed', closedNow ? 'true' : 'false');

        // レイアウト補正用イベント発火（目次等の位置ズレ防止）
        window.dispatchEvent(new Event('resize'));
    });
});
