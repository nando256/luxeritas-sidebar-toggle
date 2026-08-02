<?php
/*
Plugin Name: Luxeritas Sidebar Toggle
Plugin URI: https://donguri3.net/
Description: Luxeritasテーマ専用。右下に「サイドバー非表示」トグルボタンを追加し、メイン領域を100%に拡大します。設定画面よりボタン位置・配色・透明度・状態保持動作等をカスタマイズ可能です。
Version: 1.2.0
Author: 納戸工房
Author URI: https://donguri3.net/
*/

defined( 'ABSPATH' ) || exit;

/**
 * デフォルト設定値を取得
 */
function lux_sidebar_toggle_get_options() {
    $defaults = array(
        'text_hide_sidebar' => 'サイドバー非表示',
        'text_show_sidebar' => 'サイドバー表示',
        'target_selector'   => '#wp-footer',
        'position_bottom'   => 75,
        'position_right'    => 14,
        'z_index'           => 99,
        'bg_color'          => '#333333',
        'text_color'        => '#ffffff',
        'opacity'           => 75,
        'hover_bg_color'    => '#333333',
        'hover_text_color'  => '#ffffff',
        'hover_opacity'     => 95,
        'border_radius'     => 4,
        'keep_state'        => '1',
        'default_state'     => 'open',
        'device_display'    => 'all',
        'mobile_breakpoint' => 768,
    );
    $options = get_option( 'lux_sidebar_toggle_settings', array() );
    return wp_parse_args( $options, $defaults );
}

/**
 * フロントエンド用動的CSSの生成
 */
function lux_sidebar_toggle_generate_inline_css( $options ) {
    $bg_color      = ! empty( $options['bg_color'] ) ? $options['bg_color'] : '#333333';
    $text_color    = ! empty( $options['text_color'] ) ? $options['text_color'] : '#ffffff';
    $opacity       = floatval( $options['opacity'] ) / 100;
    $hover_bg      = ! empty( $options['hover_bg_color'] ) ? $options['hover_bg_color'] : '#333333';
    $hover_text    = ! empty( $options['hover_text_color'] ) ? $options['hover_text_color'] : '#ffffff';
    $hover_opacity = floatval( $options['hover_opacity'] ) / 100;
    $bottom        = intval( $options['position_bottom'] );
    $right         = intval( $options['position_right'] );
    $z_index       = intval( $options['z_index'] );
    $radius        = intval( $options['border_radius'] );
    $device        = $options['device_display'];
    $breakpoint    = intval( $options['mobile_breakpoint'] );

    list( $r, $g, $b )   = sscanf( $bg_color, '#%02x%02x%02x' );
    $bg_rgba             = sprintf( 'rgba(%d, %d, %d, %.2f)', $r, $g, $b, $opacity );

    list( $hr, $hg, $hb ) = sscanf( $hover_bg, '#%02x%02x%02x' );
    $hover_bg_rgba        = sprintf( 'rgba(%d, %d, %d, %.2f)', $hr, $hg, $hb, $hover_opacity );

    $css = "
.sidebar-toggle-btn {
    position: fixed !important;
    bottom: {$bottom}px !important;
    right: {$right}px !important;
    z-index: {$z_index} !important;
    background-color: {$bg_rgba} !important;
    color: {$text_color} !important;
    border-radius: {$radius}px !important;
}
.sidebar-toggle-btn:hover {
    background-color: {$hover_bg_rgba} !important;
    color: {$hover_text} !important;
}
";

    if ( $device === 'pc_only' ) {
        $max_width = $breakpoint - 1;
        $css .= "
@media screen and (max-width: {$max_width}px) {
    .sidebar-toggle-btn {
        display: none !important;
    }
}
";
    } elseif ( $device === 'mobile_only' ) {
        $css .= "
@media screen and (min-width: {$breakpoint}px) {
    .sidebar-toggle-btn {
        display: none !important;
    }
}
";
    }

    return $css;
}

