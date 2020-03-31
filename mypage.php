<?php
// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「マイページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

//============================
// 画面処理
//============================
// ログイン認証
require('auth.php');

// 画面表示用データ取得
//============================
$u_id = $_SESSION['user_id'];
// DBから投稿映画取得
$productData = getMyProducts($u_id);
// DBからレビュー投稿データを取得
$bordData = getMyAllReview($u_id);
// DBからお気に入りデータを取得
$favoriteData = getMyFavorite($u_id);

// DBからデータが何も取れていなければ何も表示しないこととする
debug('取得した投稿映画データを取得：'.print_r($productData, true));
debug('取得したレビュー投稿データ：'.print_r($bordData, true));
debug('取得したお気に入りデータ：'.print_r($favoriteData, true));

debug('画面表示終了  <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = 'マイページ';
require('head.php');
?>

  <body class="page-mypage page-2colum page-logined">
    <style>
      #main{
        border: none !important;
        }
    </style>

    <!-- メニュー -->
    <?php
        require('header.php');
    ?>
 <p id="js-show-msg" style="display:none;" class="msg-slide">
      <?php echo getSessionFlash('msg_success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <h1 class="page-title">MYPAGE</h1>

      <!-- main -->
      <section id="main">
        <section class="list panel-list">
          <h2 class="title" style="margin-bottom: 15px;">投稿映画一覧</h2>
          <?php
          if(!empty($productData)):
            foreach($productData as $key => $val):
          ?>
            <a href="registProduct.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
              <div class="panel-head">
                <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
              </div>
              <div class="panel-body">
                <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
              </div>
            </a>
          <?php
            endforeach;
          endif;
          ?>
        </section>

        <style>
          .list{
            margin-bottom: 30px;
          }
        </style>

        <section class="list list-table">
          <h2 class="title">
            レビュー投稿一覧
          </h2>
          <table class="table">
            <thead>
              <tr>
                <th>投稿日時</th>
                <th>タイトル</th>
                <th>レビュー</th>
              </tr>
            </thead>

            <tbody>
              <?php
              if(!empty($bordData)){
                foreach($bordData as $key => $val){
                  if(!empty($val['message'])){
                    // 映画情報の取得
                    $reviewProductData = getProductOne($val['bord_id']);
              ?>
                  <tr>
                    <td><?php echo sanitize(date('Y.m.d H:i:s',strtotime($val['send_date']))); ?></td>
                    <td><?php echo sanitize($reviewProductData['name']); ?></td>
                    <td><a href="msg.php?p_id=<?php echo sanitize($val['bord_id']); ?>"><?php echo mb_substr(sanitize($val['message']),0,40); ?>...</a></td>
                  </tr>
              <?php
                  }else{
              ?>
                  <tr>
                    <td>--</td>
                    <td>○○ ○○</td>
                    <td><a href="msg.php?p_id=<?php echo sanitize($val['id']); ?>">まだレビューの投稿はしていません</a></td>
                  </tr>
              <?php
                  }
                }
              }
              ?>
            </tbody>
          </table>
        </section>

        <section class="list panel-list">
          <h2 class="title" style="margin-bottom: 15px;">お気に入り一覧</h2>
          <?php
          if(!empty($favoriteData)):
            foreach($favoriteData as $key => $val):
          ?>
            <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
              <div class="panel-head">
                <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
              </div>
              <div class="panel-body">
                <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
              </div>
            </a>
          <?php
            endforeach;
          endif;
          ?>
        </section>
      </section>




    <!-- サイドバー -->
      <?php
      require('sidebar_mypage.php');
      ?>
    </div>

    <!-- footer -->
    <?php
    require('footer.php');
    ?>
