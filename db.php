<?php
//include 'define.php';


/****************************************************************/
/***** ＤＢ接続
/****************************************************************/
function db_connect($host, $dbname, $user, $pass){
	// DB接続
	$conn = "host=$host dbname=$dbname port=5432 user=$user password=$pass";
	// $conn = "host=$host dbname=$dbname user=$user password=$pass";
	$link = pg_connect($conn);
	if (!$link) {
		error_log(date("Y/m/d H:i:s") . " : " . $dbname . "　接続失敗\n---\n" . pg_last_error() . "\n---\n" , 3, 'c:/WWW/kyuyo/log/app.log');
		exit($dbname . 'への接続に失敗しました。<br>情報シス担当者はログを確認。');
	}
	//print('接続に成功しました。<br>');
//	//pg_set_client_encoding("sjis");

	return $link;
}

/****************************************************************/
/***** ＤＢ切断
/****************************************************************/
function db_close($db_con){
	$close_flag = pg_close($db_con);

	if (!$close_flag){
	    print('切断に失敗しました。<br>');
	}
}

/****************************************************************/
/***** トランザクション開始処理
/****************************************************************/
function dbTransaction($db_con, $shori_name){
	if (!(pg_query($db_con, "begin"))){
		error_log(date("Y/m/d H:i:s") . " : " . $shori_name . "：トランザクション開始処理失敗" . pg_last_error() . "\n---\n" , 3, 'c:/WWW/kyuyo/log/app.log');
		db_close($db_con);	/* ＤＢ切断 */
		exit($shori_name . "：トランザクション開始処理に失敗しました。情シス担当者はログを確認。");
	}
}

/****************************************************************/
/***** コミット処理
/****************************************************************/
function dbCommit($db_con, $shori_name){
	if (!(pg_query($db_con, "commit"))){
		error_log(date("Y/m/d H:i:s") . " : " . $shori_name . "：コミット処理失敗" . pg_last_error() . "\n---\n" , 3, 'c:/WWW/kyuyo/log/app.log');
		db_close($db_con);	/* ＤＢ切断 */
		exit($shori_name . "：コミット処理に失敗しました。情シス担当者はログを確認。");
	}
}









/****************************************************************/
/***** クエリー失敗時の後処理
/****************************************************************/
function dbAfterQueryErr($db_con, $sqlString, $shori_name){

	error_log(date("Y/m/d H:i:s") . ":" . $shori_name . "失敗\n---\n" . pg_last_error() . "\n---\n" , 3, 'c:/WWW/kyuyo/log/app.log');
	error_log(date("Y/m/d H:i:s") . " : sql = \n" . $sqlString . "\n---\n" , 3, 'c:/WWW/kyuyo/log/app.log');
	db_close($db_con);	/* ＤＢ切断 */
	exit($shori_name . "失敗。　情報シス担当者はログを確認。");
}

/****************************************************************/
/***** ロールバック処理
/****************************************************************/
function dbRollback($db_con, $shori_name){
	if (!(pg_query($db_con, "rollback"))){
		error_log(date("Y/m/d H:i:s") . " : " . $shori_name . "　ロールバック失敗\n---\n" . pg_last_error() . "\n---\n" , 3, 'c:/WWW/kyuyo/log/app.log');
		exit($shori_name . "　ロールバック失敗　情報シス担当者はログを確認。");
	}
}


////////////////////////////////////////////////////
//＜＜菊注文集計画面用＞＞
//注文情報テーブル、注文情報品目テーブル、
//注文情報支店テーブルを取得する
//$chumon_konyu_date:注文購入日
//引数が空の場合は「注文購入日」が最新のデータを返す。
//レコードがない場合は-2を返す。
//それ以外の場合は、引数の「注文購入日」に
//合致するレコードの配列を返す。
////////////////////////////////////////////////////
function getKikuChumonData($chumon_konyu_date){

	//引数が正しくない場合はエラー
	// if(empty($nendo) || empty($staff_code)){
	//   return -1;
	// }

	$sql_sel_joken = "";
	if($chumon_konyu_date){
		$sql_sel_joken = " WHERE ct.chumon_konyu_date = '{$chumon_konyu_date}'";
		// $sql_sel_joken = " ON ct.chumon_konyu_date = '{$chumon_konyu_date}'";
	}else{
		$sql_sel_joken = " WHERE ct.chumon_konyu_date = (SELECT MAX(chumon_konyu_date) FROM chumon_tbl)";
		// $sql_sel_joken = " ON ct.chumon_konyu_date = (SELECT MAX(chumon_konyu_date) FROM chumon_tbl)";
	}

	//「注文購入日」「部署」「品目」「品目色」「品目サイズ」ごとに取得する。
	//　「注文購入日」は１日分を指定、「品目」は菊のみなので、
	//実質「部署」「品目サイズ」ごと、
	//各支店ごとに菊のサイズ３つ分（'2L'、'l'、'M'）のレコードが取得される。
	$sql  = "SELECT";
	$sql .= "   ct.chumon_id";
	$sql .= " , ct.chumon_konyu_date";
	$sql .= " , cht.himmoku_cd";
	$sql .= " , cht.himmoku_color_cd";
	$sql .= " , cht.himmoku_size_cd";
	$sql .= " , sum(cht.chumon_su) chumonsu";
	$sql .= " , cht.busho_cd";
	$sql .= " , mb.busho_nm";
	$sql .= " , mb.disp_busho_nm";
	$sql .= " FROM (chumon_tbl ct";
	$sql .= " LEFT JOIN chumon_shosai_tbl cht";
	// $sql .= " INNER JOIN chumon_shosai_tbl cht";
	// $sql .= $sql_sel_joken;
	$sql .= " ON cht.himmoku_cd = 1";
	$sql .= " AND ct.chumon_id = cht.chumon_id)";

	$sql .= " LEFT JOIN mst_busho mb";
	$sql .= " ON cht.busho_cd = mb.busho_cd";

	$sql .= $sql_sel_joken;

	$sql .= " GROUP BY ct.chumon_id";
	$sql .= " , ct.chumon_konyu_date";
	$sql .= " , cht.busho_cd";
	$sql .= " , mb.busho_nm";
	$sql .= " , mb.disp_busho_nm";
	$sql .= " , cht.himmoku_cd";
	$sql .= " , cht.himmoku_color_cd";
	$sql .= " , cht.himmoku_size_cd";

	$sql .= " ORDER BY ct.chumon_konyu_date";
	$sql .= " , cht.busho_cd";
	$sql .= " , cht.himmoku_cd";
	$sql .= " , cht.himmoku_color_cd";
	$sql .= " , cht.himmoku_size_cd";
	  
	$result = pg_query($sql);
  
//test start
// error_log("取得件数 = ". pg_num_rows($result) . "\n", 3, 'c:/WWW/shiire/log/app.log');
// error_log("sql = ". $sql . "\n", 3, 'c:/WWW/shiire/log/app.log');
// error_log(print_r($chumon_arr, true), 3, 'c:/WWW/shiire/log/app.log');
//test end
	//レコードが0件の場合はエラー
	if(pg_num_rows($result) == 0){
	  return -2;
	}
  
	//レコード取得
	$rows = pg_fetch_all($result);
	// $rows = pg_fetch_all($result, PGSQL_ASSOC);
	// $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

	//取得件数が１件の場合は注文テーブル（chumon_tbl）にデータがあり、
	//注文詳細テーブル（chumon_shosai_tbl）にデータが無い。
	//すなわち、注文データが存在しない状態。この場合はこの関数を
	//呼び元での後の処理のためにその１件を削除しておく。不要だから。
	if((count($rows)==1)&&(!$rows[0]["busho_cd"])){
		$rows = array();
	}
  
	return $rows;
  }


