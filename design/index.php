<?php
session_start();

require('dbconnect.php');

// ログインチェック
  // 1.セッションにidが入っていること
  // 2.最後の行動から1時間以内であること
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
  // ログインしている
  // セッションの時間を更新
  $_SESSION['time'] = time();
  // SQLを実行し、ユーザーのデータを取得
  $sql = sprintf('SELECT * FROM members WHERE member_id = %d', mysqli_real_escape_string($db, $_SESSION['id']));
  $record = mysqli_query($db, $sql) or die (mysqli_error($db));
  $member = mysqli_fetch_assoc($record);
} else {
  // ログインしていない
  header('Location:login.php');
  exit();
}

// ツイートをデータベースに登録する
if (!empty($_POST)) {
  if ($_POST['tweet'] != '') {
    $sql = sprintf('INSERT INTO tweets SET tweet = "%s", member_id = %d, reply_tweet_id = %d, created = NOW()',
           mysqli_real_escape_string($db, $_POST['tweet']),
           mysqli_real_escape_string($db, $member['member_id']),
           $_POST['reply_tweet_id']
    );
    mysqli_query($db, $sql) or die(mysqli_error($db));

    header('Location:index.php');
    exit();
  }
}

// ページング
// ページ番号の所得と設定
if (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) {
  $page = $_REQUEST['page'];
} else {
  $page = 1;
}
// ①表示する正しいページの数値(Min)を設定
$page = max($page, 1);
  // max関数：()に指定した複数のデータから、一番大きい値を返す
  // page=-1と指定された場合、マイナスの値のページ番号は存在しないので、1に強制変換する
// ②必要なページ数を計算する
if (isset($_GET['search_word']) && !empty($_GET['search_word'])) {
  $sql = sprintf('SELECT COUNT(*) AS cnt FROM `tweets` WHERE `tweet` LIKE "%%%s%%"',
  mysqli_real_escape_string($db, $_GET['search_word']));
} else {
  $sql = 'SELECT COUNT(*) AS cnt FROM `tweets`';
}
$recordSet = mysqli_query($db, $sql) or die(mysqli_error($db));
$table = mysqli_fetch_assoc($recordSet);
  // ceil関数：切り上げる関数
$maxPage = ceil($table['cnt'] / 5);
// ③表示する正しいページの数値(Max)を設定
$page = min($page, $maxPage);
  // min関数：()に指定した複数のデータから、一番小さい値を返す
  // page=100と指定された場合、ページ番号100のデータは存在しないので、最大ページ数に強制変換する
// ④ページに表示する件数だけ取得する
$start = ($page - 1) * 5;
$start = max(0, $start);

// ツイートをデータベースから取得する



// 返信の場合
if (isset($_REQUEST['res'])) {
  $sql=sprintf('SELECT `members`.`nick_name`, `tweets`.* FROM `members`, `tweets` WHERE `members`.`member_id` = `tweets`.`member_id`
                AND `tweets`.`tweet_id` = %d ORDER BY `tweets`.`created` DESC',
                mysqli_real_escape_string($db, $_REQUEST['res'])
  );
  $record = mysqli_query($db, $sql) or die (mysqli_error($db));
  $table = mysqli_fetch_assoc($record);
  $tweet = '@' . $table['nick_name'] . ' ' . $table['tweet'];
  }

