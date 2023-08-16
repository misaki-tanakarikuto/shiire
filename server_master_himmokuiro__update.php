<?
if($_POST['val_parm']) {

    $retcode = urldecode($_POST['val_parm']);

    $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
die('接続失敗です。'.pg_last_error());

} 

    $customerArr = explode(";", $retcode);

   

    $himmoku_color_nm = $customerArr[0]; 

    $himmoku_color_cd = $customerArr[1];

    $hd_himmoku_colo_cd = $customerArr[2];

    $hd_himmoku_cd = $customerArr[3];

    $sql = pg_query("SELECT himmoku_color_cd FROM mst_himmoku_color  WHERE  himmoku_color_cd = '".$himmoku_color_cd."'");
    $row =  pg_fetch_row($sql);

    if(0 < $row[0]){
        echo   1;
         }else{
            $sqlString =   " UPDATE mst_himmoku_color  SET    
                            himmoku_color_cd = '".$himmoku_color_cd."',
                            himmoku_color_nm = '".$himmoku_color_nm."'
                             WHERE himmoku_color_cd = '".$hd_himmoku_colo_cd."'";
    
                            $result =  pg_query($sqlString);
        if(!$result){
            error_log("クエリーが失敗しました（品目マスタ更新）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
            die('クエリーが失敗しました（品目マスタ更新）'.pg_last_error());
        }
      }
    } 
?>