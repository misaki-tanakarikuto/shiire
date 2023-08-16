<?php
// キャッシュさせないための記述
header('Expires: Tue, 1 Jan 2019 00:00:00 GMT');
header('Last-Modified:' . gmdate( 'D, d M Y H:i:s' ) . 'GMT');
header('Cache-Control:no-cache,no-store,must-revalidate,max-age=0');
header('Cache-Control:pre-check=0,post-check=0',false);
header('Pragma:no-cache');

require "./db.php";
require "./tool_function.php";
// require 'common.php';	//共通処理

//表示データの日付（注文購入日）を取得（初期表示時は直近データ日付）
$chumon_konyu_date = "";
if(!empty($_POST["hd_chumon_konyu_date"])){
	$chumon_konyu_date = $_POST["hd_chumon_konyu_date"];
}
 
/*==========================================================================*/
/* 仕入ＤＢからデータを取得                                             */
/*==========================================================================*/
//DB接続
$db_con = db_connect("localhost", "shiiredb", "postgres", "41916");  //ローカルDB(shiiredb)へ接続

//**　部署マスタデータを取得
$mst_busho_arr = getMstBushoKikuChumonGamen();
$mst_busho_arr_json = json_encode($mst_busho_arr);

//**　品目マスタと品目サイズマスタから輪菊のサイズごとの入数（箱に入っている花の本数）を取得 */
$mst_himmoku_size_kiku_irisu_arr = getKikuIrisu();
$mst_himmoku_size_kiku_irisu_arr_json = json_encode($mst_himmoku_size_kiku_irisu_arr);
$kiku_2l_irisu = 0;
$kiku_l_irisu = 0;
$kiku_m_irisu = 0;
foreach($mst_himmoku_size_kiku_irisu_arr as $kiku_irisu_row){
	if($kiku_irisu_row["himmoku_size_cd"] == 1){
		$kiku_2l_irisu = intval($kiku_irisu_row["irisu"]);	//２Ｌ
	}else if($kiku_irisu_row["himmoku_size_cd"] == 2){
		$kiku_l_irisu = intval($kiku_irisu_row["irisu"]);	//Ｌ
	}else if($kiku_irisu_row["himmoku_size_cd"] == 3){
		$kiku_m_irisu = intval($kiku_irisu_row["irisu"]);	//Ｍ
	}
}

//**　セレクトボックス用の「注文購入日」を取得
$pd_chumon_konyu_date_rows = getChumonKonyuDate();

//** 最新の菊注文データを取得
$chumon_arr_json = 0;
$result = getKikuChumonData($chumon_konyu_date);



