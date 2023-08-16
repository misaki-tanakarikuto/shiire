<?
$hd_himmoku_cd = "";
if(isset($_POST["hd_himmoku_cd"]) && ($_POST["hd_himmoku_cd"])){
	$hd_himmoku_cd = $_POST["hd_himmoku_cd"];
}

$himmoku_nm = "";
$himmoku_cd = "";
$ichiba_himmoku_cd = "";


$mst_himmoku_rows = array();
$mst_himmoku_iro_rows = array();
$mst_himmoku_size_rows = array();
$mst_himmoku_hinshu_rows = array();
       //DB接続
	    $conn = "host=localhost dbname=shiiredb user=postgres password=41916";
	    $link = pg_connect($conn);
    

		function gethimmoku($hd_himmoku_cd){
	if($hd_himmoku_cd){
		$sqlWhere = " WHERE himmoku_cd = '".$hd_himmoku_cd."'";
	}
	$sqlString = "SELECT *  FROM mst_himmoku ".$sqlWhere."";
	$cd_result = pg_query($sqlString);
	if (!$cd_result) {
		die('クエリーが失敗しました。'.pg_last_error());
		print('クエリーが失敗しました。'.$sqlString);
	}
	$rows = pg_fetch_all($cd_result);

	return $rows;
}

function getiro($hd_himmoku_cd){
	if($hd_himmoku_cd){
		$sqlWhere = " WHERE himmoku_cd = '".$hd_himmoku_cd."'";
	}
	$sqlString = "SELECT *  FROM mst_himmoku_color ".$sqlWhere."";
	$cd_result = pg_query($sqlString);
	if (!$cd_result) {
		die('クエリーが失敗しました。'.pg_last_error());
		print('クエリーが失敗しました。'.$sqlString);
	}
	$rows = pg_fetch_all($cd_result);

	return $rows;
}

function getsize($hd_himmoku_cd){
	if($hd_himmoku_cd){
		$sqlWhere = " WHERE himmoku_cd = '".$hd_himmoku_cd."'";
	}
	$sqlString = "SELECT * FROM mst_himmoku_size ".$sqlWhere."";
	$cd_result = pg_query($sqlString);
	if (!$cd_result) {
		die('クエリーが失敗しました。'.pg_last_error());
		print('クエリーが失敗しました。'.$sqlString);
	}
	$rows = pg_fetch_all($cd_result);

	return $rows;
}

function gethinshu($hd_himmoku_cd){
	if($hd_himmoku_cd){
		$sqlWhere = " WHERE himmoku_cd = '".$hd_himmoku_cd."'";
	}
	$sqlString = "SELECT * FROM mst_hinshu ".$sqlWhere."";
	$cd_result = pg_query($sqlString);
	if (!$cd_result) {
		die('クエリーが失敗しました。'.pg_last_error());
		print('クエリーが失敗しました。'.$sqlString);
	}
	$rows = pg_fetch_all($cd_result);

	return $rows;
}

