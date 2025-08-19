/**
 * グローバル定数
 */
const SITE_URL = 'https://yushiishiguro.com';
const CURRENT_URL = window.location.href;
const SITE_NAME = 'YUSHI ISHIGURO PORTFOLIO SITE';
const WP_PATH = 'wp/wp-content/themes/';
const THEMES_PATH = 'swell_child/';

/**
 * コピーライトの要素を取得して、最新年とサイト名を表示する関数
 */
function updateCopyright() {
  const copyright = document.querySelector('.copyright');
  const latestYear = new Date().getFullYear();

  copyright.innerHTML =
    '<a href="' + SITE_URL + '"><span lang="en">©</span> ' + SITE_NAME + ' ' + latestYear + '</a>';
}

/**
 * 制限メッセージを削除して新たに要素を追加する関数
 */
function removeRestrictedMsg() {
  if (CURRENT_URL.includes('private') || CURRENT_URL.includes('logout')) {
    const restrictedMsg = document.getElementById('wpmem_restricted_msg');
    if (restrictedMsg) {
      restrictedMsg.innerHTML =
        'このコンテンツの閲覧は非公開実績閲覧用ユーザーに制限されています。<br>閲覧用ユーザーの方はログインページよりログインをしてください。';
      restrictedMsg.insertAdjacentHTML(
        'afterend',
        '<a href="' + SITE_URL + '/login/"><p>ログインページ</p></a>'
      );
    }
  }
}

/**
 * ログインページのリンクテキスト要素を削除する関数
 */
function removeLinkTextElementsOnLogin() {
  if (
    CURRENT_URL.includes('login') ||
    CURRENT_URL.includes('private') ||
    CURRENT_URL.includes('logout')
  ) {
    const allLinkTextElements = document.querySelectorAll('.link-text');
    const rememberMeName = document.querySelector('input[name="rememberme"]');
    const rememberMeNameFor = document.querySelector('label[for="rememberme"]');
    allLinkTextElements.forEach(function (element) {
      element.remove();
    });
    rememberMeName.remove();
    rememberMeNameFor.remove();
  }
}

/**
 * BaguetteBoxを初期化する関数
 */
function initializeBaguetteBox() {
  baguetteBox.run('.post_content', {
    animation: 'fadeIn',
  });
}

/**
 * アニメーションの有無をチェックし、ロード処理を行う関数
 */
function checkAnimationAndLoad() {
  const animationShownToday = sessionStorage.getItem('animationShownToday');
  const loading = document.getElementById('loading');
  const loadingWrapper = document.getElementById('loading-wrapper');

  /**
   * ローディング要素を非表示にする関数
   */
  function hideLoadingElement() {
    if (loading) {
      loading.classList.add('is-hidden');
      loading.addEventListener('transitionend', function () {
        loading.remove();
      });
    }
  }

  if (!animationShownToday) {
    lottie.loadAnimation({
      container: loadingWrapper,
      renderer: 'svg',
      loop: true,
      autoplay: true,
      path: `${CURRENT_URL + WP_PATH + THEMES_PATH}assets/lottie/loading.json`,
    });

    sessionStorage.setItem('animationShownToday', true);

    setTimeout(hideLoadingElement, 2000);
  } else {
    hideLoadingElement();
  }
}

/**
 * ページ全体の読み込み完了時に実行される関数をまとめる
 */
window.addEventListener('load', function () {
  initializeBaguetteBox();
  checkAnimationAndLoad();
});

/**
 * DOMツリー読み込み完了時に実行される関数をまとめる
 */
document.addEventListener('DOMContentLoaded', function () {
  updateCopyright();
  removeRestrictedMsg();
  removeLinkTextElementsOnLogin();
});
