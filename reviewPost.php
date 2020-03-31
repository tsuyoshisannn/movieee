<?php
// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「');
debug('「レビュー投稿ページ');
debug('「「「「「「「「「「「「「「「「');
debugLogstart();

// ログイン認証
require('auth.php');

// =========================
// 画面処理
// =========================
$productInfo = '';
// =========================
// 画面表示用データ取得
// =========================
// 映画IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
debug('$p_idの中身:'.$p_id);

// DBから掲示板とコメントデータを取得
$dbFormData = (!empty($p_id)) ? getMyReview($_SESSION['user_id'], $p_id) : '';
// DBから自分の評価情報を取得
$dbRatingData = (!empty($p_id)) ? getRating($_SESSION['user_id'], $p_id) : '';
// DBから詳細データを取得
$productInfo = getProductOne($p_id);
// 新規登録画面か編集画面か判別するためのフラグ
$edit_flg = (empty($dbFormData)) ? false : true;
debug('映画ID:'.$p_id);
debug('フォーム用DBデータ:'.print_r($dbFormData, true));
debug('この映画の自分の評価:'.print_r($dbRatingData, true));
debug('映画詳細データ:'.print_r($productInfo, true));



// POST送信時処理
// =============================
if (!empty($_POST)) {
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST, true));
    // 変数にユーザー情報を代入
    $message = $_POST['message'];
    $rating = $_POST['rating'];

    // 更新の場合はDBの情報と入力情報が異なる場合はバリデーションをする
    if(empty($dbFormData)){
        // 最大文字数チェック
        validMaxLen($message, 'message', 500);
        // 未入力チェック
        validRequired($message, 'message');
    }else{
        if($dbFormData['message'] !== $message){
            // 最大文字数チェック
            validMaxLen($message, 'message', 500);
            // 未入力チェック
            validRequired($message, 'message');
        }
    }
    
    if(empty($err_msg)){
        debug('バリデーションOK');


        // 例外処理
        try {
            // DBへ接続
            $dbh = dbConnect();
            // SQL文作成
            // 編集画面の場合はUPDATE文、新規投稿の場合はINSERT文を生成
            if($edit_flg){
                debug('DB更新');
                $sql = 'UPDATE message SET send_date = :date, message = :message WHERE user_id = :u_id AND bord_id = :p_id';
                $data = array(':date' => date('Y-m-d H:i:s'), ':message' => $message, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
                $sql2 = 'UPDATE rating SET rating_rank = :rating, update_date = :date WHERE user_id = :u_id AND product_id = :p_id';
                $data2 = array(':rating' => $rating, ':date' => date('Y-m-d H:i:s'), ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
            }else{
                debug('DB新規登録');
                $sql = 'INSERT INTO message (bord_id, send_date, user_id, message, create_date) VALUES (:b_id, :send_date, :u_id, :message, :create_date)';
                $data = array(':b_id' => $p_id, ':send_date' => date('Y-m-d H:i:s'), ':u_id' => $_SESSION['user_id'], ':message' => $message, ':create_date' => date('Y-m-d H:i:s'));
                $sql2 = 'INSERT INTO rating (product_id, user_id, rating_rank, create_date) VALUES (:p_id, :u_id, :rating, :create_date)';
                $data2 = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id'], ':rating' => $rating, ':create_date' => date('Y-m-d H:i:s'));
            }
            debug('SQLmessage:'.$sql);
            debug('SQLrating:'.$sql2);
            debug('流し込みデータ message:'.print_r($data, true));
            debug('流し込みデータ rating'.print_r($data2, true));
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            $stmt2 = queryPost($dbh, $sql2, $data2);

            // クエリ成功の場合
            if($stmt && $stmt2){
                $_SESSION['msg_success'] = SUC04;
                debug('掲示板へ遷移します');
                header("Location:msg.php?p_id=".$p_id); //掲示板へ
            }

        } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = (!$edit_flg) ? 'レビュー投稿' : 'レビュー編集';
require('head.php');
?>

  <body class="page-reviewPost page-1colum">
      
    <!-- メニュー -->
    <?php
    require('header.php');
    ?>

    <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

  <h1 class="page-title"><?php echo (!$edit_flg) ? 'レビュー投稿' : 'レビュー編集'; ?></h1>

    <!-- Main -->
    <section id="main">

        <div class="review-img-container">

            <div class="title">
                <span class="badge"><?php echo sanitize($productInfo['category']); ?></span><br>
                <span class="m-name"><?php echo sanitize($productInfo['name']); ?></span>
            </div>

            <div class="img-main">
                <img src="<?php echo showImg(sanitize($productInfo['pic'])); ?>" alt="メイン画像： <?php echo sanitize($productInfo['name']); ?>" id="js-switch-img-main">
            </div>
        </div>
    
        <div class="review-text">
            

         <form action="" method="post" class="form" style="width:100%;height:100%;box-sizing:border-box;">

            <!--  スターレーティング   -->
               <label class="<?php if(!empty($err_msg['message'])) echo 'err'; ?>">
                評価 <span class="label-require">必須</span>
               </label>
                <div class="star-rating">
                    <input id="star5" type="radio" name="rating" value="5" class="radio" <?php if(!empty($dbRatingData['rating_rank']) && $dbRatingData['rating_rank'] === '5'){ echo 'checked'; } ?> />
                    <label for="star5">★</label>
                    <input id="star4" type="radio" name="rating" value="4" class="radio" <?php if(!empty($dbRatingData['rating_rank']) && $dbRatingData['rating_rank'] === '4'){ echo 'checked'; } ?> />
                    <label for="star4">★</label>
                    <input id="star3" type="radio" name="rating" value="3" class="radio" <?php if(!empty($dbRatingData['rating_rank']) && $dbRatingData['rating_rank'] === '3'){ echo 'checked'; } ?> />
                    <label for="star3">★</label>
                    <input id="star2" type="radio" name="rating" value="2" class="radio" <?php if(!empty($dbRatingData['rating_rank']) && $dbRatingData['rating_rank'] === '2'){ echo 'checked'; } ?> />
                    <label for="star2">★</label>
                    <input id="star1" type="radio" name="rating" value="1" class="radio" <?php if(!empty($dbRatingData['rating_rank']) && $dbRatingData['rating_rank'] === '1'){ echo 'checked'; } ?> />
                    <label for="star1">★</label>

                    <span class="rating-view"><?php echo sanitize($dbRatingData['rating_rank']) ?></span>
                </div>

            <!-- レビュー -->

            <label class="<?php if(!empty($err_msg['message'])) echo 'err'; ?>">
                レビュー <span class="label-require">必須</span>
                <textarea name="message" id="js-count" cols="30" rows="10" style="height:150px;"><?php echo sanitize($dbFormData['message']); ?></textarea>
            </label>
            <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
            <div class="area-msg">
                <?php
                if(!empty($err_msg['message'])) echo $err_msg['message'];
                ?>
            </div>

            <div class="btn-container">
                <input type="submit" class="btn btn-mid" value="投稿する">
            </div>

        </div>

        </form>

    </section>

    <div class="product-btn">

        <!-- 一覧画面へ遷移 -->
        <div class="item-left">
            <a href="productList.php<?php echo appendGetParam(array('p_id')); ?>">&lt; 一覧に戻る</a>
        </div>
        
        <!-- レビュー投稿掲示板へ遷移する -->
        <div class="item-right">
            <a href="msg.php<?php echo '?p_id='.sanitize($p_id); ?>">掲示板へ &gt;</a>
        </div>

    </div>


    <!-- footer -->
    <?php 
    require('footer.php');
    ?>