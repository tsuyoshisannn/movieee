<?php

// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('「 トップページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

// ログイン認証
require('auth.php');

//=================================
// 画面処理
//=================================
// 画面表示用データ取得
//=================================
// GETパラメータを取得
//=================================
// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページ目
// カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
// ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';


// パラメーターに不正な値がはいっているかチェック
if(!is_int((int)$currentPageNum)){
    error_log('エラー発生：指定のページに不正な値が入りました');
    header("Location:productList.php"); //一覧ページへ
    exit();
}
// 表示件数
$listSpan = 9;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1-1)*9 = 0、2ページ目なら(2-1)*9 = 9
// DBから登録データを取得
$dbProductData = getProductList($currentMinNum, $category, $sort);
// DBからカテゴリデータを取得
$dbCategoryData = getCategory();
//debug('DBデータ：'.print_r($dbFormData,true));
//debug('カテゴリデータ：'.print_r($dbCategoryData,true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'HOME';
require('head.php');
?>

  <body class="page-home page-2colum">

  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- サイドバー -->
    <section id="sidebar">
        <form name="" method="get">
            <h1 class="title">カテゴリー</h1>
            <div class="selectbox">
                <span class="icn_select"></span>
                <select name="c_id" id="">
                  <option value="0" <?php if(getFormData('c_id', true) == 0 ){ echo 'selected'; } ?> >選択してください</option>
                  <?php
                    foreach($dbCategoryData as $key => $val){
                  ?>
                    <option value="<?php echo $val['id'] ?>" <?php if(getFormData('c_id',true) == $val['id'] ){ echo 'selected'; } ?> >
                    <?php echo $val['name']; ?>
                    </option>
                  <?php
                   }
                  ?>
                </select>
            </div>

            <h1 class="title">表示順</h1>
            <div class="selectbox">
              <span class="icn_select"></span>
              <select name="sort">
                <option value="0" <?php if(getFormData('sort',true) == 0 ){ echo 'selected'; } ?> >選択してください</option>
                <option value="1" <?php if(getFormData('sort',true) == 1 ){ echo 'selected'; } ?> >お気に入りが少ない順</option>
                <option value="2" <?php if(getFormData('sort',true) == 2 ){ echo 'selected'; } ?> >お気に入りが多い順</option>
              </select>
            </div>
            <input type="submit" value="検索">
        </form>
    </section>

    <!-- main -->
    <section id="main">
        <div class="search-title">
            <div class="search-left">
                <span class="total-num"><?php echo sanitize($dbProductData['total']); ?></span>件見つかりました。
            </div>
            <div class="search-right">
                <span class="num"><?php echo (!empty($dbProductData['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum + count(array($dbProductData['data'])); ?></span>件 / <span class="num"><?php echo sanitize($dbProductData['total']); ?></span>件中
            </div>
        </div>
        <div class="panel-list">
            <?php
               foreach($dbProductData['data'] as $key => $val):

                // DBからお気に入りの総数を取得
                $countFavorite = getFavorite($val['id']);
                
                // DBから平均評価を取得
                $number = getRatingAverage($val['id']);
                
                // 小数点第一位までに変換
                $averageRating = round($number[0], 1);
                
            ?>
              <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
                <div class="panel-head">
                  <img src="<?php echo sanitize($val['pic']); ?>" alt="<?php echo sanitize($val['name']); ?>">
                </div>
                <div class="panel-body">
                  <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
                </div>
                <div class="panel-foot">
                  <p class="p-star"><span class="star">★</span>×<?php echo sanitize($averageRating); ?></p>
                  <i class="fa fa-heart p-favorite"></i>×<span><?php echo sanitize($countFavorite); ?></span>
                </div>
              </a>
            <?php
            endforeach;
            ?>
        </div>

        <?php pagination($currentPageNum, $dbProductData['total_page']); ?>

    </section>

  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>
