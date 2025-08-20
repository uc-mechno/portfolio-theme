<?php

/* 子テーマのfunctions.phpは、親テーマのfunctions.phpより先に読み込まれることに注意してください。 */


/**
 * 親テーマのfunctions.phpのあとで読み込みたいコードはこの中に。
 */
// add_filter('after_setup_theme', function(){
// }, 11);

/**
 *
 * テーマのスタイルシートとJavaScriptファイルを読み込むためのアクションフックを追加する。
 *
 * @link https://developer.wordpress.org/reference/functions/add_action/
 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function () {
  // $timestamp を配列に格納する
  $timestamp = [
    'child-style' => date('Ymdgis', filemtime(get_stylesheet_directory() . '/assets/css/child.css')),
    'private-style' => date('Ymdgis', filemtime(get_stylesheet_directory() . '/assets/css/private.css')),
    'style' => date('Ymdgis', filemtime(get_stylesheet_directory() . '/style.css')),
    'child-script' => date('Ymdgis', filemtime(get_stylesheet_directory() . '/assets/js/child.js')),
  ];

  // cssファイルの読み込み
  wp_enqueue_style('googleapis', '//fonts.googleapis.com', [], null);
  wp_enqueue_style('gstatic', '//fonts.gstatic.com', [], null);
  wp_enqueue_style('google-fonts-montserrat', '//fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap', [], null);
  wp_enqueue_style('baguettebox-style', 'https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.11.1/baguetteBox.min.css', [], '1.11.1', false);
  wp_enqueue_style('child_style', get_stylesheet_directory_uri() . '/assets/css/child.css', [], $timestamp['child-style']);
  if (is_user_logged_in()) wp_enqueue_style('private_style', get_stylesheet_directory_uri() . '/assets/css/private.css', [], $timestamp['private-style']);
  wp_enqueue_style('style', get_stylesheet_directory_uri() . '/style.css', [], $timestamp['style']);

  // jsファイルの読み込み
  wp_enqueue_script('baguettebox-js', 'https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.11.1/baguetteBox.min.js', [], '1.11.1', true);
  wp_enqueue_script('lottie-js', 'https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js', [], '5.12.2', true);
  wp_enqueue_script('child_script', get_stylesheet_directory_uri() . '/assets/js/child.js', [], $timestamp['child-script'], true);

  /* その他の読み込みファイルはこの下に記述 */
}, 11);

/**
 * スタイルシートのlinkタグにrel="preconnect"を追加し、gstaticの場合はcrossorigin属性も追加する
 *
 * @param string $tag スタイルシートのlinkタグ
 * @param string $handle スタイルシートのハンドル名
 * @return string $tag スタイルシートのlinkタグ
 */
function replace_link_tag($tag, $handle)
{
  if ($handle === 'googleapis' || $handle === 'gstatic') {
    $tag = str_replace('stylesheet', 'preconnect', $tag);
    if ($handle === 'gstatic') {
      $tag = str_replace(' href=', ' crossorigin href=', $tag);
    }
  }
  return $tag;
}
add_filter('style_loader_tag', 'replace_link_tag', 10, 2);

/**
 * ブロックエディタ用にスタイルを追加
 *
 * @return void
 */
function child_theme_editor_style_init()
{
  // $timestamp を配列に格納する
  $timestamp = [
    'child-editor-style' => date('Ymdgis', filemtime(get_stylesheet_directory() . '/assets/css/child-editor-style.css'))
    // 'child-editor-script' => date('Ymdgis', filemtime(get_theme_file_path('/js/script.js')))
  ];
  wp_enqueue_style('child-editor-style', get_stylesheet_directory_uri() . '/assets/css/child-editor-style.css', [], $timestamp['child-editor-style']);
}
add_action('enqueue_block_editor_assets', 'child_theme_editor_style_init');

/**
 * 画像が自動リサイズされるのを無効化
 */
add_filter('big_image_size_threshold', '__return_false');

/**
 * 管理バーを表示するかどうかを制御する関数
 *
 * @param bool $content - 現在の管理バーの表示状態
 * @return bool 管理者または編集者の場合は表示、それ以外の場合は非表示
 */
function theme_show_admin_bar($content)
{
  // 管理者・編集者の権限グループの場合は表示
  if (current_user_can('administrator') || current_user_can('editor')) {
    return $content;
    // 他の権限グループの場合は非表示
  } else {
    return false;
  }
}
add_filter('show_admin_bar', 'theme_show_admin_bar');

