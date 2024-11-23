<?php 

date_default_timezone_set('Asia/Tokyo');

$comment_array = array();
$dbh = null;
$stmt = null;
$error_message = array();

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$hostname = $_ENV['HOSTNAME'];//localhost
$dbname = $_ENV['DB_NAME'];//DBの名前（ここは文字列で囲む必要はない）
$dbuser = $_ENV['DB_USER'];//DBのユーザー名(文字列で囲む必要がある)
$dbpassword = $_ENV['DB_PASSWORD'];//DBのパスワード(文字列で囲む必要がある)


try {
    $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", $dbuser, $dbpassword);
    // $dbh = new PDO("mysql:host=localhost;dbname=bbs-yt", 'root', 'souchan789');
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

//ここまで追加

/*
今後の課題
１．JavaScriptを書き込むと投稿するたびに発動するようになっている
（クロスサイトスクリプティング対策）
対策
①ユーザー入力フィールド
②クエリパラメータ
に含まれるHTMLタグやJavaScriptを表示前に無害化する

2．SQLインジェクションの対策がされていない
対策
①ユーザー入力フィールド
②URLパラメータ
からの特定のSQLキーワードや特殊文字（シングルクウォートやセミコロン）を無害化する

エスケープ処理についても調べる必要がある

*/



if(!empty($_POST['SubmitButton'])){

    //名前のチェック
    if(empty($_POST['username'])){
        echo '名前を入力してください';
        $error_message['username'] = '名前を入力してください';
    }

    //コメントのチェック
    if(empty($_POST['content'])){
        echo 'コメントを入力してください';
        $error_message['comment'] = 'コメントを入力してください';
    }

    //エラーメッセージがなければDBに書き込む
    if(empty($error_message)){
        $postDate = date('Y-m-d H:i:s');

        try{
            $stmt = $dbh->prepare("INSERT INTO `bbs-table` (`username`, `comment`, `postDate`) VALUES (:username, :comment, :postDate)");
            $stmt->bindParam(':username', $_POST['username']);
            $stmt->bindParam(':comment', $_POST['content']);
            $stmt->bindParam(':postDate', $postDate);

            $stmt->execute();
        } catch(PDOException $e){
            echo $e->getMessage();
        }
    }
}
//DB接続


//DBからコメントを取得する
$sql = 'SELECT * FROM `bbs-table`;';
$comment_array=$dbh->query($sql);

//DBの接続を閉じる
$dbh = null;

?>
  


<?php # コメントを書く時は，こんな感じで?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <title>PHP掲示板</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1 class="title">PHP掲示板アプリ</h1>
    <?php # <hr>水平線を表す?>
    <hr>
    <div class="boardWrapper">
        <section>
            <?php # ここでforeach文を使って，DBから取得したコメントを表示する
                foreach($comment_array as $comment) :?>
            <article>
                <div class="wrapper">
                    <div class="nameArea">
                        <span>名前：</span>
                        <p class='username'><?php echo $comment['username']; ?></p>
                        <time><?php echo $comment['postDate']; ?></time>
                    </div>
                    <p class="comment"><?php echo $comment['comment']; ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </section>
        <?php # ここのmethodを書き込むことで,POSTコマンドを機能できる?>
        <form class="formWrapper" method='POST'>
            <div>
                <input type="submit" value='書き込む' name='SubmitButton'>
                <label for="">名前：</label>
                <input type="text" name='username'>
            </div>
            <div>
               <textarea class="commentTextArea"  name='content'></textarea> 
            </div>
        </form>
    </div>
</body>
</html>