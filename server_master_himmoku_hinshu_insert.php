<?
   if($_POST['val_parm']) {
    $retcode = urldecode($_POST['val_parm']);
    	//DB接続
   $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
   $link = pg_connect($conn);
    
   $dataArray = explode(";", $retcode);
     
   $himmoku_hinshu_nm = $dataArray[0];

   $himmoku_hinshu_cd = $dataArray[1];


   $hd_himmoku_cd = $dataArray[3];


   $sql = pg_query("SELECT  hinshu_cd FROM mst_hinshu  WHERE  hinshu_cd = '".$himmoku_hinshu_cd."'");
   $row =  pg_fetch_row($sql);

   
   if(0 < $row[0]){
       echo   1;
        }else{
            $sqlString = " INSERT INTO mst_hinshu (	himmoku_cd,	hinshu_cd , hinshu_nm) values 
            ('".$hd_himmoku_cd."','".$himmoku_hinshu_cd."','".$himmoku_hinshu_nm."')";

 $result = pg_query($sqlString);

 if (!$result) {
  error_log("クエリーが失敗しました（品種マスタへの新規登録）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
  die('クエリーが失敗しました（品種マスタへの新規登録）'.pg_last_error());
    }
  }
}
?>