// 検索機能
if (isset($_REQUEST['search_word'])) {
$sql = sprintf('SELECT `members`.`nick_name`, `members`.`picture_path`, `tweets`.* FROM `members`, `tweets`
                WHERE `members`.`member_id` = `tweets`.`member_id` AND `tweets`.`tweet` LIKE "%%%s%%" ORDER BY `tweets`.`created` DESC LIMIT %d, 5',
                mysqli_real_escape_string($db, $_GET['search_word']), $start
);
} else {
  $sql = sprintf('SELECT `members`.`nick_name`, `members`.`picture_path`, `tweets`.* FROM `members`, `tweets`
                  WHERE `members`.`member_id` = `tweets`.`member_id` ORDER BY `tweets`.`created` DESC LIMIT %d, 5', $start);
}
$tweets = mysqli_query($db, $sql) or die(mysqli_error($db));

// htmlspecialcharsのショートカット
function h($value){
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 本文内のURLにリンクを設定
function makeLink($value){
  return mb_ereg_replace("(https?)(://[[:alnum:]¥+¥$\;¥?¥.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>' , $value);
}

?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<?php echo h($member['nick_name']); ?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
              <?php if (isset($tweet)) { ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!" ><?php echo htmlspecialchars($tweet, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <input type="hidden" name="reply_tweet_id" value="<?php echo htmlspecialchars($_REQUEST['res'], ENT_QUOTES, 'UTF-8'); ?>">
              <?php } else { ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!" ></textarea>
              <?php } ?>
              </div>
            </div>
            <ul class="paging">
              <?php
                $word = '';
                if (isset($_GET['search_word'])) {
                  $word = '&search_word=' . $_GET['search_word'];
                }
              ?>
              <!-- ツイートボタン -->
              <input type="submit" class="btn btn-info" value="つぶやく">
              &nbsp;&nbsp;&nbsp;&nbsp;
              <!-- ページング -->
                <?php if ($page > 1) { ?>
                  <li><a href="index.php?page=<?php echo($page - 1); ?><?php echo $word; ?>" class="btn btn-default">前</a></li>
                <?php } else { ?>
                  <li><a class="btn btn-default">前</a></li>
                <?php } ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <?php if ($page < $maxPage) { ?>
                  <li><a href="index.php?page=<?php echo($page + 1); ?><?php echo $word; ?>" class="btn btn-default">次</a></li>
                <?php } else { ?>
                  <li><a class="btn btn-default">次</a></li>
                <?php } ?>
            </ul>
        </form>
      </div>
      <div class="col-md-8 content-margin-top">
        <!-- 検索フォーム -->
        <form action="" method="get" class="form-horizontal" role="form">
          <?php if (isset($_GET['search_word']) && !empty($_GET['search_word'])){ ?>
           <input type="text" name="search_word" value="<?php echo $_GET['search_word']; ?>">
          <?php } else { ?>
            <input type="text" name="search_word" value="">
          <?php } ?>
          <input type="submit" class="btn btn-success btn-xs" value="検索">
        </form>
        <!-- ツイート表示 -->
          <?php while ($tweet = mysqli_fetch_assoc($tweets)) { ?>
            <div class="msg">
              <!-- プロフィール写真 -->
              <img src="member_picture/<?php echo h($tweet['picture_path']); ?>" width="48" height="48">
              <p>
                <!-- ツイート内容 -->
                <?php echo makeLink(h($tweet['tweet'])); ?>
                <!-- ニックネーム -->
                <span class="name"> (<?php echo h($tweet['nick_name']); ?>) </span>
                [<a href="index.php?res=<?php echo h($tweet['tweet_id']); ?>">Re</a>]
              </p>
              <p class="day">
                <a href="view.php?tweet_id=<?php echo h($tweet['tweet_id']); ?>">
                  <?php echo h($tweet['created']); ?>
                </a>
                <?php if ($tweet['reply_tweet_id'] > 0) { ?>
                  <a href="view.php?tweet_id=<?php echo h($tweet['reply_tweet_id']); ?>">
                    返信元のメッセージ
                  </a>
                <?php } ?>
                <?php if ($_SESSION['id'] == $tweet['member_id']) { ?>
                  [<a hres="edit.php?tweet_id=<?php echo $tweet['tweet_id']; ?>" style="color: #00994C;">編集</a>]
                  [<a href="delete.php?tweet_id=<?php echo $tweet['tweet_id']; ?>" style="color: #F33;">削除</a>]
                <?php } ?>
              </p>
            </div>
          <?php } ?>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
