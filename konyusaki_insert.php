<?php 
   
   if($_POST['val_parm']) {
    $retcode = urldecode($_POST['val_parm']);
    	//DB接続
   $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
   $link = pg_connect($conn);
    
   $dataArray = explode(";", $retcode);

         
   $konyusaki_cd = $dataArray[1];

   $old_konyusaki_cd = $dataArray[2];

   $konyusaki_nm = $dataArray[3];

   $sql = pg_query("SELECT konyusaki_cd FROM mst_konyusaki  WHERE  konyusaki_cd = '".$konyusaki_cd."'");
   $row =  pg_fetch_row($sql);
      
      if(0 < $row[0]){
        echo "その購入先コードは既に登録されています";
         }else{
   $sqlString = " INSERT INTO mst_konyusaki(	konyusaki_cd,	old_konyusaki_cd , konyusaki_nm) values 
   ('".$konyusaki_cd."','".$old_konyusaki_cd."','".$konyusaki_nm."')";

   $result = pg_query($sqlString);

   if (!$result) {
    error_log("クエリーが失敗しました（購入先マスタへの新規登録）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
    die('クエリーが失敗しました（購入先マスタへの新規登録）'.pg_last_error());
      }
    }
  }
?>