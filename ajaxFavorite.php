<?php
// 共通変数・関数ファイル読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('「 Ajax Favorite');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

//===============================
// Ajax処理
//===============================

// POSTがあり、ユーザーIDがあり、ログインしている場合
if(isset($_POST['productId']) && isset($_SESSION['user_id']) && isLogin()){
    debug('POST送信があります');
    $p_id = $_POST['productId'];
    debug('映画ID:'.$p_id);
    // 例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // レコードがあるか検索
        $sql = 'SELECT * FROM favorite WHERE product_id = :p_id AND user_id = :u_id';
        $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $resultCount = $stmt->rowCount();
        debug('$resultCountの中身：'.$resultCount);
        // レコードが１件でもある場合
        if(!empty($resultCount)){
            // レコードを削除する
            $sql = 'DELETE FROM favorite WHERE product_id = :p_id AND user_id = :u_id';
            $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data); 
        }else{
            // レコードを挿入する
            $sql = 'INSERT INTO favorite (product_id, user_id, create_date) VALUES (:p_id, :u_id, :date)';
            $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
        }

    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
debug('Ajax Favorite処理終了  <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');