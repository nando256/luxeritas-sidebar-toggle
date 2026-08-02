# Luxeritas Sidebar Toggle

WordPressテーマ「Luxeritas（ルクセリタス）」専用の独自機能拡張プラグインです。

右下に固定表示されるトグルボタンをクリックすることで、サイドバーを非表示にし、メインコンテンツ領域を100%に拡大表示します。

---

## 主な機能

- **サイドバー一括切替**: クリック一つで `#side` を非表示にし、`#main` 領域の最大幅固定（866px等）を解除して100%表示に拡大。
- **Luxeritas 管理画面への完全統合**:
  - 左メニュー「Luxeritas」の配下に「サイドバー非表示」サブメニューを追加。
  - Luxeritas の本体設定画面（`admin.php?page=luxe`）のタブバー（`SEO`, `OGP` ... `バージョン`）の末尾に「サイドバー非表示」タブを連動表示。
- **柔軟なカスタマイズ性**:
  - **ボタンテキスト**: 「サイドバー非表示」/「サイドバー表示」の文言変更。
  - **表示位置・重なり順**: `bottom` (px)、`right` (px)、`z-index`。
  - **配色・不透明度**: 通常時/ホバー時の背景色・文字色（カラーピッカー対応）および不透明度（%）。
  - **スクロール連動表示**: `#page-top` ボタンと同様に「スクロール時のみ表示」させるスクロール制御。
  - **状態保持・初期状態**: ページ遷移時に開閉状態を `localStorage` で引き継ぐかどうか、訪問時のデフォルト初期状態を設定可能。
  - **レスポンシブ制限**: 「全端末」「PCのみ」「モバイルのみ」の表示制限およびブレイクポイント設定。
- **タッチデバイス最適化**:
  - ボタンクリック直後のフォーカス自動解除 (`blur`)。
  - `@media (hover: hover)` によるモバイル・タッチ環境での `:hover` 固着防止。

---

## ディレクトリ・ファイル構成

```text
luxeritas-sidebar-toggle/
  ├── luxeritas-sidebar-toggle.php  # プラグインメインファイル (Settings API / Menu / Hook)
  ├── style.css                      # スタイルシート (レイアウト解除 / ベースボタン装飾)
  ├── sidebar-toggle.js               # フロントエンド制御スクリプト (DOM生成 / 状態管理)
  ├── LICENSE                        # ライセンスファイル (MIT License)
  └── README.md                      # リポジトリ説明ドキュメント
```

---

## インストール・使い方

1. 本リポジトリの [Releases ページ](https://github.com/nando256/luxeritas-sidebar-toggle/releases) から最新の `luxeritas-sidebar-toggle.zip` をダウンロードします。
2. WordPress 管理画面の「プラグイン」>「新規プラグインを追加」>「プラグインのアップロード」から、ダウンロードした `luxeritas-sidebar-toggle.zip` を選択してインストール・有効化します。
3. 有効化後、管理画面の **「Luxeritas」>「サイドバー非表示」** またはプラグイン一覧の「設定」リンクから各種設定を行えます。

---

## ライセンス

[MIT License](LICENSE)
