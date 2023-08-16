<?
$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());

} 

    //  部署マスタのデータを持ってくる
 $bushosql = pg_query("SELECT busho_id ,	busho_cd,	ichiba_busho_cd,	busho_nm,	disp_busho_nm	
                    FROM  mst_busho");
    $bushosql_row  =  pg_fetch_all($bushosql);

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
    <title>部署マスタメンテ</title>
</head>
<body>
<div class="container-lg">
    <div class="title_bar">部署マスタメンテ</div>
    <div class="border_line1"></div>
    <script>
        /**********************************************************************/
		/***** クリックした一覧のデータを画面上部のフォームへ展開する     *****/
		/**********************************************************************/
  function intoForm(idx){
    var l_busho_id = document.getElementById("l_busho_id" + idx);
    var l_busho_cd = document.getElementById("l_busho_cd" + idx);
    var l_ichiba_busho_cd = document.getElementById("l_ichiba_busho_cd" + idx);
    var l_busho_nm = document.getElementById("l_busho_nm" + idx);
    var l_disp_busho_nm = document.getElementById("l_disp_busho_nm" + idx);
    // 入力フォーム
    var busho_cd = document.getElementById("busho_cd");
    var ichiba_busho_cd = document.getElementById("ichiba_busho_cd");
    var busho_nm = document.getElementById("busho_nm");
    var disp_busho_nm = document.getElementById("disp_busho_nm");
    var hd_busho_id = document.getElementById("hd_busho_id");

      hd_busho_id.value = l_busho_id.value;
      busho_cd.value  = l_busho_cd.innerText;
      ichiba_busho_cd.value = l_ichiba_busho_cd.innerText;
      busho_nm.value = l_busho_nm.innerText;
       disp_busho_nm.value = l_disp_busho_nm.innerText;
    }



    function register(){
      // 入力チェック
      var wk_busho_cd = document.getElementById("busho_cd");
      if(!wk_busho_cd.value){
        self.focus();
				wk_busho_cd.focus();
				wk_busho_cd.select();
				alert("「部署コード」を入力して下さい。");
				return false;
    }
	// 小文字の数値じゃないならアラートを出す
	if (wk_busho_cd.value.match(/[^0-9]/g)){
		self.focus();
		wk_busho_cd.focus();
		wk_busho_cd.select();
		alert("部署コードに小文字の数値以外が入力されています");
		return false;
	  }
    var wk_ichiba_busho_cd = document.getElementById("ichiba_busho_cd");
    if(!wk_ichiba_busho_cd.value){
      self.focus();
      wk_ichiba_busho_cd.focus();
      wk_ichiba_busho_cd.select();
      alert("「福岡支部部署コード」を入力して下さい。");
      return false;
  }
  // 小文字の数値じゃないならアラートを出す
	if (wk_ichiba_busho_cd.value.match(/[^0-9]/g)){
		self.focus();
		wk_ichiba_busho_cd.focus();
		wk_ichiba_busho_cd.select();
		alert("福岡部署コードに小文字の数値以外が入力されています");
		return false;
	  }
  var wk_busho_nm = document.getElementById("busho_nm");
  if(!wk_busho_nm.value){
    self.focus();
    wk_busho_nm.focus();
    wk_busho_nm.select();
    alert("「部署名」を入力して下さい。");
    return false;
}
var wk_disp_busho_nm = document.getElementById("disp_busho_nm");
if(!wk_disp_busho_nm.value){
  self.focus();
  wk_disp_busho_nm.focus();
  wk_disp_busho_nm.select();
  alert("「部署表示名」を入力して下さい。");
  return false;
}
		    /**************************************/
			/***** 部署データ登録処理           *****/
			/**************************************/   
      var busho_id = document.getElementById("hd_busho_id").value
      var busho_cd = document.getElementById("busho_cd").value
      var ichiba_busho_cd = document.getElementById("ichiba_busho_cd").value
      var busho_nm = document.getElementById("busho_nm").value
      var disp_busho_nm = document.getElementById("disp_busho_nm").value


      var SendParam = "val_parm=" + ";" + busho_id + ";" + busho_cd + ";" + ichiba_busho_cd + ";" + busho_nm + ";" + disp_busho_nm;
        
      
			if(busho_id){
				//更新処理
				REG_MODE = "update";
			
				sendRequest_reg(SendParam, 'POST', 'busho_update.php', true);
			}else{
				//新規登録処理
				REG_MODE = "insert";
     
				sendRequest_reg(SendParam, 'POST', 'busho_insert.php', true);
			}
		}
  




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
		///			SERVER_RETURN = eval(objReq.responseText);

					if(REG_MODE == "update"){
						if(svr_rtn == 'その部署コードは既に登録されています'){
							alert('その部署コードは既に登録されています')
						}else{
							alert('更新しました。');
							transition('busyo_maste_mainte.php');
						}
					}else if(REG_MODE == "insert"){
						if(svr_rtn == 'その部署コードは既に登録されています'){
						alert('その部署コードは既に登録されています')
						transition('busyo_maste_mainte.php');
						}else{
							alert('登録しました。');
							transition('busyo_maste_mainte.php');
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
			var myTbl = document.getElementById('tbl0031');
			var dempyo_id = "";
						var delDt = "";			
			
				//行位置取得
				for (var i=0; i < myTbl.rows.length -1; i++) {              
							if(document.getElementById('l_busho_id' + i).checked){
								if(dempyo_id == ""){
									dempyo_id = document.getElementById('l_busho_id' + i).value;
								}else{
									dempyo_id = dempyo_id + "$" + document.getElementById('l_busho_id' + i).value;
								}
							}
						}
				 
					if(dempyo_id != ""){
							if(confirm("選択した情報を削除します。\nよろしいですか？")){
								var SendParam = "val_parm=" + dempyo_id;
								sendRequest_del(SendParam, 'POST', 'busho_delete.php', true);
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
						transition("busyo_maste_mainte.php");
					}else if(svr_rtn == '1'){
						alert("購入情報支店テーブルに登録されているため、\n削除できない部署がありました。");
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
			var hd_busho_id = document.getElementById("hd_busho_id");
			var busho_cd = document.getElementById("busho_cd");
			var ichiba_busho_cd = document.getElementById("ichiba_busho_cd");
			var busho_nm = document.getElementById("busho_nm");
			var disp_busho_nm = document.getElementById("disp_busho_nm");
			
			hd_busho_id.value = "";
		    busho_cd.value = "";
			ichiba_busho_cd.value = "";
			busho_nm.value = "";
			disp_busho_nm.value = "";
		}
		flag =false;
		function allChange(){
			flag = !flag; // trueとfalseの切り替え ! 否定演算子
			var elem = document.getElementsByName("l_busho_id");
			for(var i=0; i < elem.length; i++){
				elem[i].checked = flag;
			}
		}
</script>

 <form name="form1" class=”form-inline” method="POST">
  <table id="tbl0031" class="table">
      <div style=" text-align:left; margin:0 auto;">
              <div class="d-flex">
                  <div class="col-md-3">
    				   <label for="busho_cd" >部署コード：</label>
					   <input type="text"  id="busho_cd"  class="w-auto">
                   </div>
                   <div class="col-md-3">
					   <label for="ichiba_busho_cd">市場部署コード：</label>
					   <input type="text" id="ichiba_busho_cd" class="w-auto">
                   </div>
                   <div class="col-md-3">
                       <label for="busho_nm">部署名：</label>
	       			   <input type="text" id="busho_nm" class="w-auto">
                   </div>
                   <div class="col-md-3">
                       <label for="disp_busho_nm">画面表示名：</label>
					   <input type="text" id="disp_busho_nm" class="w-auto">
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
            <td style="background:   #f5f5f5" class="border text-center">部署コード</td>
            <td style="background:   #f5f5f5" class="border text-center">市場部署コード</td>
            <td style="background:   #f5f5f5" class="border text-center">部署名</td>
            <td style="background:   #f5f5f5" class="border text-center">画面表示名</td> 
        </tr>  
       <? 
       foreach($bushosql_row as $bushosql_rows){
       ?>
        <tr>
            <td class="border text-center col-md-1">   
            <label><input type="checkbox"  name="l_busho_id" id="l_busho_id<?= $i ?>" value="<?= $bushosql_rows['busho_id'] ?>"></label>
            </td>
            <td class="border text-center col-md-2" onclick="intoForm(<?= $i ?>)"><div name="l_busho_cd<?= $i ?>" id="l_busho_cd<?= $i ?>"><?= $bushosql_rows['busho_cd']?></div></td>        

            <td class="border text-center col-md-3" onclick="intoForm(<?= $i ?>)"> <div name="l_ichiba_busho_cd<?= $i ?>" id="l_ichiba_busho_cd<?= $i ?>" ><?= $bushosql_rows['ichiba_busho_cd']?></div></td>      

            <td class="border text-center col-md-3" onclick="intoForm(<?= $i ?>)"> <div name="l_busho_nm<?= $i ?>" id="l_busho_nm<?= $i ?>" ><?= $bushosql_rows['busho_nm']?></div></td>  

            <td class="border text-center" onclick="intoForm(<?= $i ?>)"> <div name="l_disp_busho_nm<?= $i ?>" id="l_disp_busho_nm<?= $i ?>" ><?= $bushosql_rows['disp_busho_nm']?></div></td> 
        </tr>           
       <?
    $i++;
    }
   ?>
  </table>
<input type="hidden" name="hd_busho_id" id="hd_busho_id" value=""> <!-- 更新対象の部署ＩＤ -->
</form>
</div>

</body>
</html>