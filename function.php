<?php
//==================================
//ログ
//==================================
// ログをとるか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//==================================
// デバッグ
//==================================
// デバッグフラグ
$debug_flg = true;
// デバッグログ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ：'.$str);
    }
}

//==================================
// セッション準備・セッション有効期限を延ばす
//==================================
// セッションファイルの置き場を変更する（／var／tmp／以下に置くと３０日は削除されない）
session_save_path("/var/tmp/");
// ガーベージコレクションが削除するセッションの有効期限を設定（３０日以上たっているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
// ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
// セッションを使う
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//====================================
// 画面表示処理開始ログ吐き出し関数
//====================================
function debugLogstart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
    debug('セッションID：'.session_id());
    debug('セッション変数の中身：'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        debug('ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
    }
}

//====================================
// 定数
//====================================
// エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力して下さい');
define('MSG06', '255文字以内で入力して下さい');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直して下さい。');
define('MSG08', 'そのEmailはすでに登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力して下さい');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('MSG18', '選択必須です');
define('MSG19', '500文字以内で入力して下さい。');

// 成功メッセージ
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '投稿しました');
define('SUC05', '更新しました');

//======================================
// グローバル変数
//======================================
// エラーメッセージ格納用の配列
$err_msg = array();

//=====================================
// バリデーション関数
//=====================================

