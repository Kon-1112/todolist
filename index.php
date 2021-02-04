<?php
///外部ファイルの読み込み
require_once('functions.php');
/*
issetはsubmitという名前のキーが存在し、Null以外の値がセットさせているのかを確認する
セットされていた場合はTrueを返す
セットされていない場合はFalseを返す
*/
if(isset($_POST['submit'])){
	///連想配列のタスク名に入力された変数を取り出す
	$name = $_POST['name'];
	///htmlspecialcharsを用いてSQLインジェクションなどの脅威から守る
	$name = htmlspecialchars($name,ENT_QUOTES);

	///別ファイルで定義した関数を呼び出す
	$dbh = db_connect();

	///SQLを記述,セキュリティ対策で直接データベースに値を入れない。
	///doneに初期値0を代入し、後で変更を加えることでタスク名を絞る。
	$sql = 'INSERT INTO tasks (name,done) VALUES (?,0)';
	///prepareメソッドを$sqlを渡して呼び出す。SQLが実行できるよう、準備をしてPDOStatementクラスのインスタンスを返す。
	$stmt = $dbh->prepare($sql);
	///?にnameとユーザーから入力されたタスク名の値を紐づける。
	$stmt->bindValue(1,$name,PDO::PARAM_STR);
	///SQL文を実行
	$stmt->execute();
	$dbh = null;
	unset($name);
}
///methodというキーが存在し、NULL以外の値が設定されているか and methodにsuccessという文字列があるかを確認する
if(isset($_POST['success']) && ($_POST['success'] == 'put')){
	$id = $_POST["id"];
	$id = htmlspecialchars($id,ENT_QUOTES);
	$id = (int)$id;
	$dbh = db_connect();
	///SQLでデータを更新する, idデータを1に変更する
	$sql = 'UPDATE tasks SET done = 1 WHERE id = ?';
	$stmt = $dbh->prepare($sql);

	$stmt->bindValue(1,$id,PDO::PARAM_INT);
	$stmt->execute();

	$dbn = null;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Todoリスト</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<style>
		.align{
			text-align: center;
		}
		li{
			list-style: none;
		}
		.resize{
			padding-right: 2% ;
		}
		.list{
			display: flex;
		}
		form{
			padding: 10px;
		}
	</style>
</head>
<body>
	<h1 class="alert alert-dark">Todoリスト</h1>
	<form action="index.php" method="POST">
		<div class="resize">
			<ul>
				<div class="align">
					<li><span>タスク名</span><input type="text" name="name" class="form-control form-control-lg" placeholder="タスク名を入力" required></li>
				</div><br>
				<li><input type="submit" name="submit" class="btn btn-primary btn-lg btn-block"></li>
			</ul>
		</div>
	</form>
	<ul>
	<?php
	$dbh = db_connect();
	///テーブルから条件下のデータを取得する,ORDER BY id DESCとは、降順で取得するという意味である。
	$sql = 'SELECT id,name,done FROM tasks WHERE done = 0 ORDER BY id DESC';
	$stmt = $dbh->prepare($sql);
	$stmt->execute();
	$dbh = null;
	///PDO:::FETCH_ASSOCは連想配列での形で取得する。fetchメソッドの取得が失敗した場合はFalseが返るのでループは終了する。
	///if ($task=$stmt->fetch(PDO::FETCH_ASSOC)){
	///	print '
	///	<p class="alert alert-success">データベースに '. $task['name'] .' を追加しました</p>';
	///}
	while($task=$stmt->fetch(PDO::FETCH_ASSOC)){
		print '<li>';
		print $task['name'];

		print '
			<form action="index.php" method="POST">
				<table border="1">
					<input type="hidden" name="success" value="put">
					<input type="hidden" name="id" value="'. $task['id'] .'">
				<button type="submit" class="btn btn-danger">完了</button>	
			</form>
		';
		print '</li>';
	}
	?>
	</ul>
</body>
</html>