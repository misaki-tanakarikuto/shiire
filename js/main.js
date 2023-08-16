// function transition(val){
//   document.form1.action=val;
//   document.form1.submit();
// //		return false;
// }



//********************//
//***** 定数定義 *****//
//********************//
//手当ＩＤ
var YOBI_TEATE_1       = 1;		//手当予備１
var YOBI_TEATE_2       = 2;		//手当予備２
var YOBI_TEATE_3       = 3;		//手当予備３
var YOBI_TEATE_4       = 4;		//手当予備４
var YOBI_TEATE_5       = 5;		//手当予備５
var YOBI_TEATE_6       = 6;		//手当予備６
var TOSI_TEATE         = 7;		//都市手当
var CHOSEI_TEATE       = 8;		//調整手当
var TENKINJUTAKU_TEATE = 9;		//転勤住宅手当
var TANSINFUNIN_TEATE  = 10;	//単身赴任手当
var KAZOKU_TEATE       = 11;	//家族手当
var KORITU_ZAN_TEATE   = 12;	//効率残業手当
var KAIKIN_TEATE       = 13;	//皆勤手当
var SHOREI_TEATE       = 14;	//奨励手当
//↓手当ＩＤ変更前
//var TOSI_TEATE = 1;				//都市手当
//var CHOSEI_TEATE = 2;			//調整手当
//var TENKINJUTAKU_TEATE = 3;		//転勤住宅手当
//var TANSINFUNIN_TEATE = 4;		//単身赴任手当
//var KAZOKU_TEATE = 5;			//家族手当
//var KORITU_ZAN_TEATE = 6;		//効率残業手当
//var KAIKIN_TEATE = 7;			//皆勤手当
//var SHOREI_TEATE = 8;			//奨励手当
//var YOBI_TEATE_1 = 1001;		//手当予備１
//var YOBI_TEATE_2 = 1002;		//手当予備２
//var YOBI_TEATE_3 = 1003;		//手当予備３
//var YOBI_TEATE_4 = 1004;		//手当予備４
//var YOBI_TEATE_5 = 1005;		//手当予備５


//月給・年俸
var GEKKYU_CODE = 0;		//月給
var NEMPO_CODE = 1;			//年俸
var GEKKYU = "月給";		//月給
var NEMPO = "年俸";			//年俸

//確定状態
var MIKAKUTEI_CODE = 0;			//未確定
var KARIKAKUTEI_CODE = 1;		//仮確定
var KAKUTEI_CODE = 2;			//確定済
var MIKAKUTEI = "未確定";		//未確定
var KARIKAKUTEI = "仮確定";		//仮確定
var KAKUTEI = "確定済";			//確定済

//職責
var DUTIES_IPPAN_CODE = 0;			//一般
var DUTIES_SHUNIN_CODE = 4000;		//主任
var DUTIES_KAKARICHO_CODE = 5000;	//係長
var DUTIES_KACHO_DAIRI_CODE = 6000;	//課長代理
var DUTIES_KACHO_CODE = 7000;		//課長
var DUTIES_BUCHO_DAIRI_CODE = 8000;	//部長代理
var DUTIES_BUCHO_CODE = 9000;		//部長
var DUTIES_SIKKOYAKUIN_CODE = 9700;		//執行役員（本部長）
var DUTIES_IPPAN = "一般";			//一般
var DUTIES_SHUNIN = "主任";		//主任
var DUTIES_KAKARICHO = "係長";	//係長
var DUTIES_KACHO_DAIRI = "課長代理";	//課長代理
var DUTIES_KACHO = "課長";		//課長
var DUTIES_BUCHO_DAIRI = "部長代理";	//部長代理
var DUTIES_BUCHO = "部長";		//部長
var DUTIES_SIKKOYAKUIN = "執行役員";	//執行役員（本部長）

//その他
const WEEK_PER_YEAR = 52.14;	//年間の週の数






