<?php
// db接続
$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());

} 

// 購入先情報取得
$konyusakisql = pg_query("SELECT konyusaki_cd,	old_konyusaki_cd,	konyusaki_nm
                    FROM  mst_konyusaki");
$konyusakisql_row  =  pg_fetch_all($konyusakisql);

$i = 0;

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
    <title>購入先マスタメンテ</title>
  
</head>
<body>
<script>
    
    
    var	REG_MODE = "";


        /**********************************************************************/
		/***** クリックした一覧のデータを画面上部のフォームへ展開する     *****/
		/**********************************************************************/
  function intoForm(idx){
    var l_konyusaki_cd = document.getElementById("l_konyusaki_cd" + idx);
    var l_old_konyusaki_cd = document.getElementById("l_old_konyusaki_cd" + idx);
    var l_konyusaki_nm = document.getElementById("l_konyusaki_nm" + idx);
    var s_konyusaki_cd = document.getElementById("s_konyusaki_cd" + idx);
    // 入力フォーム
    var konyusaki_cd = document.getElementById("konyusaki_cd");
    var old_konyusaki_cd = document.getElementById("old_konyusaki_cd");
    var konyusaki_nm = document.getElementById("konyusaki_nm");
    var hd_konyusaki_cd = document.getElementById("hd_konyusaki_cd");
          
      hd_konyusaki_cd.value    = s_konyusaki_cd.value;
      konyusaki_cd.value  = l_konyusaki_cd.innerText;
      old_konyusaki_cd.value = l_old_konyusaki_cd.innerText;
      konyusaki_nm.value = l_konyusaki_nm.innerText;
    }

    function register(){
      // 入力チェック
      var wk_konyusaki_cd = document.getElementById("konyusaki_cd");
      if(!wk_konyusaki_cd.value){
				alert("「購入先コード」を入力して下さい。");
				return false;
    }
	// 小文字の数値じゃないならアラートを出す
	if (wk_konyusaki_cd.value.match(/[^0-9]/g)){
            alert("「購入先コード」に小文字の数値以外が入力されています");
		return false;
	  }
  var wk_konyusaki_nm = document.getElementById("konyusaki_nm");
  if(!wk_konyusaki_nm.value){
    alert("「購入先名」を入力して下さい。");
    return false;
     }
     
     	    /**************************************/
			/***** 部署データ登録処理           *****/
			/**************************************/
      var k_konyusaki_cd = document.getElementById("hd_konyusaki_cd").value        
      var konyusaki_cd = document.getElementById("konyusaki_cd").value
      var old_konyusaki_cd = document.getElementById("old_konyusaki_cd").value
      var konyusaki_nm = document.getElementById("konyusaki_nm").value


      var SendParam = "val_parm=" + ";" + konyusaki_cd + ";" + old_konyusaki_cd + ";" + konyusaki_nm + ";" + k_konyusaki_cd;
			if(k_konyusaki_cd){
				//更新処理
				REG_MODE = "update";
                alert(k_konyusaki_cd);
				sendRequest_reg(SendParam, 'POST', 'konyusaki_update.php', true);
			}else{
				//新規登録処理
				REG_MODE = "insert";
                alert(SendParam);
				sendRequest_reg(SendParam, 'POST', 'konyusaki_insert.php', true);
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
					var svr_rtn = objReq.responseText;
                    // alert("データ登録 " + objReq.responseText);
		// /			SERVER_RETURN = eval(objReq.responseText);
					if(REG_MODE == "update"){
						if(svr_rtn == 'その購入先コードは既に登録されています'){
							alert('その購入先コードは既に登録されています')
						}else{
							alert('更新しました。');
							transition('konyusaki_maste.php');
						}
					}else if(REG_MODE == "insert"){
						if(svr_rtn == 'その購入先コードは既に登録されています'){
						alert('その購入先コードは既に登録されています')
						transition('konyusaki_maste.php');
						}else{
							alert('登録しました。');
							transition('konyusaki_maste.php');
						}
					}
					REG_MODE = "";
				} else {
					alert("ERROR: " + objReq.statusText);
				}
			}
		}
             //  削除処理　
		function  del(){
			var myTbl = document.getElementById('tbl0032');
			var dempyo_id = "";
						var delDt = "";			
			
				//行位置取得
				for (var i=0; i < myTbl.rows.length -1; i++) {              
							if(document.getElementById('s_konyusaki_cd' + i).checked){
								if(dempyo_id == ""){
									dempyo_id = document.getElementById('s_konyusaki_cd' + i).value;
								}else{
									dempyo_id = dempyo_id + "$" + document.getElementById('s_konyusaki_cd' + i).value;
								}
							}
						}
				 
					if(dempyo_id != ""){
                      
							if(confirm("選択した情報を削除します。\nよろしいですか？")){
								var SendParam = "val_parm=" + dempyo_id;
								sendRequest_del(SendParam, 'POST', 'konyusaki_delete.php', true);
							}
						}else{
							alert("選択されていません。");
						}
								return false; 
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

					if(svr_rtn == '0'){
						transition("konyusaki_maste.php");
					}else if(svr_rtn == '1'){
						alert("購入情報支店テーブルテーブルに登録されているため、\n削除できない購入先がありました。");
					}else{
						alert(svr_rtn);
					}

				} else {
					alert("ERROR: " + objReq.statusText);
				}
			}
		}
        		/**********************************************************/
		/***** 入力フォームをクリアする                       *****/
		/**********************************************************/
		function clear_input(){
			var hd_busho_id = document.getElementById("hd_konyusaki_cd");
			var busho_cd = document.getElementById("konyusaki_cd");
			var ichiba_busho_cd = document.getElementById("old_konyusaki_cd");
			var disp_busho_nm = document.getElementById("konyusaki_nm");
			
			hd_konyusaki_cd.value = "";
		    konyusaki_cd.value = "";
			old_konyusaki_cd.value = "";
			konyusaki_nm.value = "";
		}
		flag =false;
		function allChange(){
			flag = !flag; // trueとfalseの切り替え ! 否定演算子
			var elem = document.getElementsByName("s_konyusaki_cd");
			for(var i=0; i < elem.length; i++){
				elem[i].checked = flag;
			}
		}
			   


        
        
