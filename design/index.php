<?php
session_start();

require('dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
  // ログインしている
  $_SESSION['time'] = time();
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
             mysqli_real_escape_string($db, $_POST['reply_tweet_id'])
      );
      mysqli_query($db, $sql) or die(mysqli_error($db));

      header('Location:index.php');
      exit();
    }
  }

  // ツイートをデータベースから取得する
  $sql = sprintf('SELECT `members`.`nick_name`, `members`.`picture_path`, `tweets`.* FROM `members`, `tweets`
                  WHERE `members`.`member_id` = `tweets`.`member_id` ORDER BY `tweets`.`created` DESC');
  $posts = mysqli_query($db, $sql) or die(mysqli_error($db));

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
        <legend>ようこそ<?php echo htmlspecialchars($member['nick_name']); ?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <li><a href="index.php" class="btn btn-default">前</a></li>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li><a href="index.php" class="btn btn-default">次</a></li>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
        <?php while ($post = mysqli_fetch_assoc($posts)) { ?>
        <div class="msg">
          <!-- プロフィール写真 -->
          <img src="member_picture/<?php echo htmlspecialchars($post['picture_path'], ENT_QUOTES, 'UTF-8'); ?>" width="48" height="48">
          <p>
            <!-- ツイート内容 -->
            <?php echo htmlspecialchars($post['tweet'], ENT_QUOTES, 'UTF-8'); ?>
            <!-- ニックネーム -->
            <span class="name"> (<?php echo htmlspecialchars($post['nick_name'], ENT_QUOTES, 'UTF-8') ?>) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.php">
              <?php echo htmlspecialchars($post['created'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
            [<a ="#" style="color: #00994C;">編集</a>]
            <?php if ($_SESSION['id'] == $post['member_id']) { ?>
              [<a href="delete.php?id=<?php echo htmlspecialchars($post['tweet_id']); ?>" style="color: #F33;">削除</a>]
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