// フロントエンド用スクリプト・スタイルの追加
add_action( 'wp_enqueue_scripts', function() {
    $theme = wp_get_theme();
    if ( $theme->get_template() !== 'luxeritas' ) {
        return;
    }

    $options = lux_sidebar_toggle_get_options();

    wp_enqueue_style(
        'lux-sidebar-toggle-style',
        plugin_dir_url( __FILE__ ) . 'style.css',
        array(),
        '1.2.0'
    );
    wp_add_inline_style( 'lux-sidebar-toggle-style', lux_sidebar_toggle_generate_inline_css( $options ) );

    wp_enqueue_script(
        'lux-sidebar-toggle-script',
        plugin_dir_url( __FILE__ ) . 'sidebar-toggle.js',
        array(),
        '1.2.0',
        true
    );

    wp_localize_script(
        'lux-sidebar-toggle-script',
        'luxSidebarToggleParams',
        array(
            'textHideSidebar' => $options['text_hide_sidebar'],
            'textShowSidebar' => $options['text_show_sidebar'],
            'targetSelector' => $options['target_selector'],
            'keepState'      => $options['keep_state'],
            'defaultState'   => $options['default_state'],
        )
    );
});

// 管理画面メニューの追加（Luxeritasのサブメニューとして登録）
add_action( 'admin_menu', function() {
    $theme = wp_get_theme();
    if ( $theme->get_template() !== 'luxeritas' ) {
        add_options_page(
            'Luxeritas Sidebar Toggle 設定',
            'Luxeritas Sidebar Toggle',
            'manage_options',
            'luxeritas-sidebar-toggle',
            'lux_sidebar_toggle_render_admin_page'
        );
        return;
    }

    add_submenu_page(
        'luxe',
        'サイドバー非表示設定',
        'サイドバー非表示',
        'manage_options',
        'luxeritas-sidebar-toggle',
        'lux_sidebar_toggle_render_admin_page'
    );
}, 99 );

// Luxeritas 設定ページ（admin.php?page=luxe）のタブバーに「サイドバー非表示」タブをJSで挿入
add_action( 'admin_footer', function() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'toplevel_page_luxe' ) {
        return;
    }
    $target_url = esc_url( admin_url( 'admin.php?page=luxeritas-sidebar-toggle' ) );
    ?>
    <script>
    jQuery(document).ready(function($) {
        var $wrapper = $('.nav-tab-wrapper');
        if ($wrapper.length && !$wrapper.find('a[href*="luxeritas-sidebar-toggle"]').length) {
            $wrapper.append('<a href="<?php echo $target_url; ?>" class="nav-tab">サイドバー非表示</a>');
        }
    });
    </script>
    <?php
} );

// プラグイン一覧ページ（plugins.php）に「設定」アクションリンクを追加
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=luxeritas-sidebar-toggle' ) ) . '">設定</a>';
    array_unshift( $links, $settings_link );
    return $links;
} );

// 管理画面用アセットの読み込み (カラーピッカー等)
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( strpos( $hook, 'luxeritas-sidebar-toggle' ) === false ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
});

// Settings API の登録
add_action( 'admin_init', function() {
    register_setting(
        'lux_sidebar_toggle_settings_group',
        'lux_sidebar_toggle_settings',
        array(
            'sanitize_callback' => 'lux_sidebar_toggle_sanitize_options',
        )
    );

    add_settings_section( 'lux_sec_text', 'ボタン表示テキスト設定', null, 'luxeritas-sidebar-toggle' );
    add_settings_field( 'text_hide_sidebar', '「非表示」時のボタン文字', 'lux_field_text_hide_sidebar_cb', 'luxeritas-sidebar-toggle', 'lux_sec_text' );
    add_settings_field( 'text_show_sidebar', '「表示」時のボタン文字', 'lux_field_text_show_sidebar_cb', 'luxeritas-sidebar-toggle', 'lux_sec_text' );

    add_settings_section( 'lux_sec_position', 'ボタンの配置・表示設定', null, 'luxeritas-sidebar-toggle' );
    add_settings_field( 'target_selector', '挿入ターゲット要素 (CSSセレクタ)', 'lux_field_target_selector_cb', 'luxeritas-sidebar-toggle', 'lux_sec_position' );
    add_settings_field( 'position_bottom', '下からの距離 (bottom px)', 'lux_field_position_bottom_cb', 'luxeritas-sidebar-toggle', 'lux_sec_position' );
    add_settings_field( 'position_right', '右からの距離 (right px)', 'lux_field_position_right_cb', 'luxeritas-sidebar-toggle', 'lux_sec_position' );
    add_settings_field( 'z_index', '重なり順序 (z-index)', 'lux_field_z_index_cb', 'luxeritas-sidebar-toggle', 'lux_sec_position' );

    add_settings_section( 'lux_sec_design', 'デザイン・配色・透明度設定', null, 'luxeritas-sidebar-toggle' );
    add_settings_field( 'bg_color', '通常時 背景色・透明度', 'lux_field_bg_color_cb', 'luxeritas-sidebar-toggle', 'lux_sec_design' );
    add_settings_field( 'text_color', '通常時 文字色', 'lux_field_text_color_cb', 'luxeritas-sidebar-toggle', 'lux_sec_design' );
    add_settings_field( 'hover_bg_color', 'ホバー時 背景色・透明度', 'lux_field_hover_bg_color_cb', 'luxeritas-sidebar-toggle', 'lux_sec_design' );
    add_settings_field( 'hover_text_color', 'ホバー時 文字色', 'lux_field_hover_text_color_cb', 'luxeritas-sidebar-toggle', 'lux_sec_design' );
    add_settings_field( 'border_radius', '角丸 (border-radius px)', 'lux_field_border_radius_cb', 'luxeritas-sidebar-toggle', 'lux_sec_design' );

    add_settings_section( 'lux_sec_behavior', '動作・ページ遷移設定', null, 'luxeritas-sidebar-toggle' );
    add_settings_field( 'keep_state', 'ページ遷移時の状態保持', 'lux_field_keep_state_cb', 'luxeritas-sidebar-toggle', 'lux_sec_behavior' );
    add_settings_field( 'default_state', '初期表示状態', 'lux_field_default_state_cb', 'luxeritas-sidebar-toggle', 'lux_sec_behavior' );

    add_settings_section( 'lux_sec_responsive', 'レスポンシブ・表示端末設定', null, 'luxeritas-sidebar-toggle' );
    add_settings_field( 'device_display', '表示対象デバイス', 'lux_field_device_display_cb', 'luxeritas-sidebar-toggle', 'lux_sec_responsive' );
    add_settings_field( 'mobile_breakpoint', 'モバイル判定ブレイクポイント (px)', 'lux_field_mobile_breakpoint_cb', 'luxeritas-sidebar-toggle', 'lux_sec_responsive' );
});