//===================================================================================
//注文データが無い場合、部署マスタの「菊注文画面で使用」が１の部署の
//注文ゼロのデータを作る
//===================================================================================
if((!count($result))||($result == -2)){
	$chumon_dt_zero_arr = array();
	$i = 0;
	foreach($mst_busho_arr as $mst_busho_row){
		//品目サイズ＝２Ｌのレコードを注文数ゼロで追加
		$chumon_dt_zero_arr[$i]["chumon_konyu_date"] = $chumon_konyu_date;
		$chumon_dt_zero_arr[$i]["himmoku_cd"] = 0;
		$chumon_dt_zero_arr[$i]["himmoku_color_cd"] = 0;
		$chumon_dt_zero_arr[$i]["himmoku_size_cd"] = 1;
		$chumon_dt_zero_arr[$i]["chumonsu"] = 0;	//これは注文する箱の数
		$chumon_dt_zero_arr[$i]["honsu"] = 0;	//「注文本数」を追加しておく（これは注文する花の本数）
		$chumon_dt_zero_arr[$i]["busho_cd"] = $mst_busho_row["busho_cd"];
		$chumon_dt_zero_arr[$i]["busho_nm"] = $mst_busho_row["busho_nm"];
		$chumon_dt_zero_arr[$i]["disp_busho_nm"] = $mst_busho_row["disp_busho_nm"];
		$i++;
		//品目サイズ＝Ｌのレコードを注文数ゼロで追加
		$chumon_dt_zero_arr[$i]["chumon_konyu_date"] = $chumon_konyu_date;
		$chumon_dt_zero_arr[$i]["himmoku_cd"] = 0;
		$chumon_dt_zero_arr[$i]["himmoku_color_cd"] = 0;
		$chumon_dt_zero_arr[$i]["himmoku_size_cd"] = 2;
		$chumon_dt_zero_arr[$i]["chumonsu"] = 0;
		$chumon_dt_zero_arr[$i]["honsu"] = 0;	//「注文本数」を追加しておく
		$chumon_dt_zero_arr[$i]["busho_cd"] = $mst_busho_row["busho_cd"];
		$chumon_dt_zero_arr[$i]["busho_nm"] = $mst_busho_row["busho_nm"];
		$chumon_dt_zero_arr[$i]["disp_busho_nm"] = $mst_busho_row["disp_busho_nm"];
		$i++;
		//品目サイズ＝Ｍのレコードを注文数ゼロで追加
		$chumon_dt_zero_arr[$i]["chumon_konyu_date"] = $chumon_konyu_date;
		$chumon_dt_zero_arr[$i]["himmoku_cd"] = 0;
		$chumon_dt_zero_arr[$i]["himmoku_color_cd"] = 0;
		$chumon_dt_zero_arr[$i]["himmoku_size_cd"] = 3;
		$chumon_dt_zero_arr[$i]["chumonsu"] = 0;
		$chumon_dt_zero_arr[$i]["honsu"] = 0;	//「注文本数」を追加しておく
		$chumon_dt_zero_arr[$i]["busho_cd"] = $mst_busho_row["busho_cd"];
		$chumon_dt_zero_arr[$i]["busho_nm"] = $mst_busho_row["busho_nm"];
		$chumon_dt_zero_arr[$i]["disp_busho_nm"] = $mst_busho_row["disp_busho_nm"];
		$i++;
		//部署ごとの注文本数合計のレコードを追加（画面表示処理のため）
		$chumon_dt_zero_arr[$i]["chumon_konyu_date"] = $chumon_konyu_date;
		$chumon_dt_zero_arr[$i]["himmoku_cd"] = 0;
		$chumon_dt_zero_arr[$i]["himmoku_color_cd"] = 0;
		$chumon_dt_zero_arr[$i]["himmoku_size_cd"] = 0;	//「品目サイズコード」がゼロの場合は注文本数の合計行用のレコードと一覧を作るときに判断させる
		$chumon_dt_zero_arr[$i]["chumonsu"] = 0;
		$chumon_dt_zero_arr[$i]["honsu"] = 0;	//「注文本数」を追加しておく
		$chumon_dt_zero_arr[$i]["busho_honsu_gokei"] = 0;	//一覧中の一番下の行（注文本数合計行）用の列を追加
		$chumon_dt_zero_arr[$i]["busho_cd"] = $mst_busho_row["busho_cd"];
		$chumon_dt_zero_arr[$i]["busho_nm"] = $mst_busho_row["busho_nm"];
		$chumon_dt_zero_arr[$i]["disp_busho_nm"] = $mst_busho_row["disp_busho_nm"];
		$i++;
	}
	unset($mst_busho_row);

	$chumon_arr_json = json_encode($chumon_dt_zero_arr);
//test start
// error_log("注文データが無い\n", 3, 'c:/WWW/shiire/log/app.log');
// error_log(print_r($chumon_dt_zero_arr, true), 3, 'c:/WWW/shiire/log/app.log');
//test end
// }

//===================================================================================
//注文データがある場合
//===================================================================================
}else{
// if($result !== -2){
	$chumon_arr = $result;

	//注文idを取得しておく（hiddenにセットするため）
	$chumon_id = $chumon_arr[0]["chumon_id"];
	
	//ＤＢから取得した菊注文データを調整する。
	//===================================================================================
	//１．部署マスタの「菊注文画面で使用」＝１の部署
	//は全て一覧に表示させる。注文が無くても（注文情報詳細テーブルにデータが無くても）表示させるということ。
	//この画面の処理では一覧表部分は注文データの配列から作られる。
	//だからその配列に１のデータも入れておく必要がある。以下その処理ね。
	//===================================================================================
	$umu = 0;
	$j = 0;
	$tsuikadt_busho_arr = array();
	foreach($mst_busho_arr as $mst_busho_row){
		//部署マスタの部署コードが注文データ配列内にあるかどうか確認
		for($i=0; $i<count($chumon_arr); $i++){
			if($mst_busho_row["busho_cd"] == $chumon_arr[$i]["busho_cd"]){
				$umu = 1;
				break;
			}
		}
		unset($chumon_row);

		//部署マスタの部署コードが注文データ配列内に無かった場合は作る
		if(!$umu){
			//必要な部署が注文データに存在しないので追加する（２Ｌサイズだけ追加しておいてＬとＭは下の処理に任せる）
			// $tsuikadt_busho_arr[$j]["chumon_id"] = $chumon_arr[0]["chumon_id"];
			$tsuikadt_busho_arr[$j]["chumon_konyu_date"] = $chumon_arr[0]["chumon_konyu_date"];
			$tsuikadt_busho_arr[$j]["himmoku_cd"] = $chumon_arr[0]["himmoku_cd"];
			$tsuikadt_busho_arr[$j]["himmoku_color_cd"] = 0;
			$tsuikadt_busho_arr[$j]["himmoku_size_cd"] = 1;
			$tsuikadt_busho_arr[$j]["chumonsu"] = 0;
			$tsuikadt_busho_arr[$j]["honsu"] = 0;	//「注文本数」を追加しておく
			$tsuikadt_busho_arr[$j]["busho_cd"] = $mst_busho_row["busho_cd"];
			$tsuikadt_busho_arr[$j]["busho_nm"] = $mst_busho_row["busho_nm"];
			$tsuikadt_busho_arr[$j]["disp_busho_nm"] = $mst_busho_row["disp_busho_nm"];
			$j++;
		}
		$umu = 0;
	}
	unset($mst_busho_row);

	//ＤＢから取得したデータに一覧表示用の追加データ（注文がゼロだったサイズのデータと部署ごとの注文本数合計のレコード）
	//を加えたデータの配列を作る。
	$wk_chumon_dt_all_busho_arr = array_merge($chumon_arr, $tsuikadt_busho_arr);

	//配列を「部署」「品目サイズ」でソートしておく
	//-列方向の配列を得る
	$busho_cd = array_column($wk_chumon_dt_all_busho_arr, 'busho_cd');
	$himmoku_size_cd = array_column($wk_chumon_dt_all_busho_arr, 'himmoku_size_cd');

	//-$wk_chumon_dt_all_busho_arrのデータを「部署コード」の昇順、「品目サイズ」の昇順にソートする
	array_multisort($busho_cd, SORT_ASC, $himmoku_size_cd, SORT_ASC, $wk_chumon_dt_all_busho_arr);

	//===================================================================================
	//各支店に「２Ｌ」「Ｌ」「Ｍ」のレコードが存在するようにする（ない場合は追加する）。
	//===================================================================================
	$old_busho_cd = $wk_chumon_dt_all_busho_arr[0]["busho_cd"];
	// $old_busho_cd = $chumon_arr[0]["busho_cd"];
	$wk_2l = "";	
	$wk_l = "";
	$wk_m = "";
	$wk_busho_honsu_gokei = 0;	//部署毎の「注文本数」の合計計算用
	$tsuikadt_chumon_arr = array();	//部署ごとの「２Ｌ」「Ｌ」「Ｍ」の注文ゼロの場合のレコードと注文本数合計のレコード
	$j = 0;	//追加レコード配列の添字用
	for($i=0; $i<count($wk_chumon_dt_all_busho_arr); $i++){
		//部署が変わった場合
		if($old_busho_cd !== $wk_chumon_dt_all_busho_arr[$i]["busho_cd"]){
			//品目サイズ＝２Ｌのレコードが無かった部署には２Ｌのレコードを注文数ゼロで追加（画面表示処理のため）
			if(!$wk_2l){
				//部署の[２Ｌ]が無かった場合
				//２Ｌのレコードを追加（「本数」ゼロ）
				chumonZeroRecordAdd($j, $i-1, 1);
				$j++;
			}
			//品目サイズ＝Ｌのレコードが無かった部署にはＬのレコードを注文数ゼロで追加（画面表示処理のため）
			if(!$wk_l){
				chumonZeroRecordAdd($j, $i-1, 2);
				$j++;
			}
			//品目サイズ＝Ｍのレコードが無かった部署にはＭのレコードを注文数ゼロで追加（画面表示処理のため）
			if(!$wk_m){
				chumonZeroRecordAdd($j, $i-1, 3);
				$j++;
			}
			$old_busho_cd = $wk_chumon_dt_all_busho_arr[$i]["busho_cd"];

			//部署ごとの注文本数合計のレコードを追加（画面表示処理のため）
			chumonHonsuGokeiRecAdd($j, $i-1, $wk_busho_honsu_gokei);
			$wk_busho_honsu_gokei = 0;
			$j++;
			$wk_2l = "";
			$wk_l = "";
			$wk_m = "";
		}

		switch($wk_chumon_dt_all_busho_arr[$i]["himmoku_size_cd"]){
			case 1:
				//  「２Ｌ」の場合
				//    ２Ｌのレコードに「注文本数」列を追加し、
				//    chumonsu * (２Ｌサイズひと箱内の本数)の値を入れる。
				$wk_2l = 1;
				$wk_2l_honsu = $wk_chumon_dt_all_busho_arr[$i]["chumonsu"] * $kiku_2l_irisu;
				// $wk_2l_honsu = $wk_chumon_dt_all_busho_arr[$i]["chumonsu"] * 150;
				$wk_chumon_dt_all_busho_arr[$i]["honsu"] = $wk_2l_honsu;
				$wk_busho_honsu_gokei += $wk_2l_honsu;
				
				break;
			case 2:
				//  「Ｌ」の場合
				//    Ｌのレコードに「注文本数」列を追加し、
				//    chumonsu * (Ｌサイズひと箱内の本数)の値を入れる。
				$wk_l = 1;
				$wk_l_honsu = $wk_chumon_dt_all_busho_arr[$i]["chumonsu"] * $kiku_l_irisu;
				// $wk_l_honsu = $wk_chumon_dt_all_busho_arr[$i]["chumonsu"] * 200;
				$wk_chumon_dt_all_busho_arr[$i]["honsu"] = $wk_l_honsu;
				$wk_busho_honsu_gokei += $wk_l_honsu;

				break;
			case 3:
				//  「Ｍ」の場合
				//    Ｍのレコードに「注文本数」列を追加し、
				//    chumonsu * (Ｍサイズひと箱内の本数)の値を入れる。
				$wk_m = 1;
				$wk_m_honsu = $wk_chumon_dt_all_busho_arr[$i]["chumonsu"] * $kiku_m_irisu;
				// $wk_m_honsu = $wk_chumon_dt_all_busho_arr[$i]["chumonsu"] * 250;
				$wk_chumon_dt_all_busho_arr[$i]["honsu"] = $wk_m_honsu;
				$wk_busho_honsu_gokei += $wk_m_honsu;

				break;
		}
	}

	//最後の部署に[２Ｌ]が無かった場合
	//２Ｌのレコードを追加（「本数」ゼロ）
	if(!$wk_2l){
		chumonZeroRecordAdd($j, $i-1, 1);
		$j++;
	}
	//最後の部署に[Ｌ]が無かった場合
	//Ｌのレコードを追加（「本数」ゼロ）
	if(!$wk_l){
		chumonZeroRecordAdd($j, $i-1, 2);
		$j++;
	}
	//最後の部署に[Ｍ]が無かった場合
	//Ｍのレコードを追加（「本数」ゼロ）
	if(!$wk_m){
		chumonZeroRecordAdd($j, $i-1, 3);
		$j++;
	}

	//最後の部署の注文本数合計のレコードを追加（画面表示処理のため）
	chumonHonsuGokeiRecAdd($j, $i-1, $wk_busho_honsu_gokei);

	//ＤＢから取得したデータに一覧表示用の追加データ（注文がゼロだったサイズのデータと部署ごとの注文本数合計のレコード）
	//を加えたデータの配列を作る。
	$wk_lst_dt_arr = array_merge($wk_chumon_dt_all_busho_arr, $tsuikadt_chumon_arr);


	//配列を「部署」「品目サイズ」でソートしておく
	//-列方向の配列を得る
	$busho_cd = array_column($wk_lst_dt_arr, 'busho_cd');
	$himmoku_size_cd = array_column($wk_lst_dt_arr, 'himmoku_size_cd');

	//-$wk_lst_dt_arrのデータを「部署コード」の昇順、「品目サイズ」の昇順にソートする
	array_multisort($busho_cd, SORT_ASC, $himmoku_size_cd, SORT_ASC, $wk_lst_dt_arr);

	$chumon_arr_json = json_encode($wk_lst_dt_arr);
}