if($hd_himmoku_cd){
	//***** 顧客マスタデータ取得
	$mst_himmoku_rows = gethimmoku($hd_himmoku_cd);
//test start
//error_log(print_r($mst_himmoku_saidan_rows, true), 3, 'c:/WWW/nidasi/log/app.log');
//test end
	
	$himmoku_nm = $mst_himmoku_rows[0]["himmoku_nm"];
	$himmoku_cd = $mst_himmoku_rows[0]["himmoku_cd"];
	$ichiba_himmoku_cd = $mst_himmoku_rows[0]["ichiba_himmoku_cd"];
	
	//***** 品目色マスタデータ取得
	$mst_himmoku_iro_rows = getiro($hd_himmoku_cd);
	
	//***** 品目サイズマスタデータ取得
	$mst_himmoku_size_rows = getsize($hd_himmoku_cd);

	$mst_himmoku_hinshu_rows = gethinshu($hd_himmoku_cd);
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="bootstrap\css\bootstrap.min.css">
	<script src="js/main.js"></script>
    <title>品目マスタ編集画面</title>
<script>

        var REG_MODE = "";
		var iro_REG_MODE = "";
		var DEL_MODE = "";
		var sizu_REG_MODE = "";
		var hinshu_REG_MODE = "";


	    /*********************************************************/
		/* 新規品目データを入力する状態にする（真っさらにリロード）               */
		/*********************************************************/
		function new_register(){
			if(confirm("データをクリアして新規入力状態にします。")){
				document.getElementById("hd_himmoku_cd").value = "";
				document.getElementById("himmoku_nm").value = "";
				document.getElementById("ichiba_himmoku_cd").value = "";
				transition('master_himmoku_edit.php');
			}
			return false;
		}
  function register(){
			/************************/
			/***** 入力チェック *****/
			/************************/
			/**** 顧客名 ****/
			var wk_himmoku_nm = document.getElementById("himmoku_nm");
			/* 必須入力チェック */
			if(!wk_himmoku_nm.value){
				self.focus();
				wk_himmoku_nm.focus();
				alert("「品目名」を入力して下さい。");
				return false;
			}
			/**** 品目コード ****/
			var wk_himmoku_cd = document.getElementById("himmoku_cd");
			/* 必須入力チェック */
			if(!wk_himmoku_cd.value){
				self.focus();
				wk_himmoku_cd.focus();
				alert("「品目コード」を入力して下さい。");
				return false;
			}
				/**** 市場品目コード ****/
				var wk_ichiba_himmoku_cd = document.getElementById("ichiba_himmoku_cd");
			/* 必須入力チェック */
			if(!wk_ichiba_himmoku_cd.value){
				self.focus();
				wk_ichiba_himmoku_cd.focus();
				alert("「市場品目コード」を入力して下さい。");
				return false;
			}

			/**************************************/
			/***** データ登録処理             *****/
			/**************************************/
			var himmoku_nm = wk_himmoku_nm.value;				//品目名
			var himmoku_cd = wk_himmoku_cd.value;		//品目コード
			var ichiba_himmoku_cd = wk_ichiba_himmoku_cd.value;			//市場品目コード
			var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;

			var SendParam = "val_parm=" + himmoku_nm + ";" + himmoku_cd + ";" + ichiba_himmoku_cd + ";" +  hd_himmoku_cd;
		
			if(hd_himmoku_cd){
				if(hd_himmoku_cd != himmoku_cd){
					alert('更新する場合は前回と同じ品目コードを入力してください')
				}else{
				//更新処理
				REG_MODE = "update";
				sendRequest_reg(SendParam, 'POST', 'server_master_himmoku_update.php', true);
				}
			}else{
				//新規登録処理
				REG_MODE = "insert";
				sendRequest_reg(SendParam, 'POST', 'server_master_himmoku_insert.php', true);
			}
		}


		function register_iro() {
				var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			if(!hd_himmoku_cd){
				alert('まず基本情報を登録してください。');
				return false;
			}
			/************************/
			/***** 入力チェック *****/
			/************************/
			var wk_himmoku_color_cd = document.getElementById("himmoku_color_cd");
			/* 必須入力チェック */
			if(!wk_himmoku_color_cd.value){
				self.focus();
				wk_himmoku_color_cd.focus();
				alert("「品目色コード」を入力して下さい。");
				return false;
			}
			// 小文字の数値じゃないならアラートを出す
	        if (wk_himmoku_color_cd.value.match(/[^0-9]/g)){
		         self.focus();
		         wk_busho_cd.focus();
		         wk_busho_cd.select();
		         alert("品目色コードに小文字の数値以外が入力されています");
		         return false;
	           }

			var wk_himmoku_color_nm = document.getElementById("himmoku_color_nm");
			/* 必須入力チェック */
			if(!wk_himmoku_color_nm.value){
				self.focus();
				wk_himmoku_color_nm.focus();
				alert("「品目色名」を入力して下さい。");
				return false;
			}
			/**************************************/
			/***** データ登録処理             *****/
			/**************************************/
			var hd_himmoku_colo_cd = document.getElementById("hd_himmoku_colo_cd").value;
			var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			var himmoku_color_cd = wk_himmoku_color_cd.value;
			var himmoku_color_nm = wk_himmoku_color_nm.value;		
            
			var SendParam = "val_parm=" + himmoku_color_nm + ";" + himmoku_color_cd + ";" + hd_himmoku_colo_cd + ";" +  hd_himmoku_cd;
		
			if(hd_himmoku_colo_cd){
				//更新処理
				iro_REG_MODE = "update";
				sendRequest_reg(SendParam, 'POST', 'server_master_himmokuiro__update.php', true);
			}else{
				//新規登録処理
				iro_REG_MODE = "insert";
				sendRequest_reg(SendParam, 'POST', 'server_master_himmokuiro_insert.php', true);
			}
		}
		function register_size() {
				var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			if(!hd_himmoku_cd){
				alert('まず基本情報を登録してください。');
				return false;
			}
			/************************/
			/***** 入力チェック *****/
			/************************/
			var wk_himmoku_size_cd = document.getElementById("himmoku_size_cd");
			/* 必須入力チェック */
			if(!wk_himmoku_size_cd.value){
				self.focus();
				wk_himmoku_size_cd.focus();
				alert("「品目サイズコード」を入力して下さい。");
				return false;
			}
			// 小文字の数値じゃないならアラートを出す
	        if (wk_himmoku_size_cd.value.match(/[^0-9]/g)){
		         alert("品目サイズコードに小文字の数値以外が入力されています");
		         return false;
	           }

			var wk_himmoku_size_nm = document.getElementById("himmoku_size_nm");
			/* 必須入力チェック */
			if(!wk_himmoku_size_nm.value){
				self.focus();
				wk_himmoku_size_nm.focus();
				alert("「品目サイズ名」を入力して下さい。");
				return false;
			}
			var wk_himmoku_irisu = document.getElementById("irisu");
			/**************************************/
			/***** データ登録処理             *****/
			/**************************************/
			var hd_himmoku_size_cd = document.getElementById("hd_himmoku_size_cd").value;
			var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			var himmoku_size_cd = wk_himmoku_size_cd.value;
			var himmoku_size_nm = wk_himmoku_size_nm.value;		
			var himmoku_irisu = wk_himmoku_irisu.value;				
            
			var SendParam = "val_parm=" + himmoku_size_nm + ";" + himmoku_size_cd + ";" + hd_himmoku_size_cd + ";" +  hd_himmoku_cd + ";" + himmoku_irisu;
		
			if(hd_himmoku_size_cd){
				//更新処理
				sizu_REG_MODE = "update";
				sendRequest_reg(SendParam, 'POST', 'server_master_himmoku_sizu__update.php', true);
			}else{
				//新規登録処理
				sizu_REG_MODE = "insert";
				sendRequest_reg(SendParam, 'POST', 'server_master_himmoku_sizu_insert.php', true);
			}
		}

		function register_hinshu() {
				var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			if(!hd_himmoku_cd){
				alert('まず基本情報を登録してください。');
				return false;
			}
			/************************/
			/***** 入力チェック *****/
			/************************/
			var wk_himmoku_hinshu_cd = document.getElementById("himmoku_hinshu_cd");
			/* 必須入力チェック */
			if(!wk_himmoku_hinshu_cd.value){
				self.focus();
				wk_himmoku_hinshu_cd.focus();
				alert("「品種コード」を入力して下さい。");
				return false;
			}
			// 小文字の数値じゃないならアラートを出す
	        if (wk_himmoku_hinshu_cd.value.match(/[^0-9]/g)){
		         alert("品種コードに小文字の数値以外が入力されています");
		         return false;
	           }

			var wk_himmoku_hinshu_nm = document.getElementById("himmoku_hinshu_nm");
			/* 必須入力チェック */
			if(!wk_himmoku_hinshu_nm.value){
				self.focus();
				wk_himmoku_hinshu_nm.focus();
				alert("「品種名」を入力して下さい。");
				return false;
			}
			/**************************************/
			/***** データ登録処理              *****/
			/**************************************/
			var hd_himmoku_hinshu_cd = document.getElementById("hd_himmoku_hinshu_cd").value;
			var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			var himmoku_hinshu_cd = wk_himmoku_hinshu_cd.value;
			var himmoku_hinshu_nm = wk_himmoku_hinshu_nm.value;						
            
			var SendParam = "val_parm=" + himmoku_hinshu_nm + ";" + himmoku_hinshu_cd + ";" + hd_himmoku_hinshu_cd + ";" +  hd_himmoku_cd;
			if(hd_himmoku_hinshu_cd){
				//更新処理
			
				hinshu_REG_MODE = "update";
				sendRequest_reg(SendParam, 'POST', 'server_master_himmoku_hinshu__update.php', true);
			}else{
				//新規登録処理
				hinshu_REG_MODE = "insert";
				sendRequest_reg(SendParam, 'POST', 'server_master_himmoku_hinshu_insert.php', true);
			}
		}
//##################################################
// Ajax
// Serverへデータを送信
//##################################################
function sendRequest_reg(data, method, url, async) {

objReq = createHttpRequest();
if (objReq == false) {
	return null;
}
objReq.onreadystatechange = procReqChange_reg;
if (method == 'GET') {
	url = url + encodeURI(data);
}
objReq.open(method, url, async);
if (method == 'POST') {
	objReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
}
objReq.send(data);
}

//##################################################
// Ajax
// create XMLHttpRequest object
//##################################################
function createHttpRequest() {
// for ie7, Mozilla, FireFox, Opera8

if (window.XMLHttpRequest) {
	try {
		return new XMLHttpRequest();
	} catch (e) {
		return false;
	}
}
// for ie5, ie6
else if (window.ActiveXObject) {
	try {
		return new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			return new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e2) {
			return false;
		}
	}
}
else {
	return false;
}
}

        //##################################################
		// Ajax
		// サーバから結果を受信
		//##################################################
		function procReqChange_reg() {
			if (objReq.readyState == 4) {				//4 = complete（読み込んだデータの解析完了、または失敗した。つまり処理が終わった） 

				if (objReq.status == 200) {				//成功!!
		///			alert("データ登録 " + objReq.responseText);
					var svr_rtn = objReq.responseText;
		///			SERVER_RETURN = eval(objReq.responseText);

					if(REG_MODE == "update"){
						if(svr_rtn == 1){
							alert('その品目コードは既に登録されています');
						}else{
							alert('更新しました。');
						}
					}else if(REG_MODE == "insert"){
						if(svr_rtn == '0'){
							alert('その品目コードは既に登録されています');
						}else{
							alert('登録しました。');
						}
					}
					REG_MODE = "";

					if(iro_REG_MODE == "update"){
						if(svr_rtn == 1){
							alert('その品目色コードは登録されています');
						}else{
							transition("master_himmoku_edit.php");
						}
					}else if(iro_REG_MODE == "insert"){
						if(svr_rtn == '1'){
							alert('その品目色コードは登録されています');
						}else{
							transition("master_himmoku_edit.php");
						}
					}
					iro_REG_MODE = "";
					if(sizu_REG_MODE == "update"){
						if(svr_rtn == 1){
							alert('その品目サイズコードは登録されています');
						}else{
							transition("master_himmoku_edit.php");
						}
					}else if(sizu_REG_MODE == "insert"){
						if(svr_rtn == '1'){
							alert('その品目サイズコードは登録されています');
						}else{
							transition("master_himmoku_edit.php");
						}
					}
					sizu_REG_MODE = "";

					if(hinshu_REG_MODE == "update"){
						if(svr_rtn == 1){
							alert('その品種コードは登録されています');
						}else{
							transition("master_himmoku_edit.php");
						}
					}else if(hinshu_REG_MODE == "insert"){
						if(svr_rtn == '1'){
							alert('その品種コードは登録されています');
						}else{
							transition("master_himmoku_edit.php");
						}
					}
					hinshu_REG_MODE = "";
					if(hinshu_REG_MODE == "update"){
						if(svr_rtn == 1){
							alert('その品目サイズコードは登録されています');
						}else{
							transition("master_himmoku_edit.php");
						}
					}else if(hinshu_REG_MODE == "insert"){
						if(svr_rtn == '1'){
							alert('その品目サイズコードは登録されています');
						}else{
							transition("master_himmoku_edit.php");
						}
					}
					hinshu_REG_MODE = "";
				} else {
					alert("ERROR: " + objReq.statusText);
				}
				
			}
		}
		

		/*********************************************************/
		/* 品目色情報登録用入力フォームをクリアする                          */
		/*********************************************************/
		function clear_iro_form(){
			document.getElementById("himmoku_color_cd").value = "";
			document.getElementById("hd_himmoku_colo_cd").value = "";
			document.getElementById("himmoku_color_nm").value = "";
		}
		/*********************************************************/
		/* 品目サイズ情報登録用入力フォームをクリアする                          */
		/*********************************************************/
		function clear_size_form(){
			document.getElementById("himmoku_size_cd").value = "";
			document.getElementById("hd_himmoku_size_cd").value = "";
			document.getElementById("himmoku_size_nm").value = "";
			document.getElementById("irisu").value = "";
		}

		/**********************************************************/
		/***** 品目色一覧の全行を全て選択/全て解除                    *****/
		/**********************************************************/
		var flag_saidan =false;
		function allChange_iro(){
		
			var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			if(!hd_himmoku_cd){
				alert('まず基本情報を登録してください。');
				document.getElementById("himmoku_nm").focus();
				return false;
			}
		
			flag_saidan = !flag_saidan; // trueとfalseの切り替え ! 否定演算子
			var elem = document.getElementsByName("s_himmoku_color_cd");
			for(var i=0; i < elem.length; i++){
				elem[i].checked = flag_saidan;
			}
		}

		/**********************************************************/
		/***** 品目サイズ一覧の全行を全て選択/全て解除                    *****/
		/**********************************************************/
		var flag_saidan =false;
		function allChange_size(){
		
			var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			if(!hd_himmoku_cd){
				alert('まず基本情報を登録してください。');
				document.getElementById("himmoku_nm").focus();
				return false;
			}
		
			flag_saidan = !flag_saidan; // trueとfalseの切り替え ! 否定演算子
			var elem = document.getElementsByName("s_himmoku_size_cd");
			for(var i=0; i < elem.length; i++){
				elem[i].checked = flag_saidan;
			}
		}



		/**********************************************************/
		/***** 品種一覧の全行を全て選択/全て解除                    *****/
		/**********************************************************/
		var flag_saidan =false;
		function allChange_hinshu(){
		
			var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
			if(!hd_himmoku_cd){
				alert('まず基本情報を登録してください。');
				document.getElementById("himmoku_nm").focus();
				return false;
			}
		
			flag_saidan = !flag_saidan; // trueとfalseの切り替え ! 否定演算子
			var elem = document.getElementsByName("s_himmoku_hinshu_cd");
			for(var i=0; i < elem.length; i++){
				elem[i].checked = flag_saidan;
			}
		}

		/**********************************************************/
		/***** 品目色データ更新準備（一覧のデータを登録用フォームへ展開する    *****/
		/**********************************************************/
		function into_form_iro(num){

         //クリックした行のデータを登録用フォームに入力された状態にする
        var himmoku_color_cd = document.getElementById("himmoku_color_cd");
        var hd_himmoku_colo_cd = document.getElementById("hd_himmoku_colo_cd");
        var himmoku_color_nm = document.getElementById("himmoku_color_nm");
        var l_himmoku_color_cd = document.getElementById("l_himmoku_color_cd"+ num);
        var l_himmoku_color_nm = document.getElementById("l_himmoku_color_nm"+ num);
        hd_himmoku_colo_cd.value = l_himmoku_color_cd.innerText;
        himmoku_color_cd.value = l_himmoku_color_cd.innerText;
        himmoku_color_nm.value = l_himmoku_color_nm.innerText;
}

	    /**********************************************************/
		/***** 品種サイズデータ更新準備（一覧のデータを登録用フォームへ展開する    *****/
		/**********************************************************/
		function into_form_hinshu(num){

        //クリックした行のデータを登録用フォームに入力された状態にする
        var himmoku_hinshu_cd = document.getElementById("himmoku_hinshu_cd");
        var hd_himmoku_hinshu_cd = document.getElementById("hd_himmoku_hinshu_cd");
        var himmoku_hinshu_nm = document.getElementById("himmoku_hinshu_nm");
        var l_himmoku_hinshu_cd = document.getElementById("l_himmoku_hinshu_cd"+ num);
        var l_himmoku_hinshu_nm = document.getElementById("l_himmoku_hinshu_nm"+ num);
        hd_himmoku_hinshu_cd.value = l_himmoku_hinshu_cd.innerText;
        himmoku_hinshu_cd.value = l_himmoku_hinshu_cd.innerText;
        himmoku_hinshu_nm.value = l_himmoku_hinshu_nm.innerText;
        }    



		    /**********************************************************/
		/***** 品目サイズデータ更新準備（一覧のデータを登録用フォームへ展開する    *****/
		/**********************************************************/
		function into_form_size(num){

        //クリックした行のデータを登録用フォームに入力された状態にする
        var himmoku_size_cd = document.getElementById("himmoku_size_cd");
        var hd_himmoku_size_cd = document.getElementById("hd_himmoku_size_cd");
        var himmoku_size_nm = document.getElementById("himmoku_size_nm");
        var irisu = document.getElementById("irisu");
        var l_himmoku_size_cd = document.getElementById("l_himmoku_size_cd"+ num);
        var l_himmoku_size_nm = document.getElementById("l_himmoku_size_nm"+ num);
        var l_himmoku_size_irisu = document.getElementById("l_himmoku_size_irisu"+ num);
        hd_himmoku_size_cd.value = l_himmoku_size_cd.innerText;
        himmoku_size_cd.value = l_himmoku_size_cd.innerText;
        himmoku_size_nm.value = l_himmoku_size_nm.innerText;
        irisu.value = l_himmoku_size_irisu.innerText;
        }    



        /**********************************************************/
		/***** 品目色データ削除処理                         *****/
		/**********************************************************/
		function del_iro(){
		
		var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
		if(!hd_himmoku_cd){
			alert('まず基本情報を登録してください。');
			document.getElementById("himmoku_nm").focus();
			return false;
		}
	
		//***** 削除する斎場コードをすべて取得 *****//
		var myTbl = document.getElementById('tbl0031');
		var size_cd = "";
		var delDt = "";

//test start
//alert("length = "+ myTbl.rows.length);
//test end

		//行位置取得
		for (var i=0; i < myTbl.rows.length-1; i++) {
//test start
//alert("i = "+ i);
//alert("length = "+ myTbl.rows.length);
//test end
			if(document.getElementById('s_himmoku_color_cd' + i).checked){
				if(size_cd == ""){
					size_cd = document.getElementById('s_himmoku_color_cd' + i).value;
				}else{
					size_cd = size_cd + "$" + document.getElementById('s_himmoku_color_cd' + i).value;
				}
			}
		}

		if(size_cd != ""){
			if(confirm("選択した情報を削除します。\nよろしいですか？")){
				DEL_MODE = "del";
				var SendParam = "val_parm=" + size_cd;

				sendRequest_del(SendParam, 'POST', 'server_master_customer_iro_delete.php', true);
			}
		}else{
			alert("選択されていません。");
		}

	}

	    /**********************************************************/
		/***** 品目サイズデータ削除処理                         *****/
		/**********************************************************/
		function del_size(){
		
		var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
		if(!hd_himmoku_cd){
			alert('まず基本情報を登録してください。');
			document.getElementById("himmoku_nm").focus();
			return false;
		}
	
		//***** 削除する斎場コードをすべて取得 *****//
		var myTbl = document.getElementById('tbl0032');
		var size_cd = "";
		var delDt = "";

//test start
//alert("length = "+ myTbl.rows.length);
//test end

		//行位置取得
		for (var i=0; i < myTbl.rows.length-1; i++) {
//test start
//alert("i = "+ i);
//alert("length = "+ myTbl.rows.length);
//test end
			if(document.getElementById('s_himmoku_size_cd' + i).checked){
				if(size_cd == ""){
					size_cd = document.getElementById('s_himmoku_size_cd' + i).value;
				}else{
					size_cd = size_cd + "$" + document.getElementById('s_himmoku_size_cd' + i).value;
				}
			}
		}

		if(size_cd != ""){
			if(confirm("選択した情報を削除します。\nよろしいですか？")){
				DEL_MODE = "del";
				var SendParam = "val_parm=" + size_cd;

				sendRequest_del(SendParam, 'POST', 'server_master_customer_size_delete.php', true);
			}
		}else{
			alert("選択されていません。");
		}

	}
	 /**********************************************************/
		/***** 品種サイズデータ削除処理                         *****/
		/**********************************************************/
		function del_hinshu(){
		
		var hd_himmoku_cd = document.getElementById("hd_himmoku_cd").value;
		if(!hd_himmoku_cd){
			alert('まず基本情報を登録してください。');
			document.getElementById("himmoku_nm").focus();
			return false;
		}
	
		//***** 削除する斎場コードをすべて取得 *****//
		var myTbl = document.getElementById('tbl0033');
		var hinshu_cd = "";
		var delDt = "";

//test start
//alert("length = "+ myTbl.rows.length);
//test end

		//行位置取得
		for (var i=0; i < myTbl.rows.length-1; i++) {
//test start
//alert("i = "+ i);
//alert("length = "+ myTbl.rows.length);
//test end
			if(document.getElementById('s_himmoku_hinshu_cd' + i).checked){
				if(hinshu_cd == ""){
					hinshu_cd = document.getElementById('s_himmoku_hinshu_cd' + i).value;
				}else{
					hinshu_cd = hinshu_cd + "$" + document.getElementById('s_himmoku_hinshu_cd' + i).value;
				}
			}
		}

		if(hinshu_cd != ""){
			if(confirm("選択した情報を削除します。\nよろしいですか？")){
				DEL_MODE = "del";
				var SendParam = "val_parm=" + hinshu_cd;

				sendRequest_del(SendParam, 'POST', 'server_master_customer_hinshu_delete.php', true);
			}
		}else{
			alert("選択されていません。");
		}

	}
	    //##################################################
		// Ajax
		// Serverへデータを送信
		//##################################################
		function sendRequest_del(data, method, url, async) {

objReq = createHttpRequest();
if (objReq == false) {
	return null;
}
objReq.onreadystatechange = procReqChange_del;
if (method == 'GET') {
	url = url + encodeURI(data);
}
objReq.open(method, url, async);
if (method == 'POST') {
	objReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
}
objReq.send(data);
}

        //##################################################
		// Ajax
		// サーバから結果を受信
		//##################################################
		function procReqChange_del() {
			if (objReq.readyState == 4) {				//4 = complete（読み込んだデータの解析完了、または失敗した。つまり処理が終わった） 

				if (objReq.status == 200) {				//成功!!
		///			alert("データ削除 " + objReq.responseText);
					var svr_rtn = objReq.responseText;
		///			SERVER_RETURN = eval(objReq.responseText);
		///			var svr_rtn = eval(objReq.responseText);

					if(DEL_MODE == "del"){
						/*** 顧客斎場データ削除処理後 ***/
						var rtn_txt = svr_rtn.split(';');
//test start
//alert("svr_rtn = " + svr_rtn);
//test end
        if(rtn_txt[0] == '0'){
            //正常終了
            var dempyo_umu_cnt = rtn_txt[1].substr(0 ,1);
            dempyo_umu_cnt = dempyo_umu_cnt - 0;
            var delete_cnt = rtn_txt[1].substr(1 ,1);
            delete_cnt = delete_cnt - 0;
            
            if(dempyo_umu_cnt == 0){
                alert("全て削除されました。\n削除件数：　"+delete_cnt);
                transition("master_himmoku_edit.php");
            }else if(delete_cnt == 0){
                alert("購入情報品目テーブルに登録されているため１件も削除されませんでした。\n削除件数：　"+delete_cnt);
                transition("master_himmoku_edit.php");
            }else{
                alert("購入情報品目テーブルに登録されている色は削除できませんでした。\n削除件数：　"+delete_cnt+"\n削除不可件数： "+dempyo_umu_cnt);
                transition("master_himmoku_edit.php");
            }
        }else{
            //以上終了
            alert(svr_rtn);
        }

					}else if(DEL_MODE == "saidan"){
						/*** データ削除処理後 ***/
						if(svr_rtn != '0'){
							alert(svr_rtn);
						}else{
							transition("master_customer_edit.php");
						}
					}

				} else {
					alert("ERROR: " + objReq.statusText);
				}
			}
		}