function lux_sidebar_toggle_sanitize_options( $input ) {
    $output   = array();
    $defaults = lux_sidebar_toggle_get_options();

    $output['text_hide_sidebar'] = isset( $input['text_hide_sidebar'] ) ? sanitize_text_field( $input['text_hide_sidebar'] ) : $defaults['text_hide_sidebar'];
    $output['text_show_sidebar'] = isset( $input['text_show_sidebar'] ) ? sanitize_text_field( $input['text_show_sidebar'] ) : $defaults['text_show_sidebar'];
    $output['target_selector']   = isset( $input['target_selector'] ) ? sanitize_text_field( $input['target_selector'] ) : $defaults['target_selector'];
    $output['position_bottom']   = isset( $input['position_bottom'] ) ? intval( $input['position_bottom'] ) : $defaults['position_bottom'];
    $output['position_right']    = isset( $input['position_right'] ) ? intval( $input['position_right'] ) : $defaults['position_right'];
    $output['z_index']           = isset( $input['z_index'] ) ? intval( $input['z_index'] ) : $defaults['z_index'];

    $output['bg_color']   = ( isset( $input['bg_color'] ) && preg_match( '/^#[a-fA-F0-9]{6}$/', $input['bg_color'] ) ) ? $input['bg_color'] : $defaults['bg_color'];
    $output['text_color'] = ( isset( $input['text_color'] ) && preg_match( '/^#[a-fA-F0-9]{6}$/', $input['text_color'] ) ) ? $input['text_color'] : $defaults['text_color'];
    $output['opacity']    = isset( $input['opacity'] ) ? min( 100, max( 0, intval( $input['opacity'] ) ) ) : $defaults['opacity'];

    $output['hover_bg_color']   = ( isset( $input['hover_bg_color'] ) && preg_match( '/^#[a-fA-F0-9]{6}$/', $input['hover_bg_color'] ) ) ? $input['hover_bg_color'] : $defaults['hover_bg_color'];
    $output['hover_text_color'] = ( isset( $input['hover_text_color'] ) && preg_match( '/^#[a-fA-F0-9]{6}$/', $input['hover_text_color'] ) ) ? $input['hover_text_color'] : $defaults['hover_text_color'];
    $output['hover_opacity']    = isset( $input['hover_opacity'] ) ? min( 100, max( 0, intval( $input['hover_opacity'] ) ) ) : $defaults['hover_opacity'];

    $output['border_radius']     = isset( $input['border_radius'] ) ? max( 0, intval( $input['border_radius'] ) ) : $defaults['border_radius'];
    $output['keep_state']        = ( isset( $input['keep_state'] ) && in_array( $input['keep_state'], array( '1', '0' ), true ) ) ? $input['keep_state'] : $defaults['keep_state'];
    $output['default_state']     = ( isset( $input['default_state'] ) && in_array( $input['default_state'], array( 'open', 'closed' ), true ) ) ? $input['default_state'] : $defaults['default_state'];
    $output['device_display']    = ( isset( $input['device_display'] ) && in_array( $input['device_display'], array( 'all', 'pc_only', 'mobile_only' ), true ) ) ? $input['device_display'] : $defaults['device_display'];
    $output['mobile_breakpoint'] = isset( $input['mobile_breakpoint'] ) ? max( 300, intval( $input['mobile_breakpoint'] ) ) : $defaults['mobile_breakpoint'];

    return $output;
}

