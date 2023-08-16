<?php 
   
   if($_POST['val_parm']) {
    $retcode = urldecode($_POST['val_parm']);
    	//DB接続
   $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
   $link = pg_connect($conn);
                         
   $dataArray = explode(";", $retcode);


   $busho_cd = $dataArray[2];

   $ichiba_busho_cd = $dataArray[3];

   $busho_nm = $dataArray[4];

   $disp_busho_nm = $dataArray[5];

   $sql = pg_query("SELECT busho_cd FROM mst_busho  WHERE  busho_cd = '".$busho_cd."'");
   $row =  pg_fetch_row($sql);
      
      if(0 < $row[0]){
        echo "その部署コードは既に登録されています";
         }else{
   $sqlString = " INSERT INTO mst_busho(	busho_cd,	ichiba_busho_cd , busho_nm,	disp_busho_nm) values 
   ('".$busho_cd."','".$ichiba_busho_cd."','".$busho_nm."','".$disp_busho_nm."')";

   $result = pg_query($sqlString);

   if (!$result) {
    error_log("クエリーが失敗しました（部署マスタへの新規登録）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
    die('クエリーが失敗しました（部署マスタへの新規登録）'.pg_last_error());
      }
    }
  }
?>