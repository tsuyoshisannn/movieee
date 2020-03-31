<?php

// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ユーザー登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

// POST送信されていた場合
if(!empty($_POST)){

  // 変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  $username = $_POST['username'];
  $age = $_POST['age'];
  $gender = $_POST['gender'];
  // 画像をアップロードし、パスを格納
  $pic = ( !empty($_FILES['pic']['name']) ) ? uploadImg($_FILES['pic'],'pic') : '';
  // 画像をPOSTしていない（登録していない）がすでにDBに登録されている場合、DBのパスを入れる(POSTには反映されないので)
  $pic = ( empty($pic) && !empty($dbFormData['pic']) ) ? $dbFormData['pic'] : $pic;

  // 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');
  validRequired($username, 'username');

  // 未選択チェック
  validAge($age, 'age');
  validGender($gender, 'gender');


  if(empty($err_msg)){

    // email形式チェック
    validEmail($email, 'email');
    // email最大文字数チェック
    validMaxLen($email, 'email');
    // email重複チェック
    validEmailDup($email);

    // パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    // パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    // パスワードの最小文字数チェック
    validMinLen($pass, 'pass');

    // パスワード（再入力）の最大文字数チェック
    validMaxLen($pass_re, 'pass_re');
    // パスワード（再入力）の最小文字数チェック
    validMinLen($pass_re, 'pass_re');

    // 名前の最大文字数チェック
    validMaxLen($username, 'username');


    if(empty($err_msg)){

      // パスワードとパスワード再入力があっているかチェック
      validMatch($pass, $pass_re, 'pass_re');

      if(empty($err_msg)){

        // 例外処理
        try{
          // DBへ接続
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'INSERT INTO users (email, password, username, age, gender, pic, login_time, create_date) VALUES(:email, :pass, :username, :age, :gender, :pic, :login_time, :create_date)';
          $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT),
                        ':username' => $username, ':age' => $age, ':gender' => $gender, 'pic' => $pic, 
                        ':login_time' => date('Y-m-d H:i:s'),
                        ':create_date' => date('Y-m-d H:i:s'));
          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          // クエリ成功の場合
          if($stmt){
            // ログイン有効期限（デフォルトを１時間とする）
            $sesLimit = 60*60;
            // 最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            // ユーザーIDを格納
            $_SESSION['user_id'] = $dbh->lastInsertId();

            debug('セッション変数の中身：'.print_r($_SESSION,true));

            header("Location:productList.php");  //トップページへ遷移
          }

        } catch (Exception $e){
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }


      }
    
    }
  }
}
?>

<?php
$siteTitle = 'ユーザー登録';
require('head.php');
?>

  <body class="page-signup page-1colum">

    <!-- ヘッダー -->
    <?php
    require('header.php');
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

     <!-- main -->
     <section id="main">

      <div class="form-container">

        <form action="" method="post" class="form">
            <h2 class="title">ユーザー登録</h2>
            <div class="area-msg">
                <?php
                if(!empty($err_msg['common'])) echo $err_msg['common'];
                ?>
            </div>
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
            </label>
            <div class="area-msg">
                <?php
                if(!empty($err_msg['email'])) echo $err_msg['email'];
                ?>
            </div>
            
            <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
              パスワード <span style="font-size:12px">※英数字６文字以上</span>
              <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
            </label>
            <div class="area-msg">
               <?php
               if(!empty($err_msg['pass'])) echo $err_msg['pass'];
               ?>
            </div>
            
            <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
              パスワード（再入力）
              <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
            </label>
            <div class="area-msg">
                <?php
                if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];
                ?>
            </div>

            <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            ニックネーム
            <input type="text" name="username" value="<?php if(!empty($_POST['username'])) echo $_POST['username']; ?>">
            </label>
            <div class="area-msg">
              <?php
              if(!empty($err_msg['username'])) echo $err_msg['username'];
              ?>
            </div>

            <label class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
            年齢
            <select name="age">
              <option value=""> 選択して下さい</option>
              <option value="10才以下">10才以下</option>
              <option value="10代">10代</option>
              <option value="20代">20代</option>
              <option value="30代">30代</option>
              <option value="40代">40代</option>
              <option value="50代">50代</option>
              <option value="60代">60代</option>
              <option value="70才以上">70才以上</option>
            </select>
            </label>
            <div class="area-msg">
              <?php
              if(!empty($err_msg['age'])) echo $err_msg['age'];
              ?>
            </div>

            <label class="<?php if(!empty($err_msg['gender'])) echo 'err'; ?>">
            性別<br>
                <input type="radio" name="gender" value="男">男
                <input type="radio" name="gender" value="女">女
                <input type="radio" name="gender" value="？">？
            </label>
            <div class="area-msg">
            <?php
              if(!empty($err_msg['gender'])) echo $err_msg['gender'];
            ?>
            </div>
            
            プロフィール画像
            <label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>" style="height:370px;line-height:370px;">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="file" name="pic" class="input-file" style="height:370px;">
              <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:nane;' ?>">
              ドラッグ＆ドロップ
            </label>
            <div class="area-msg">
                <?php
                if(!empty($err_msg['pic'])) echo $err_msg['pic'];
                ?>
            </div>

            <div class="btn-container">
                <input type="submit" class="btn btn-mid" value="登録する">
            </div>
        
            </div>



        </form>
      </div>

     </section>

    </div>

    <!-- footer -->
    <?php
    require('footer.php');
    ?>