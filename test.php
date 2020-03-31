<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「 テスト ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogstart();

$data = array(
    'pic' =>
        array(
            'name' => 'abc',
            'type' => 'png',
            'tmp_name' => '',
            'error' => 4,
            'size' => 0
        ),
        );
        $i = 0;
        foreach($data as $key => $val){
            $i++;
            debug('======================='.$i.'回目のループ処理================');
            debug('$key:'.print_r($key,true));
            debug('$val:'.print_r($val,true));
        }