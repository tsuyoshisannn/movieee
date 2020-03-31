<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ログアウトページ  ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

debug('ログアウトします。');
// セッションを削除（ログアウトする）
session_destroy();
debug('ログインページへ遷移します');
// ログインページへ遷移します
header("Location:login.php");
exit();