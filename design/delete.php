<?php

session_start();

require('dbconnect.php');

if (isset($_SESSION['id'])) {
	$id = $_REQUEST['id'];
	echo $_SESSION['id'] . '<br>';

	// 投稿を検査する
	$sql = sprintf('SELECT * FROM tweets WHERE tweet_id = %d', mysqli_real_escape_string($db, $id));
	$record = mysqli_query($db, $sql) or die(mysqli_error($db));
	$table = mysqli_fetch_assoc($record);
	echo $table . '<br>';
	if ($table['member_id'] == $_SESSION['id']) {
		// 削除
		echo 'test if文中' . '<br>';
		$sql = sprintf('DELETE FROM tweets WHERE tweet_id = %d', mysqli_real_escape_string($db, $id));
		mysqli_query($db, $sql) or die(mysqli_error($db));
	}
}

// header('Location:index.php');
exit();

?>