function lux_field_text_hide_sidebar_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="text" name="lux_sidebar_toggle_settings[text_hide_sidebar]" value="' . esc_attr( $options['text_hide_sidebar'] ) . '" class="regular-text">';
    echo '<p class="description">サイドバーが表示されているときにボタンに表示するテキストです。</p>';
}

function lux_field_text_show_sidebar_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="text" name="lux_sidebar_toggle_settings[text_show_sidebar]" value="' . esc_attr( $options['text_show_sidebar'] ) . '" class="regular-text">';
    echo '<p class="description">サイドバーが非表示のときにボタンに表示するテキストです。</p>';
}

function lux_field_target_selector_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="text" name="lux_sidebar_toggle_settings[target_selector]" value="' . esc_attr( $options['target_selector'] ) . '" class="regular-text">';
    echo '<p class="description">トグルボタンを挿入するDOM要素のCSSセレクタ（デフォルト: <code>#wp-footer</code>）。存在しない場合は <code>body</code> に配置されます。</p>';
}

function lux_field_position_bottom_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="number" name="lux_sidebar_toggle_settings[position_bottom]" value="' . esc_attr( $options['position_bottom'] ) . '" class="small-text"> px';
    echo '<p class="description">画面下部からの距離（デフォルト: 75px）。</p>';
}

function lux_field_position_right_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="number" name="lux_sidebar_toggle_settings[position_right]" value="' . esc_attr( $options['position_right'] ) . '" class="small-text"> px';
    echo '<p class="description">画面右側からの距離（デフォルト: 14px）。</p>';
}

function lux_field_z_index_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="number" name="lux_sidebar_toggle_settings[z_index]" value="' . esc_attr( $options['z_index'] ) . '" class="small-text">';
    echo '<p class="description">他の浮遊ボタン（トップへ戻る等）との重なり順序（デフォルト: 99）。</p>';
}

function lux_field_bg_color_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="text" name="lux_sidebar_toggle_settings[bg_color]" value="' . esc_attr( $options['bg_color'] ) . '" class="lux-color-picker" data-default-color="#333333"> ';
    echo ' 不透明度: <input type="number" name="lux_sidebar_toggle_settings[opacity]" value="' . esc_attr( $options['opacity'] ) . '" min="0" max="100" class="small-text"> %';
    echo '<p class="description">通常時の背景色と不透明度 (0% = 完全透明, 100% = 不透明)。</p>';
}

function lux_field_text_color_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="text" name="lux_sidebar_toggle_settings[text_color]" value="' . esc_attr( $options['text_color'] ) . '" class="lux-color-picker" data-default-color="#ffffff">';
    echo '<p class="description">通常時のボタン文字色。</p>';
}

function lux_field_hover_bg_color_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="text" name="lux_sidebar_toggle_settings[hover_bg_color]" value="' . esc_attr( $options['hover_bg_color'] ) . '" class="lux-color-picker" data-default-color="#333333"> ';
    echo ' 不透明度: <input type="number" name="lux_sidebar_toggle_settings[hover_opacity]" value="' . esc_attr( $options['hover_opacity'] ) . '" min="0" max="100" class="small-text"> %';
    echo '<p class="description">マウスホバー時の背景色と不透明度。</p>';
}

function lux_field_hover_text_color_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="text" name="lux_sidebar_toggle_settings[hover_text_color]" value="' . esc_attr( $options['hover_text_color'] ) . '" class="lux-color-picker" data-default-color="#ffffff">';
    echo '<p class="description">マウスホバー時のボタン文字色。</p>';
}

function lux_field_border_radius_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="number" name="lux_sidebar_toggle_settings[border_radius]" value="' . esc_attr( $options['border_radius'] ) . '" min="0" class="small-text"> px';
    echo '<p class="description">ボタンの角丸サイズ（デフォルト: 4px）。</p>';
}

