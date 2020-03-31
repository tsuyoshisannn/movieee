<footer id="footer">
    Copyright <a href="index.php">movieee</a>. All Right Reserved.
</footer>

<script src="js/vendor/jquery-3.4.1.min.js"></script>



<script>
$(function(){
    var $ftr = $('#footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
        $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;' });
    }
    
    // メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s ]+|[\s ]+$/g, "").length){
        $jsShowMsg.slideToggle('slow');
        setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
    }


    // 画像ライブプレビュー
    var $dropArea = $('.area-drop');
    var $fileInput = $('.input-file');
    $dropArea.on('click', function(e){
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e){
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e){
        $dropArea.css('border', 'none');
        var file = this.files[0],                  //2. files配列にファイルが入っています
            $img = $(this).siblings('.prev-img'),  //3. jQueryのsiblingsメソッドで兄弟のimgを取得
            fileReader = new FileReader();         //4. ファイルを読み込むFileReaderオブジェクト

        //5. 読み込みが完了した際のイベントハンドラ。 imgのsrcにデータをセット
        fileReader.onload = function(event) {
            // 読み込んだデータをimgに設定
            $img.attr('src', event.target.result).show();
        };

        // 6. 画像読み込み
        fileReader.readAsDataURL(file);

    });

    // テキストエリアアカウント
    var $countUp = $('#js-count'),
        $countView = $('#js-count-view');
    $countUp.on('keyup', function(e){
        $countView.html($(this).val().length);
    });

    // 画像切り替え
    var $switchImgSubs = $('.js-switch-img-sub'),
        $switchImgMain = $('#js-switch-img-main');
    $switchImgSubs.on('click',function(e){
        $switchImgMain.attr('src',$(this).attr('src'));
    });

    // お気に入り登録・削除
    var $favorite,
        favoriteProductId;
    $favorite = $('.js-click-favorite') || null; //空と明示するためにnullにする
    favoriteProductId = $favorite.data('productid') || null;
    // 数値の０はfalseと判定されてしまう。product_idが０の場合もあり得るので、０をtrueとする場合にはundefinedとnullを判定する
    if(favoriteProductId !== undefined && favoriteProductId !== null){
        $favorite.on('click',function(){
            var $this = $(this);
            $.ajax({
                type: "POST",
                url: "ajaxFavorite.php",
                data: { productId : favoriteProductId}
            }).done(function( data ){
                console.log('Ajax Success');
                // クラス属性をtoggleでつけ外しする
                $this.toggleClass('active');
            }).fail(function( msg ) {
                console.log('Ajax Error');
            });
        });
    }

    // ５段階評価 クリックされたらその数字を表示する
    var $star = $("input[name='rating']"),
        $ratingview = $('.rating-view');
    $star.on('click', function(e){
        $ratingview.html($(this).val());
    });

});
</script>
</body>
</html>