//DB切断
db_close($db_con);

/********************************************************************** */
/***  任意の菊サイズ（２Ｌ、Ｌ、Ｍ）のレコードを追加する　　　　　　　　　*/
/***  【引数】　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　*/
/***  　　１．レコード追加用配列の添字　　　　　　　　　　　　　　　　　　*/
/***  　　２．注文データ配列の添字　　　　　　　　　　　　　　　　　　　　*/
/***  　　３．サイズコード　　　　　　　　　　　　　　　　　　　　　　　　*/
/********************************************************************** */
function chumonZeroRecordAdd($i1, $i2, $size){
	global $tsuikadt_chumon_arr, $wk_chumon_dt_all_busho_arr;
	// $tsuikadt_chumon_arr[$i1]["chumon_id"] = $wk_chumon_dt_all_busho_arr[$i2]["chumon_id"];
	$tsuikadt_chumon_arr[$i1]["chumon_konyu_date"] = $wk_chumon_dt_all_busho_arr[$i2]["chumon_konyu_date"];
	$tsuikadt_chumon_arr[$i1]["himmoku_cd"] = $wk_chumon_dt_all_busho_arr[$i2]["himmoku_cd"];
	$tsuikadt_chumon_arr[$i1]["himmoku_color_cd"] = 0;
	$tsuikadt_chumon_arr[$i1]["himmoku_size_cd"] = $size;
	$tsuikadt_chumon_arr[$i1]["chumonsu"] = 0;
	$tsuikadt_chumon_arr[$i1]["honsu"] = 0;	//「注文本数」を追加しておく
	$tsuikadt_chumon_arr[$i1]["busho_cd"] = $wk_chumon_dt_all_busho_arr[$i2]["busho_cd"];
	$tsuikadt_chumon_arr[$i1]["busho_nm"] = $wk_chumon_dt_all_busho_arr[$i2]["busho_nm"];
	$tsuikadt_chumon_arr[$i1]["disp_busho_nm"] = $wk_chumon_dt_all_busho_arr[$i2]["disp_busho_nm"];
}

