<?php

// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ログインページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

// ログイン認証
require('auth.php');

//======================
// ログイン画面処理
//======================
// POST送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');

    // 変数にユーザー情報を代入
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_save = (!empty($_POST['pass_save'])) ? true : false; //ショートハンド（略記法）という書き方
    // if(!empty($_POST['pass_save'])){
    // $pass_save = true
    // }else{
    // $pass_save = false};
    // これがショートハンドらしい

    // emailの型式チェック
    validEmail($email, 'emial');
    // emailの最大文字数チェック
    validMaxLen($email, 'email');

    // パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    // パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    // パスワードの最小文字数チェック
    validMinLen($pass, 'pass');

    // 未入力チェック
    validRequired($email, 'email');
    validRequired($pass, 'pass');

    if(empty($err_msg)){
        debug('バリデーションOKです。');

        // 例外処理
        try{
            // DBへ接続
            $dbh = dbConnect();
            // SQL文を作成
            $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg =0';
            debug('$sqlの中身：'.print_r($sql, true));
            $data = array(':email' => $email);
            debug('$dataの中身：'.print_r($data, true));
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            debug('$stmtの中身：'.print_r($stmt, true));
            // クエリ結果の値を取得
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            debug('クエリ結果の中身：'.print_r($result,true));

            // パスワード照合
            // password_verify — パスワードがハッシュにマッチするかどうかを調べる
            if(!empty($result) && password_verify($pass, array_shift($result))){
                debug('パスワードがマッチしました。');

                // ログイン有効期限（デフォルトを１時間とする）
                $sesLimit = 60*60;
                // 最終ログイン日時を現在日時に
                $_SESSION['login_date'] = time(); //time関数は1970年1月1日00:00:00を0として、1秒経過するごとに1ずつ増加させた値が入る
            
            // ログイン保持にチェックがある場合
            if($pass_save){
                debug('ログイン保持にチェックがあります。');
                // ログイン有効期限を30日にしてセット
                $_SESSION['login_limit'] = $sesLimit * 24 * 30;
                }else{
                    debug('ログイン保持にチェックはありません。');
                    // 次回からログイン保持しないので、ログイン有効期限を１時間後にセット
                    $_SESSION['login_limit'] = $sesLimit;
                }
                // ユーザーIDを格納
                $_SESSION['user_id'] = $result['id'];

                debug('セッション変数の中身：'.print_r($_SESSION,true));
                debug('リスト画面へ遷移します。');
                header("Location:productList.php"); //マイページへ
                exit();
            }else{
                debug('パスワードがアンマッチです。');
                $err_msg['common'] = MSG09;
            }

          } catch (Exception $e){
              error_log('エラー発生：'.$e->getMessage());
              $err_msg['common'] = MSG07;
          }
    }
}
debug('画面表示終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<?php
$siteTitle = 'ログイン';
require('head.php');
?>

  <body class="page-login page-1colum">

      <!-- ヘッダー -->
      <?php
        require('header.php');
      ?>
      <!-- メインコンテンツ -->
      <div id="contents" class="site-width">

       <!-- メイン -->
       <section id="main">

       <div class="form-conteiner">

         <form action="" method="post" class="form">
             <h2 class="title">ログイン</h2>
             <div class="area-msg">
                 <?php
                 if(!empty($err_msg['common'])) echo $err_msg['common'];
                 ?>
             </div>
             <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
             メールアドレス
              <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
             </label>
             <div class="area-msg">
                 <?php
                 if(!empty($err_msg['email'])) echo $err_msg['email'];
                 ?>
             </div>
             <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
             パスワード
              <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
             </label>
             <div class="area-msg">
                 <?php
                 if(!empty($err_msg['pass'])) echo $err_msg['pass'];
                 ?>
             </div>
             <label>
                 <input type="checkbox" name="pass_save">次回ログインを省略する
             </label>
              <div class="btn-container">
                  <input type="submit" class="btn btn-mid" value="ログイン">
              </div>
         </form>
       </div>

       </section>

      </div>

      <!-- footer -->
      <?php
      require('footer.php');
      ?>