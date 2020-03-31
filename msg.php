<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('「掲示板ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

//===============================
// 画面処理
//~==============================
$p_id = '';
$viewData = '';
$productInfo = '';
$reviewUserId = '';
$reviewUserInfo = '';
$countFavorite = '';
$averageRating = '';

//===============================
// 画面表示用データ取得
//===============================
// GETパラメータ取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBからメッセージデータを取得
$viewData = getMsgsAndBord($p_id);
debug('取得したDBメッセージデータ：'.print_r($viewData, true));
// 登録情報を取得
$productInfo = getProductOne($p_id);
debug('取得したDBデータ：'.print_r($productInfo, true));
// 映画情報が取れたかチェック
if(empty($productInfo)){
  error_log('エラー発生:映画情報が取得できませんでした。');
  header("Location:mypage.php");
}
// DBからお気に入りの総数を取得
$countFavorite = getFavorite($p_id);
debug('お気に入りの総数：'.print_r($countFavorite, true));
// DBから平均評価を取得
$number = getRatingAverage($p_id);
debug('平均評価:'.print_r($number, true));
// 小数点第一位までに変換
$averageRating = round($number[0], 1);
debug('平均評価：'.print_r($averageRating, true));

// 未ログインユーザーによりパラメータ操作があった場合トップページへ遷移する
if(empty($_SESSION['user_id'])){
  error_log('エラー発生:未ログインユーザーです');
  debug('$_SESSIONの中身:'.print_r($_SESSION, true));
  header("Location:index.php"); //トップページへ遷移
  exit();
}



debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '掲示板';
require('head.php');
?>

  <body class="page-msg page-1colum">


  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <p id="js-show-msg" style="display:none;" class="msg-slide">
   <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- Main -->
    <section id="main">

      <div class="bord-information-container">

          <!-- 画像 -->
            <div class="bord-img">
              <img src="<?php echo showImg(sanitize($productInfo['pic'])); ?>" alt="メイン画像： <?php echo sanitize($productInfo['name']); ?>">
            </div>

          <div class="bord-txt-container">

              <!-- タイトル -->
              <div class="bord-title">
                  <span class="badge"><?php echo sanitize($productInfo['category']); ?></span>
                  <span class="bord-name"><?php echo sanitize($productInfo['name']); ?></span>
              </div>

              <!-- 平均評価 -->
              <div class="average-star">
                <span class="txt">平均評価</span><br>
                <span class="a-star">★</span>
                <span class="a-star-view"><?php echo sanitize($averageRating); ?></span>
              </div>

              <!-- お気に入りの総数 -->
              <div class="total-favorite">
                <span class="txt">お気に入り総数</span><br>
                <i class="fa fa-heart t-favorite"></i>
                <span class="t-favorite-view"><?php echo sanitize($countFavorite); ?></span>
              </div>

          </div>

      </div>

      <!-- レビューコメント -->
      <div class="area-bord" id="js-scroll-bottom">
        <?php 
          if (!empty($viewData)) {
              foreach ($viewData as $key => $val) {
                   // viewDataからユーザーID情報を取得
                    $reviewUserId = $viewData[$key]['user_id'];
                    debug('$reviewUserIdの中身:'.$reviewUserId);
                    $reviewUserInfo = getUser($reviewUserId);
                    debug('ユーザー情報：'.print_r($reviewUserInfo, true));
                    
                  if (!empty($val['user_id']) == $reviewUserId){
                      ?>
              
                  <div class="msg-cnt msg-left">
                    <div class="avatar">
                      <img src="<?php echo sanitize(showImg($reviewUserInfo['pic'])); ?>" alt="" class="avatar"><br>
                    </div>
                    <p class="msg-inrTxt">
                      <span class="triangle"></span>
                      <?php echo sanitize($val['message']); ?>
                    </p>
                    <div class="txt-info-container">
                      <!-- ニックネーム -->
                      <div class="txt-name" style="float: left;  font-size: 14px; margin-right: 50px;"><?php echo sanitize($reviewUserInfo['username']); ?></div>
                      <!-- 書き込み時間 -->
                      <div class="txt-time" style="float: right;  font-size: 14px; margin-left: 30%;"><?php echo sanitize($val['send_date']); ?></div>
                       <!-- 投稿者の評価情報 -->
                       <div class="txt-rating" style="float: right; font-size: 14px; margin-right: 50px;"><span style="color: #ffcc00;">★</span>×<?php echo getRatingBord($val['user_id'],$p_id); ?></div>
                    </div>
                  </div>
                  
        <?php
                  }
              }
          }else{
              ?>
            <p style="text-align: center;line-height:20;">投稿はまだありません</p>
        <?php
          }
        ?>



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

      </div>

    </section>

    <script src="js/vendor/jquery-3.4.1.min.js"></script>

    <script>
      $(function(){
        // scrollHeightはスクロールビューの高さを取得するもの
        $('#js-scroll-bottom').animate({scrollTop: $('#js-scroll-bottom')[0].scrollHeight}, 'fast');
      });
    </script>

  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>