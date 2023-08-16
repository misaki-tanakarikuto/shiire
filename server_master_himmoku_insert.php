<?
if($_POST['val_parm']) {

    $retcode = urldecode($_POST['val_parm']);

    $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
die('接続失敗です。'.pg_last_error());

} 

    $customerArr = explode(";", $retcode);

   

    $himmoku_nm = $customerArr[0]; 

    $himmoku_cd = $customerArr[1];

    $ichiba_himmoku_cd = $customerArr[2];

    $hd_himmoku_cd = $customerArr[3];


    $sql = pg_query("SELECT himmoku_cd FROM mst_himmoku  WHERE  himmoku_cd = '".$himmoku_cd."'");
    $row =  pg_fetch_row($sql);

    
    if(0 < $row[0]){
        echo   1;
         }else{
            $sqlString = " INSERT INTO mst_himmoku(	himmoku_cd,	ichiba_himmoku_cd , himmoku_nm) values 
              ('".$himmoku_cd."','".$ichiba_himmoku_cd."','".$himmoku_nm."')";

   $result = pg_query($sqlString);

   if (!$result) {
    error_log("クエリーが失敗しました（品目マスタへの新規登録）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
    die('クエリーが失敗しました（品目マスタへの新規登録）'.pg_last_error());
      }
    }


}