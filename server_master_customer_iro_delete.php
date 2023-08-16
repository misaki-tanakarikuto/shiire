<?php
	if($_POST['val_parm']) {

		$retcode = urldecode($_POST['val_parm']);

		$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
        $link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());

} 

		$customerArr = explode("$", $retcode);
		

		$dempyo_umu_cnt = 0;		//１件でも購入情報品目テーブルに存在したカウントアップ
		$delele_cnt = 0;		//１件でも削除したらカウントアップ
		foreach($customerArr as $customer){
		
			/************************************/
			/***** 購入情報品目テーブル検索 *****/
			/************************************/
			$sqlWhere = " where himmoku_color_cd = ".$customer."";

			//購入情報品目情報を取得
			$sqlString = "SELECT count(*) FROM konyu_himmoku_tbl " . $sqlWhere . "";
			$result = pg_query($sqlString);
			if (!$result) {
				die('クエリーが失敗しました（購入情報品目select）'.pg_last_error());
				print('クエリーが失敗しました（購入情報品目select）<br>');
			}
			$cstm_dt = pg_fetch_array($result);    //

			if($cstm_dt[0]){
				//購入情報品目テーブルに登録されている場合は削除しない
				$dempyo_umu_cnt++;
			}else{
				//購入情報品目テーブルに登録されていない場合は削除する
				$sqlString = "delete from mst_himmoku_color where himmoku_color_cd = " . $customer . ";";
				$result = pg_query($sqlString);

				if (!$result) {
					error_log("クエリーが失敗しました（品目色マスタデータ削除）sql=\n" . $sqlString . "\n" , 3, 'c:/WWW/siire/log/app.log');
					die('クエリーが失敗しました（品目色マスタデータ削除）'.pg_last_error());
				}
				//削除した
				$delele_cnt++;

			}
		}


		$ret_status = "0;".strval($dempyo_umu_cnt).strval($delele_cnt);

		echo $ret_status;

	} else {
		echo 'パラメータエラー';
	}

?>