<?
if($_POST['val_parm']) {

    $retcode = urldecode($_POST['val_parm']);

    $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
die('接続失敗です。'.pg_last_error());

} 

    $customerArr = explode(";", $retcode);

   

    $himmoku_sizu_nm = $customerArr[0]; 

    $himmoku_sizu_cd = $customerArr[1];

    $hd_himmoku_sizu_cd = $customerArr[2];

    $hd_himmoku_cd = $customerArr[3];

    $himmoku_irisu = $customerArr[4];

    $sql = pg_query("SELECT himmoku_size_cd FROM mst_himmoku_size  WHERE  himmoku_size_cd = '".$himmoku_sizu_cd."'");
    $row =  pg_fetch_row($sql);

    if(0 < $row[0]){
        echo   1;
         }else{
            $sqlString =   " UPDATE mst_himmoku_size  SET    
                            himmoku_size_cd = '".$himmoku_sizu_cd."',
                            himmoku_size_nm = '".$himmoku_sizu_nm."',
                            irisu = '".$himmoku_irisu."'
                             WHERE himmoku_size_cd = '".$hd_himmoku_sizu_cd."'";
    
                            $result =  pg_query($sqlString);
        if(!$result){
            error_log("クエリーが失敗しました（品目サイズマスタ更新）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
            die('クエリーが失敗しました（品目サイズマスタ更新）'.pg_last_error());
        }
      }
    } 
?>