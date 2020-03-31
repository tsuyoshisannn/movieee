<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('「 映画登録ページ   ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

// ログイン認証
require('auth.php');

// =========================
// 画面処理
// =========================

// 画面表示用データ取得
// =========================
// GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// if(!empty($_GET['p_id'])){
//    $_GET['p_id'] = $_GET['p_id']
//}else{
//    $_GET['p_id'] = '';
//}
// DBから商品データを取得
$dbFormData = (!empty($p_id)) ? getProduct($p_id) : '';
// 新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
// DBからカテゴリデータを取得
$dbCategoryData = getCategory();
debug('商品ID: '.$p_id);
debug('フォーム用DBデータ: '.print_r($dbFormData,true));
debug('カテゴリデータ： '.print_r($dbCategoryData,true));



// POST送信時処理
// =============================
if (!empty($_POST)) {
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST, true));
    debug('FILE情報：'.print_r($_FILES, true));

    // 変数にユーザー情報を代入
    $name = $_POST['name'];
    $category = $_POST['category_id'];
    $comment = $_POST['comment'];
    // 画像をアップロードし、パスを格納
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
    // 画像をPOSTしてない（登録していない）がすでにDBの登録されている場合、DBのパスを入れる（POSTには反映されないので）
    $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;


    // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
    if (empty($dbFormData)) {
        // 未入力チェック
        validRequired($name, 'name');
        // 最大文字数チェック
        validMaxLen($name, 'name');
        // セレクトボックスチェック
        validSelect($category, 'category_id');
        // 未入力チェック
        validRequired($comment, 'comment');
        // 最大文字数チェック
        validMaxLen($comment, 'comment', 500);
    } else {
        if ($dbFormData['name'] !== $name) {
            // 未入力チェック
            validRequired($name, 'name');
            // 最大文字数チェック
            validMaxLen($name, 'name');
        }
        if ($dbFormData['category_id'] !== $category) {
            // セレクトボックスチェック
            validSelect($category, 'category_id');
        }
        if ($dbFormData['comment'] !== $comment) {
            // 未入力チェック
            validRequired($comment, 'comment');
            // 最大文字数チェック
            validMaxComment($comment, 'comment', 500);
            
        }
    }

    if (empty($err_msg)) {
        debug('バリデーションOKです。');

        // 例外処理
        try {
            // DBへ接続
            $dbh = dbConnect();
            // SQL文作成
            // 編集画面の場合はUPDATE文、新規登録の場合はINSERT文を生成
            if ($edit_flg) {
                debug('DB更新です。');
                $sql = 'UPDATE product SET name = :name, category_id = :category, comment = :comment, pic = :pic, user_id = :u_id WHERE id = :p_id';
                $data = array(':name' => $name, ':category' => $category, ':comment' => $comment, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], 'p_id' => $p_id);
            } else {
                debug('DB新規登録です。');
                $sql = 'INSERT INTO product (name, category_id, comment, pic, user_id, create_date ) values (:name, :category, :comment, :pic, :u_id, :date)';
                $data = array(':name' => $name, ':category' => $category, ':comment' =>$comment, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
            }
            debug('SQL:'.$sql);
            debug('流しこみデータ：'.print_r($data, true));
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);

            // クエリ成功の場合
            if ($stmt) {
                $_SESSION['msg_success'] = SUC04;
                debug('マイページへ遷移します。');
                header("Location:mypage.php");//マイページへ
            }
        } catch (Exception $e) {
            error_log('エラー発生：' .$e->getMessage());
            $err_msg['common'] = MSG07;
        }

        // 新規作成の場合は掲示板テーブルも作成
        if (!$edit_flg) {
            try {
                // DBへ接続
                $dbh = dbConnect();
                // SQL文作成
                $sql = 'INSERT INTO bord (user_id,  create_date) VALUES (:u_id, :date)';
                $data = array(':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
                // クエリ実行
                $stmt = queryPost($dbh, $sql, $data);

            } catch (Exception $e) {
                error_log('エラー発生：' . $e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = (!$edit_flg) ? '映画投稿' : '映画編集';
require('head.php');
?>

  <body class="page-profEdit page-2colum page-logined">

    <!-- メニュー -->
    <?php
    require('header.php');
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
        <h1 class="page-title"><?php echo (!$edit_flg) ? '映画投稿' : '映画編集'; ?></h1>
        <!-- main -->
        <section id="main">
            <div class="form-container">
                <form action="" method="post" class="form" enctype="multipart/form-data" style="width:100%;height:100%;box-sizing:border-box;">
                <div class="area-msg">
                    <?php
                    if(!empty($err_msg['comon'])) echo $err_msg['comon'];
                    ?>
                </div>
                <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?> ">
                  タイトル<span class="label-require">必須</span>
                  <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
                </label>
                <div class="area-msg">
                    <?php
                    if(!empty($err_msg['name'])) echo $err_msg['name'];
                    ?>
                </div>
                <label class="<?php if(!empty($err_msg['category_id'])) echo 'err'; ?>">
                  カテゴリ<span class="label-require">必須</span>
                  <select name="category_id" id="">
                      <option value="" <?php if(getFormData('category_id') == 0){ echo 'selected';} ?> >選択してください</option>
                    <?php
                      foreach($dbCategoryData as $key => $val){
                    ?>
                      <option value="<?php echo $val['id'] ?>" <?php if(getFormData('category_id') == $val['id'] ){echo 'selected'; } ?> >
                        <?php echo $val['name']; ?>
                      </option>
                    <?php
                      }
                    ?>
                  </select>
                </label>
                <div class="area-msg">
                    <?php
                    if(!empty($err_msg['category_id'])) echo $err_msg['category_id'];
                    ?>
                </div>
                <label class="<?php if(!empty($err_msg['comment'])) echo 'err'; ?>">
                  あらすじ<span class="label-require">必須</span>
                  <textarea name="comment" id="js-count" cols="30" rows="10" style="height:150px;"><?php echo getFormData('comment'); ?></textarea>
                </label>
                <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
                <div class="area-msg">
                    <?php
                    if(!empty($err_msg['comment'])) echo $err_msg['comment'];
                    ?>
                </div>
                <div style="overflow:hidden;">
                  <div class="imgDrop-container">
                      画像
                      <label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?> ">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic" class="input-file">
                        <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
                          ドラッグ＆ドロップ
                      </label>
                      <div class="area-msg">
                          <?php
                          if(!empty($err_msg['pic'])) echo $err_msg['pic'];
                          ?>
                      </div>
                  </div>
                </div>
                  
                  <div class="btn-container">
                      <input type="submit" class="btn btn-mid" value="<?php echo (!$edit_flg) ? '映画投稿' : '映画編集'; ?>">
                  </div>
                </form>
            </div>
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