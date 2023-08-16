<?
   if($_POST['val_parm']) {
    $retcode = urldecode($_POST['val_parm']);
    	//DB接続
   $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
   $link = pg_connect($conn);
    
   $dataArray = explode(";", $retcode);
     
   $himmoku_size_nm = $dataArray[0];

   $himmoku_size_cd = $dataArray[1];

   $hd_himmoku_cd = $dataArray[3];

   $irisu = $dataArray[4];


   $sql = pg_query("SELECT himmoku_size_cd FROM mst_himmoku_size  WHERE  himmoku_size_cd = '".$himmoku_size_cd."'");
   $row =  pg_fetch_row($sql);

   
   if(0 < $row[0]){
       echo   1;
        }else{
            $sqlString = " INSERT INTO mst_himmoku_size (	himmoku_cd,	himmoku_size_cd , himmoku_size_nm, irisu) values 
            ('".$hd_himmoku_cd."','".$himmoku_size_cd."','".$himmoku_size_nm."','".$irisu."')";

 $result = pg_query($sqlString);

 if (!$result) {
  error_log("クエリーが失敗しました（品目サイズマスタへの新規登録）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
  die('クエリーが失敗しました（品目サイズマスタへの新規登録）'.pg_last_error());
    }
  }
}
?>