function lux_field_keep_state_cb() {
    $options = lux_sidebar_toggle_get_options();
    $keep    = $options['keep_state'];
    echo '<label><input type="radio" name="lux_sidebar_toggle_settings[keep_state]" value="1" ' . checked( $keep, '1', false ) . '> ページ遷移しても状態を保持する (localStorageを使用)</label><br>';
    echo '<label><input type="radio" name="lux_sidebar_toggle_settings[keep_state]" value="0" ' . checked( $keep, '0', false ) . '> ページ遷移時は毎回初期状態に戻す (状態を保持しない)</label>';
    echo '<p class="description">訪問者がページを移動した際に、サイドバーの開閉状態を引き継ぐかどうかを設定します。</p>';
}

function lux_field_default_state_cb() {
    $options = lux_sidebar_toggle_get_options();
    $state   = $options['default_state'];
    echo '<label><input type="radio" name="lux_sidebar_toggle_settings[default_state]" value="open" ' . checked( $state, 'open', false ) . '> サイドバーを表示 (デフォルト)</label><br>';
    echo '<label><input type="radio" name="lux_sidebar_toggle_settings[default_state]" value="closed" ' . checked( $state, 'closed', false ) . '> サイドバーを最初から非表示にする</label>';
    echo '<p class="description">サイトにアクセスした際、または状態維持が無効な場合の初期表示状態を指定します。</p>';
}

function lux_field_device_display_cb() {
    $options = lux_sidebar_toggle_get_options();
    $device  = $options['device_display'];
    echo '<label><input type="radio" name="lux_sidebar_toggle_settings[device_display]" value="all" ' . checked( $device, 'all', false ) . '> すべてのデバイスで表示</label><br>';
    echo '<label><input type="radio" name="lux_sidebar_toggle_settings[device_display]" value="pc_only" ' . checked( $device, 'pc_only', false ) . '> PCのみ表示 (指定画面幅未満は非表示)</label><br>';
    echo '<label><input type="radio" name="lux_sidebar_toggle_settings[device_display]" value="mobile_only" ' . checked( $device, 'mobile_only', false ) . '> モバイルのみ表示 (指定画面幅以上は非表示)</label>';
}

function lux_field_mobile_breakpoint_cb() {
    $options = lux_sidebar_toggle_get_options();
    echo '<input type="number" name="lux_sidebar_toggle_settings[mobile_breakpoint]" value="' . esc_attr( $options['mobile_breakpoint'] ) . '" min="300" class="small-text"> px';
    echo '<p class="description">PC/モバイル切り替えの分岐画面幅（デフォルト: 768px）。</p>';
}

function lux_sidebar_toggle_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $luxe_base = admin_url( 'admin.php?page=luxe' );
    ?>
    <div class="wrap">
        <h1>Luxeritas 独自機能設定</h1>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url( $luxe_base . '&active=seo' ); ?>" class="nav-tab">SEO</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=ogp' ); ?>" class="nav-tab">OGP</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=title' ); ?>" class="nav-tab">タイトル</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=pagination' ); ?>" class="nav-tab">ページネーション</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=amp' ); ?>" class="nav-tab">AMP</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=pwa' ); ?>" class="nav-tab">PWA</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=optimize' ); ?>" class="nav-tab">圧縮・最適化</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=style' ); ?>" class="nav-tab">CSS</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=script' ); ?>" class="nav-tab">Javascript</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=lazyload' ); ?>" class="nav-tab">Lazy Load</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=icon' ); ?>" class="nav-tab">アイコンフォント</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=comment' ); ?>" class="nav-tab">コメント</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=search' ); ?>" class="nav-tab">検索</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=copyright' ); ?>" class="nav-tab">コピーライト</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=others' ); ?>" class="nav-tab">その他</a>
            <a href="<?php echo esc_url( $luxe_base . '&active=version' ); ?>" class="nav-tab">バージョン</a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=luxeritas-sidebar-toggle' ) ); ?>" class="nav-tab nav-tab-active">サイドバー非表示</a>
        </h2>
        <p style="margin-top: 15px;">Luxeritasテーマ用サイドバー切替トグルボタンの表示・配色・動作設定を行えます。</p>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'lux_sidebar_toggle_settings_group' );
            do_settings_sections( 'luxeritas-sidebar-toggle' );
            submit_button( '設定を保存' );
            ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($){
        $('.lux-color-picker').wpColorPicker();
    });
    </script>
    <?php
}
