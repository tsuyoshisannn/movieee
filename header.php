<header>
    <div class="site-width">
        <h1><a href="productList.php">movieee</a></h1>
        <nav id="top-nav">
            <ul>
                <?php
                if(empty($_SESSION['user_id'])){
                ?>
                   <li><a href="signup.php">会員登録</a></li>
                   <li><a href="login.php">ログイン</a></li>
                <?php
                  }else{
                ?>
                   <li><a href="mypage.php">マイページ</a></li>
                   <li><a href="logout.php">ログアウト</a></li>
                <?php
                  }
                ?>
            </ul>
        </nav>
    </div>
</header>