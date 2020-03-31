<?php
// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「');
debug('「詳細ページ');
debug('「「「「「「「「「「「「「「「「');
debugLogstart();

//=============================
// 画面処理
//=============================

// 画面表示用データ取得
//=============================
// 映画IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから詳細データを取得
$viewData = getProductOne($p_id);
// パラメータに不正な値が入っているかチェック
if(empty($viewData)){
    error_log('エラー発生：指定ページに不正な値が入りました');
    header("Location:productList.php"); //トップページへ
    exit();
}
debug('取得したDBデータ：'.print_r($viewData,true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '詳細画面';
require('head.php');
?>

  <body class="page-productDetail page-1colum">
  
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

  <!-- Main -->
  <section id="main">

      <div class="product-img-container">
          <!-- 画像 -->
          <div class="img-main">
              <img src="<?php echo showImg(sanitize($viewData['pic'])); ?>" alt="メイン画像： <?php echo sanitize($viewData['name']); ?>" id="js-switch-img-main">
          </div>
    
     <div class="product-text">
        <div class="title">
            <span class="badge"><?php echo sanitize($viewData['category']); ?></span>
            <?php echo sanitize($viewData['name']); ?>
        <!--  お気に入り  -->
        <i class="fa fa-heart icn-favorite js-click-favorite <?php if(isFavorite($_SESSION['user_id'], $viewData['id'])){ echo 'active'; } ?>" aria-hidden="true" data-productid="<?php echo sanitize($viewData['id']); ?>" ></i>
        </div>

        <!-- あらすじ -->
        <div class="product-detail">
            <p><?php echo sanitize($viewData['comment']); ?></p>
        </div>

        <!-- 編集ボタン -->
        <div class="regist-btn">
            <a href="registProduct.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$p_id : '?p_id='.$p_id; ?>">編集する</a>
        </div>
     </div>


  </section>

  </div>


    <div class="product-btn">

        <!-- 一覧画面へ遷移 -->
        <div class="item-left">
            <a href="productList.php<?php echo appendGetParam(array('p_id')); ?>">&lt; 一覧に戻る</a>
        </div>
        <!-- レビュー投稿画面へ遷移 -->
        <div class="item-senter">
            <a href="reviewPost.php<?php echo '?p_id='.sanitize($p_id); ?>">&phi; レビューを投稿する</a>
        </div>
        <!-- レビュー投稿掲示板へ遷移する -->
        <div class="item-right">
            <a href="msg.php<?php echo '?p_id='.sanitize($p_id); ?>">掲示板へ &gt;</a>
        </div>

    </div>

  <!-- footer  -->
  <?php
  require('footer.php');
  ?>