<?

$sel_himmoku_name = "";
if(isset($_POST["sel_himmoku_name"]) && ($_POST["sel_himmoku_name"])){
	$sel_himmoku_name = $_POST["sel_himmoku_name"];
}

$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());

} 
$mst_himmoku_rows = array();

function getSogisha($sel_himmoku_name){
	// 品目データをマスタから取得する
	$sqlWhere = "";
	if($sel_himmoku_name){
		$sqlWhere = "WHERE  himmoku_nm like '%".$sel_himmoku_name."%'";
	}
	$sqlString = "SELECT himmoku_cd,	ichiba_himmoku_cd,	himmoku_nm	 FROM  mst_himmoku ".$sqlWhere."";
//	$sqlString = "SELECT * FROM master_customer WHERE bmn_code = '" . $bmn_code . "' order by customer_name_kn";
	$cd_result = pg_query($sqlString);
	if (!$cd_result) {
		die('クエリーが失敗しました。'.pg_last_error());
		print('クエリーが失敗しました。'.$sqlString);
	}
	$rows = pg_fetch_all($cd_result);

error_log("********* start ************************************\n", 3, 'c:/WWW/siire/log/app.log');
error_log("bbbb\n", 3, 'c:/WWW/siire/log/app.log');
error_log("********* end ************************************\n", 3, 'c:/WWW/siire/log/app.log');
return $rows;
}

$mst_himmoku_rows = getSogisha($sel_himmoku_name);
    

    $i = 0
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
    <title>品目マスタ</title>
    <script>
        /**********************************************************/
		/***** 一覧の全行を全て選択/全て解除                  *****/
		/**********************************************************/
		flag =false;
		function allChange(){
			flag = !flag; // trueとfalseの切り替え ! 否定演算子
			var elem = document.getElementsByName("s_himmoku_cd");
			for(var i=0; i < elem.length; i++){
				elem[i].checked = flag;
			}
		}





        /*********************************************************/
		/* 検索条件をクリア                                      */
		/*********************************************************/
		function clear_keyword(){
			document.getElementById("sel_himmoku_name").value = "";
		}



        /*********************************************************/
		/* 品目データ条件検索処理                                */
		/*********************************************************/
		function sel(){
			transition("hinmoku_maste.php");
		}

        function del(){
			//***** 削除する顧客ＩＤをすべて取得 *****//
			var myTbl = document.getElementById('tbl0031');
			var customer_id = "";
			var delDt = "";

//test start
//alert("length = "+ myTbl.rows.length);
//test end

			//行位置取得
			for (var i=0; i < myTbl.rows.length -1; i++) {
				if(document.getElementById('s_himmoku_cd' + i).checked){
					if(customer_id == ""){
						customer_id = document.getElementById('s_himmoku_cd' + i).value;
					}else{
						customer_id = customer_id + "$" + document.getElementById('s_himmoku_cd' + i).value;
					}
				}
			}

			if(customer_id != ""){
				if(confirm("選択した情報を削除します。\nよろしいですか？")){
					var SendParam = "val_parm=" + customer_id;

					sendRequest_del(SendParam, 'POST', 'server_master_himmoku_delete.php', true);
				}
			}else{
				alert("選択されていません。");
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
function procReqChange_del() {
if (objReq.readyState == 4) {				//4 = complete（読み込んだデータの解析完了、または失敗した。つまり処理が終わった） 

    if (objReq.status == 200) {				//成功!!
///			alert("データ削除 " + objReq.responseText);
        var svr_rtn = objReq.responseText;
///			SERVER_RETURN = eval(objReq.responseText);
///			var svr_rtn = eval(objReq.responseText);


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
                transition("hinmoku_maste.php");
            }else if(delete_cnt == 0){
                alert("購入情報品目テーブルに登録されているため１件も削除されませんでした。\n削除件数：　"+delete_cnt);
                transition("hinmoku_maste.php");
            }else{
                alert("購入情報品目テーブルに登録されている顧客は削除できませんでした。\n削除件数：　"+delete_cnt+"\n削除不可件数： "+dempyo_umu_cnt);
                transition("hinmoku_maste.php");
            }
        }else{
            //以上終了
            alert(svr_rtn);
        }

//					if(svr_rtn != '0'){
//						alert(svr_rtn);
//					}else{
//						transition("master_customer.php");
//					}

    } else {
        alert("ERROR: " + objReq.statusText);
    }
}
}


	    /*********************************************************/
		/* 顧客データ登録用画面へ遷移（更新）                    */
		/*********************************************************/
		function editCustomer(idx){
			s_himmoku_cd = document.getElementById("s_himmoku_cd"+idx);
			hd_himmoku_cd = document.getElementById("hd_himmoku_cd");
			hd_himmoku_cd.value = s_himmoku_cd.value;
			transition("master_himmoku_edit.php");
		}

    </script>
</head>
<body>
<div class="container-lg">
    <div class="title_bar">品目マスタ</div>
    <div class="border_line1"></div>



<form name="form1"  method="POST">
  <table id="tbl0031" class="table">
        <div style="text-align:center; padding:30px 0 0 0;">
			<label for="sel_himmoku_name" accesskey="n">品目名：</label>
			<input type="text" id="sel_himmoku_name" name="sel_himmoku_name" value="<?= $sel_himmoku_name ?>" style="width:280px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" value="クリア" class="btn btn-primary" id="" onclick="clear_keyword();">
			<input type="button" value="検索" class="btn btn-primary" id="" onclick="sel();">
		</div>
            <div class="border_line1"></div> 
            <input type="button" value="新規登録" class="btn btn-primary" id="" onclick="newCustomer()">
              <input  type="button" value="削　除" class="btn btn-primary" id="" onclick="del()">
              <input type="button" value="全選択/解" class="btn btn-primary" id="" onclick="allChange()">
        <tr>
            <td style="background:   #f5f5f5" class="border text-center">選択</td>
            <td style="background:   #f5f5f5" class="border text-center">品目名</td>
            <td style="background:   #f5f5f5" class="border text-center">品目コード</td>
            <td style="background:   #f5f5f5" class="border text-center">市場品目コード</td>
        </tr>  
       <? 
       foreach($mst_himmoku_rows as $mst_himmoku_row){
       ?>
        <tr>
            <td class="border text-center col-md-1">   
            <label><input type="checkbox"  name="s_himmoku_cd" id="s_himmoku_cd<?= $i ?>" value="<?= $mst_himmoku_row['himmoku_cd'] ?>"></label>
            </td>
            <td class="border text-center col-md-6" onclick="editCustomer(<?= $i ?>)"><div name="l_himmoku_nm<?= $i ?>" id="l_himmoku_nm<?= $i ?>"><?= $mst_himmoku_row['himmoku_nm']?></div></td>        

            <td class="border text-center col-md-1" onclick="editCustomer(<?= $i ?>)"> <div name="l_himmoku_cd<?= $i ?>" id="l_himmoku_cd<?= $i ?>" ><?= $mst_himmoku_row['himmoku_cd']?></div></td>      

            <td class="border text-center col-md-1" onclick="editCustomer(<?= $i ?>)"> <div name="l_ichiba_himmoku_cd<?= $i ?>" id="l_ichiba_himmoku_cd<?= $i ?>" ><?= $mst_himmoku_row['ichiba_himmoku_cd']?></div></td>  
        </tr>           
       <?
    $i++;
    }
   ?>
  </table>
<input type="hidden" name="hd_himmoku_cd" id="hd_himmoku_cd" value=""> <!-- 削除対象の品目ＩＤ -->
</form>
</div>

</body>
</html>