</script>
<div class="container-lg">  
   <div class="title_bar">購入先マスタメンテ</div>
    <div class="border_line1"></div>
<form name="form1" class="form-inline" method="POST">
  <table id="tbl0032" class="table">
  <div style=" text-align:left; margin:0 auto;">
              <div class="d-flex">
                  <div class="col-md-4">
    				   <label for="konyusaki_cd" >購入先コード：</label>
					   <input type="text"  id="konyusaki_cd"  class="w-auto">
                   </div>
                   <div class="col-md-4">
					   <label for="old_konyusaki_cd">旧購入先コード：</label>
					   <input type="text" id="old_konyusaki_cd" class="w-auto">
                   </div>
                   <div class="col-md-4">
                       <label for="konyusaki_nm">購入先名：</label>
	       			   <input type="text" id="konyusaki_nm" class="w-auto">
                   </div>
                </div>
  </div>            
                <div style="text-align:center; padding:30px 0 0 0;">
                    <input type="button" value="クリア"  class="btn btn-primary" onclick="clear_input()">
                    <input type="button" value="登録" class="btn btn-primary" id="" onclick="register()">
                </div>
                <div class="border_line1"></div> 

                <input  type="button" value="削　除" class="btn btn-primary" id="" onclick="del()">
                <input type="button" value="全選択/解" class="btn btn-primary" id="" onclick="allChange()">

        <tr>
            <td style="background:   #f5f5f5" class="border text-center">選択</td>
            <td style="background:   #f5f5f5" class="border text-center">購入先コード</td>
            <td style="background:   #f5f5f5" class="border text-center">旧購入先コード</td>
            <td style="background:   #f5f5f5" class="border text-center">購入先名</td>
        </tr>  
        <? 
       foreach($konyusakisql_row as $konyusakisql_rows){
       ?>
        <tr>
            <td class="border text-center col-md-1">   
            <label><input type="checkbox"  name="s_konyusaki_cd" id="s_konyusaki_cd<?= $i ?>" value="<?= $konyusakisql_rows['konyusaki_cd'] ?>"></label>
            </td>
            <td class="border text-center col-md-2" onclick="intoForm(<?= $i ?>)"><div name="l_konyusaki_cd<?= $i ?>" id="l_konyusaki_cd<?= $i ?>"><?= $konyusakisql_rows['konyusaki_cd']?></div></td>        

            <td class="border text-center col-md-3" onclick="intoForm(<?= $i ?>)"> <div name="l_old_konyusaki_cd<?= $i ?>" id="l_old_konyusaki_cd<?= $i ?>" ><?= $konyusakisql_rows['old_konyusaki_cd']?></div></td>      

            <td class="border text-center col-md-3" onclick="intoForm(<?= $i ?>)"> <div name="l_konyusaki_nm<?= $i ?>" id="l_konyusaki_nm<?= $i ?>" ><?= $konyusakisql_rows['konyusaki_nm']?></div></td>  
        </tr>           
       <?
    $i++;
    }
   ?>
  </table>
  <input type="hidden" name="hd_konyusaki_cd" id="hd_konyusaki_cd" value=""> <!-- 更新対象の購入先ＩＤ -->    
</div>
</form>
</body>
</html>