////////////////////////////////////////////////////
//＜＜菊購入データ入力画面用＞＞
//注文情報テーブル、購入情報品目テーブル、
//購入情報支店テーブルを取得する
//$chumon_konyu_date:注文購入日
//引数が空の場合は「注文購入日」が最新のデータを返す。
//レコードがない場合は-2を返す。
//それ以外の場合は、引数の「注文購入日」に
//合致するレコードの配列を返す。
////////////////////////////////////////////////////
function getKikuKonyuData($chumon_konyu_date){

	$sql_sel_joken = "";
	if($chumon_konyu_date){
		$sql_sel_joken = " WHERE ct.chumon_konyu_date = '{$chumon_konyu_date}'";
		// $sql_sel_joken = " ON ct.chumon_konyu_date = '{$chumon_konyu_date}'";
	}else{
		$sql_sel_joken = " WHERE ct.chumon_konyu_date = (SELECT MAX(chumon_konyu_date) FROM chumon_tbl)";
		// $sql_sel_joken = " ON ct.chumon_konyu_date = (SELECT MAX(chumon_konyu_date) FROM chumon_tbl)";
	}

	//ひとつの「注文購入日」の「購入情報支店テーブル」のレコード数分のデータを取得する。
	$sql  = "SELECT";
 	$sql .= "ct.chumon_id";
 	$sql .= " , ct.chumon_konyu_date";
 	$sql .= " , mk.konyusaki_nm";
 	$sql .= " , kht.sanchi_nm1";
 	$sql .= " , kht.sanchi_nm2";
 	$sql .= " , kht.tokyu";
 	$sql .= " , kht.biko";
 	$sql .= " , kht.flower_size";
 	$sql .= " , kht.irisu";
 	$sql .= " , kht.kuchisu";
 	$sql .= " , kht.tanka";
 	$sql .= " , mb.busho_nm ";
 	$sql .= " FROM (((chumon_tbl ct";

	$sql .= " inner join konyu_himmoku_tbl kht";
 	$sql .= " on ct.chumon_id = kht.chumon_id)";

	$sql .= " left join konyu_himmoku_siten_tbl khst";
 	$sql .= " on kht.chumon_id = khst.chumon_id";
	$sql .= " and kht.konyu_himmoku_id = khst.konyu_himmoku_id)";

	$sql .= " left join mst_busho mb";
 	$sql .= " on khst.busho_cd = mb.busho_cd)";

	$sql .= " left join mst_konyusaki mk";
 	$sql .= " on kht.konyusaki_cd = mk.konyusaki_cd";

	$sql .= $sql_sel_joken;
 	$sql .= " ORDER BY ct.chumon_id";
	  
	$result = pg_query($sql);
  
//test start
// error_log("取得件数 = ". pg_num_rows($result) . "\n", 3, 'c:/WWW/shiire/log/app.log');
// error_log("sql = ". $sql . "\n", 3, 'c:/WWW/shiire/log/app.log');
// error_log(print_r($chumon_arr, true), 3, 'c:/WWW/shiire/log/app.log');
//test end
	//レコードが0件の場合はエラー
	if(pg_num_rows($result) == 0){
	  return -2;
	}
  
	//レコード取得
	$rows = pg_fetch_all($result);
	// $rows = pg_fetch_all($result, PGSQL_ASSOC);
	// $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);
  
	return $rows;
  }
  
/****************************************************************/
/***** 部署マスタ情報取得
/***** ［引数］
/***** 　１：なし
/****************************************************************/
function getMstBushoKikuChumonGamen(){

	// $wkWhere  = "";
	// if($whatuse){
	// 	switch($whatuse){
	// 		case 1:
	// 			$wkWhere  = " WHERE use_kiku_chumon = 1";	//菊注文画面で使用するものを取得する
	// 	}
	// }
	$sqlString = "SELECT * FROM mst_busho";
	$sqlString .= " WHERE use_kiku_chumon = 1";
	$sqlString .= " ORDER BY busho_cd;";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}
 
/****************************************************************/
/***** 輪菊のサイズごとの入数（箱に入っている花の本数）を取得
/***** ［引数］
/***** 　１：なし
/****************************************************************/
function getKikuIrisu(){

	$sqlString = "SELECT";
	$sqlString .= " mh.himmoku_cd";
	$sqlString .= " , mh.himmoku_nm";
	$sqlString .= " , mjs.himmoku_size_cd";
	$sqlString .= " , mjs.irisu";
	$sqlString .= " FROM mst_himmoku mh";
	$sqlString .= " LEFT JOIN mst_himmoku_size mjs";
	$sqlString .= " ON mh.himmoku_cd = mjs.himmoku_cd";
	$sqlString .= " AND mh.himmoku_cd = 1";
	$sqlString .= " AND mh.del_flg = 0";
	$sqlString .= " WHERE mjs.del_flg = 0";
	$sqlString .= " ORDER BY mjs.himmoku_size_cd";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
			
}
 