/********************************************************************** */
/***  注文本数合計のレコードを追加する　　　　　　　　　　　　　　　　　　*/
/***  【引数】　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　*/
/***  　　１．レコード追加用配列の添字　　　　　　　　　　　　　　　　　　*/
/***  　　２．注文データ配列の添字　　　　　　　　　　　　　　　　　　　　*/
/***  　　３．注文本数合計値　　　　　　　　　　　　　　　　　　　　　　　*/
/********************************************************************** */
function chumonHonsuGokeiRecAdd($i1, $i2, $honsu_gokei){
	global $tsuikadt_chumon_arr, $wk_chumon_dt_all_busho_arr;
	// $tsuikadt_chumon_arr[$i1]["chumon_id"] = $wk_chumon_dt_all_busho_arr[$i2]["chumon_id"];
	$tsuikadt_chumon_arr[$i1]["chumon_konyu_date"] = $wk_chumon_dt_all_busho_arr[$i2]["chumon_konyu_date"];
	$tsuikadt_chumon_arr[$i1]["himmoku_cd"] = $wk_chumon_dt_all_busho_arr[$i2]["himmoku_cd"];
	$tsuikadt_chumon_arr[$i1]["himmoku_color_cd"] = 0;
	$tsuikadt_chumon_arr[$i1]["himmoku_size_cd"] = 0;	//「品目サイズコード」がゼロの場合は注文本数の合計行用のレコードと一覧を作るときに判断させる
	$tsuikadt_chumon_arr[$i1]["chumonsu"] = 0;
	$tsuikadt_chumon_arr[$i1]["honsu"] = 0;	//「注文本数」を追加しておく
	$tsuikadt_chumon_arr[$i1]["busho_honsu_gokei"] = $honsu_gokei;	//一覧中の一番下の行（注文本数合計行）用の列を追加
	$tsuikadt_chumon_arr[$i1]["busho_cd"] = $wk_chumon_dt_all_busho_arr[$i2]["busho_cd"];
	$tsuikadt_chumon_arr[$i1]["busho_nm"] = $wk_chumon_dt_all_busho_arr[$i2]["busho_nm"];
	$tsuikadt_chumon_arr[$i1]["disp_busho_nm"] = $wk_chumon_dt_all_busho_arr[$i2]["disp_busho_nm"];
}
  
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>仕入システム</title>

	<!-- bootstrap5のcss　ダウンロードファイルを使用する場合 -->
	<link href="bootstrap5/bootstrap.min.css" rel="stylesheet">
	<!-- bootstrap5のcss ＣＤＮを使用する場合 -->
	<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> -->

	<link href="css/main.css" rel="stylesheet" type="text/css">
	
	<!-- jQuery cdn -->
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<!-- <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script> -->

	<!-- <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css"> -->
	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script> -->
	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script> -->

	<!-- datepicker(カレンダー)日本語化用 -->
	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>	 -->

	
	<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
	<!-- <link rel="stylesheet" href="jquery/jquery-ui.min.css"> -->
	<!-- <link rel="stylesheet" href="jquery/jquery-ui.structure.min.css"> -->
	<!-- <link rel="stylesheet" href="jquery/jquery-ui.theme.min.css"> -->

	<!-- <link rel="stylesheet" href="jquery/jquery-ui.min.css"> -->
	<!-- <link rel="stylesheet" href="/resources/demos/style.css"> -->
	<script src="jquery/jquery-3.6.3.min.js"></script>
	<script src="jquery/jquery-ui.min.js"></script>
	<!-- jQueryのdatepickerを日本語化するためのもの -->
	<script src="jquery/datepicker-ja.js"></script>

	<script>
	//** マスタから取得したデータをjsへ */
	var mst_busho_arr = <?= $mst_busho_arr_json ?>;	//部署マスタ
	var mst_himmoku_size_kiku_irisu_arr = <?= $mst_himmoku_size_kiku_irisu_arr_json ?>;	//輪菊のサイズごとの入数


	//** ＤＢから取得した菊注文データをjsへ
	var chumon_arr = <?= $chumon_arr_json ?>;


	<!-- datepicker用 -->
	$( function() {
		// $( "#new_chumon_konyu_date" ).datepicker();
		$( "#new_chumon_konyu_date" ).datepicker({
			dateFormat: 'yy-mm-dd',
		});
	} );
	</script>

	<script src="js/main.js"></script>
	<script>
		//ブラウザバックしたとき、フォームのキャッシュが残るため
		window.onpageshow = function(){
		//   document.getElementById("form1").reset();
		};

		function init(){
			/***** ヘッダー位置固定 *****/
			disp_head_body();

	    	// setCheckBox();

		}

		//ブラウザリサイズ時のヘッダーの高さ調整
		var timer = false;
		$(window).resize(function() {
		    if (timer !== false) {
		        clearTimeout(timer);
		    }
		    timer = setTimeout(function() {
		        console.log('resized');
					disp_head_body();
		    }, 200);
		});
	</script>
	<style>
  .kakutei_jotai_0 {
    color:black;
  }
  .kakutei_jotai_1 {
    color:blue;
  }
  .kakutei_jotai_2 {
    color:red;
  }
  .record td {
    cursor:pointer;
  }
	</style>
	</head>
	<body onLoad="init()" class="back_color_01">
	<form name="form1" id="form1" method="POST" class="">
		<div class="container sticky-top back_color_01"><!-- 一番外側のコンテナ -->
			<header class="back_color_01" id="headerArea">
				<div class="row">
					<div class="col text_align_left"><?= $login_id ?></div>
					<div class="col-6 text_align_center"><h1>菊注文集計</h1></div>
					<div class="col text_align_right">
						<input type="button" value="ログアウト" class="btn btn-link" id="" onclick="logout()">
					</div>
				</div>

				<div class="row">
					<div class="col text_align_right">
						<input type="button" value="メインメニュー" class="btn btn-outline-secondary" id="" onclick="transition('main_menu.php')" style="margin:0 5px;">
					</div>
				</div>

				<div class="border_disp_max"></div>	<!-- ただの横線 -->

				<div class="row">
					<div class="col-2">
						<select class="form-control" id="disp_chumon_konyu_date" name="disp_chumon_konyu_date" onChange="chgDate()">