</script>
</head>
<body>
<form name="form1" method="POST">
<div class="container">
    <div class="title_bar">品目マスタ編集画面</div>
	            <input type="button" value="新　規" class="btn_01" id="" onclick="new_register()">
				<input type="button" value="一　覧" class="btn_01" id="" onclick="transition('himmoku_maste.php')">
				<input type="button" value="ログアウト" class="btn_01" id="" onclick="logout()">

				<div class="border_line1"></div> 

				<div class="text-center">基本情報</div>

			<div class="d-grid gap-2">	
				<tr>
			  <div class="text-center">
					<td>品目名：</td>
					<td><input type="text" id="himmoku_nm" name="himmoku_nm"  value="<?= $himmoku_nm ?>"></td>
				</tr>
				<tr>
					<td>品目コード：</td>
					<td><input type="text" id="himmoku_cd" name="himmoku_cd"  value="<?= $himmoku_cd ?>"></td>
				</tr>
				<tr>
					<td>市場品目コード：</td>
					<td><input type="text" id="ichiba_himmoku_cd" name="ichiba_himmoku_cd"  value="<?= $ichiba_himmoku_cd ?>"></td>
				</tr>
				
	        </div>
		
	</div>
	<input class="d-grid  mx-auto" type="button" value="登　録" class="btn_01" id="" onclick="register()">

				

				<div class="border_line1"></div> 
				<div class="text-center">品目色情報</div>
            <div class="d-grid gap-2">
					<div class="text-center">色コード：
						<input type="text" id="himmoku_color_cd" name="himmoku_color_cd"  value="">
					</div>
					</td>
					<div class="text-center">品目色名：
						<input type="text" id="himmoku_color_nm" name="himmoku_color_nm" value="">
				  	</div>
	          
			  <div class="d-grid gap-2 col-1 mx-auto">
				<input type="button" value="クリア"  id="" onclick="clear_iro_form()">
				<input type="button" value="登　録" id="" onclick="register_iro()">
	          </div>
			</div>
			<table id="tbl0031" class="table">
					<tr>
						<td class="border text-center">選択</td>
						<td class="border text-center">色名</td>
						<td class="border text-center">品目色コード</td>
					</tr>
					<input type="button" id="btn_stype_chk_on_off" value="全選択/解" class="btn_01" onclick="allChange_iro()">
			        <input type="button" id="btn_stype_del" value="削　除" class="btn_01" onclick="del_iro()">
	         
