<?php
// キャッシュさせないための記述
header('Expires: Tue, 1 Jan 2019 00:00:00 GMT');
header('Last-Modified:' . gmdate( 'D, d M Y H:i:s' ) . 'GMT');
header('Cache-Control:no-cache,no-store,must-revalidate,max-age=0');
header('Cache-Control:pre-check=0,post-check=0',false);
header('Pragma:no-cache');

require "./db.php";
require "./tool_function.php";
require 'common.php';	//共通処理

//表示データの日付（注文購入日）を取得（初期表示時は直近データ日付）
$chumon_konyu_date = "";
if(!empty($_POST["hd_chumon_konyu_date"])){
	$chumon_konyu_date = $_POST["hd_chumon_konyu_date"];
}

//表示データの購入先コードを取得（初期表示時は空）
$konyusaki_cd = "";
if(!empty($_POST["hd_konyusaki_cd"])){
	$konyusaki_cd = $_POST["hd_konyusaki_cd"];
}
 
/*==========================================================================*/
/* 仕入ＤＢからデータを取得                                             */
/*==========================================================================*/
//DB接続
$db_con = db_connect("localhost", "shiiredb", "postgres", "41916");  //ローカルDB(shiiredb)へ接続

//**　部署マスタデータを取得
$mst_busho_arr = getMstBushoKikuChumonGamen();
$mst_busho_arr_json = json_encode($mst_busho_arr);

//**　セレクトボックス用の「注文購入日」を取得
$pd_chumon_konyu_date_rows = getChumonKonyuDate();

//**　セレクトボックス用の購入先情報を取得
$pd_konyusaki_rows = getKonyusaki();

//** 最新の菊購入データを取得
$konyu_rows = getKikuKonyuData($chumon_konyu_date);
$konyu_rows_json = json_encode($konyu_rows);

//DB切断
db_close($db_con);
  
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

	//** ＤＢから取得した菊購入データをjsへ
	var konyu_rows = <?= $konyu_rows_json ?>;


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
					<div class="col-6 text_align_center"><h1>菊購入データ入力</h1></div>
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

				<div class="row margin_t10_b10">
					<div class="col-4">
					</div>
					<div class="col-4 paddinng_top09 text_align_left">
						　　　注文本数：<span id="disp_2l_honsu">
					</div>
					<!-- <div class="col-4 text_align_right">
						<input id="btn_input_chumon_kuchisu" name="" type="button" value="PDF出力" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#input_chumon_modal" onclick="">
						<input id="btn_i" name="" type="button" value="購入内容印刷" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#input_chumon_modal" onclick="">
					</div> -->
				</div>

				<div class="row margin_t10_b10">
					<div class="col-4">
					</div>
					<div class="col-4 paddinng_top09 text_align_left">
						　　　購入本数：<span id="disp_2l_honsu">
					</div>
					<div class="col-4 text_align_right">
						<input id="btn_input_chumon_kuchisu" name="" type="button" value="PDF出力" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#input_chumon_modal" onclick="">
						<input id="btn_i" name="" type="button" value="購入内容印刷" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#input_chumon_modal" onclick="">
					</div>
				</div>

				<div class="row margin_t10_b10">
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
					<div class="col-2">
						<select class="form-control" id="disp_konyusaki" name="disp_konyusaki" onChange="chgDate()">
							<option value="" selected></option>
<?php
foreach($pd_konyusaki_rows as $pd_konyusaki_row){
	if($konyusaki_cd == $pd_konyusaki_row['konyusaki_cd']){
?>
							<option value="<?= $pd_konyusaki_row['konyusaki_cd'] ?>" selected><?= $pd_konyusaki_row['konyusaki_nm'] ?></option>
<?php
	}else{
?>
							<option value="<?= $pd_konyusaki_row['konyusaki_cd'] ?>"><?= $pd_konyusaki_row['konyusaki_nm'] ?></option>
<?php
	}
}
unset($pd_jugyoin_row);
?>
						</select>
					</div>
					<div class="col-4 paddinng_top09 text_align_left">
						　　　中　　値：<span id="disp_2l_honsu">
					</div>
					<div class="col-4 text_align_right">
						<input id="" name="" type="button" value="行削除" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#add_date_modal" onclick="">
						<input id="" name="" type="button" value="行追加" class="btn btn-outline-secondary" id="" onclick="KariKakutei()">
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
		<input type="hidden" id="hd_konyusaki_cd" name="hd_konyusaki_cd">





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