// バリデーション関数（未入力チェック）
function validRequired($str, $key){
    if($str === ''){ //金額フォームなどを考えると数値の０はOKにし、空文字はダメにする
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}



// バリデーション関数（Email形式チェック）
function validEmail($str,$key){
    if(!preg_match("/^([a-zA-Z0-9])+(a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}
// バリデーション関数（Email重複チェック）
function validEmailDup($email){
    global $err_msg;
    // 例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        // PDO::FETCH_ASSOCは結果を連想配列として返すようにPDOに指示するもの
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで１つ目だけ取り出して判定します
        if(!empty(array_shift($result))){
            $err_msg['email'] = MSG08;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
// バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}
// バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}
// バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 255){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}

// バリデーション関数（最大文字数コメントチェック）
function validMaxComment($str, $key, $max = 500){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key] = MSG19;
    }
}

// バリデーション関数（半角チェック）
function validHalf($str, $key){
    if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}

// 半角数字チェック
function validNumber($str, $key){
    if(!preg_match("/^[0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG17;
    }
}
// 固定長チェック
function validLength($str, $key, $len = 8){
    if( mb_strlen($str) !== $len){
        global $err_msg;
        $err_msg[$key] = $len . MSG14;
    }
}
// パスワードチェック
function validPass($str, $key){
    // 半角英数字チェック
    validHalf($str, $key);
    // 最大文字数チェック
    validMaxLen($str, $key);
    // 最小文字数チェック
    validMinLen($str, $key);
}
// selectboxチェック
function validSelect($str, $key){
    if(!preg_match("/^[0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG18;
    }
}

// 年齢確認
function validAge($age, $key){
    if($age === "") {
        global $err_msg;
        $err_msg[$key] = MSG18;
    }
  }

// 性別チェック
function validGender($gender, $key){
    if(isset($gender) === false){
        global $err_msg;
        $err_msg[$key] = MSG18;
       }
}

// エラーメッセージ表示
function getErrMsg($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
        return $err_msg[$key];
    }
}

//=====================================
// ログイン認証
//=====================================
function isLogin(){
    // ログインしている場合
    if( !empty($_SESSION['login_date']) ){
        debug('ログイン済みユーザーです。');

        // 現在日時が最終ログイン日時＋有効期限を超えていた場合
        if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
            debug('ログイン有効期限オーバーです');

            // セッションを削除（ログアウトする）
            session_destroy();
            return false;
        }else{
            debug('ログイン有効期限以内です');
            return true;
        }

    }else{
        debug('未ログインユーザーです');
        return false;
    }
}



//=====================================
// データベース
//=====================================
// DB接続関数
function dbConnect(){
    // DBへの接続準備
    $db = parse_url($_SERVER['CLEARDB_DATABASE_URL']);
    $db['dbname'] = ltrim($db['path'], '/');
    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8";
    $user = $db['user'];
    $password = $db['pass'];
    $options = array(
        // SQL実行失敗時にはエラーコードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // デフォルトフェッチモードを連想配列型式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // バッファードクエリを使う（一度に結果セットを全て取得し、サーバー負荷を軽減）
        // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    // PDOオブジェクト生成（DBへ接続）
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
}

function queryPost($dbh, $sql, $data){
    // クエリー作成
    $stmt = $dbh->prepare($sql);
    // プレースホルダーに値をセットし、SQL文を実行
    if(!$stmt->execute($data)){
        debug('クエリに失敗しました。');
        debug('失敗したSQL:'.print_r($stmt, true));
        debug('SQLエラー'.print_r($stmt->errorInfo(),true));
        global $err_msg;
        $err_msg['common'] = MSG07;
        return 0;
    }
    debug('クエリ成功。');
    return $stmt;
}

function getUser($u_id){
    debug('ユーザー情報を取得します。');
    // 例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ結果のデータを１レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
function getProduct($p_id){
    debug('映画情報を取得します。');
    debug('映画ID:'. $p_id);
    // 例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM product WHERE id = :p_id AND delete_flg = 0';
        $data = array(':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            // クエリ結果のデータを１レコード返却
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }

    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

function getProductList($currentMinNum = 1, $category, $sort, $span = 9){
    debug('登録情報を取得します。');
    //例外処理
    try {
        //DBに接続
        $dbh = dbConnect();
        //件数用のSQL文作成
        $sql = 'SELECT * FROM product';
        if(!empty($sort && empty($category))){
            switch($sort){
                case 1:
                  $sql .= ' LEFT JOIN
                            (SELECT product_id , COUNT(product_id) AS `favorite_count` FROM favorite AS f GROUP BY product_id) favorite
                            ON id = product_id ORDER BY `favorite_count` ASC';
                  break;
                case 2:
                  $sql .= ' LEFT JOIN
                            (SELECT product_id , COUNT(product_id) AS `favorite_count` FROM favorite AS f GROUP BY product_id) favorite
                            ON id = product_id ORDER BY `favorite_count`  DESC';
                  break;
            }
        }
        if(!empty($sort) && !empty($category)){
            switch($sort){
            case 1:
              $sql .= ' LEFT JOIN
                        (SELECT product_id , COUNT(product_id) AS `favorite_count` FROM favorite AS f GROUP BY product_id) favorite
                        ON id = product_id WHERE category_id = '.$category .' ORDER BY `favorite_count` ASC';
              break;
            case 2:
              $sql .= ' LEFT JOIN
                        (SELECT product_id , COUNT(product_id) AS `favorite_count` FROM favorite AS f GROUP BY product_id) favorite
                        ON id = product_id WHERE category_id = '.$category .' ORDER BY `favorite_count` DESC';
              break;
            }
        }
        if(!empty($category) && empty($sort)) $sql .= ' WHERE category_id = '.$category;
        $data = array();
        debug('SQL上:'.$sql);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst['total'] = $stmt->rowCount(); //総レコード数
        $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
        if(!$stmt){
            return false;
        }

        //ページング用のSQL文作成
        $sql = 'SELECT * FROM product';
        if(!empty($sort) && empty($category)){
           switch($sort){
             case 1:
               $sql .= ' LEFT JOIN 
                        (SELECT product_id , COUNT(product_id) AS `favorite_count` FROM favorite AS f GROUP BY product_id) favorite 
                        ON id = product_id ORDER BY `favorite_count` ASC';
               break;
             case 2:
               $sql .= ' LEFT JOIN 
                        (SELECT product_id , COUNT(product_id) AS `favorite_count` FROM favorite AS f GROUP BY product_id) favorite 
                        ON id = product_id ORDER BY `favorite_count` DESC';
               break;
         }
        }
        if(!empty($sort) && !empty($category)){
            switch($sort){
            case 1:
              $sql .= ' LEFT JOIN
                        (SELECT product_id , COUNT(product_id) AS `favorite_count` FROM favorite AS f GROUP BY product_id) favorite
                        ON id = product_id WHERE category_id = '.$category .' ORDER BY `favorite_count` ASC';
              break;
            case 2:
              $sql .= ' LEFT JOIN
                        (SELECT product_id , COUNT(product_id) AS `favorite_count` FROM favorite AS f GROUP BY product_id) favorite
                        ON id = product_id WHERE category_id = '.$category .' ORDER BY `favorite_count` DESC';
              break;
            }
        }
        if(!empty($category) && empty($sort)) $sql .= ' WHERE category_id = '.$category;
        $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
        $data = array();
        debug('SQL下:'.$sql);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
        // クエリ結果のデータを全レコードを格納
        $rst['data'] = $stmt->fetchAll();
        return $rst;
        }else{
        return false;
        }

    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
    }

function getProductOne($p_id){
    debug('登録情報を取得します。');
    debug('映画ID：'.$p_id);
    // 例外処理
    try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT p.id, p.name, p.comment, p.pic, p.user_id, p.create_date, p.update_date, c.name AS category
                FROM product AS p LEFT JOIN category AS c ON p.category_id = c.id WHERE p.id = :p_id AND p.delete_flg = 0 AND c.delete_flg = 0';
        $data = array(':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            // クエリ結果のデータを１レコード返却
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }

    }catch (Exception $e){
        error_log('エラー発生：'. $e->getMessage());
    }
}

function getMyProducts($u_id){
    debug('自分の投稿情報を取得します。');
    debug('ユーザーID：'.$u_id);
    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'SELECT * FROM product WHERE user_id = :u_id AND delete_flg = 0';
      $data = array(':u_id' => $u_id);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
  
      if($stmt){
        // クエリ結果のデータを全レコード返却
        return $stmt->fetchAll();
      }else{
        return false;
      }
  
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }

function getMsgsAndBord($p_id){
    debug('msg情報を取得します。');
    // 例外処理
    try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM message WHERE bord_id = :p_id AND delete_flg = 0 ORDER BY send_date DESC';
        $data = array(':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            // クエリ結果の全データを返却
            return $stmt->fetchAll();
        }else{
            return false;
        }

    } catch (Exception $e){
        error_log('エラー発生：' .$e->getMessage());
    }
}



function getReview($p_id){
    debug('レビュー情報を取得します。');
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM message WHERE bord_id = :p_id AND delete_flg = 0';
        $data = array(':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
    }
}

function getMyReview($u_id, $p_id){
    debug('自分のレビュー情報を取得します。');
    debug('ユーザーID:'.$u_id);
    debug('映画ID:'.$p_id);
    //例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM message WHERE bord_id = :p_id AND user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id, ':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }

  function getMyAllReview($u_id){
    debug('自分のレビュー情報を全て取得します。');
    debug('ユーザーID:'.$u_id);
    //例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM message WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            return $stmt->fetchAll();
        }else{
            return false;
        }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }

function getRating($u_id, $p_id){
      debug('自分の評価情報を取得します。');
      debug('ユーザーID:'.$u_id);
      debug('映画ID:'.$p_id);
    //   例外処理
    try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT rating_rank FROM rating WHERE user_id = :u_id AND product_id = :p_id';
        $data = array(':u_id' => $u_id, ':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            // クエリ結果の全データを返却
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }

    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

function getRatingBord($u_id, $p_id){
    debug('自分の評価情報を取得します。');
    debug('ユーザーID:'.$u_id);
    debug('映画ID:'.$p_id);
  //   例外処理
  try{
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'SELECT rating_rank FROM rating WHERE user_id = :u_id AND product_id = :p_id';
      $data = array(':u_id' => $u_id, ':p_id' => $p_id);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
          // クエリ結果の全データを返却
          return $stmt->fetch(PDO::FETCH_COLUMN);
      } else {
          return false;
      }

  } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
  }
}

function getRatingAverage($p_id){
    debug('この映画の平均評価を取得します');
    debug('映画ID:'.$p_id);
    // 例外処理
    try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT AVG(rating_rank) FROM rating WHERE product_id = :p_id';
        $data = array(':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        

        if($stmt){
            return $stmt->fetch(PDO::FETCH_BOTH);
        }else{
            return 0;
        }

    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
    }
  }

function getCategory(){
    debug('カテゴリー情報を取得します。');
    // 例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM category';
        $data = array();
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            // クエリ結果の全データを返却
            return $stmt->fetchAll();
        }else{
            return false;
        }

    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

function isFavorite($u_id, $p_id){
    debug('お気に入り情報があるか確認します');
    debug('ユーザーID:'.$u_id);
    debug('映画ID:'.$p_id);
    // 例外処理
    try {
        // 例外処理
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM favorite WHERE product_id = :p_id AND user_id = :u_id';
        $data = array(':u_id' => $u_id, ':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt->rowCount()){
            debug('お気に入りです');
            return true;
        }else{
            debug('特に気に入っていません');
            return false;
        }

    } catch (Exception $e){
        error_log('エラー発生:' . $e->getMessage());
    }
}

function getMyFavorite($u_id){
    debug('自分のお気に入り情報を取得しています');
    debug('ユーザーID:'.$u_id);
    // 例外処理
    try {
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM favorite AS f LEFT JOIN product AS p ON f.product_id = p.id WHERE f.user_id = :u_id';
        $data = array(':u_id' => $u_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            // クエリ結果の全データを返却
            return $stmt->fetchAll();
        } else {
            return false;
        }

    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

function getFavorite($p_id){
    debug('この映画のお気に入りの数を調べます。');
    debug('映画ID:'.$p_id);
    // 例外処理
    try{
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT COUNT(product_id) FROM favorite WHERE product_id = :p_id';
        $data = array(':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if($stmt){
            return $stmt->fetch(PDO::FETCH_COLUMN);
        }else{
            return false;
        }
    } catch (Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}


//========================================
// メール送信
//========================================
function sendMail($from, $to, $subject, $comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        // 文字化けしないように設定（お決まりパターン）
        mb_language("japanese"); //現在使っている言語を設定する
        mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するか設定

        // メールを送信（送信結果はtrueかfalseでかえってくる）
        $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
        // 送信結果を判定
        if ($result) {
            debug('メールを送信しました。');
        } else {
            debug('【エラー発生】メールの送信に失敗しました。');
        }

    }
}
//============================
// その他
//============================
// サニタイズ
function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES,"UTF-8");
}

// フォーム入力保持
function getFormData($str, $flg = false){
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }
    global $dbFormData;
    global $err_msg;
    // ユーザーデータがある場合
    if(!empty($dbFormData)){
    // フォームのエラーがある場合
     if(!empty($err_msg[$str])){
        // POSTにデータがある場合
        if(isset($method[$str])){
            return sanitize($method[$str]);
        }else{
            // ない場合（基本あり得ない）はDBの情報を表示
            return sanitize($dbFormData[$str]);
        }
    }else{
        // POSTにデータがあり、DBの情報と違う場合
        if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
            return sanitize($method[$str]);
        }else{
            return sanitize($dbFormData[$str]);
        }
    }
  }else{
      if(isset($method[$str])){
          return sanitize($method[$str]);
      }
  }
}
// sessionを一回だけ取得できる
function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
        $data = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $data;
    }
}
// 認証キー作成
function makeRandKey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}
//画像処理
function uploadImg($file, $key){
    debug('画像アップロード処理開始');
    debug('FILE情報：'.print_r($file,true));

    if (isset($file['error']) && is_int($file['error'])) {
        try {
            // バリデーション
            // $file['error]の値を確認。配列内には[UPLOAD_ERR_OK]などの定数が入っている。
            // 「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として０や１などの数値が入っている。
            switch ($file['error']){
                case UPLOAD_ERR_OK: //OK
                    break;
                case UPLOAD_ERR_NO_FILE:   //ファイル未選択の場合
                    throw new RuntimeException('ファイルが選択されていません。');
                case UPLOAD_ERR_INI_SIZE:  //php.ini定義の最大サイズを超過した場合
                    throw new RuntimeException('php.iniのファイルサイズを超過してます。');
                case UPLOAD_ERR_FORM_SIZE: //フォーム定義の最大サイズを超過した場合
                    throw new RuntimeException('ファイルサイズが大きすぎます。');
                default: //その他の場合
                    throw new RuntimeException('その他のエラーが発生しました。');
            }

            // $file['mime']の値はブラウザ側の偽装可能なので、MIMEタイプを自前でチェックする
            // exif_imagetype関数は「IMAGE_GIF」 「IMAGETYPE_JPEG」などの定数を返す
            $type = @exif_imagetype($file['tmp_name']);
            if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { //第三引数にはtrueをつけると厳密にチェックしてくれるので必ずつける
                throw new RuntimeException('画像形式が未対応です');
            }

            // ファイルデータからSHA-1ハッシュをとってファイル名を決定し、ファイルを保持する
            // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性あり、
            // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
            // image_type_to_extension関数はファイルの拡張子を取得するもの
            $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);

            if(!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
                throw new RuntimeException('ファイル保存時にエラーが発生しました。');
            }
            // 保存したファイルパスのパーミッション（権限）を変更する
            // ６４４は自分は見て描いていい権限、他人は見るだけ
            chmod($path, 0644);

            debug('ファイルは正常にアップロードされました');
            debug('ファイルパス：'.$path);
            return $path;

        } catch (RuntimeException $e) {

            debug($e->getMessage());
            global $err_msg;
            $err_msg[$key] = $e->getMessage();
        }
    }
}
// ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用パラメータリンク
// $pageColNum : ページネーション表示数
function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
    // 現在のページが、総ページ数と同じかつ総ページ数が表示項目数以上なら、左にリンク４つ出す
    if($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum - 4;
        $maxPageNum = $currentPageNum;
        // 現在のページが、総ページ数の1ページ前なら、左にリンク３個、右に１個だす
    }elseif($currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum - 3;
        $maxPageNum = $currentPageNum + 1;
        // 現ページが２の場合は左にリンク１個、右にリンク３個だす
    }elseif($currentPageNum == 2 && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum - 1;
        $maxPageNum = $currentPageNum + 3;
        // 現ページが１の場合には左に何も出さないで右に５個出す
    }elseif($currentPageNum == 1 && $totalPageNum > $pageColNum){
        $minPageNum = $currentPageNum;
        $maxPageNum = 5;
        // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
    }elseif($totalPageNum < $pageColNum){
        $minPageNum = 1;
        $maxPageNum = $totalPageNum;
        // それ以外は左に２個出す
    }else{
        $minPageNum = $currentPageNum - 2;
        $maxPageNum = $currentPageNum + 2;
    }

    echo '<div class="pagination">';
      echo '<ul class="pagination-list">';
        if($currentPageNum != 1){
            echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
        }
        for($i = $minPageNum; $i <= $maxPageNum; $i++){
            echo '<li class="list-item ';
            if($currentPageNum == $i ){ echo 'active';}
            echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
        }
        if($currentPageNum != $maxPageNum && $maxPageNum > 1){
            echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
        }
      echo '</ul>';
    echo '</div>';
}
// 画面表示用関数
function showImg($path){
    if(empty($path)){
        return 'img/sample-img.png';
    }else{
        return $path;
    }
}
// GETパラメーター付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
    if(!empty($_GET)){
        $str = '?';
        foreach ($_GET as $key => $val) {
             if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
                $str .= $key.'='.$val.'&';
            }
        }
        $str = mb_substr($str, 0, -1, "UTF-8");
        return $str;
    }
}