<?php
$i = 0;
if($mst_himmoku_iro_rows != null && is_array($mst_himmoku_iro_rows)){
	foreach($mst_himmoku_iro_rows as $mst_himmoku_iro_row){
?>
                    <tr>
						<td class="border text-center col-md-1"	>
							<input type="checkbox" name="s_himmoku_color_cd" id="s_himmoku_color_cd<?= $i ?>" value="<?= $mst_himmoku_iro_row['himmoku_color_cd'] ?>"> <!-- 品目色コード -->
						</td>
						<td onclick="into_form_iro('<?= $i ?>')" class="border text-center col-md-10">
							<div id="l_himmoku_color_nm<?= $i ?>" name="l_himmoku_color_nm<?= $i ?>"><?= $mst_himmoku_iro_row['himmoku_color_nm'] ?></div>  <!-- 品目色名 -->
						</td>
						<td onclick="into_form_iro('<?= $i ?>')"class="border text-center col-md-1">
							<div id="l_himmoku_color_cd<?= $i ?>" name="l_himmoku_color_cd<?= $i ?>"  style=""><?= $mst_himmoku_iro_row['himmoku_color_cd'] ?></div> 
						</td>
					</tr>
			<?php
		$i++;
	}
}
?>
  </table>
  <div class="border_line1"></div> 
				<div class="text-center">品目サイズ情報</div>
            <div class="d-grid gap-2">
					<div class="text-center">サイズコード：
						<input type="text" id="himmoku_size_cd" name="himmoku_size_cd"  value="">
					</div>
					<div class="text-center">品目サイズ名：
						<input type="text" id="himmoku_size_nm" name="himmoku_size_nm" value="">
				  	</div>

					  <div class="text-center">入数：
						<input type="text" id="irisu" name="irisu" value="">
				  	</div>
	          
				<div class="d-grid gap-2 col-1 mx-auto">
				<input type="button" value="クリア"  id="" onclick="clear_size_form()">
				<input type="button" value="登　録" id="" onclick="register_size()">
	            </div>
			</div>
			<table id="tbl0032" class="table">
					<tr>
						<td class="border text-center">選択</td>
						<td class="border text-center">サイズ名</td>
						<td class="border text-center">品目サイズコード</td>
						<td class="border text-center">入数</td>
					</tr>
					<input type="button" id="btn_stype_chk_on_off" value="全選択/解" class="btn_01" onclick="allChange_size()">
			            <input type="button" id="btn_stype_del" value="削　除" class="btn_01" onclick="del_size()">
	         