/**
 * 特定のサブスクライバーがホームにリダイレクトされるかどうかをチェックする関数
 *
 * @param int $user_id - ユーザーID
 * @return void
 */
function subscriber_go_to_home($user_id)
{
  $user = get_userdata($user_id);

  // サブスクライバーが投稿を編集する権限を持っていない場合は、ホームにリダイレクトする
  if (!$user->has_cap('edit_posts')) {
    wp_redirect(get_home_url());
    exit();
  }
}
add_action('auth_redirect', 'subscriber_go_to_home');

/**
 * ユーザーのログインリダイレクトを処理する関数
 *
 * @param string $redirect_to - リダイレクト先のURL
 * @param int $user_id - ユーザーID
 * @return string カスタマイズされたリダイレクト先のURL
 * @link https://qiita.com/SearleDemon/items/43d337608352ef95da93
 */

function my_login_redirect($redirect_to, $user_id)
{
  // 管理者権限を持つユーザーの場合
  if (user_can($user_id, 'administrator')) {
    // ログアウトする
    wp_logout();
  } else {
    // 管理者以外の場合は'/private/'にリダイレクト
    return home_url('/private/');
  }
  // デフォルトのリダイレクト先は'/'とする
  return home_url('/');
}
add_filter('wpmem_login_redirect', 'my_login_redirect', 10, 2);

/**
 * ログアウト後のリダイレクト先を指定する関数
 *
 * @param {string} $redirect_to - ログアウト後にリダイレクトする先のURL
 * @return {string} - リダイレクト先のURLを返す
 */
function my_logout_redirect_1($redirect_to)
{
  // URLを直接記述する例　自ドメインであればhome_urlメソッドを使った方がスマートですね
  return home_url();
}
add_filter('wpmem_logout_redirect', 'my_logout_redirect_1');

/**
 * WP-Membersのデフォルトテキストを変更する関数
 *
 * @param array $text - デフォルトテキストの配列
 * @return array 変更されたデフォルトテキストの配列
 */
function theme_wpmem_default_text($text)
{
  $text['login_username'] = 'ユーザー名';
  $text['login_heading'] = 'ログイン';
  return $text;
}
add_filter('wpmem_default_text', 'theme_wpmem_default_text');

/**
 * ユーザー登録フォームの行をラップする関数
 *
 * @param array $args フィルター前の引数
 * @param string $tag タグ名
 * @return array フィルター後の引数
 */
function my_register_form_row_wrapper($args, $tag)
{
  // 行の前後にHTMLタグを追加
  $args = [
    'row_before' => '<div class="form_custom d-flex">',
    'row_after'  => '</div>',
  ];

  return $args;
}
add_filter('wpmem_login_form_args', 'my_register_form_row_wrapper', 10, 2);

/**
 * my_head 関数
 *
 * wp_head アクションに登録されたコールバック関数。
 * ページのヘッダー内に特定の HTML コードを出力する。
 */
function my_head()
{
  if (!is_user_logged_in() || current_user_can('subscriber')) {
    echo <<<HTML
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-KMHRQTT5');</script>
    <!-- End Google Tag Manager -->
    HTML;
  }
}
add_action('wp_head', 'my_head');


/**
 * body_open1 関数
 *
 * wp_body_open アクションに登録されたコールバック関数。
 * ページのボディ要素が開かれる直前に特定の HTML コードを出力する。
 */
function my_body_open1()
{
  if (!is_user_logged_in() || current_user_can('subscriber')) {
    echo <<<HTML
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KMHRQTT5"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    HTML;
  }
}
add_action('wp_body_open', 'my_body_open1');

/**
 * body_open2 関数
 *
 * フロントページの場合にローディング要素を出力する関数
 * ページのボディ要素が開かれる直前に特定の HTML コードを出力する。
 */
function my_body_open2()
{
  if (is_front_page()) {
    echo <<<HTML
    <div id="loading"><div id="loading-wrapper"></div></div>
    HTML;
  }
}
add_action('wp_body_open', 'my_body_open2');

/**
 * リダイレクト機能を実行する関数
 *
 * 現在のURLを確認し、'logout' または 'private' を含む場合にログイン状態を確認してリダイレクトを行う。
 */
function redirectFunc()
{
  $url = $_SERVER['REQUEST_URI'];
  $hostname = 'ここにURL';

  if (strpos($url, 'logout') || strpos($url, 'private')) {
    if (!is_user_logged_in()) {
      wp_safe_redirect(esc_url($hostname . 'login/'), 302);
      exit;
    }
  }
}
add_action('get_header', 'redirectFunc');
