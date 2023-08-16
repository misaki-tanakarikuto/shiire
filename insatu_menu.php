<?php
$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());
}


$result = pg_query("SELECT	konyusaki_nm FROM mst_konyusaki
where   del_flg = '0'");

$konyu_date = pg_query('SELECT DISTINCT	chumon_konyu_date FROM chumon_tbl  ORDER BY chumon_konyu_date DESC');
?>
<html>
<head><title>菊購入先別注文データ印刷メニュー</title></head>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="bootstrap\css\bootstrap.min.css">

<body>
<div class="container">
 <div class="title_bar"> 品目任意期間比較表</div>
     <form name="form1" action="insatu.php" method="POST">
       <div class="d-flex justify-content-center">
              <select class="row justify-content-center"name="example">
        <?php
        //購入された日付をプルダウンメニューで表示する
          for ($i = 0 ; $i < pg_num_rows($konyu_date) ; $i++){
                 $konyu_date_rows = pg_fetch_array($konyu_date);
        ?>
                 <option ><?=$konyu_date_rows['chumon_konyu_date'] ?></option>
                  <?php     
                   }
                  ?>
     </select>
       </div>
    <br>
<?php //市場一覧ををボタンで表示する
   for ($i = 0 ; $i < pg_num_rows($result) ; $i++){
    $rows = pg_fetch_array($result);
?>
  <div class="p-4 text-center">
 
    <button name="sijou" type="submit" onclick="location.href='insatu.php'" value="<?= $rows['konyusaki_nm'] ?>"><?= $rows['konyusaki_nm'] ?> 市場</button>
  <div>
<?php
}
?>
</form>
</div>
</body>
</html>