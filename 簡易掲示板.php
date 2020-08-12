<!--php-->
<?php
  //◆◆データベース接続◆◆//
  $dsn = 'データベース名';
  $user = 'ユーザーID';
  $password = 'パスワード';
  //インスタンス化
  $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

  //【投稿フォーム】
  //送信されたものがある場合
  if(isset($_POST["submit"])){
    //中身が空でない場合（編集）
    if(!empty($_POST["num"]) && !empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["password"])){
      //送られてきた値を変数に代入
      $number = $_POST["num"];
      $name = $_POST["name"];
      $comment = $_POST["comment"];
      $password = ($_POST["password"]);
      $post_datetime = date("Y/m/d H:i:s");

      //編集対象番号のそれぞれのレコードの値を取得
      //SELECT文を使って抽出
      $sql = 'SELECT * FROM POSTTABLE WHERE post_num=:num';
      //prepareで準備
      $stmt = $pdo -> prepare($sql);
      $stmt -> bindParam(':num', $number, PDO::PARAM_INT);
      //executeで実行 
      $stmt -> execute();
      //selectしたレコード列を二重の配列として抽出
      $selected_Rows = $stmt -> fetchAll();

      if(!empty($selected_Rows[0])){
        //該当の行を代入
        $get_contents = $selected_Rows[0];
        //パスワードを代入
        $get_Password = $get_contents['password'];
        //パスワードが正しい場合、フォームに編集対象番号、名前、内容をセットする
        if($get_Password==$password){
          //UPDATE文で編集
          $sql ='UPDATE POSTTABLE SET post_num=:num, name=:name, comment=:comment, password=:password, post_datetime=:post_datetime WHERE post_num=:num';
          //prepareで準備
          $stmt = $pdo -> prepare($sql);
          $stmt -> bindParam(':num', $number, PDO::PARAM_STR);
          $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
          $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
          $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
          $stmt -> bindParam(':post_datetime', $post_datetime, PDO::PARAM_STR);
          //executeで実行
          $stmt -> execute();
          //完了メッセージ
          echo "投稿番号".$number."を編集しました。";
        }else{
          //エラーメッセージ
          echo "パスワードが違います。";
        }
      }

    //中身が空でない場合（新規投稿） 
    }elseif(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["password"])){
      //投稿内容を変数に代入
      $name = $_POST["name"];
      $comment = $_POST["comment"];
      $password = ($_POST["password"]);
      $post_datetime = date("Y/m/d H:i:s");

      //一番新しい投稿番号を取得
      //SELECT文を使って抽出
      $sql ='SELECT * FROM POSTTABLE WHERE post_num =(SELECT Max(post_num) FROM POSTTABLE)';
      $stmt = $pdo -> query($sql);
      //二重構造
      $selected_Rows = $stmt -> fetchAll(PDO::FETCH_ASSOC);
      if(!empty($selected_Rows[0])){
        //一番最後の行
        $last_Row = $selected_Rows[0];
        $last_num = $last_Row['post_num'];
        $num = $last_num+1;
      }else{
        $num = 1;
      }
      //INSERT文でデータの入力
      //prepare文で準備
      $sql = $pdo -> prepare("INSERT INTO POSTTABLE (post_num, name, comment, password, post_datetime) 
                              VALUES(:post_num, :name, :comment, :password, :post_datetime)");
      //bindParam()関数で値を入力　execute()関数を用した際に値が確定する
      $sql -> bindParam(':post_num', $num, PDO::PARAM_STR);
      $sql -> bindParam(':name', $name, PDO::PARAM_STR);
      $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
      $sql -> bindParam(':password', $password, PDO::PARAM_STR);
      $sql -> bindParam(':post_datetime', $post_datetime, PDO::PARAM_STR);
      //executeで実行
      $sql -> execute();
      //完了メッセージ
      echo "コメントをデータベースに入力しました";
    }else{
      //エラーメッセージ
      echo "投稿者、コメント、パスワードすべてに入力してください。";
    }
  }


  //削除フォーム
  if(isset($_POST["deleteButton"])){
    //中身が空でない場合
    if(!empty($_POST["deleteNo"]) && !empty($_POST["delete_pass"])){
      //変数を代入
      $deleteNo = $_POST["deleteNo"];
      $delete_pass = $_POST["delete_pass"];
      //passの確認
      //SELECT文で抽出
      $sql = 'SELECT * FROM POSTTABLE WHERE post_num=:deleteNo';
      //prepareで準備
      $stmt = $pdo -> prepare($sql);
      $stmt -> bindParam(':deleteNo', $deleteNo, PDO::PARAM_INT);
      //executeで実行
      $stmt -> execute();
      //取得したレコード列を二重配列にする
      $selected_Rows= $stmt->fetchAll();

      if(!empty($selected_Rows[0])){
        //削除する列を代入
        $get_contents = $selected_Rows[0];
        //パスワードを代入
        $get_Password = $get_contents['password'];
        //パスワードが正しい場合、削除する
        if($get_Password == $delete_pass){
          //DELETEを使い削除する
          $sql = 'DELETE FROM POSTTABLE WHERE post_num=:deleteNo';
          //prepareで準備
          $stmt =$pdo -> prepare($sql);
          $bind = array(':deleteNo' => $deleteNo);
          //executeで実行
          $stmt -> execute($bind);
          //完了メッセージ
          echo "投稿番号". $deleteNo. "が削除されました。";
        }else{
          //エラーメッセージ
          echo "パスワードが違います。";
        }
      }else{
        //エラーメッセージ
        echo "該当する投稿番号がありません。";
      }
    }else{
      //エラーメッセージ
      echo "削除対象番号とパスワードすべてに入力してください。";
    }
  }
  


  //編集されたものがある場合
  if(isset($_POST["editButton"])){
    //中身が空でない場合
    if(!empty($_POST["editNo"]) && !empty($_POST["edit_password"])){
      //変数に代入
      $editNo = $_POST["editNo"];
      $edit_password = $_POST["edit_password"];
      //編集対象番号のそれぞれの列の値を取得
      //SELECT文を使って抽出
      $sql = 'SELECT * FROM POSTTABLE WHERE post_num = :editNo';
      //prepareで準備
      $stmt = $pdo -> prepare($sql);
      $stmt -> bindParam(':editNo', $editNo, PDO::PARAM_INT);
      //executeで実行
      $stmt -> execute();
      //編集番号のレコード列を配列にする
      $selected_Rows= $stmt->fetchAll();

      if(!empty($selected_Rows[0])){
        //該当の行を代入
        $get_contents = $selected_Rows[0];
        //パスワードを代入
        $get_Password = $get_contents['password'];

        //パスワードが正しい場合に実行
        if($get_Password ==  $edit_password){
          //フォームに編集対象番号、投稿者、コメントをセットする
          $set_num = $get_contents['post_num'];
          $set_name = $get_contents['name'];
          $set_comment = $get_contents['comment'];
        }else{
          //エラーメッセージ
          echo "パスワードが違います。";
        }
      }else{
        //エラーメッセージ
        echo "該当する投稿番号がありません。";
      }

    }else{
      //エラーメッセージ
      echo "編集対象番号、パスワードすべてに入力してください。";
    }
  }