<?php
foreach($pd_chumon_konyu_date_rows as $pd_chumon_konyu_date_row){
	if($chumon_konyu_date == $pd_chumon_konyu_date_row['chumon_konyu_date']){
?>
							<option value="<?= $pd_chumon_konyu_date_row['chumon_konyu_date'] ?>" selected><?= $pd_chumon_konyu_date_row['chumon_konyu_date'] ?></option>
<?php
	}else{
?>
							<option value="<?= $pd_chumon_konyu_date_row['chumon_konyu_date'] ?>"><?= $pd_chumon_konyu_date_row['chumon_konyu_date'] ?></option>
<?php
	}
}
unset($pd_jugyoin_row);
?>
						</select>
					</div>
					<div class="col-6 paddinng_top09 text_align_left">
						２Ｌ：<span id="disp_2l_honsu"></span>本、　Ｌ：<span id="disp_l_honsu"></span>本、　Ｍ：<span id="disp_m_honsu"></span>本
					</div>
					<div class="col-4 text_align_right">
						<input id="btn_input_chumon_kuchisu" name="" type="button" value="注文口数入力" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#input_chumon_modal" onclick="">
						<input id="" name="" type="button" value="新規注文" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#add_date_modal" onclick="">
						<input id="" name="" type="button" value="購入ﾃﾞｰﾀ入力画面" class="btn btn-outline-secondary" id="" onclick="KariKakutei()">
					</div>
				</div>
			</header>
		</div>

		<br>

		<div class="container"><!-- 一番外側のコンテナ -->
			<div class="row">
				<div class="col">
					<table id="tbl_lst" class="table table-bordered table-striped table-hover ">
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div><!-- 一番外側のコンテナ -->

		<input type="hidden" id="hd_chumon_id" name="hd_chumon_id" value="<?= $chumon_id ?>">
		<input type="hidden" id="hd_chumon_konyu_date" name="hd_chumon_konyu_date">
		<input type="hidden" id="hd_new_chumon_konyu_date" name="hd_new_chumon_konyu_date">






		<!-- 注文購入日を新規追加するモーダル -->
		<div class="modal fade" id="add_date_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">注文データ新規作成</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">注文購入日を選択して「作成」ボタンをクリックしてください。</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<input type="text" class="form-control" id="new_chumon_konyu_date" name="new_chumon_konyu_date" placeholder="クリックして選択" readonly=”readonly”>
						</div>
						<!-- <div class="col-md-4 ms-auto">.col-md-4 .ms-auto</div> -->
					</div>			
				</div>			



			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
				<button type="button" class="btn btn-primary" id="btn_make_newdate">作　成</button>
				<!-- <button type="button" class="btn btn-primary" onclick="newChumonKonyuDate()">作　成</button> -->
			</div>
			</div>
		</div>
		</div>

		<!-- 注文口数を入力するモーダル（「注文口数入力」ボタンクリック時に表示されるモーダル） -->
		<div class="modal fade" id="input_chumon_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">注文口数入力</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">

				<div class="container-fluid">
					<div class="row margin_t20_b20">
						<div class="col-md-5">

						<select class="form-control" id="busho_sel1" name="busho_sel1" onChange="inputModal1ValueSet()">
						<!-- <select class="form-control" id="busho_sel1" name="busho_sel1" onChange="chgDate()"> -->
