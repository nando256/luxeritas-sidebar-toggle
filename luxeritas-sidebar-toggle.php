<?php
/*
Plugin Name: Luxeritas Sidebar Toggle
Plugin URI: https://donguri3.net/
Description: Luxeritasテーマ専用。右下に「サイドバー非表示」トグルボタンを追加し、メイン領域を100%に拡大します。
Version: 1.0.0
Author: 納戸工房
Author URI: https://donguri3.net/
*/

defined( 'ABSPATH' ) || exit;

// Luxeritasテーマが有効な場合のみスクリプト・スタイルを読み込む
add_action( 'wp_enqueue_scripts', function() {
    $theme = wp_get_theme();
    if ( $theme->get_template() !== 'luxeritas' ) {
        return;
    }

    // CSSの読み込み
    wp_enqueue_style(
        'lux-sidebar-toggle-style',
        plugin_dir_url( __FILE__ ) . 'style.css',
        array(),
        '1.0.0'
    );

    // JavaScriptの読み込み（フッター出力）
    wp_enqueue_script(
        'lux-sidebar-toggle-script',
        plugin_dir_url( __FILE__ ) . 'sidebar-toggle.js',
        array(),
        '1.0.0',
        true
    );
});
