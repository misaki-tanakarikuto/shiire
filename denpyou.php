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

// db接続
$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());
}

$result = pg_query("SELECT	konyusaki_nm FROM mst_konyusaki
where   del_flg = '0'");


$busho = pg_query("SELECT	busho_nm FROM mst_busho
where   del_flg = '0'");
    $busho_row  =  pg_fetch_all($busho);

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

	</head>
    <body onLoad="init()" class="back_color_01">
	<form name="form1" id="form1" method="POST" class="">


    <input id="" name="" type="button" value="購入データインポート" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#add_date_modal" onclick="">



		<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> -->

		<!-- bootstrap5のjs　ダウンロードファイルを使用する場合 -->
		<script src="bootstrap5/bootstrap.bundle.min.js"></script>
		<!-- bootstrap5のjs　ＣＤＮを使用する場合 -->
		<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
	    <!-- <script>
		document.getElementById("chumon_konyu_date").focus();
		</script> -->
		<table class="table table-bordered">
        <tr>
          <td class="text-center header ">選択</td>
          <td class="text-center header ">購入先</td>
          <td class="text-center header ">支店名</td>
          <td class="text-center header ">品目</td>
          <td class="text-center header ">品種</td>
          <td class="text-center header ">色</td>
          <td class="text-center header ">階級</td>
          <td class="text-center header ">等級</td>
          <td class="text-center header ">輪数</td>
          <td class="text-center header ">長さ</td>
          <td class="text-center header ">入数</td>
          <td class="text-center header ">口数</td>
          <td class="text-center header ">残数</td>
		  <? 
          foreach($busho_row as $busho_rows){
          ?>
		  <td class="text-center header "><?= $busho_rows['busho_nm']?></td>
		  <?
		  }
		  ?>
		  <td class="text-center header ">単価</td>
		  <td class="text-center header ">産地１</td>
		  <td class="text-center header ">産地２</td>
</table>



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
						<div class="col-md-3">購入先</div>
				
					
						<div class="col-md-3">
						<select class="row justify-content-center"name="example" id="levelSelect2">
        <?php
        //購入された日付をプルダウンメニューで表示する
          for ($i = 0 ; $i < pg_num_rows($result) ; $i++){
                 $konyu_date_rows = pg_fetch_array($result);
        ?>
                 <option ><?=$konyu_date_rows['konyusaki_nm'] ?></option>
                  <?php     
                   }
                  ?>
     </select>
				</div>
	 <div class="row">
						<div class="col-md-3">
						<input type="file" name="名前" accept=".csv" id="levelSelect">
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

		<script>
//========================================================================================================
//Ajax
//========================================================================================================
$(function(){
    //小窓の「作成」ボタンをクリックしてajax通信を行う
    $('#btn_make_newdate').click(function(){
		/** 日付入力チェック */
		var new_date = $('#levelSelect').val();
		if(!new_date){
			alert("ファイルを選択してください");
		}else{
			/** サーバーへ送るデータを作成する */
		    var	new_date = new_date + ";" + $('#levelSelect2').val();
			var send_data = new_date;
			// var send_data = [{'chumon_konyu_date' : new_date}];
			// let send_data = new Array();
			// send_data["chumon_konyu_date"] = $("#new_chumon_konyu_date").val();

			$.ajax({
				url: 'server_import.php',
				// url: '/ajax/test.html',
				/* 自サイトのドメインであれば、https://kinocolog.com/ajax/test.html というURL指定も可 */
				type: 'POST',
				data: {
					val_parm: send_data
				},
				dataType: 'html'  //SON形式のデータとして評価しJavaScriptのオブジェクトに変換
				//   dataType: 'json'  //SON形式のデータとして評価しJavaScriptのオブジェクトに変換
			}).done(function(data){
				/* 通信成功時 */
				if(data == 0){
					alert(new_date + " の注文データがインポートされました。");
					transition("kiku_chumon.php");	//菊注文画面をリロード
				}else{
					console.log(data);
					alert(data);
				}
				// $("#return").append('<p>'+data.id+' : '+data.school+' : '+data.skill+'</p>');
	//            $('.result').html(data); //取得したHTMLを.resultに反映
				
			}).fail(function(jqXHR, textStatus, errorThrown){
			// }).fail(function(data){
				/* 通信失敗時 */
				// alert('通信失敗！:'+ data);
				alert('ファイルの取得に失敗しました。');
				console.log("ajax通信に失敗しました");
				console.log("jqXHR          : " + jqXHR.status); // HTTPステータスが取得
				console.log("textStatus     : " + textStatus);    // タイムアウト、パースエラー
				console.log("errorThrown    : " + errorThrown.message); // 例外情報
				// console.log("URL            : " + url);
						
			}).always(function(data) {
				/*　通信成功失敗問わず行う処理　*/
			});
		}
    });
});
		</script>
	</body>
	</form>

</html>
<?php
// db_close($link);
?>