<?php
foreach($mst_busho_arr as $mst_busho){
?>
									<option value="<?= $mst_busho['busho_cd'] ?>"><?= $mst_busho['busho_nm'] ?></option>
<?php
}
unset($mst_busho);
?>
							</select>
						</div>
					</div>
					<div class="row margin_t20_b20">
						<div class="col-md-5">
							<input type="tel" class="form-control" id="input_chumonsu_2l" name="input_chumonsu_2l" placeholder="２Ｌサイズの注文数" style="ime-mode: disabled;" onkeydown="return OnlyNumber(event)" oncontextmenu="return false;">
						</div>
					</div>			
					<div class="row margin_t20_b20">
						<div class="col-md-5">
							<input type="tel" class="form-control" id="input_chumonsu_l" name="input_chumonsu_l" placeholder="Ｌサイズの注文数" style="ime-mode: disabled;" onkeydown="return OnlyNumber(event)" oncontextmenu="return false;">
						</div>
					</div>			
					<div class="row margin_t20_b20">
						<div class="col-md-5">
							<input type="tel" class="form-control" id="input_chumonsu_m" name="input_chumonsu_m" placeholder="Ｍサイズの注文数" style="ime-mode: disabled;" onkeydown="return OnlyNumber(event)" oncontextmenu="return false;">
						</div>
					</div>			
				</div>			

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
				<button type="button" class="btn btn-primary" id="btn_make_newdate" onclick="inputChumonKuchiSu()">入　力</button>
			</div>
			</div>
		</div>
		</div>




		<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> -->

		<!-- bootstrap5のjs　ダウンロードファイルを使用する場合 -->
		<script src="bootstrap5/bootstrap.bundle.min.js"></script>
		<!-- bootstrap5のjs　ＣＤＮを使用する場合 -->
		<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->

		<script src="js/kiku_chumon.js"></script>
	    <!-- <script>
		document.getElementById("chumon_konyu_date").focus();
		</script> -->

		<script>