?>


<!--html-->
<!DOCTYPE html>
<html lang="ja">
  <head>
   <meta charset="UTF-8"> 
   <title>mission_3-5.php</title>
  </head>
  <body>
     <!--入力フォーム-->
     <h1>投稿</h1>
     <form action = "" method="post">
          <!--入力-->
          投稿番号：
          <input type="text" name="num" value="<?php if(isset($set_num)){echo $set_num;} ?>"><br>
          投稿者：
          <input type="text" name="name" value="<?php if(isset($set_name)){echo $set_name;} ?>"><br>
          投稿内容：
          <input type="text" name="comment" value="<?php if(isset($set_comment)){echo $set_comment;} ?>">
          パスワード：
          <input type="text" name="password">
          <!--送信ボタン-->
          <input type="submit" name="submit"><br>
          <!--削除番号-->
          削除対象番号：
          <input type="text" name="deleteNo">
          パスワード：
          <input type="text" name="delete_pass">
          <!--削除ボタン-->
          <input type="submit" name="deleteButton" value="削除"><br>
          <!--編集対象番号-->
          編集対象番号：
          <input type="text" name="editNo">
          パスワード：
          <input type="text" name="edit_password">
          <!--編集ボタン-->
          <input type="submit" name="editButton" value="編集">
     </form>
     <h2>投稿内容</h2>
  </body>
</html>

<!--php-->
<?php
//◆◆データベース接続◆◆//
$dsn = 'データベース名';
$user = 'ユーザーID';
$password = 'パスワード';
//インスタンス化
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//SELECT文を使って抽出し、表示する
$sql = 'SELECT post_num, name, comment, password, post_datetime FROM POSTTABLE';
$stmt = $pdo -> query($sql);
$results = $stmt -> fetchAll();
foreach($results as $row){
  //$rowの中にカラム名が入る
  echo $row['post_num'].',';
  echo $row['name'].',';
  echo $row['comment'].',';
  echo $row['password'].',';
  echo $row['post_datetime'].'<br>';
  echo "<hr>";

}
?>