<?php
$j = 0;
if($mst_himmoku_size_rows != null && is_array($mst_himmoku_size_rows)){
	foreach($mst_himmoku_size_rows as $mst_himmoku_size_row){
?>
                    <tr>
						<td class="border text-center col-md-1"	>
							<input type="checkbox" name="s_himmoku_size_cd" id="s_himmoku_size_cd<?= $j ?>" value="<?= $mst_himmoku_size_row['himmoku_size_cd'] ?>"> <!-- 品目色コード -->
						</td>
						<td onclick="into_form_size('<?= $j ?>')" class="border text-center col-md-8">
							<div id="l_himmoku_size_nm<?= $j ?>" name="l_himmoku_size_nm<?= $j ?>"><?= $mst_himmoku_size_row['himmoku_size_nm'] ?></div>  <!-- 品目色名 -->
						</td>
						<td onclick="into_form_size('<?= $j ?>')"class="border text-center col-md-3">
							<div id="l_himmoku_size_cd<?= $j ?>" name="l_himmoku_size_cd<?= $j ?>"  style=""><?= $mst_himmoku_size_row['himmoku_size_cd'] ?></div> 
						</td>
						<td onclick="into_form_size('<?= $j ?>')"class="border text-center col-md-1">
							<div id="l_himmoku_size_irisu<?= $j ?>" name="l_himmoku_size_irisu<?= $j ?>"  style=""><?= $mst_himmoku_size_row['irisu'] ?></div> 
						</td>
					</tr>
			<?php
		$j++;
	}
}
?>
  </table>

  <div class="border_line1"></div> 
				<div class="text-center">品種情報</div>
            <div class="d-grid gap-2">
					<div class="text-center">品種コード：
						<input type="text" id="himmoku_hinshu_cd" name="himmoku_hinshu_cd"  value="">
					</div>
					<div class="text-center">品種名：
						<input type="text" id="himmoku_hinshu_nm" name="himmoku_hinshu_nm" value="">
				  	</div>
	          
				<div class="d-grid gap-2 col-1 mx-auto">
				<input type="button" value="クリア"  id="" onclick="clear_size_form()">
				<input type="button" value="登　録" id="" onclick="register_hinshu()">
	            </div>
			</div>
			<table id="tbl0033" class="table">
					<tr>
						<td class="border text-center">選択</td>
						<td class="border text-center">品種名</td>
						<td class="border text-center">品種コード</td>
					</tr>
					<input type="button" id="btn_stype_chk_on_off" value="全選択/解" class="btn_01" onclick="allChange_hinshu()">
			        <input type="button" id="btn_stype_del" value="削　除" class="btn_01" onclick="del_hinshu()">
	         