/****************************************************************/
/***** 注文購入日取得
/***** ［引数］
/***** 　１：なし
/****************************************************************/
function getChumonKonyuDate(){

	$sqlString = "SELECT DISTINCT chumon_konyu_date FROM chumon_tbl";
	$sqlString .= " ORDER BY chumon_konyu_date DESC;";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 購入先マスタから購入先情報を取得
/***** ［引数］
/***** 　１：なし
/****************************************************************/
function getKonyusaki(){

	$sqlString = "SELECT konyusaki_cd ,konyusaki_nm";
	$sqlString .= " FROM mst_konyusaki ";
	$sqlString .= " WHERE del_flg = 0";
	$sqlString .= " ORDER BY konyusaki_cd";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

























/****************************************************************/
/***** 年度マスタに指定年度があるかどうか確認する処理
/****************************************************************/
function getNendoMst($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT count(*) FROM mst_nendo" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_row($result);
	return $row;
}

/****************************************************************/
/***** 従業員評価点情報取得
/****************************************************************/
function getNendoData($nendo){
	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM mst_nendo" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** 従業員情報取得
/****************************************************************/
function getJugyoin($nendo, $staff_code = ""){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	if($staff_code){
		$wkWhere .= " AND staff_code = " . $staff_code;
	}
	$sqlString = "SELECT * FROM mst_jugyoin" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 従業員評価点情報取得
/****************************************************************/
function getHyoka($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM hyoka_tbl" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 評価点昇給額マスタ情報取得
/****************************************************************/
function getMstHyokaShokyugaku($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM mst_hyoka_shokyugaku" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 申請用基本給マスタ情報取得
/****************************************************************/
function getMstKihonkyuSinsei($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString  = "SELECT * FROM sinsei_mst_kihonkyu" . $wkWhere;
	$sqlString .= " ORDER BY age;";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 本データ用基本給マスタ情報取得
/****************************************************************/
function getMstKihonkyuHon($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString  = "SELECT * FROM hon_mst_kihonkyu" . $wkWhere;
	$sqlString .= " ORDER BY age;";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** シミュレーション基本給マスタ情報取得
/****************************************************************/
function getMstKihonkyuSimu($nendo, $pattern){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$wkWhere .= " AND pattern_id = '" . $pattern . "'";
	$sqlString  = "SELECT * FROM simu_mst_kihonkyu" . $wkWhere;
	$sqlString .= " ORDER BY age;";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 申請用固定基本給マスタ情報取得
/****************************************************************/
function getMstKoteiKihonkyuSinsei($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString  = "SELECT * FROM sinsei_mst_kotei_kihonkyu" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** 本データ用固定基本給マスタ情報取得
/****************************************************************/
function getMstKoteiKihonkyuHon($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString  = "SELECT * FROM hon_mst_kotei_kihonkyu" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** シミュレーション固定基本給マスタ情報取得
/****************************************************************/
function getMstKoteiKihonkyuSimu($nendo, $pattern){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$wkWhere .= " AND pattern_id = '" . $pattern . "'";
	$sqlString  = "SELECT * FROM simu_mst_kotei_kihonkyu" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** 最低賃金マスタ情報取得
/****************************************************************/
function getMstSaiteiChingin($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM mst_saitei_chingin" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 福岡県の最低賃金マスタ情報取得
/****************************************************************/
function getMstSaiteiChinginFukuoka($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$wkWhere .= " AND todofuken_cd = '" . 40 . "'";
	$sqlString = "SELECT * FROM mst_saitei_chingin" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** 年間休日マスタ情報取得
/****************************************************************/
function getMstNenkankyujitu($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM mst_nenkankyujitu" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** 申請用年間休日マスタ情報取得
/****************************************************************/
function getMstNenkankyujituSinsei($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM sinsei_mst_nenkankyujitu" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** 本データ用年間休日マスタ情報取得
/****************************************************************/
function getMstNenkankyujituHon($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM hon_mst_nenkankyujitu" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** シミュレーション年間休日マスタ情報取得
/****************************************************************/
function getMstNenkankyujituSimu($nendo, $pattern){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$wkWhere .= " AND pattern_id = '" . $pattern . "'";
	$sqlString = "SELECT * FROM simu_mst_nenkankyujitu" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/****************************************************************/
/***** 申請用給与テーブル情報取得
/****************************************************************/
function getJugyoinKyuyoSinsei($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM sinsei_kyuyo_tbl" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 本データ用給与テーブル情報取得
/***** 　引数１：　年度（指定なし可）                           */ 
/****************************************************************/
function getJugyoinKyuyoHon($nendo){

	$wkWhere = "";
	if($nendo){
		$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	}
	$sqlString = "SELECT * FROM hon_kyuyo_tbl" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 本データ用給与テーブル に以下のテーブルを結合して取得
/***** ・従業員マスタ
/***** ・本データ用支店別残業時間テーブル
/***** ・部署マスタ
/***** 　引数１：　年度（指定なし可）                           *
/****************************************************************/
function getJugyoinKyuyoZanHon($nendo){

	$wkWhere = "";
	if($nendo){
		$wkWhere  = " WHERE hkt.nendo = '" . $nendo . "'";
	}
	$sqlString  = "SELECT";
	$sqlString .= " hkt.nendo";
	$sqlString .= ", hkt.staff_code";
	$sqlString .= ", hkt.duties_cd";
	$sqlString .= ", hkt.gekkyu_nempo";
	$sqlString .= ", hkt.shokuseki_kyu";
	$sqlString .= ", hkt.kihon_kyu";
	$sqlString .= ", hkt.kotei_kihon_kyu";
	$sqlString .= ", hkt.kaikin_teate";
	$sqlString .= ", hkt.tosi_teate";
	$sqlString .= ", hkt.shorei_teate";
	$sqlString .= ", hkt.chosei_teate";
	$sqlString .= ", hkt.tenkin_jutaku_teate";
	$sqlString .= ", hkt.tansinfunin_teate";
	$sqlString .= ", hkt.kazoku_teate";
	$sqlString .= ", hkt.korituzangyo_teate";
	$sqlString .= ", hkt.korituzangyo_teate_gaitogaku";
	$sqlString .= ", hkt.nempo_nengaku";
	$sqlString .= ", hkt.nempo_12_14";
	$sqlString .= ", hkt.nempo_shoyo_1";
	$sqlString .= ", hkt.nempo_shoyo_2";
	$sqlString .= ", hkt.shikyugaku_a";
	$sqlString .= ", hkt.zangyo_tanka";
//	$sqlString .= ", z.nendo";
	$sqlString .= ", mj.busho_cd";
	$sqlString .= ", z.tujo_zan_jikan";
	$sqlString .= ", z.sinya_zan_jikan";
	$sqlString .= ", z.kyujitu_rodo_jikan";
	$sqlString .= ", z.kyujitusinya_rodo_jikan";
	$sqlString .= ", z.kyujitusinya_rodo_jikan";
	$sqlString .= ", mb.busho_name";
	$sqlString .= " FROM ((hon_kyuyo_tbl hkt";
	$sqlString .= " LEFT JOIN mst_jugyoin mj";
	$sqlString .= " ON hkt.nendo = mj.nendo";
	$sqlString .= " AND hkt.staff_code = mj.staff_code)";
	$sqlString .= " LEFT JOIN hon_siten_zangyo_jikan_tbl z";	//「本データ用支店別残業時間テーブル」　最新年度（来年度）は残業の実績データがないのでこちらを使う
	$sqlString .= " ON hkt.nendo = z.nendo";
	$sqlString .= " AND mj.busho_cd = z.busho_cd)";
	$sqlString .= " LEFT JOIN mst_busho mb";
	$sqlString .= " ON hkt.nendo = mb.nendo";
	$sqlString .= " AND mj.busho_cd = mb.busho_cd";
	$sqlString .= $wkWhere;
	$sqlString .= " ORDER BY mj.busho_cd;";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 本データ用給与テーブル に以下のテーブルを結合して取得
/***** ・従業員マスタ
/***** ・残業実績テーブル
/***** ・部署マスタ
/***** 　引数１：　年度（指定なし可）                           *
/****************************************************************/
function getJugyoinKyuyoZanJissekiHon($nendo){

	$wkWhere = "";
	if($nendo){
		$wkWhere  = " WHERE hkt.nendo = '" . $nendo . "'";
	}
	$sqlString  = "SELECT";
	$sqlString .= " hkt.nendo";
	$sqlString .= ", hkt.staff_code";
	$sqlString .= ", hkt.duties_cd";
	$sqlString .= ", hkt.gekkyu_nempo";
	$sqlString .= ", hkt.shokuseki_kyu";
	$sqlString .= ", hkt.kihon_kyu";
	$sqlString .= ", hkt.kotei_kihon_kyu";
	$sqlString .= ", hkt.kaikin_teate";
	$sqlString .= ", hkt.tosi_teate";
	$sqlString .= ", hkt.shorei_teate";
	$sqlString .= ", hkt.chosei_teate";
	$sqlString .= ", hkt.tenkin_jutaku_teate";
	$sqlString .= ", hkt.tansinfunin_teate";
	$sqlString .= ", hkt.kazoku_teate";
	$sqlString .= ", hkt.korituzangyo_teate";
	$sqlString .= ", hkt.korituzangyo_teate_gaitogaku";
	$sqlString .= ", hkt.nempo_nengaku";
	$sqlString .= ", hkt.nempo_12_14";
	$sqlString .= ", hkt.nempo_shoyo_1";
	$sqlString .= ", hkt.nempo_shoyo_2";
	$sqlString .= ", hkt.shikyugaku_a";
	$sqlString .= ", hkt.zangyo_tanka";
//	$sqlString .= ", z.nendo";
	$sqlString .= ", mj.busho_cd";
	$sqlString .= ", z.tujo_zan_jikan";
	$sqlString .= ", z.sinya_zan_jikan";
	$sqlString .= ", z.kyujitu_rodo_jikan";
	$sqlString .= ", z.kyujitusinya_rodo_jikan";
	$sqlString .= ", z.kyujitusinya_rodo_jikan";
	$sqlString .= ", mb.busho_name";
	$sqlString .= " FROM ((hon_kyuyo_tbl hkt";
	$sqlString .= " LEFT JOIN mst_jugyoin mj";
	$sqlString .= " ON hkt.nendo = mj.nendo";
	$sqlString .= " AND hkt.staff_code = mj.staff_code)";
	$sqlString .= " LEFT JOIN zangyo_jisseki_tbl z";	//「残業実績テーブル」　前年度以前はこちらを使う
	$sqlString .= " ON hkt.nendo = z.nendo";
	$sqlString .= " AND hkt.staff_code = z.staff_code)";
	$sqlString .= " LEFT JOIN mst_busho mb";
	$sqlString .= " ON hkt.nendo = mb.nendo";
	$sqlString .= " AND mj.busho_cd = mb.busho_cd";
	$sqlString .= $wkWhere;
	$sqlString .= " ORDER BY mj.busho_cd;";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** シミュレーション給与テーブル情報取得
/****************************************************************/
function getJugyoinKyuyoSimu($nendo, $pattern){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$wkWhere .= " AND pattern_id = '" . $pattern . "'";
	$sqlString = "SELECT * FROM simu_kyuyo_tbl" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** シミュレーション用給与テーブル情報取得                   */
/***** 　引数１：　年度（指定なし可）                           */ 
/***** 　引数２：　パターン（指定なし可）                       */ 
/****************************************************************/
function getSimuKyuyo($nendo, $pattern){

	$wkWhere = "";
	if($nendo){
		$wkWhere  = " AND nendo = '" . $nendo . "'";
	}
	if($pattern){
		$wkWhere  = " AND pattern_id = '" . $pattern . "'";
	}
	$wkWhere = " WHERE " . ltrim($wkWhere, " AND");
	$sqlString = "SELECT * FROM simu_kyuyo_tbl" . $wkWhere . ";";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** シミュレーション用給与テーブル ＆ 従業員マスタ情報取得   */
/***** 　引数１：　年度（指定なし可）                           */ 
/***** 　引数２：　パターン（指定なし可）                       */ 
/****************************************************************/
function getSimuKyuyoJugyoin($nendo, $pattern){

	$wkWhere = "";
	if($nendo){
		$wkWhere  = " AND sjkt.nendo = '" . $nendo . "'";
		$wkWhere .= " AND mj.nendo = '" . $nendo . "'";
	}
	if($pattern){
		$wkWhere .= " AND sjkt.pattern_id = " . $pattern;
	}
	if($wkWhere){
		$wkWhere = " WHERE " . ltrim($wkWhere, " AND");
	}
	$sqlString  = "SELECT";
	$sqlString .= " sjkt.staff_code";
	$sqlString .= ",mj.busho_cd";
	$sqlString .= " FROM simu_kyuyo_tbl sjkt";
	$sqlString .= " LEFT JOIN mst_jugyoin mj";
	$sqlString .= " ON sjkt.staff_code = mj.staff_code";
	$sqlString .= $wkWhere . ";";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 申請用給与テーブル ＆ 従業員マスタ情報取得               */
/***** 　引数１：　年度（指定なし可）                           */ 
/****************************************************************/
function getSinseiKyuyoJugyoin($nendo){

	$wkWhere = "";
	if($nendo){
		$wkWhere  = " WHERE skt.nendo = '" . $nendo . "'";
		$wkWhere .= " AND mj.nendo = '" . $nendo . "'";
	}
	$sqlString  = "SELECT";
	$sqlString .= " skt.staff_code";
	$sqlString .= " ,mj.busho_cd";
	$sqlString .= " FROM sinsei_kyuyo_tbl skt";
	$sqlString .= " LEFT JOIN mst_jugyoin mj";
	$sqlString .= " ON skt.staff_code = mj.staff_code";
	$sqlString .= $wkWhere . ";";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 申請用手当マスタから指定年度の手当データを取得
/****************************************************************/
function getTeateSinsei($nendo){

	$sqlString  = "SELECT";
	$sqlString .= " smt.nendo";
	$sqlString .= " ,smt.teate_id";
	$sqlString .= " ,smt.teate_name";
	$sqlString .= " ,smt.yobi_flg";
	$sqlString .= " ,smt.muko_flg";
	$sqlString .= " ,sms.teate_shosai_id";
	$sqlString .= " ,sms.teate_shosai_name";
	$sqlString .= " ,sms.sikyu_gaku";
	$sqlString .= " ,sms.kijungaku_jogen";
	$sqlString .= " ,sms.kijungaku_kagen";
	$sqlString .= " ,sms.duties_cd";
	$sqlString .= " FROM sinsei_mst_teate smt";
	$sqlString .= " LEFT JOIN sinsei_mst_teate_shosai sms";
	$sqlString .= " ON smt.teate_id = sms.teate_id";
	$sqlString .= " AND smt.nendo = sms.nendo";
	$sqlString .= " WHERE smt.nendo = '" . $nendo . "'";
	$sqlString .= " ORDER BY smt.teate_id";
	$sqlString .= " ,sms.teate_shosai_id;";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/*****************************************************************************/
/***** シミュレーション手当マスタから指定年度、指定パターンの手当データを取得
/*****************************************************************************/
function getTeateSimu($nendo, $pattern){

	$sqlString  = "SELECT";
	$sqlString .= " smt.nendo";
	$sqlString .= " ,smt.pattern_id";
	$sqlString .= " ,smt.teate_id";
	$sqlString .= " ,smt.teate_name";
	$sqlString .= " ,smt.yobi_flg";
	$sqlString .= " ,smt.muko_flg";
	$sqlString .= " ,sms.teate_shosai_id";
	$sqlString .= " ,sms.teate_shosai_name";
	$sqlString .= " ,sms.sikyu_gaku";
	$sqlString .= " ,sms.kijungaku_jogen";
	$sqlString .= " ,sms.kijungaku_kagen";
	$sqlString .= " ,sms.duties_cd";
	$sqlString .= " FROM simu_mst_teate smt";
	$sqlString .= " LEFT JOIN (";
	$sqlString .= " SELECT * FROM simu_mst_teate_shosai";
	$sqlString .= " WHERE nendo = '" . $nendo . "') sms";
	$sqlString .= " ON smt.teate_id = sms.teate_id";
	$sqlString .= " AND smt.pattern_id = sms.pattern_id";
	$sqlString .= " WHERE smt.nendo = '" . $nendo . "'";
	$sqlString .= " AND smt.pattern_id = " . $pattern;
	$sqlString .= " ORDER BY smt.pattern_id, smt.teate_id"; 

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 申請用手当マスタから、手当の情報を取得
/****************************************************************/
function getMstTeateSinsei($nendo, $teate_id){

	$wkWhere  = " WHERE smt.nendo = '" . $nendo . "'";
	$wkWhere .= " AND smt.teate_id = " . $teate_id . "";
	$sqlString  = "SELECT * FROM sinsei_mst_teate as smt";
	$sqlString .= " LEFT JOIN sinsei_mst_teate_shosai as smts";
	$sqlString .= " ON smt.teate_id = smts.teate_id" . $wkWhere . ";";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** シミュレーション手当マスタから、手当の情報を取得
/****************************************************************/
function getMstTeateSimu($nendo, $pattern_id, $teate_id){

	$sqlString  = "SELECT";
	$sqlString .= " smt.nendo";
	$sqlString .= " ,smt.pattern_id";
	$sqlString .= " ,smt.teate_id";
	$sqlString .= " ,smt.teate_name";
	$sqlString .= " ,smt.yobi_flg";
	$sqlString .= " ,smt.muko_flg";
	$sqlString .= " ,smts.teate_shosai_id";
	$sqlString .= " ,smts.teate_shosai_name";
	$sqlString .= " ,smts.sikyu_gaku";
	$sqlString .= " ,smts.kijungaku_jogen";
	$sqlString .= " ,smts.kijungaku_kagen";
	$sqlString .= " ,smts.duties_cd";
	$sqlString .= " FROM simu_mst_teate as smt";
	$sqlString .= " LEFT JOIN simu_mst_teate_shosai as smts";
	$sqlString .= " ON smt.teate_id = smts.teate_id";
	$sqlString .= " AND smt.pattern_id = smts.pattern_id";
	$sqlString .= " WHERE smt.nendo = '" . $nendo . "'";
	$sqlString .= " AND smt.pattern_id = " . $pattern_id . "";
	$sqlString .= " AND smt.teate_id = " . $teate_id . "";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 勤務地マスタ情報取得
/****************************************************************/
function getMstKimmuchi($nendo){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM mst_kimmuchi" . $wkWhere;
	$sqlString .= " ORDER BY todofuken_cd;";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** １ヶ月の平均所定労働時間を求める（申請用データ）
/***** （式）
/***** 365　－　72(年間公休数)　＝　293(年間出勤日数)
/***** 293　×　１日の所定労働時間　＝　2051(年間所定労働時間)
/***** 2051　÷　12　＝　１ヶ月の平均所定労働時間
/***** 　※「72（年間公休数）」と「1日の所定労働時間」は、
/***** 　　年間休日マスタの値を使用。
/***** 　※2019-04-26現在の現行値は「170.91」
/****************************************************************/
// function shoteiRodoJikanMonthSinsei($nendo){

// 	//*　年間休日マスタから、指定年度のデータを取得
// 	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
// 	$sqlString = "SELECT * FROM sinsei_mst_nenkankyujitu" . $wkWhere . ";";

// 	$result = pg_query($sqlString);
// 	$row = pg_fetch_array($result);

// 	$wk_nen_shukkin_nissu = 365 - $row["nenkan_kyujitu"];
// 	$wk_nen_shotei_rodo_jikan = $wk_nen_shukkin_nissu * $row["rodo_jikan_per_day"];
// 	$wk_nen_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan / 12;
// 	//少数第三位以降を切り捨て
// 	$wk_nen_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan * 100;
// 	$wk_nen_shotei_rodo_jikan = floor($wk_nen_shotei_rodo_jikan);
// 	$tuki_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan / 100;

// 	return $tuki_shotei_rodo_jikan;
// }

/****************************************************************/
/***** １ヶ月の平均所定労働時間を求める（シミュレーションデータ）
/***** （式）
/***** 365　－　72(年間公休数)　＝　293(年間出勤日数)
/***** 293　×　１日の所定労働時間　＝　2051(年間所定労働時間)
/***** 2051　÷　12　＝　１ヶ月の平均所定労働時間
/***** 　※「72（年間公休数）」と「1日の所定労働時間」は、
/***** 　　年間休日マスタの値を使用。
/***** 　※2019-04-26現在の現行値は「170.91」
/****************************************************************/
// function shoteiRodoJikanMonthSimu($nendo, $pattern){

// 	//*　年間休日マスタから、指定年度のデータを取得
// 	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
// 	$wkWhere .= " AND pattern_id = '" . $pattern . "'";
// 	$sqlString = "SELECT * FROM simu_mst_nenkankyujitu" . $wkWhere . ";";

// 	$result = pg_query($sqlString);
// 	$row = pg_fetch_array($result);

// 	$wk_nen_shukkin_nissu = 365 - $row["nenkan_kyujitu"];
// 	$wk_nen_shotei_rodo_jikan = $wk_nen_shukkin_nissu * $row["rodo_jikan_per_day"];
// 	$wk_nen_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan / 12;
// 	//少数第三位以降を切り捨て
// 	$wk_nen_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan * 100;
// 	$wk_nen_shotei_rodo_jikan = floor($wk_nen_shotei_rodo_jikan);
// 	$tuki_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan / 100;

// 	return $tuki_shotei_rodo_jikan;
// }

/****************************************************************/
/***** １ヶ月の平均所定労働時間を求める
/***** （式）
/***** 365　－　72(年間公休数)　＝　293(年間出勤日数)
/***** 293　×　１日の所定労働時間　＝　2051(年間所定労働時間)
/***** 2051　÷　12　＝　１ヶ月の平均所定労働時間
/***** 　※「72（年間公休数）」と「1日の所定労働時間」は、
/***** 　　年間休日マスタの値を使用。
/***** 　※2019-04-26現在の現行値は「170.91」
/****************************************************************/
function shotei_rodo_jikan_month($nendo){

	//*　年間休日マスタから、指定年度のデータを取得
	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$sqlString = "SELECT * FROM mst_nenkankyujitu" . $wkWhere . ";";

	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);

	$wk_nen_shukkin_nissu = 365 - $row["nenkan_kyujitu"];
	$wk_nen_shotei_rodo_jikan = $wk_nen_shukkin_nissu * $row["rodo_jikan_per_day"];
	$wk_nen_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan / 12;
	//少数第三位以降を切り捨て
	$wk_nen_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan * 100;
	$wk_nen_shotei_rodo_jikan = floor($wk_nen_shotei_rodo_jikan);
	$tuki_shotei_rodo_jikan = $wk_nen_shotei_rodo_jikan / 100;

	return $tuki_shotei_rodo_jikan;
}

/****************************************************************/
/***** 年度マスタから直近年度から指定数分の年度を取得する
/****************************************************************/
function getNendoSiteisu($num){

	$sqlString = "SELECT * FROM mst_nendo ORDER BY nendo DESC LIMIT " . $num . ";";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** kanridb から、指定日に在職中の従業員情報を取得する
/****************************************************************/
function getZaishokuStaff($ymd){

	$sqlString  = "SELECT SI.code, SI.status, SI.retirement, SI.ho_day_total, SI.sex, SI.company_date, SI.photo_name, SI.support, SI.birthday,";
	$sqlString .= " ST.last_name_kanji, ST.first_name_kanji, DAT.section_code, DAT.siten_code, DAT.duties_code,";
	$sqlString .= " DAT.post_code, DAT.employ_code, DAT.sect_name, DAT.duties_name, DAT.post_name, DAT.employ_name";
	$sqlString .= " FROM ( SELECT DISTINCT C.code, C.section_code, C.siten_code, D.duties_code, D.post_code,";
	$sqlString .= " SCT.name AS sect_name, D.employ_code, DT.name AS duties_name, PT.name AS post_name, ET.name AS employ_name";
	$sqlString .= " FROM ( SELECT code, section_code, siten_code, update, RANK() OVER(PARTITION BY code ORDER BY update DESC ) AS rnk1";
	$sqlString .= " FROM list_busho WHERE update <= '" . $ymd . "' ) AS C";
	$sqlString .= " LEFT JOIN section_table AS SCT ON C.section_code = SCT.code";
	$sqlString .= " JOIN (((( SELECT code, duties_code, post_code, employ_code, update,";
	$sqlString .= " RANK() OVER( PARTITION BY code ORDER BY update DESC ) AS rnk2";
	$sqlString .= " FROM list_shokuyaku WHERE update <= '" . $ymd . "' ) as D ";
	$sqlString .= " LEFT JOIN duties_table AS DT ON D.duties_code=DT.code )";
	$sqlString .= " LEFT JOIN post_table AS PT ON D.post_code=PT.code )";
	$sqlString .= " LEFT JOIN employ_table AS ET ON D.employ_code=ET.code ) ON C.code = D.code";
	$sqlString .= " AND C.rnk1 = 1 AND D.rnk2 = 1 ORDER BY C.code ) AS DAT,";
	$sqlString .= " staff_info SI, staff_table ST";
	$sqlString .= " WHERE DAT.code=SI.code AND ST.code=SI.code AND company_date <= '" . $ymd . "'";
	$sqlString .= " AND (status ='1' Or (status='20' AND retirement >= '" . $ymd . "') )";
	$sqlString .= " AND DAT.employ_code >= '1000' AND DAT.duties_code <= 9700 ORDER BY DAT.section_code, DAT.duties_code DESC, SI.code";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** ログインユーザーが管理する部下
/***** （ユーザー管理従業員マスタに登録されいる社員）の情報
/***** を取得する。
/****************************************************************/
function getUserKanriJugyoinSinsei($nendo){

	$sqlString  = "SELECT";
	$sqlString .= " mj.staff_code";
	$sqlString .= ", mj.staff_name";
	$sqlString .= ", mj.busho_cd";
	$sqlString .= ", mb.busho_name";
	$sqlString .= ", skt.shikyugaku_a";
	$sqlString .= ", skt.sagaku_teate";
	$sqlString .= ", skt.kakutei_jotai";

	$sqlString .= ", (skt.shikyugaku_a * 12) soshikyugaku";
	$sqlString .= ", (skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) shikyu_getugaku";
	$sqlString .= ", ((skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) * 12) shikyu_nengaku";

	$sqlString .= ", (CASE WHEN skt.kakutei_jotai = " . MIKAKUTEI_CODE . " THEN '" . MIKAKUTEI . "' ELSE";
	$sqlString .= " (CASE WHEN skt.kakutei_jotai = " . KARIKAKUTEI_CODE . " THEN '" . KARIKAKUTEI . "' ELSE";
	$sqlString .= " (CASE WHEN  skt.kakutei_jotai = " . KAKUTEI_CODE . " THEN '" . KAKUTEI . "' ELSE '' END) END) END) kakutei";
	$sqlString .= ", (CASE WHEN mj2.staff_name IS NOT NULL THEN mj2.staff_name ELSE '' END) update_user_name";
	$sqlString .= " FROM ((mst_jugyoin as mj";
	$sqlString .= " LEFT JOIN sinsei_kyuyo_tbl AS skt";
	$sqlString .= " ON mj.staff_code = skt.staff_code)";
	$sqlString .= " LEFT JOIN mst_busho AS mb";
	$sqlString .= " ON mj.busho_cd = mb.busho_cd)";
	$sqlString .= " LEFT JOIN ( SELECT nendo, staff_code, staff_name FROM mst_jugyoin WHERE nendo = '" . $nendo . "' ) as mj2";
	$sqlString .= " ON skt.update_user = mj2.staff_code";

	$sqlString .= " WHERE mj.status = 0";
	$sqlString .= " AND mj.nendo = '" . $nendo . "'";
	$sqlString .= " AND mj.duties_cd <= 8000";
	$sqlString .= " AND mj.kanrisha_staff_code = " . $_SESSION['staff_code'];
	$sqlString .= " AND mj.status = 0";
	$sqlString .= " AND skt.nendo = '" . $nendo . "'";
	$sqlString .= " AND mb.nendo = '" . $nendo . "'";
	$sqlString .= " ORDER BY mj.busho_cd, mj.staff_code";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** ログインユーザーが管理する部署
/***** を取得する。
/****************************************************************/
function getUserKanriBusho($nendo){

	$sqlString  = "SELECT";
	$sqlString .= " busho_cd";
	$sqlString .= " ,busho_name";
	$sqlString .= " FROM";
	$sqlString .= " mst_busho";
	$sqlString .= " WHERE";
	$sqlString .= " nendo = '" . $nendo . "'";
	$sqlString .= " AND kanrisha_staff_code = " . $_SESSION['staff_code'];
	$sqlString .= " ORDER BY busho_cd";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 以下の情報を取得する
/***** 　１．ログインユーザーが管理する部署の従業員（正社員）
/***** 　２．ログインユーザーが管理する部署の子部署の従業員（正社員）
/****************************************************************/
function getUserKanriBushoJugyoinSinsei($nendo){
	//------------------------------------------------------------------------
	//引数で指定された年度のログインユーザーが管理者となっている部署を取得する
	//------------------------------------------------------------------------
	$sqlString  = "SELECT busho_cd";
	$sqlString .= " FROM mst_busho";
	$sqlString .= " WHERE nendo = '" . $nendo . "'";
	$sqlString .= " AND kanrisha_staff_code = " . $_SESSION['staff_code'];

	$result = pg_query($sqlString);
	$kanri_busho_rows = pg_fetch_all($result);

	//ログインユーザーが管理する部署が無い場合はfalseを返す
	if(empty($kanri_busho_rows)){
		return $kanri_busho_rows;
	}

	//---------------------------------------------------------------------------------------------
	//上で取得した部署が親部署となっている部署を取得（ログインユーザーが管理する部署の子孫部署を取得）
	//---------------------------------------------------------------------------------------------
	$all_busho_arr = array();
	getShisonBushoAll($nendo, $all_busho_arr, $kanri_busho_rows);


	//---------------------------------------------------------------------------------------------
	//ログインユーザーが管理する部署（子孫含む）に所属する従業員の情報を取得する
	//---------------------------------------------------------------------------------------------
	$sqlWhere = "";
	//ログインユーザーが管理する部署を検索条件としてセット
	foreach($all_busho_arr as $all_busho){
		$sqlWhere .= " OR mj.busho_cd = " . $all_busho["busho_cd"];
	}
	unset($all_busho);

	$sqlWhere = ltrim($sqlWhere, ' OR');
	$sqlWhere = " AND (" . $sqlWhere . " )";

	$sqlString  = "SELECT";
	$sqlString .= " mj.staff_code";
	$sqlString .= ", mj.staff_name";
	$sqlString .= ", mb.busho_name";
	$sqlString .= ", mb.busho_cd";
	$sqlString .= ", skt.shikyugaku_a";
	$sqlString .= ", skt.sagaku_teate";
	$sqlString .= ", skt.kakutei_jotai";

	$sqlString .= ", (skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) shikyu_getugaku";
	$sqlString .= ", ((skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) * 12) shikyu_nengaku";

	$sqlString .= ", (skt.shikyugaku_a * 12) soshikyugaku";
	$sqlString .= ", (CASE WHEN skt.kakutei_jotai = " . MIKAKUTEI_CODE . " THEN '" . MIKAKUTEI . "' ELSE";
	$sqlString .= " (CASE WHEN skt.kakutei_jotai = " . KARIKAKUTEI_CODE . " THEN '" . KARIKAKUTEI . "' ELSE";
	$sqlString .= " (CASE WHEN  skt.kakutei_jotai = " . KAKUTEI_CODE . " THEN '" . KAKUTEI . "' ELSE '' END) END) END) kakutei";
	$sqlString .= " ,(CASE WHEN mj2.staff_name IS NOT NULL THEN mj2.staff_name ELSE '' END) update_user_name";

	$sqlString .= " FROM ((mst_jugyoin as mj";
	$sqlString .= " LEFT JOIN sinsei_kyuyo_tbl AS skt";
	$sqlString .= " ON mj.staff_code = skt.staff_code)";
	$sqlString .= " LEFT JOIN mst_busho AS mb";
	$sqlString .= " ON mj.busho_cd = mb.busho_cd)";
	$sqlString .= " LEFT JOIN ( SELECT nendo, staff_code, staff_name FROM mst_jugyoin WHERE nendo = '" . $nendo . "' ) as mj2";
	$sqlString .= " ON skt.update_user = mj2.staff_code";

	$sqlString .= " WHERE mj.status = 0";
	$sqlString .= " AND mj.nendo = '" . $nendo . "'";
	$sqlString .= " AND mj.duties_cd <= 8000";
	$sqlString .= " AND mj.status = 0";
	$sqlString .= $sqlWhere;
	$sqlString .= " AND skt.nendo = '" . $nendo . "'";
	$sqlString .= " AND mb.nendo = '" . $nendo . "'";
	$sqlString .= " ORDER BY mj.busho_cd, mj.staff_code";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}
/****************************************************************/
/***** 以下の情報を取得する
/***** 　第三引数（配列）に入っている部署コードを親に持つ部署を
/***** 　子孫部署全て取得する
/****************************************************************/
function getShisonBushoAll($nendo, &$all_busho_arr, $oyabusho_rows){
	
	$whereString = " WHERE nendo = '" . $nendo . "'";
	$whereString_busho = "";
	foreach($oyabusho_rows as $oyabusho_row){
		array_push($all_busho_arr, $oyabusho_row);		//結果配列

		if(!$whereString_busho){
			$whereString_busho = " AND (( oya_busho_cd = " . $oyabusho_row["busho_cd"] . " )";
		}
		$whereString_busho .= " OR ( oya_busho_cd = " . $oyabusho_row["busho_cd"] . " )";
	}
	unset($oyabusho_row);
	$whereString .= $whereString_busho . " )";

	$sqlString  = "SELECT busho_cd";
	$sqlString .= " FROM mst_busho";
	$sqlString .= $whereString;

	$result = pg_query($sqlString);

	//子部署が存在した場合
	if(pg_num_rows($result) != '0'){
		$kobusho_array = array();
		while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
			array_push($kobusho_array,$rows);
		}
	  //自分自身を実行する(再帰処理)
	  getShisonBushoAll($nendo, $all_busho_arr, $kobusho_array);
	}
}

/****************************************************************/
/***** 職責マスタを取得する
/****************************************************************/
function getMstShokuseki($nendo){

	$sqlString  = "SELECT *";
	$sqlString .= " FROM";
	$sqlString .= " mst_shokuseki";
	$sqlString .= " WHERE";
	$sqlString .= " nendo = '" . $nendo . "'";
	$sqlString .= " ORDER BY duties_cd";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 本データ用職責・職責給マスタを取得する
/****************************************************************/
function getMstShokusekiHon($nendo){

	$sqlString  = "SELECT *";
	$sqlString .= " FROM";
	$sqlString .= " hon_mst_shokuseki";
	$sqlString .= " WHERE";
	$sqlString .= " nendo = '" . $nendo . "'";
	$sqlString .= " ORDER BY duties_cd";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 本データ用職責・職責給マスタを取得する
/***** 執行役員以下（duties_cd <= 9700）を取得
/****************************************************************/
function getMstShokusekiHonSkyakuinIka($nendo){

	$sqlString  = "SELECT *";
	$sqlString .= " FROM";
	$sqlString .= " hon_mst_shokuseki";
	$sqlString .= " WHERE";
	$sqlString .= " nendo = '" . $nendo . "'";
	$sqlString .= " AND duties_cd <= 9700";
	$sqlString .= " ORDER BY duties_cd";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 申請用職責・職責給マスタを取得する
/***** 部長代理以下（duties_cd <= 8000）を取得
/****************************************************************/
function getMstShokusekiSinseiBudaiIka($nendo){

	$sqlString  = "SELECT *";
	$sqlString .= " FROM";
	$sqlString .= " sinsei_mst_shokuseki";
	$sqlString .= " WHERE";
	$sqlString .= " nendo = '" . $nendo . "'";
	$sqlString .= " AND duties_cd <= 8000";
	$sqlString .= " ORDER BY duties_cd";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** シミュレーション職責・職責給マスタを取得する
/***** 部長代理以下（duties_cd <= 8000）を取得
/****************************************************************/
function getMstShokusekiSimuBudaiIka($nendo, $pattern_id){

	$sqlString  = "SELECT *";
	$sqlString .= " FROM";
	$sqlString .= " simu_mst_shokuseki";
	$sqlString .= " WHERE nendo = '" . $nendo . "'";
	$sqlString .= " AND pattern_id = " . $pattern_id;
	$sqlString .= " AND duties_cd <= 8000";
	$sqlString .= " ORDER BY duties_cd";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 支店別残業時間テーブル情報取得
/****************************************************************/
function getSitenZangyoJikanSimu($nendo, $pattern_id, $busho_cd){

	$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	$wkWhere .= " AND pattern_id = " . $pattern_id;
	$wkWhere .= " AND busho_cd = " . $busho_cd;
	$sqlString = "SELECT * FROM simu_siten_zangyo_jikan_tbl" . $wkWhere;
	$sqlString .= " ORDER BY busho_cd;";

	$result = pg_query($sqlString);

	//レコードが0件の場合はエラー
	if(pg_num_rows($result) == 0){
		return -2;
	}

	$row = pg_fetch_array($result);

	return $row;
}

/****************************************************************/
/***** シミュレーションパターンマスタ情報取得
/****************************************************************/
function getSimPatternLimitRangeByYear($nendo_min, $nendo_max){

	$wkWhere  = " WHERE '" . $nendo_min . "' <= nendo";
	$wkWhere .= " AND nendo <= '" . $nendo_max . "'";
	$sqlString = "SELECT * FROM simu_mst_pattern" . $wkWhere;
	$sqlString .= " ORDER BY nendo, pattern_id;";
//	$sqlString .= " ORDER BY nendo, pattern_id DESC;";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/**************************************************************************/
/***** kanridbの　勤務地マスタ情報取得（支店、事務所等、出張所、ＳＨＯＰ）
/**************************************************************************/
function getSitenMasterTable(){

	$sqlString  = "SELECT sitencode, sitenname, todofuken_cd";
	$sqlString .= " FROM sitenmastertable";
	$sqlString .= " WHERE status = 1";
	$sqlString .= " AND kind <= 10";	//支店、事務所等、出張所、ＳＨＯＰ
	$sqlString .= " ORDER BY sitencode";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/**************************************************************************/
/***** kyuyodbの　コード管理マスタ情報取得
/**************************************************************************/
function getMstCode($nendo){

	$sqlString = "SELECT * FROM mst_code_kanri WHERE nendo = '" . $nendo . "';";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/**************************************************************************/
/***** kyuyodbの　給与按分マスタ情報取得
/**************************************************************************/
function getMstKyuyoAmbun($nendo){

	$sqlString = "SELECT * FROM mst_kyuyo_ambun WHERE nendo = '" . $nendo . "';";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/**************************************************************************/
/***** kyuyodbの　シミュレーションパターンマスタ情報取得
/**************************************************************************/
function getSimuMstPattern($nendo){

	$sqlString = "SELECT * FROM simu_mst_pattern WHERE nendo = '" . $nendo . "';";

	$result = pg_query($sqlString);
	$row = pg_fetch_array($result);
	return $row;
}

/**************************************************************************/
/***** kyuyodbの　手当マスタ情報取得
/**************************************************************************/
function getMstTeateHon($nendo){

	$sqlString = "SELECT * FROM hon_mst_teate WHERE nendo = '" . $nendo . "';";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/**************************************************************************/
/***** kyuyodbの　手当マスタ情報取得
/**************************************************************************/
function getMstTeateShosaiHon($nendo){

	$sqlString = "SELECT * FROM hon_mst_teate_shosai WHERE nendo = '" . $nendo . "';";

	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;

}

/****************************************************************/
/***** 残業実績テーブル情報取得
/***** 　引数１：　年度（指定なし可）                           */ 
/****************************************************************/
function getZangyoJisseki($nendo=""){

	$wkWhere = "";
	if($nendo){
		$wkWhere  = " WHERE nendo = '" . $nendo . "'";
	}
	$sqlString = "SELECT * FROM zangyo_jisseki_tbl" . $wkWhere . ";";
	$result = pg_query($sqlString);
	$rows = pg_fetch_all($result);
	return $rows;
}

/****************************************************************/
/***** 本データ用支店別残業時間テーブル情報取得
/****************************************************************/
function getSitenZangyoJikanHon($nendo="", $busho_cd=""){

	$wkWhere = "";
	if($nendo){
		$wkWhere  = " nendo = '" . $nendo . "'";
	}
	if($busho_cd){
		if($wkWhere){
			$wkWhere .= " AND";
		}
		$wkWhere .= " busho_cd = " . $busho_cd;
	}
	$wkWhere = " WHERE" . $wkWhere;
	
	$sqlString = "SELECT * FROM hon_siten_zangyo_jikan_tbl" . $wkWhere;
	$sqlString .= " ORDER BY busho_cd;";

	$result = pg_query($sqlString);

	//レコードが0件の場合はエラー
	if(pg_num_rows($result) == 0){
		return -2;
	}

	$row = pg_fetch_array($result);

	return $row;
}


?>
