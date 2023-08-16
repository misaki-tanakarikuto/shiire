<?   

if($_POST['val_parm']) {

    $retcode = urldecode($_POST['val_parm']);
          
       	//DB接続
    $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
    $link = pg_connect($conn);


    $dataArr = explode("$", $retcode);


       //    削除処理開始

       foreach($dataArr as $dataArrs){
        // 購入情報支店テーブル検索
        $sqlString = "SELECT count(*) FROM konyu_himmoku_siten_tbl where busho_cd = '". $dataArrs ."'";
        $result = pg_query($sqlString);
         
        if (!$result) {
            die('クエリーが失敗しました（部署マスタselect）'.pg_last_error());
            echo('クエリーが失敗しました（部署マスタselect）');


       }
         
       $ret_default_busho    =  pg_fetch_row($result);
             
       if(0 < $ret_default_busho[0]){
        //購入支店テーブルに登録されている場合は削除不可
        $ret_status = 1;
       }else { 
        $sqlString = "delete from mst_busho where busho_id = '" . $dataArrs . "'";
           $result = pg_query($sqlString);

           if (!$result) {
            error_log("クエリーが失敗しました（部署マスタデータ削除）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
            die('クエリーが失敗しました（部署マスタデータ削除）'.pg_last_error());
       }
       $ret_status = 0;
      }
     }
     echo $ret_status;
    }else {
		echo 'パラメータエラー';
	}
?>