<?php
$t = 0;
if($mst_himmoku_hinshu_rows != null && is_array($mst_himmoku_hinshu_rows)){
	foreach($mst_himmoku_hinshu_rows as $mst_himmoku_hinshu_row){
?>
                    <tr>
						<td class="border text-center col-md-1">
							<input type="checkbox" name="s_himmoku_hinshu_cd" id="s_himmoku_hinshu_cd<?= $t ?>" value="<?= $mst_himmoku_hinshu_row['hinshu_cd'] ?>"> <!-- 品目色コード -->
						</td>
						<td onclick="into_form_hinshu('<?= $t ?>')" class="border text-center col-md-8">
							<div id="l_himmoku_hinshu_nm<?= $t ?>" name="l_himmoku_hinshu_nm<?= $t ?>"><?= $mst_himmoku_hinshu_row['hinshu_nm'] ?></div>  <!-- 品目色名 -->
						</td>
						<td onclick="into_form_hinshu('<?= $t ?>')"class="border text-center col-md-3">
							<div id="l_himmoku_hinshu_cd<?= $t ?>" name="l_himmoku_hinshu_cd<?= $t ?>"  style=""><?= $mst_himmoku_hinshu_row['hinshu_cd'] ?></div> 
						</td>
					</tr>
			<?php
		$t++;
	}
}
?>
  </table>

	<input type="hidden" name="hd_himmoku_cd" id="hd_himmoku_cd" value="<?= $hd_himmoku_cd ?>"> <!-- 更新対象のＩＤ -->
			<input type="hidden" id="hd_himmoku_colo_cd" name="hd_himmoku_colo_cd" value="">
			<input type="hidden" id="hd_himmoku_size_cd" name="hd_himmoku_size_cd" value="">
			<input type="hidden" id="hd_himmoku_hinshu_cd" name="hd_himmoku_hinshu_cd" value="">
</form>
</body>
</html>