String.prototype.trim = function() {
    return this.replace(/^[\s　]+|[\s　]+$/g, '');
////	return this.replace(/^\s+|\s+$/g, "");
}

	function login(){
		document.form1.action="main_menu.php";
		document.form1.submit();
	}

	function logout(){
		if(confirm('ログアウトします。')){
			document.form1.action="logout.php";
			document.form1.submit();
		}
	}

	function transition(val){
		document.form1.action=val;
		document.form1.submit();
//		return false;
	}

	/**********************************************************************/
	/***** ヘッダー部分の高さを取得し、データ部分の表示位置を調整する *****/
	/**********************************************************************/
	function disp_head_body(){

		//************************************
		//画面ヘッダー部分の高さを取得
		//************************************
		// head_height = document.getElementById("headerArea").offsetHeight;
		head_height = document.getElementById("headerArea").clientHeight;

		//**************************************
		//微調整
		//**************************************
		if(2 <= head_height){
			head_height = head_height - 2;
		}else{
			head_height = 0;
		}
//test start
// console.log("head_height= "+head_height);
//test end
		var body = document.getElementsByTagName('body')[0];
		body.style.paddingTop = head_height + "px";
//		document.getElementById("contentsArea").style.paddingTop = head_height + "px";
	}



//	//***** テーブルマウスオーバー時の色変更(セレクトBOX無し) *****//
//	function cellOver(obj){
//		//main.cssで、table001のtdのカラーを設定しているため、tdの色が変更できない。とりあえず文字色のみ変更
//		document.getElementById("tr" + obj).style.backgroundColor = "#ece9d8";
////		return false;
//	}
//	function cellOut(obj){
//		document.getElementById("tr" + obj).style.backgroundColor = "#f3f3f3";
////		return false;
//	}
//
//	//***** テーブルマウスオーバー時の色変更(セレクトBOX有り) *****//
//	function cellOverSel(obj){
//		//main.cssで、table001のtdのカラーを設定しているため、tdの色が変更できない。とりあえず文字色のみ変更
//////		document.getElementById("tr_sel_" + obj).style.backgroundColor = "#ece9d8";
//		document.getElementById("tr" + obj).style.backgroundColor = "#ece9d8";
///*		document.getElementById("tr_cstmr" + obj).style.backgroundColor = "#ece9d8";*/
//////		document.getElementById("tr_order" + obj).style.backgroundColor = "#ece9d8";
//////		document.getElementById("tr_genka" + obj).style.backgroundColor = "#ece9d8";
//////		document.getElementById("tr_seikyu" + obj).style.backgroundColor = "#ece9d8";
////		return false;
//	}
//	function cellOutSel(obj){
//////		document.getElementById("tr_sel_" + obj).style.backgroundColor = "#f3f3f3";
//		document.getElementById("tr" + obj).style.backgroundColor = "#f3f3f3";
///*		document.getElementById("tr_cstmr" + obj).style.backgroundColor = "#f3f3f3";*/
//////		document.getElementById("tr_order" + obj).style.backgroundColor = "#f3f3f3";
//////		document.getElementById("tr_genka" + obj).style.backgroundColor = "#f3f3f3";
//////		document.getElementById("tr_seikyu" + obj).style.backgroundColor = "#f3f3f3";
////		return false;
//	}
//
//	//***** テーブルマウスオーバー時の色変更(セレクトBOX有り) "発送済"の行 *****//
//	function cellOverSel_hzumi(obj){
//		document.getElementById("tr" + obj).style.backgroundColor = "#ffb2bb";
//////		document.getElementById("tr" + obj).style.backgroundColor = "#ffbaba";
//	}
//	function cellOutSel_hzumi(obj){
//		document.getElementById("tr" + obj).style.backgroundColor = "#ff98a4";
//////		document.getElementById("tr" + obj).style.backgroundColor = "#ff8080";
//	}
//
//	//***** テーブルマウスオーバー時の色変更(セレクトBOX有り) "未請求"の行 *****//
//	function cellOverSel_miseiq(obj){
//		document.getElementById("tr" + obj).style.backgroundColor = "#caf4eb";
////		document.getElementById("tr" + obj).style.backgroundColor = "#98d998";
//	}
//	function cellOutSel_miseiq(obj){
//		document.getElementById("tr" + obj).style.backgroundColor = "#a0e9d9";
////		document.getElementById("tr" + obj).style.backgroundColor = "#7cfb7c";
//	}
//
//	//***** テーブルマウスオーバー時の色変更(セレクトBOX有り) "不要"の行 *****//
//	function cellOverSel_fuyo(obj){
//		document.getElementById("tr" + obj).style.backgroundColor = "#dbdbdb";
//	}
//	function cellOutSel_fuyo(obj){
//		document.getElementById("tr" + obj).style.backgroundColor = "#b6b6b6";
//	}
//
//	//***** テーブルマウスオーバー時の色変更(セレクトBOX有り) *****//
//	function cellOverSelChumon(obj){
//		//main.cssで、table001のtdのカラーを設定しているため、tdの色が変更できない。とりあえず文字色のみ変更
//		document.getElementById("tr_sel_" + obj).style.backgroundColor = "#ece9d8";
//		document.getElementById("tr" + obj).style.backgroundColor = "#ece9d8";
//	}
//	function cellOutSelChumon(obj){
//		document.getElementById("tr_sel_" + obj).style.backgroundColor = "#f3f3f3";
//		document.getElementById("tr" + obj).style.backgroundColor = "#f3f3f3";
//	}
//
//	//***** テーブルタイトルセルマウスオーバー時の色変更 *****//
//	function cellOverTitle(obj){
//		obj.style.backgroundColor = "#dceaff";
//
////		document.getElementById(obj).style.backgroundColor = "#ece9d8";
//	}
//	function cellOutTitle(obj){
//		obj.style.backgroundColor = "#d0e3ff";
////		document.getElementById(obj).style.backgroundColor = "#f3f3f3";
//	}
	/****************************************************************
	 * 全角から半角への変換関数
	 * 入力値の英数記号を半角変換して返却
	 * [引数]   strVal: 入力値
	 * [返却値] String(): 半角変換された文字列
	****************************************************************/
	function toHalfWidth(strVal){
	  // 半角変換
	  var halfVal = strVal.replace(/[！-～]/g,
	    function( tmpStr ) {
	      // 文字コードをシフト
	      return String.fromCharCode( tmpStr.charCodeAt(0) - 0xFEE0 );
	    }
	  );
	 
	  // 文字コードシフトで対応できない文字の変換
	  return halfVal.replace(/”/g, "\"")
	    .replace(/’/g, "'")
	    .replace(/‘/g, "`")
	    .replace(/￥/g, "\\")
	    .replace(/　/g, " ")
	    .replace(/〜/g, "~");
	}	
	/****************************************************************
	* 機　能： 入力された値が郵便番号で「XXXXXXX」になっているか調べる
	* 引　数： pCodeStr　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckpcode(pCodeStr) {
		if(pCodeStr.match(/^\d{3}\-\d{4}$/)){		//「XXX-XXXX」形式
//		if(pCodeStr.match(/^\d{7}$/)){
			return true;
		}else{
			return false;
		}
	}

	/****************************************************************
	* 機　能： 入力された値が日付で"YYYY/MM/DD"形式になっているか調べる
	* 引　数： dateStr　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckDate(dateStr) {
	    // 正規表現による書式チェック
	    if(!dateStr.match(/^\d{4}\/\d{2}\/\d{2}$/)){
	        return false;
	    }
	    var vYear = dateStr.substr(0, 4) - 0;
	    var vMonth = dateStr.substr(5, 2) - 1; // Javascriptは、0-11で表現
	    var vDay = dateStr.substr(8, 2) - 0;
	    // 月,日の妥当性チェック
	    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
	        var vDt = new Date(vYear, vMonth, vDay);
	        if(isNaN(vDt)){
	            return false;
	        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}

	/****************************************************************
	* 機　能： 入力された値が日付で"YYYY-MM-DD"形式になっているか調べる
	* 引　数： dateStr　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckDateH(dateStr) {
	    // 正規表現による書式チェック
	    if(!dateStr.match(/^\d{4}\-\d{2}\-\d{2}$/)){
	        return false;
	    }
	    var vYear = dateStr.substr(0, 4) - 0;
	    var vMonth = dateStr.substr(5, 2) - 1; // Javascriptは、0-11で表現
	    var vDay = dateStr.substr(8, 2) - 0;
	    // 月,日の妥当性チェック
	    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
	        var vDt = new Date(vYear, vMonth, vDay);
	        if(isNaN(vDt)){
	            return false;
	        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}

	/****************************************************************
	* 機　能： 入力された値が日付で"YYYY-MM-DD"形式になっているか調べる
	*          月と日は１桁でもよい。
	* 引　数： dateStr　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckDateH1ketaOk(dateStr) {
	    // 正規表現による書式チェック
	    if(!dateStr.match(/^\d{4}\-\d{1,2}\-\d{1,2}$/)){
	        return false;
	    }
		var wk_ymd = dateStr.split("-");
	    var vYear = wk_ymd[0] - 0;
	    var vMonth = wk_ymd[1] - 1; // Javascriptは、0-11で表現
	    var vDay = wk_ymd[2] - 0;
	    // 月,日の妥当性チェック
	    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
	        var vDt = new Date(vYear, vMonth, vDay);
	        if(isNaN(vDt)){
	            return false;
	        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}

	/****************************************************************
	* 機　能： 入力された値が日付で"YYYY/MM"形式になっているか調べる
	*          月は１桁でもよい。
	* 引　数： dateStr　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckYmH1ketaOk(ymStr) {
	    // 正規表現による書式チェック
	    if(!ymStr.match(/^\d{4}\/\d{1,2}$/)){
	        return false;
	    }
	    var vYear = ymStr.substr(0, 4) - 0;
	    var vMonth = ymStr.substr(5, 2) - 1; // Javascriptは、0-11で表現
//	    var vDay = ymStr.substr(8, 2) - 0;
	    // 月,日の妥当性チェック
	    if(vMonth >= 0 && vMonth <= 11){
//	    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
	        var vDt = new Date(vYear, vMonth, 1);
//	        var vDt = new Date(vYear, vMonth, vDay);
	        if(isNaN(vDt)){
	            return false;
	        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth){
//	        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}

	/****************************************************************
	* 機　能： 入力された値が日付で"MM/DD"形式になっているか調べる
	*          月と日は１桁でもよい。
	* 引　数： dateStr　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckMD1ketaOk(dateStr) {
	    // 正規表現による書式チェック
	    if(!dateStr.match(/^\d{1,2}\/\d{1,2}$/)){
	        return false;
	    }
		var wk_ymd = dateStr.split("/");
	    var vYear = 2000;
//	    var vYear = wk_ymd[0] - 0;
	    var vMonth = wk_ymd[0] - 1; // Javascriptは、0-11で表現
	    var vDay = wk_ymd[1] - 0;
	    // 月,日の妥当性チェック
	    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
	        var vDt = new Date(vYear, vMonth, vDay);
	        if(isNaN(vDt)){
	            return false;
	        }else if(vDt.getMonth() == vMonth && vDt.getDate() == vDay){
//	        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}

	/****************************************************************
	* 機　能： 入力された値が日付で"MM-DD"形式になっているか調べる
	*          月と日は１桁でもよい。
	* 引　数： dateStr　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckMDH1ketaOk(dateStr) {
	    // 正規表現による書式チェック
	    if(!dateStr.match(/^\d{1,2}\-\d{1,2}$/)){
	        return false;
	    }
		var wk_ymd = dateStr.split("-");
	    var vYear = 2000;
	    var vMonth = wk_ymd[0] - 1; // Javascriptは、0-11で表現
	    var vDay = wk_ymd[1] - 0;
	    // 月,日の妥当性チェック
	    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
	        var vDt = new Date(vYear, vMonth, vDay);
	        if(isNaN(vDt)){
	            return false;
	        }else if(vDt.getMonth() == vMonth && vDt.getDate() == vDay){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}

//	/****************************************************************
//	* 機　能： 入力された値が日付で"YYYY-MM"形式になっているか調べる
//	*          月は１桁でもよい。
//	* 引　数： dateStr　入力された値
//	* 戻り値： 正：true　不正：false
//	****************************************************************/
//	function ckYmH1ketaOk(ymStr) {
//	    // 正規表現による書式チェック
//	    if(!ymStr.match(/^\d{4}\-\d{1,2}$/)){
//	        return false;
//	    }
//	    var vYear = ymStr.substr(0, 4) - 0;
//	    var vMonth = ymStr.substr(5, 2) - 1; // Javascriptは、0-11で表現
////	    var vDay = ymStr.substr(8, 2) - 0;
//	    // 月,日の妥当性チェック
//	    if(vMonth >= 0 && vMonth <= 11){
////	    if(vMonth >= 0 && vMonth <= 11 && vDay >= 1 && vDay <= 31){
//	        var vDt = new Date(vYear, vMonth, 1);
////	        var vDt = new Date(vYear, vMonth, vDay);
//	        if(isNaN(vDt)){
//	            return false;
//	        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth){
////	        }else if(vDt.getFullYear() == vYear && vDt.getMonth() == vMonth && vDt.getDate() == vDay){
//	            return true;
//	        }else{
//	            return false;
//	        }
//	    }else{
//	        return false;
//	    }
//	}
	
	/****************************************************************
	* 機　能： 入力された値が電話番号で
	*          「(1～4桁)-XXXX、(2～5桁)-(1～4桁)-XXXX」になっているか調べる
	* 引　数： telStr　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckTel(telStr){
		if(telStr.match(/^\d{1,4}-\d{4}$|^\d{2,5}-\d{1,4}-\d{4}$/)){
			return true;
		}else{
			return false;
		}
	}

	/****************************************************************
	* 機　能： 入力された値が数値であるか調べる（空文字やnullはfalse、マイナス値はtrue）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckNumMinus(str){
		if(str == null || str == ""){
			return false;
		}
		str = str + "";
		if(str.match(/^-?[0-9]+$/)){
			return true;
		}
		return false;
	}

	/****************************************************************
	* 機　能： 入力された値が数値であるか調べる（空文字やnullはfalse）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckNum(str){
		if(str == null || str == ""){
			return false;
		}
		str = str + "";
		if(str.match(/[^0-9]/g)){
			return false;
		}
		return true;
	}

	/****************************************************************
	* 機　能： 入力された値が数値であるか調べる（空文字やnullはtrue）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckNum2(str){
		if(str == null || str == ""){
			return true;
		}

		if(str.match(/[^0-9]/g)){
			return false;
		}

		return true;
	}

	/****************************************************************
	* 機　能： 入力された値が数値であるか調べる（小数も可。空文字やnullはfalse）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckDecimal(str){
		if(str == null || str == ""){
			return false;
		}

		if(!str.match(/^\d+(\.(\d+))*$/)){
			return false;
		}

		return true;
	}

	/****************************************************************
	* 機　能： 入力された値が数値であるか調べる（小数も可。空文字やnullはtrue）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckDecimal2(str){
		if(str == null || str == ""){
			return true;
		}

		if(!str.match(/^\d+(\.(\d+))*$/)){
			return false;
		}

		return true;
	}

	/****************************************************************
	* 機　能： 入力された値が数値であるか調べる
	*（３桁までの整数。少数はあっても無くてもよい。少数ある場合は第一位まで。空文字やnullはfalse）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckNumFormat31(str){
		if(str == null || str == ""){
			return false;
		}

		str = str + "";
		if(!str.match(/^[0-9]{1,3}(\.[0-9])?$/)){
			return false;
		}

		return true;
	}

	/****************************************************************
	* 機　能： 入力された値が金額であるか調べる（空文字やnullはtrue）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckMoney(str){
		if(str == null || str == ""){
			return true;
		}

		if(str.search(/^(0|[1-9]\d{0,2}(,\d{3})*)$/) != -1){
		}else{
			if(str.match(/[^0-9]/g)){
				return false;
			}
////			return false;
		}


		return true;
	}

	/****************************************************************
	* 機　能： 入力された値が整数値かどうか調べる（99.0はtrue）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckInteger(str){
		str = parseFloat(str);
		return Math.round(str) === str;
	}

	/****************************************************************
	* 機　能： 入力された値が正しい西暦（4桁の数字）であるか調べる（空文字やnullはtrue）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckYear(str){
		if(str == null || str == ""){
			return true;
		}

		if(!str.match(/^\d{4}$/)){
//		if(str.search(/\d{4}/) == -1){
//		if(str !== (/\d{4}/)){
//		if(str.search(/^\d{4}$/) == -1){
			return false;
		}
		return true;
	}

	/****************************************************************
	* 機　能： 入力された値が正しい月（２桁の数字）であるか調べる（空文字やnullはtrue）
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckMonth(str){
		if(str == null || str == ""){
			return true;
		}

		if(!str.match(/^\d{2}$/)){
			return false;
		}
		return true;
	}

	/****************************************************************
	* 機　能： 入力された月（"MM"or"M"）形式になっているか調べる
	*          月は１桁でもよい。
	* 引　数： Str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckMonth1ketaOk(Str) {
	    // 正規表現による書式チェック
	    if(!Str.match(/^\d{1,2}$/)){
	        return false;
	    }
	    var vMonth = Str - 0;
	    var vMonth = vMonth - 1; // Javascriptは、0-11で表現
	    // 月の妥当性チェック
	    if(vMonth >= 0 && vMonth <= 11){
	        var vDt = new Date(2016, vMonth, 1);
	        if(isNaN(vDt)){
	            return false;
	        }else if(vDt.getMonth() == vMonth){
	            return true;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}

	/****************************************************************
	* 機　能： 入力された値を3桁でカンマ区切りにする
	* 引　数： str　入力された値
	* 戻り値： 3桁カンマ区切りされた値
	****************************************************************/
	function numberFormat(str) {
		var num = new String(str).replace(/,/g, "");
		while(num != (num = num.replace(/^(-?\d+)(\d{3})/, "$1,$2")));
		return num;
	}

	/****************************************************************
	* 機　能： E-mail形式チェック
	* 引　数： str　入力された値
	* 戻り値： 正：true　不正：false
	****************************************************************/
	function ckEmail(str){
		/* E-mail形式の正規表現パターン */
		/* @が含まれていて、最後が .(ドット)でないなら正しいとする */
		var seiki=/[!#-9A-~]+@+[a-z0-9]+.+[^.]$/i;
		/* 入力された値がパターンにマッチするか調べる */
		if(str.match(seiki)){
//			alert(str.match(/[!#-9A-~]+@+[a-z0-9]+.+[^.]$/i)+"\n\nメールアドレスの形式は正しいです");
//			alert(str.match(seiki)+"\n\nメールアドレスの形式は正しいです");
			return true;
		}else{
//			alert("メールアドレスの形式が不正です");
			return false;
		}
	}

	/****************************************************************
	* 機　能： 年齢取得
	* 引　数： str　生年月日（yyyy-mm-dd）
	* 戻り値： 年齢
	****************************************************************/
	function calculateAge(birthday){
		var today=new Date();
		today=today.getFullYear()*10000+today.getMonth()*100+100+today.getDate();
		birthday=parseInt(birthday.replace(/\//g,''));
//		birthday=parseInt(birthday.replace(/-/g,''));
		return (Math.floor((today-birthday)/10000));
	}

	/****************************************************************
	* 機　能： 全ての文字列 s1 を s2 に置き換える
	* 引　数： 置換対象を含む文字列, 置換対象文字列, 置換後文字列
	* 戻り値： 正：true　不正：false
	****************************************************************/
	// 全ての文字列 s1 を s2 に置き換える  
	function replaceAll(expression, org, dest){
	    return expression.split(org).join(dest);  
	}  

	/****************************************************************
	* 機　能： 前月の「年月」を取得
	* 引　数： year 年
	*       ： month 月
	* 戻り値： 引数で指定した日付の前月の年月
	****************************************************************/
	function getLastMonth(year, month) {
		var wk_year = eval(year);
		var wk_month = eval(month);
		if(wk_month == 1){
			wk_year = wk_year - 1;
			wk_month = 12;
		}else{
			wk_month = wk_month - 1;
		}
		var y = String(wk_year);
		var m = String(100 + wk_month).substr(1,2);
		return [y, m];
	}

	/****************************************************************
	* 機　能： 翌月の「年月」を取得
	* 引　数： year 年
	*       ： month 月
	* 戻り値： 引数で指定した日付の翌月の年月
	****************************************************************/
	function getNextMonth(year, month) {
		var wk_year = eval(year);
		var wk_month = eval(month);
		if(wk_month == 12){
			wk_year = wk_year + 1;
			wk_month = 1;
		}else{
			wk_month = wk_month + 1;
		}
		var y = String(wk_year);
		var m = String(100 + wk_month).substr(1,2);
		return [y, m];
	}

	/****************************************************************
	* 機　能： カンマ編集されている金額からカンマを消す
	* 引　数： obj：対象オブジェクト
	* 戻り値： なし
	****************************************************************/
	function off_format(obj){
		obj.value = obj.value.replace(/,/g, "");
		obj.select();
//		return false;
	}

	/****************************************************************
	* 機　能： カンマ編集されている金額からカンマを消す
	* 引　数： value：対象値
	* 戻り値： なし
	****************************************************************/
	function off_format_val(val){
		val = val + "";	//対象値と文字列にする
		var off_value = val.replace(/,/g, "");
		return off_value;
	}


	/****************************************************************
	* 機　能： カンマ編集されていない金額を３桁区切りでカンマ編集する
	* 引　数： obj：対象オブジェクト
	* 戻り値： なし
	****************************************************************/
	function on_format(obj){
		obj.value = numberFormat(obj.value);
//		return true;
	}

	/****************************************************************
	* 機　能： 何日前、何日後の日付を取得する
	* 引　数： 年、月、日、加算日数(減算はマイナス値)
	* 戻り値： "yyyy/mm/dd"
	****************************************************************/
	function computeDate(year, month, date, addDate){
		var myDate = new Date(year + "/" + month + "/" + date);
		var dayOfMonth = myDate.getDate();
		myDate.setDate(dayOfMonth + addDate);

		yyyy = myDate.getFullYear();
		mm = myDate.getMonth() + 1;
		dd = myDate.getDate();
		if (mm < 10) { mm = "0" + mm; }
		if (dd < 10) { dd = "0" + dd; }
		var ret = yyyy + "/" + mm + "/" + dd;
		return ret;
	}


	/************************************************/
	/***** 強制的にクリックイベントを発生させる *****/
	/************************************************/
	function event_click(obj){
		if(obj.fireEvent){ // for IE
			obj.fireEvent("onclick");
		}else{ // for Firefox, Chrome, Safari
			var evt = document.createEvent("MouseEvents");
			evt.initEvent("click", false, true);
			obj.dispatchEvent(evt);
		}
	}

	/***************************************************************
	* 機能：日付プルダウン作成する。閏年も正しく判定可能
	* 引数：日付プルダウンのselectタグのid、西暦(４桁)、月(１or２桁)
	***************************************************************/
	function generateDay(id, y, m) {
//	  var y = document.getElementById(f + '_year').options[document.getElementById(f + '_year').selectedIndex].text;
//	  var m = document.getElementById(f + '_month').options[document.getElementById(f + '_month').selectedIndex].text;

	  // 閏年判定
		if (2 == m && (0 == y % 400 || (0 == y % 4 && 0 != y % 100))) {
			var last = 29;
		} else {
			var last = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31)[m - 1];
		}

		// 要素取得と初期化
		obj = document.getElementById(id);
		obj.length = 0;

		var valDate;
		
		//  日の要素生成
		for (var i = 0; i < last; i++) {
			valDate = i + 1;
			obj.options[obj.length++] = new Option(valDate + "日", valDate);
//			obj.options[obj.length++] = new Option(i + 1, i + 1);
		}
	}

	/*********************************************************/
	/* 数値のみ入力可能にする（少数不可）                    */
	/*********************************************************/
	function OnlyNumber(evt){

		var evt = evt || window.event;
		var c = evt.keyCode;


//			window.event.returnValue = false;

		// 48～57=0～9のキー、96～105=テンキーの0～9、8=バックスペース、9=タブキー、32=スペースキー、37=左矢印キー、39=右矢印キー、46=Deleteキー、18=Altキー、112～123=F1～F12キー、190=.キー
		if((48<=c && c<=57) || (96<=c && c<=105) || c==8 || c == 9 || c==32 || c == 37 || c == 39 || c == 46 || c == 18 || (112<=c && c<=123)){
//		if((48<=c && c<=57) || (96<=c && c<=105) || c==8 || c == 9 || c==32 || c == 37 || c == 39 || c == 46 || c == 18 || (112<=c && c<=123) || c == 190){
//			if((48<=c && c<=57) || (96<=c && c<=105) || c==8 || c == 9 || c==32 || c == 37 || c == 39 || c == 46 || c == 18 || (112<=c && c<=123) ){
			return true;
		}
		return false;
	}
	/*********************************************************/
	/* 数値のみ入力可能にする（少数可）                      */
	/*********************************************************/
	function OnlyNumberDecimal(evt){

		var evt = evt || window.event;
		var c = evt.keyCode;


//			window.event.returnValue = false;

		// 48～57=0～9のキー、96～105=テンキーの0～9、8=バックスペース、9=タブキー、32=スペースキー、37=左矢印キー、39=右矢印キー、46=Deleteキー、18=Altキー、112～123=F1～F12キー、190=.キー
//		if((48<=c && c<=57) || (96<=c && c<=105) || c==8 || c == 9 || c==32 || c == 37 || c == 39 || c == 46 || c == 18 || (112<=c && c<=123)){
		if((48<=c && c<=57) || (96<=c && c<=105) || c==8 || c == 9 || c==32 || c == 37 || c == 39 || c == 46 || c == 18 || (112<=c && c<=123) || c == 190){
//			if((48<=c && c<=57) || (96<=c && c<=105) || c==8 || c == 9 || c==32 || c == 37 || c == 39 || c == 46 || c == 18 || (112<=c && c<=123) ){
			return true;
		}
		return false;
	}

	//**********************************************************************
	//** 「１ヶ月平均所定労働時間」を求める
	//** 【求める式】
	//** ３６５　－　７２（年間公休数）　＝　２９３（年間出勤日数）
	//** ２９３　×　１日の所定労働時間　＝　２０５１（年間所定労働時間）
	//** ２０５１　÷　１２　＝　１ヶ月の平均所定労働時間
	//** 　※2019-04-17現在の現行値は 170.91 （小数点第三位以降は切り捨て）
	//**********************************************************************
	function getHeikinShoteiRodoJikan(nenkan_kyujitu, rodo_jikan_per_day){
		//*** 年間公休数
		var nenkan_kyujitu = nenkan_kyujitu - 0;
		//*** １日の所定労働時間（１日あたりの労働時間）
		var rodo_jikan_per_day = rodo_jikan_per_day - 0;
		//*** 年間出勤日数
		var nenkan_shukkin = 365 - nenkan_kyujitu;
		//*** 年間所定労働時間
		var nenkan_rodo = nenkan_shukkin * rodo_jikan_per_day;
		//*** １ヶ月の平均所定労働時間（時間は小数点第三位以降切り捨て）
		var rodo_per_month = nenkan_rodo / 12;
		rodo_per_month = rodo_per_month * 100;
		rodo_per_month = Math.floor(rodo_per_month);
		rodo_per_month = rodo_per_month / 100;
		
		return rodo_per_month;
	}

	//**********************************************************************
	//** javascript　で小数計算する際に発生してしまう誤差を解消する
	//** 【誤差が発生する例】
	//**　　1.1 + 1.3 = 2.4000000000000004
	//** 【対応方法】
	//** 	対象値に対して以下の処理を行う
	//** 　１．10000を掛ける
	//** 　２．四捨五入
	//** 　３．10000で割る
	//**********************************************************************
	function jsShosuGosaTaio(target){
		
		var ret = 0;

		ret = Math.round(target * 10000);
		ret = ret / 10000;

		return ret;

	}

	/***************************************************************************/
	/***** 給与シミュレーションポップアップ（or　タグ）を起動する　　　　　*****/
	/***************************************************************************/
	function simulation_open(){

		// window.open('main_menu.php');

		window.open('kyuyo_simulation.php', null, 'width=500,toolbar=yes,menubar=yes,scrollbars=yes');

		// //従業員コードを渡さない（hiddenを空にしてPOSTで渡らないようにする）
		// document.getElementById("staff_code").value = "";
		// transition("sml_kyuyo_input.php");

	}

	/***************************************************************************/
	/***** 日付フォーマット（第二引数に指定した形式で返す）　　　　　　　　*****/
	/***** 【例】　　　　　　　　　　　　　　　　　　　　　　　　　　　　　*****/
	/***** 「formatDate(new Date(), 'YYYY年MM月DD日')」　　　　　　　　　 *****/
	/***** 「formatDate(new Date(), 'YYYY/MM/DD')」　　　　　　　　　　　 *****/
	/***** 「formatDate(new Date(), 'YYYY-MM-DD')」　　　　　　　　　　　 *****/
	/***************************************************************************/
	function formatDate(date, format) {
 
		format = format.replace(/YYYY/, date.getFullYear());
		format = format.replace(/MM/, date.getMonth() + 1);
		format = format.replace(/DD/, date.getDate());
	 
		return format;
	}

	