// //========================================================================================================
// //Ajax
// //========================================================================================================
// $(function(){
//     //小窓の「作成」ボタンをクリックしてajax通信を行う
//     $('#btn_make_newdate').click(function(){
// 		/** 日付入力チェック */
// 		var new_date = $('#new_chumon_konyu_date').val();
// 		if(!new_date){
// 			alert("注文購入日を選択してください。");
// 		}else{
// 			/** サーバーへ送るデータを作成する */
// 			var send_data = {'chumon_konyu_date' : new_date};
// 			// var send_data = [{'chumon_konyu_date' : new_date}];
// 			// let send_data = new Array();
// 			// send_data["chumon_konyu_date"] = $("#new_chumon_konyu_date").val();

// 			$.ajax({
// 				url: 'server_kiku_chumon_newdate_insert.php',
// 				// url: '/ajax/test.html',
// 				/* 自サイトのドメインであれば、https://kinocolog.com/ajax/test.html というURL指定も可 */
// 				type: 'POST',
// 				data: {
// 					val_parm: send_data
// 				},
// 				dataType: 'html'  //SON形式のデータとして評価しJavaScriptのオブジェクトに変換
// 				//   dataType: 'json'  //SON形式のデータとして評価しJavaScriptのオブジェクトに変換
// 			}).done(function(data){
// 				/* 通信成功時 */
// 				if(data == 0){
// 					alert(new_date + " の注文データが作成されました。");
// 					transition("kiku_chumon.php");	//菊注文画面をリロード
// 				}else{
// 					alert(data);
// 				}
// 				// $("#return").append('<p>'+data.id+' : '+data.school+' : '+data.skill+'</p>');
// 	//            $('.result').html(data); //取得したHTMLを.resultに反映
				
// 			}).fail(function(jqXHR, textStatus, errorThrown){
// 			// }).fail(function(data){
// 				/* 通信失敗時 */
// 				// alert('通信失敗！:'+ data);
// 				alert('ファイルの取得に失敗しました。');
// 				console.log("ajax通信に失敗しました");
// 				console.log("jqXHR          : " + jqXHR.status); // HTTPステータスが取得
// 				console.log("textStatus     : " + textStatus);    // タイムアウト、パースエラー
// 				console.log("errorThrown    : " + errorThrown.message); // 例外情報
// 				// console.log("URL            : " + url);
						
// 			}).always(function(data) {
// 				/*　通信成功失敗問わず行う処理　*/
// 			});
// 		}
//     });
// });
		</script>
	</body>
	</form>

</html>
<?php
// db_close($link);
?>
