//別のjsファイルを読み込むための関数
function appendScript(URL) {
	var el = document.createElement('script');
	el.src = URL;
};

//maim.jsを読み込む
appendScript("js/main.js");

(function() {
	//画面ロード前に実行される
	//ＤＯＭ使用不可。
})()


//ログインユーザーが管理する対象の社員かどうか判断するための変数
var kanri_taisho = 0;

//給与データをＤＢに登録する時これ見て判断。
//給与規定に反する項目があるかどうか確認するための変数
var err_flg_shokuseki_kyu = 0;	//職責給規定違反フラグ
var err_flg_tenkin_jutaku_teate = 0;	//転勤住宅手当規定違反フラグ

//月給・年俸フラグ
//初期値は登録済みの値、画面上で月給／年俸を切り替えると、
//その都度値が変更される。
var gekkyu_nempo_val = kyuyo_arr["gekkyu_nempo"];

//「勤務地」を退避するための変数
//登録時に使用する。「勤務地」が初期表示時から変更されている場合は登録不可にする。
//「勤務地」は『siten_cd』『todofuken_cd』の２つの値を持つが、『siten_cd』のみ退避しておく。
var taihi_siten_cd = kyuyo_arr["kimmuchi_cd"];


//内部で画面表示と同じ値を保持しておく。
//"月給"⇔"年俸"の切替時に画面上の値は消えてもここは保持される
//再度"月給"⇔"年俸"を切り替えた場合、ここの変数から画面にセットする。
//ＤＢへはここの変数の値を登録する。
//”月給””年俸”関係なく全ての値を登録する。
var taihi_tansinfunin_flg = 0;	//単身赴任フラグ
var taihi_shaho_fuyo_flg = 0;	//社保扶養フラグ
var taihi_shokuseki_kyu = 0;	//月給－職責給
var taihi_kihon_kyu = 0;	//月給－基本給（年齢給）
var taihi_kotei_kihon_kyu = 0;	//月給－固定基本給
var taihi_kaikin_teate = 0;	//月給－皆勤手当
var taihi_tosi_teate = 0;	//月給－都市手当
var taihi_shorei_teate = 0;	//月給－奨励手当
var taihi_chosei_teate = 0;	//月給－調整手当
var taihi_tenkin_jutaku_teate = 0;	//月給－転勤住宅手当
var taihi_tansinfunin_teate = 0;	//月給－単身赴任手当
var taihi_kazoku_teate = 0;	//月給－家族手当
var taihi_korituzangyo_teate = 0;	//月給－効率残業手当＿支給額
var taihi_korituzangyo_teate_gaitogaku = 0;	//月給－効率残業手当＿該当額
var taihi_nempo_nengaku = 0;	//年俸－年額
var taihi_nempo_uchi_koteizang_dai = 0;	//年俸－うち固定残業代
var taihi_nempo_uchi_eigyo_teate = 0;	//年俸－うち営業手当
// var taihi_shikyugaku_a = 0;	//支給額Ａ（毎月の支給額）
// var taihi_sagaku_teate = 0;	//前年との差額（手当）
var taihi_zangyo_tanka = 0;	//残業単価
//var taihi_kekkin_kojo = 0;	//欠勤控除
//var taihi_chikoku_sotai_kojo = 0;	//遅刻早退控除
//var taihi_biko = 0;	//備考

// var taihi_kihon_kyu = 0;	//年齢給
// var taihi_kotei_kihon_kyu = 0;	//固定基本給
// var taihi_kaikin_teate = 0;	//皆勤手当
// var taihi_shorei_teate = 0;	//奨励手当
// var taihi_chosei_teate = 0;	//調整手当
// var taihi_tenkin_jutaku_teate = 0;	//転勤住宅手当
// var taihi_tansinfunin_teate  = 0;	//単身赴任手当
// var taihi_tansinfunin_flg = 0;	//単身赴任フラグ
// var taihi_kazoku_teate = 0;	//家族手当
// var taihi_shaho_fuyo_flg = 0;	//社保扶養フラグ
// var taihi_nempo_uchi_koteizang_dai = 0;	//うち固定残業代
// var taihi_nempo_uchi_eigyo_teate = 0;	//うち営業手当
// var taihi_zan_tanka = 0;		//「残業単価」
var taihi_tujo_zan_jikan = 0;	//「通常残業時間」
var taihi_tujo_zan_teate = 0;	//「通常残業手当」
var taihi_sinya_zan_jikan = 0;	//「深夜残業時間」
var taihi_sinya_zan_teate = 0;	//「深夜残業手当」
var taihi_kyujitu_rodo_jikan = 0;	//「休日労働時間」
var taihi_kyujitu_rodo_teate = 0;	//「休日労働手当」
var taihi_kyujitusinya_rodo_jikan = 0;	//「休日深夜労働時間」
var taihi_kyujitusinya_rodo_teate = 0;	//「休日深夜労働手当」
var taihi_zangyo_teate_gokei = 0;	//「残業手当」

//画面上のデータが、初期表示後に更新されたかどうかを判定する変数
//reCalc()でオンにする
var update_flg = 0;

document.addEventListener("DOMContentLoaded", function() {
	//HTML読み込み後で描画前に処理するものをここに記述
	// ＤＯＭ使用可

	//========================================================================================================
	//プルダウンで選択した社員が、ログインユーザーが管理する社員かどうか確認
	//ログインユーザーが管理する社員の場合は編集可能ということで管理対象確認用変数に １ をセットする。
	//　①確定状態　と、
	//　②ログインユーザーに管理される人であるかどうか
	//　の２つで判断する。
	//* ②の「ログインユーザーに管理される人であるかどうか」は、プルダウンのvalueに判断用の値がある。
	//* makePullDownDT()でセットしている。
	//========================================================================================================
	var sel_jugyoin = document.getElementById("sel_jugyoin");
	var wk_sel_jugyoin_value = sel_jugyoin.options[sel_jugyoin.selectedIndex].value;
	var sel_jugyoin_value = wk_sel_jugyoin_value.split(";");
	if(sel_jugyoin_value[1] == '1'){
		kanri_taisho = 1;
	}

	//========================================================================================================
	//下記条件の場合は編集可能なので、入力可能なタグを使用する。
	//それ以外は編集できないタグ（divやtd）を用意する。
	//条件１：ログインユーザーが一般ユーザーで、年度確定状態が"未確定"で、表示対象者がログインユーザーの管理対象者で、"未確定"　状態
	//条件２：ログインユーザーが運用管理者（経理課ユーザー）で、表示対象者が　"未確定"　状態
	//条件３：ログインユーザーがシステム管理者（情シスユーザー）で、表示対象者が　"未確定"　状態
	//条件４：ログインユーザーが運用管理者（経理課ユーザー）で、表示対象者が　"仮確定"　状態
	//条件５：ログインユーザーがシステム管理者（情シスユーザー）で、表示対象者が　"仮確定"　状態
	//条件６：ログインユーザーが運用管理者（経理課ユーザー）で、表示対象者が　"確定"　状態
	//条件７：ログインユーザーがシステム管理者（情シスユーザー）で、表示対象者が　"確定"　状態
	//========================================================================================================
	switch(true){
		//条件１：ログインユーザーが一般ユーザーで、年度確定状態が"未確定"で、表示対象者がログインユーザーの管理対象者で、給与データが"未確定"　状態
		case (USER_SHUBETU == 1) && (mst_nendo_row["kakutei_jotai"] == '0') && (kyuyo_arr["kakutei_jotai"] == '0') && (kanri_taisho == 1) :
		
			//「仮確定」ボタンを表示する
			document.getElementById("btn_kari_kakutei").setAttribute('class', "btn btn-default display_inline");
		
			//「登録」ボタンを表示する
			document.getElementById("btn_toroku").setAttribute('class', "btn btn-default display_inline");
		
			//「勤務地」の「初期値」ボタンを表示する
			document.getElementById("btn_kimmuchi").setAttribute('class', "btn btn-default display_inline");
			
			//入力項目を入力可能状態にする（運用管理ユーザー、システム管理ユーザー、一般ユーザー　共通）
			kyotsuEdit();
			
			//編集不可項目のデータをセット
			kyotsuNonEdit();
			
			//"月給"の場合、残業時間設定用モーダル表示機能追加（一般ユーザー用）
			if(gekkyu_nempo_val == 0){
				zangyoModalSet("#zangyo_modal");
			}
			
			break;

		//条件２：ログインユーザーが運用管理者（経理課ユーザー）で、表示対象者が　"未確定"　状態
		//条件３：ログインユーザーがシステム管理者（情シスユーザー）で、表示対象者が　"未確定"　状態
		case (USER_SHUBETU == 4) && (kyuyo_arr["kakutei_jotai"] == '0') :
		case (USER_SHUBETU == 5) && (kyuyo_arr["kakutei_jotai"] == '0') :
		
			//「仮確定」ボタンを表示する
			document.getElementById("btn_kari_kakutei").setAttribute('class', "btn btn-default display_inline");
		
			//「確定」ボタンを表示する
			document.getElementById("btn_kakutei").setAttribute('class', "btn btn-default display_inline");
		
			//「登録」ボタンを表示する
			// document.getElementById("btn_toroku").setAttribute('class', "btn btn-default display_inline");
		
			//「勤務地」の「初期値」ボタンを表示する
			document.getElementById("btn_kimmuchi").setAttribute('class', "btn btn-default display_inline");

			//入力項目を入力可能状態にする（運用管理ユーザー、システム管理ユーザー、一般ユーザー　共通）
			kyotsuEdit();
			
			//"月給"の場合、残業時間設定用モーダル表示機能追加（一般ユーザー用）
			if(gekkyu_nempo_val == 0){
				zangyoModalSet("#zangyo_modal");
				// zangyoModalSet("#kanri_zangyo_modal");
			}
			
			//運用管理者ユーザー用入力項目を入力可能状態にする
			kanriEdit();

			break;

		//条件４：ログインユーザーが運用管理者（経理課ユーザー）で、表示対象者が　"仮確定"　状態
		//条件５：ログインユーザーがシステム管理者（情シスユーザー）で、表示対象者が　"仮確定"　状態
		case (USER_SHUBETU == 4) && (kyuyo_arr["kakutei_jotai"] == '1') :
		case (USER_SHUBETU == 5) && (kyuyo_arr["kakutei_jotai"] == '1') :
		
			//「仮確定解除」ボタンを表示する
			document.getElementById("btn_kari_kakutei_kaijo").setAttribute('class', "btn btn-default display_inline");
		
			//「確定」ボタンを表示する
			document.getElementById("btn_kakutei").setAttribute('class', "btn btn-default display_inline");
		
			//「登録」ボタンを表示する
			// document.getElementById("btn_toroku").setAttribute('class', "btn btn-default display_inline");
		
			//「勤務地」の「初期値」ボタンを表示する
			document.getElementById("btn_kimmuchi").setAttribute('class', "btn btn-default display_inline");

			//入力項目を入力可能状態にする（運用管理ユーザー、システム管理ユーザー、一般ユーザー　共通）
			kyotsuEdit();
			
			//"月給"の場合、残業時間設定用モーダル表示機能追加（一般ユーザー用）
			if(gekkyu_nempo_val == 0){
				zangyoModalSet("#zangyo_modal");
				// zangyoModalSet("#kanri_zangyo_modal");
			}
			
			//運用管理者ユーザー用入力項目を入力可能状態にする
			kanriEdit();

			break;

		//条件６：ログインユーザーが運用管理者（経理課ユーザー）で、表示対象者が　"確定"　状態
		//条件７：ログインユーザーがシステム管理者（情シスユーザー）で、表示対象者が　"確定"　状態
		case (USER_SHUBETU == 4) && (kyuyo_arr["kakutei_jotai"] == '2') :
		case (USER_SHUBETU == 5) && (kyuyo_arr["kakutei_jotai"] == '2') :
		
			//「確定解除」ボタンを表示する
			document.getElementById("btn_kakutei_kaijo").setAttribute('class', "btn btn-default display_inline");
		
			//「登録」ボタンを表示する
			// document.getElementById("btn_toroku").setAttribute('class', "btn btn-default display_inline");
		
			//「勤務地」の「初期値」ボタンを表示する
			document.getElementById("btn_kimmuchi").setAttribute('class', "btn btn-default display_inline");

			//入力項目を入力可能状態にする（運用管理ユーザー、システム管理ユーザー、一般ユーザー　共通）
			kyotsuEdit();
			
			//"月給"の場合、残業時間設定用モーダル表示機能追加（一般ユーザー用）
			if(gekkyu_nempo_val == 0){
				zangyoModalSet("#zangyo_modal");
				// zangyoModalSet("#kanri_zangyo_modal");
			}
			
			//運用管理者ユーザー用入力項目を入力可能状態にする
			kanriEdit();

			break;

		default :
			//編集不可のため、編集できないタグを追加する。
			//* 月給・年俸
			var gk = document.createElement('div');
			gk.setAttribute('id', "cb_gekkyu_nempo");
			gk.setAttribute('name', "cb_gekkyu_nempo");
			gk.innerHTML = '';
			document.getElementById("gekkyu_nempo_box").appendChild(gk);
			//* 月給・年俸の値をセット
			if(kyuyo_arr["gekkyu_nempo"]+"" == 0){
				document.getElementById("cb_gekkyu_nempo").innerText = GEKKYU;
			}else if(kyuyo_arr["gekkyu_nempo"]+"" == 1){
				document.getElementById("cb_gekkyu_nempo").innerText = NEMPO;
			}
			
			//編集不可項目のデータをセット
			kyotsuNonEdit();

	}

	//========================================================================================================
	//年度をセット
	//========================================================================================================
	document.getElementById("nendo").innerText = nendo + "年度申請データ";
	document.getElementById("nendo_hon").innerText = nendo + "年度\n承認(承認予定)\nデータ";
	document.getElementById("nendo_minus1").innerText = nendo_minus1 + "年度";
	document.getElementById("nendo_sagaku").innerText = nendo_minus1 + "年度と\n" + nendo + "年度\n申請データ\nの差額";
	document.getElementById("nendo_minus2").innerText = nendo_minus2 + "年度";
	document.getElementById("nendo_minus3").innerText = nendo_minus3 + "年度";
	document.getElementById("nendo_minus4").innerText = nendo_minus4 + "年度";
	document.getElementById("nendo_minus5").innerText = nendo_minus5 + "年度";

	//========================================================================================================
	//対象年度の給与データを画面にセット
	//========================================================================================================
	if(kyuyo_arr != 0){

		var kakutei_jotai = "";
		var kakutei_color = "";
		var nendo_kakutei_jotai = "";
		var nendo_kakutei_color = "";
		var kyuyodata_kakutei_jotai = "";
		var kyuyodata_kakutei_color = "";
	
		//確定状態をセット
		//* ログインユーザーが運用管理者（経理課ユーザー）、又はシステム管理者（情シスユーザー）の場合
		if((USER_SHUBETU == 4) || (USER_SHUBETU == 5)){
			
			if((mst_nendo_row["kakutei_jotai"] == '0')&&(kyuyo_arr["kakutei_jotai"] == '0')){
				nendo_kakutei_jotai = "年度：未確定";
				nendo_kakutei_color = "";
				kyuyodata_kakutei_jotai = "給与データ：未確定";
				kyuyodata_kakutei_color = "";

			}else if((mst_nendo_row["kakutei_jotai"] == '0')&&(kyuyo_arr["kakutei_jotai"] == '1')){
				nendo_kakutei_jotai = "年度：未確定";
				nendo_kakutei_color = "";
				kyuyodata_kakutei_jotai = "給与データ：仮確定";
				kyuyodata_kakutei_color = "orange";

			}else if((mst_nendo_row["kakutei_jotai"] == '0')&&(kyuyo_arr["kakutei_jotai"] == '2')){
				nendo_kakutei_jotai = "年度：未確定";
				nendo_kakutei_color = "";
				kyuyodata_kakutei_jotai = "給与データ：確定";
				kyuyodata_kakutei_color = "red";

			}else if((mst_nendo_row["kakutei_jotai"] == '2')&&(kyuyo_arr["kakutei_jotai"] == '0')){
				nendo_kakutei_jotai = "年度：確定";
				nendo_kakutei_color = "red";
				kyuyodata_kakutei_jotai = "給与データ：未確定";
				kyuyodata_kakutei_color = "";

			}else if((mst_nendo_row["kakutei_jotai"] == '2')&&(kyuyo_arr["kakutei_jotai"] == '1')){
				nendo_kakutei_jotai = "年度：確定";
				nendo_kakutei_color = "red";
				kyuyodata_kakutei_jotai = "給与データ：仮確定";
				kyuyodata_kakutei_color = "orange";

			}else if((mst_nendo_row["kakutei_jotai"] == '2')&&(kyuyo_arr["kakutei_jotai"] == '2')){
				nendo_kakutei_jotai = "年度：確定";
				nendo_kakutei_color = "red";
				kyuyodata_kakutei_jotai = "給与データ：確定";
				kyuyodata_kakutei_color = "red";

			}

			document.getElementById("nendo_kakutei_jotai").innerText = nendo_kakutei_jotai;
			document.getElementById("nendo_kakutei_jotai").style.color = nendo_kakutei_color;
			document.getElementById("nendo_kakutei_jotai").style.display = "block";

			document.getElementById("kyuyodata_kakutei_jotai").innerText = kyuyodata_kakutei_jotai;
			document.getElementById("kyuyodata_kakutei_jotai").style.color = kyuyodata_kakutei_color;
			document.getElementById("kyuyodata_kakutei_jotai").style.display = "block";


		//* ログインユーザーが運用管理者（経理課ユーザー）、又はシステム管理者（情シスユーザー）以外の場合
		}else{

			var kakutei_jotai = "";

			//年度確定状態が"確定"の場合は　"確定"　と表示する
			//年度確定状態が"仮確定"の場合も　"確定"　と表示する。なぜなら、
			//一般ユーザーは"仮確定"だと、社員毎の確定状態だと思ってしまうから。
			//上司に仮確定解除を頼めば解除できると勘違いするから。
			//年度確定状態は運用管理者（経理課ユーザー）のみが変更可能。
			//よって運用管理者によって"仮確定"にされた場合は"確定"と表示する。
			//※2019-12-30時点では年度確定状態の"仮確定"は使用しない予定だが、ロジックは入れておく。
			if(mst_nendo_row["kakutei_jotai"] == '1'){
				kakutei_jotai = "確定済";
				kakutei_color = "red";

			}else if(mst_nendo_row["kakutei_jotai"] == '2'){
				kakutei_jotai = "確定済";
				kakutei_color = "red";

			}else if(kyuyo_arr["kakutei_jotai"] == '0'){
				kakutei_jotai = "未確定";
				kakutei_color = "";

			}else if(kyuyo_arr["kakutei_jotai"] == '1'){
				kakutei_jotai = "仮確定";
				kakutei_color = "orange";

			}else if(kyuyo_arr["kakutei_jotai"] == '2'){
				kakutei_jotai = "確定済";
				kakutei_color = "red";

			}

			document.getElementById("nendo_kakutei_jotai").innerText = kakutei_jotai;
			document.getElementById("nendo_kakutei_jotai").style.color = kakutei_color;
			document.getElementById("nendo_kakutei_jotai").style.display = "block";
			// document.getElementById("kakutei_jotai").innerText = kakutei_jotai;
			// document.getElementById("kakutei_jotai").style.color = kakutei_color;

		}

		//各項目の値を画面にセットする
		taishoNendoDataSet();
	}

	//最低賃金チェック
	chkSaiteichingin();
	
	//========================================================================================================
	//対象年度の給与データ（本データ）を画面にセット
	//========================================================================================================
	if(kyuyo_hon_arr != 0){
		taishoNendoHonDataSet();
	}
	
	//========================================================================================================
	//対象年度－１年度の給与データ（本データ）を画面にセット
	//========================================================================================================
	if(kyuyo_minus1_arr != 0){
		kakoNendoDataSet(1);
	}
	
	//========================================================================================================
	//対象年度－２年度の給与データ（本データ）を画面にセット
	//========================================================================================================
	if(kyuyo_minus2_arr != 0){
		kakoNendoDataSet(2);
	}
	
	//========================================================================================================
	//対象年度－３年度の給与データ（本データ）を画面にセット
	//========================================================================================================
	if(kyuyo_minus3_arr != 0){
		kakoNendoDataSet(3);
	}
	
	//========================================================================================================
	//対象年度－４年度の給与データ（本データ）を画面にセット
	//========================================================================================================
	if(kyuyo_minus4_arr != 0){
		kakoNendoDataSet(4);
	}
	
	//========================================================================================================
	//対象年度－５年度の給与データ（本データ）を画面にセット
	//========================================================================================================
	if(kyuyo_minus5_arr != 0){
		kakoNendoDataSet(5);
	}

	//========================================================================================================
	//（対象年度－１年度）と対象年度の差額の列の値を画面にセット
	//========================================================================================================
	if((kyuyo_arr != 0)&&(kyuyo_minus1_arr != 0)){
		//対象年度と、その前年度がどちらも"月給"の場合のみ処理実行
		if((kyuyo_arr["gekkyu_nempo"] == 0)&&(kyuyo_minus1_arr["gekkyu_nempo"] == 0)){
		
			//「評価点による昇給額」
//			var shokyu_gaku_sagaku_obj = document.getElementById("shokyu_gaku_sagaku");
//			var shokyu_gaku_sagaku = kyuyo_arr["shokyu_gaku"] - kyuyo_minus1_arr["shokyu_gaku"];
//			shokyu_gaku_sagaku_obj.innerText = numberFormat(shokyu_gaku_sagaku);

			//「職責給ー合計」
			var shokuseki_kyu_sagaku_obj = document.getElementById("shokuseki_kyu_sagaku");
			var shokuseki_kyu_sagaku = kyuyo_arr["shokuseki_kyu"] - kyuyo_minus1_arr["shokuseki_kyu"];
			shokuseki_kyu_sagaku_obj.innerText = numberFormat(shokuseki_kyu_sagaku);
			
			//「年齢給」
			var kihon_kyu_sagaku_obj = document.getElementById("kihon_kyu_sagaku");
			var kihon_kyu_sagaku = kyuyo_arr["kihon_kyu"] - kyuyo_minus1_arr["kihon_kyu"];
			kihon_kyu_sagaku_obj.innerText = numberFormat(kihon_kyu_sagaku);
			
			//「固定基本給」
			var kotei_kihon_kyu_sagaku_obj = document.getElementById("kotei_kihon_kyu_sagaku");
			var kotei_kihon_kyu_sagaku = kyuyo_arr["kotei_kihon_kyu"] - kyuyo_minus1_arr["kotei_kihon_kyu"];
			kotei_kihon_kyu_sagaku_obj.innerText = numberFormat(kotei_kihon_kyu_sagaku);
			
			//「皆勤手当」
			var kaikin_teate_sagaku_obj = document.getElementById("kaikin_teate_sagaku");
			var kaikin_teate_sagaku = kyuyo_arr["kaikin_teate"] - kyuyo_minus1_arr["kaikin_teate"];
			kaikin_teate_sagaku_obj.innerText = numberFormat(kaikin_teate_sagaku);
			
			//「都市手当」
			var tosi_teate_sagaku_obj = document.getElementById("tosi_teate_sagaku");
			var tosi_teate_sagaku = kyuyo_arr["tosi_teate"] - kyuyo_minus1_arr["tosi_teate"];
			tosi_teate_sagaku_obj.innerText = numberFormat(tosi_teate_sagaku);
			
			//「奨励手当」
			var shorei_teate_sagaku_obj = document.getElementById("shorei_teate_sagaku");
			var shorei_teate_sagaku = kyuyo_arr["shorei_teate"] - kyuyo_minus1_arr["shorei_teate"];
			shorei_teate_sagaku_obj.innerText = numberFormat(shorei_teate_sagaku);

			//「調整手当」
			var chosei_teate_sagaku_obj = document.getElementById("chosei_teate_sagaku");
			var chosei_teate_sagaku = kyuyo_arr["chosei_teate"] - kyuyo_minus1_arr["chosei_teate"];
			chosei_teate_sagaku_obj.innerText = numberFormat(chosei_teate_sagaku);

			//「残業代の基礎となる手当ー小計」
			var zankiso_shokei_sagaku_obj = document.getElementById("zankiso_shokei_sagaku");
			var zankiso_shokei = calcZanKisoShokei(kyuyo_arr);
			var zankiso_shokei_minus1 = calcZanKisoShokei(kyuyo_minus1_arr);
			zankiso_shokei = zankiso_shokei - zankiso_shokei_minus1;
			zankiso_shokei_sagaku_obj.innerText = numberFormat(zankiso_shokei);

			//「転勤住宅手当」
			var tenkin_jutaku_teate_sagaku_obj = document.getElementById("tenkin_jutaku_teate_sagaku");
			var tenkin_jutaku_teate_sagaku = kyuyo_arr["tenkin_jutaku_teate"] - kyuyo_minus1_arr["tenkin_jutaku_teate"];
			tenkin_jutaku_teate_sagaku_obj.innerText = numberFormat(tenkin_jutaku_teate_sagaku);

			//「単身赴任手当」
			var tansinfunin_teate_sagaku_obj = document.getElementById("tansinfunin_teate_sagaku");
			var tansinfunin_teate_sagaku = kyuyo_arr["tansinfunin_teate"] - kyuyo_minus1_arr["tansinfunin_teate"];
			tansinfunin_teate_sagaku_obj.innerText = numberFormat(tansinfunin_teate_sagaku);

			//「残業代の基礎となる手当ー合計」
			var zankiso_total_sagaku_obj = document.getElementById("zankiso_total_sagaku");
			var zankiso_total = calcZanKisoGokei(kyuyo_arr);
			var zankiso_total_minus1 = calcZanKisoGokei(kyuyo_minus1_arr);
			zankiso_total = zankiso_total - zankiso_total_minus1;
			zankiso_total_sagaku_obj.innerText = numberFormat(zankiso_total);

			//「月給－家族手当」
			var kazoku_teate_sagaku_obj = document.getElementById("kazoku_teate_sagaku");
			var kazoku_teate_sagaku = kyuyo_arr["kazoku_teate"] - kyuyo_minus1_arr["kazoku_teate"];
			kazoku_teate_sagaku_obj.innerText = numberFormat(kazoku_teate_sagaku);

			//「効率残業手当」
			var korituzangyo_teate_sagaku_obj = document.getElementById("korituzangyo_teate_sagaku");
			var korituzangyo_teate_sagaku = kyuyo_arr["korituzangyo_teate"] - kyuyo_minus1_arr["korituzangyo_teate"];
			korituzangyo_teate_sagaku_obj.innerText = numberFormat(korituzangyo_teate_sagaku);

			//「残業代の基礎とならない手当－合計」
			var zan_nonkiso_total_sagaku_obj = document.getElementById("zan_nonkiso_total_sagaku");
			var zan_nonkiso_total = calcNonZanKisoGokei(kyuyo_arr);
			var zan_nonkiso_total_minus1 = calcNonZanKisoGokei(kyuyo_minus1_arr);
			zan_nonkiso_total = zan_nonkiso_total - zan_nonkiso_total_minus1;
			zan_nonkiso_total_sagaku_obj.innerText = numberFormat(zan_nonkiso_total);

		//対象年度と、その前年度がどちらも"年俸"の場合のみ処理実行
		}else if((kyuyo_arr["gekkyu_nempo"] == 1)&&(kyuyo_minus1_arr["gekkyu_nempo"] == 1)){
			
			//「年俸－昇給－年額」
			

			//「年俸ー年額合計」
			var nempo_nengaku_sagaku_obj = document.getElementById("nempo_nengaku_sagaku");
			var nempo_nengaku_sagaku = kyuyo_arr["nempo_nengaku"] - kyuyo_minus1_arr["nempo_nengaku"];
			nempo_nengaku_sagaku_obj.innerText = numberFormat(nempo_nengaku_sagaku);

			//「年俸ー12で割った金額／14で割った金額」
			var nempo_getugaku_sagaku_obj = document.getElementById("nempo_getugaku_sagaku");
			var nempo_getugaku = kyuyo_arr["shikyugaku_a"];
			var nempo_getugaku_minus1 = kyuyo_minus1_arr["shikyugaku_a"];
			nempo_getugaku = nempo_getugaku - nempo_getugaku_minus1;
			nempo_getugaku_sagaku_obj.innerText = numberFormat(nempo_getugaku);

			//「年俸－家族手当」
			var nempo_kazoku_teate_sagaku_obj = document.getElementById("nempo_kazoku_teate_sagaku");
			var kazoku_teate_sagaku = kyuyo_arr["kazoku_teate"] - kyuyo_minus1_arr["kazoku_teate"];
			nempo_kazoku_teate_sagaku_obj.innerText = numberFormat(kazoku_teate_sagaku);

			//「うち固定残業」
			var nempo_uchi_koteizang_dai_sagaku_obj = document.getElementById("nempo_uchi_koteizang_dai_sagaku");
			var nempo_uchi_koteizang_dai_sagaku = kyuyo_arr["nempo_uchi_koteizang_dai"] - kyuyo_minus1_arr["nempo_uchi_koteizang_dai"];
			nempo_uchi_koteizang_dai_sagaku_obj.innerText = numberFormat(nempo_uchi_koteizang_dai_sagaku);

			//「うち営業手当」
			var nempo_uchi_eigyo_teate_sagaku_obj = document.getElementById("nempo_uchi_eigyo_teate_sagaku");
			var nempo_uchi_eigyo_teate_sagaku = kyuyo_arr["nempo_uchi_eigyo_teate"] - kyuyo_minus1_arr["nempo_uchi_eigyo_teate"];
			nempo_uchi_eigyo_teate_sagaku_obj.innerText = numberFormat(nempo_uchi_eigyo_teate_sagaku);

		}
		//「支給額Ａ」
		var shikyugaku_a_sagaku_obj = document.getElementById("shikyugaku_a_sagaku");
		var shikyugaku_a_sagaku = kyuyo_arr["shikyugaku_a"] - kyuyo_minus1_arr["shikyugaku_a"];
		shikyugaku_a_sagaku_obj.innerText = numberFormat(shikyugaku_a_sagaku);

	}

});



$(function () {

//	//show.bs.modal	モーダル・ダイアログを開くshowメソッドを呼び出した時のイベント。
//	$('#sampleModal').on('show.bs.modal', function (event) {
//	});
//
//	//shown.bs.modal	モーダル・ダイアログが完全に表示された時のイベント。
//	$('#sampleModal').on('shown.bs.modal', function (event) {
//	});
//
//	//hide.bs.modal	モーダル・ダイアログを閉じるhideメソッドを呼び出した時のイベント。
//	$('#sampleModal').on('hide.bs.modal', function () {
//	});
//
//	//hidden.bs.modal	モーダル・ダイアログが完全に非表示になった時のイベント。
//	$('#sampleModal').on('hidden.bs.modal', function () {
//	});
//
//	//残業時間・手当入力用モーダルを開くshowメソッドを呼び出した時のイベント。
//	$('#zangyo_modal').on('show.bs.modal', function (event) {
//	});

	//「年間休日」「１日あたりの労働時間」「１日あたりの休憩時間」設定用モーダルが閉じたときの処理
	$('#nenkan_kyujitu_modal').on('hidden.bs.modal', function () {
		setNenkanNissuModal();
	});

	//職責給　昇給訂正用モーダルが閉じたときの処理
	$('#teisei_modal').on('hidden.bs.modal', function () {
		teiseiTekiyou('teisei');
	});

	//職責給　昇給嘆願用モーダルが閉じたときの処理
	$('#tangan_modal').on('hidden.bs.modal', function () {
		teiseiTekiyou('tangan');
	});

	//残業時間・手当入力用モーダルが閉じたときの処理
	$('#zangyo_modal').on('hidden.bs.modal', function () {
		zangyoTekiyou();
	});

	// //経理課用　残業時間・手当入力用モーダルが閉じたときの処理
	// $('#kanri_zangyo_modal').on('hidden.bs.modal', function () {
	// 	kanriZangyoTekiyou();
	// });

});


//****************************************************************************
//　該当年度の本データ（承認データもしくは承認予定データ）の列の
//　表示・非表示を切り替える。
//*****************************************************************************
function hon_dt_disp(){

	var hon_dt_col = document.getElementsByClassName("hon_dt_col");
	var disp_type = "table-cell";
	if(hon_dt_col[0].style.display == "table-cell"){
		disp_type = "none";
	}
	for(i=0; i < hon_dt_col.length; i++){
		hon_dt_col[i].style.display = disp_type;
	}
}

//****************************************************************************
//　運用管理者ユーザー、システム開発ユーザー、一般ユーザー共通の編集可能項目
//　を編集可能状態にする。
//*****************************************************************************
function gamen_senni(target){
	
	//年度確定状態が"未確定"で、給与データも"未確定"の場合のみチェックを行う
	if((mst_nendo_row["kakutei_jotai"] == '0') && (kyuyo_arr["kakutei_jotai"] == '0')){
		if(update_flg == 0){
			transition(target);
		}else if(confirm("更新が破棄されますが、よろしいですか？\n表示中の値を保存する場合は、この小窓で「キャンセル」をクリックし、\n画面上の「登録」ボタンで保存してください。")){
			transition(target);
		}
	}else{
		transition(target);
	}

}

//****************************************************************************
//　運用管理者ユーザー、システム管理ユーザー、一般ユーザー共通の編集可能項目
//　を編集可能状態にする。
//*****************************************************************************
function kyotsuEdit(){

	//編集可能にするため、inputタグ等を追加する
	//* 月給・年俸
	var gn1 = document.createElement('input');
	gn1.setAttribute('type', "radio");
	gn1.setAttribute('id', "cb_gekkyu");
	gn1.setAttribute('name', "cb_gekkyu_nempo");
	gn1.setAttribute('value', "0");
	gn1.setAttribute('onChange', "beforChgGekkyuNempo()");
	document.getElementById("gekkyu_nempo_box").appendChild(gn1);

	var gn1_lbl = document.createElement('label');
	gn1_lbl.setAttribute('for', "cb_gekkyu");
	gn1_lbl.innerText = '　月給';
	document.getElementById("gekkyu_nempo_box").appendChild(gn1_lbl);

	var gn_br = document.createElement('br');
	document.getElementById("gekkyu_nempo_box").appendChild(gn_br);

	var gn2 = document.createElement('input');
	gn2.setAttribute('type', "radio");
	gn2.setAttribute('id', "cb_nempo");
	gn2.setAttribute('name', "cb_gekkyu_nempo");
	gn2.setAttribute('value', "1");
	gn2.setAttribute('onChange', "beforChgGekkyuNempo()");
	document.getElementById("gekkyu_nempo_box").appendChild(gn2);

	var gn2_lbl = document.createElement('label');
	gn2_lbl.setAttribute('for', "cb_nempo");
	gn2_lbl.innerText = '　年俸';
	document.getElementById("gekkyu_nempo_box").appendChild(gn2_lbl);
	
	//月給の場合
	if(kyuyo_arr["gekkyu_nempo"] == 0){
		//* 月給・年俸に値をセット
		document.getElementById("cb_gekkyu").checked = true;
		document.getElementById("cb_nempo").checked = false;

		//* 昇給訂正（モーダル表示機能追加）
		var teisei_gaku = document.getElementById('teisei_gaku');
		teisei_gaku.setAttribute('data-toggle', "modal");
		teisei_gaku.setAttribute('data-target', "#teisei_modal");
	
		//* 昇給嘆願（モーダル表示機能追加）
		var tangan_gaku = document.getElementById('tangan_gaku');
		tangan_gaku.setAttribute('data-toggle', "modal");
		tangan_gaku.setAttribute('data-target', "#tangan_modal");

		//* 残業手当を適用する／残業手当を支給しない（ラジオボタン）
		document.getElementById("cb_zangyo_tekiyo_y").setAttribute('onclick', "");	//使用可能な状態にする
		document.getElementById("cb_zangyo_tekiyo_n").setAttribute('onclick', "");	//使用可能な状態にする

	//年俸の場合
	}else if(kyuyo_arr["gekkyu_nempo"] == 1){
		//* 月給・年俸に値をセット
		document.getElementById("cb_gekkyu").checked = false;
		document.getElementById("cb_nempo").checked = true;

		//* 昇給訂正（モーダル表示機能追加）
		var teisei_gaku = document.getElementById('nempo_teisei_gaku');
		teisei_gaku.setAttribute('data-toggle', "modal");
		teisei_gaku.setAttribute('data-target', "#teisei_modal");
	
		//* 昇給嘆願（モーダル表示機能追加）
		var tangan_gaku = document.getElementById('nempo_tangan_gaku');
		tangan_gaku.setAttribute('data-toggle', "modal");
		tangan_gaku.setAttribute('data-target', "#tangan_modal");

		//* １２分割して毎月支給／１４分割して毎月と賞与時期に支給（ラジオボタン追加）
		document.getElementById("cb_nempo_divide_12").setAttribute('onclick', "");	//使用可能な状態にする
		document.getElementById("cb_nempo_divide_14").setAttribute('onclick', "");	//使用可能な状態にする
	}
	

}


//****************************************************************************
//　運用管理者ユーザー、システム管理ユーザー　の２種以外のユーザー共通の
//　編集不可項目のデータをセットする。
//*****************************************************************************
function kyotsuNonEdit(){

	//単身赴任フラグの値を取得する
	var tansinfunin_flg = kyuyo_arr["tansinfunin_flg"];
	tansinfunin_flg = tansinfunin_flg - 0;

	//月給の場合、以下の値をセット
	if(kyuyo_arr["gekkyu_nempo"] == 0){

		//* 「固定基本給」の値をセット
		document.getElementById("kotei_kihon_kyu").value = numberFormat(kyuyo_arr["kotei_kihon_kyu"]);

		//* 「調整手当」の値をセット
		document.getElementById("chosei_teate").value = numberFormat(kyuyo_arr["chosei_teate"]);
		//* 「転勤住宅手当」の値をセット
		document.getElementById("tenkin_jutaku_teate").value = numberFormat(kyuyo_arr["tenkin_jutaku_teate"]);
		//* 「単身赴任」チェックボックスに値をセット
		if(tansinfunin_flg){
			//「単身赴任」チェックボックスをオン
			document.getElementById("tansinfunin").checked = true;
		}
		document.getElementById("tansinfunin_value").value = tansinfunin_flg;
		//* 「単身赴任手当」の値をセット
		document.getElementById("tansinfunin_teate").innerText = numberFormat(kyuyo_arr["tansinfunin_teate"]);
	
	//年俸の場合、以下の値をセット
	}else if(kyuyo_arr["gekkyu_nempo"] == 1){
		//* 「うち固定残業代」をセット
		document.getElementById("nempo_uchi_koteizang_dai").value = numberFormat(kyuyo_arr["nempo_uchi_koteizang_dai"]);
		//* 「うち営業手当」をセット
		document.getElementById("nempo_uchi_eigyo_teate").value = numberFormat(kyuyo_arr["nempo_uchi_eigyo_teate"]);
	}

	// 「単身赴任」チェックボックスをreadonly状態にする
	document.getElementById("tansinfunin").setAttribute("onclick", "return false;");
	// 「月給－社保扶養」チェックボックスをreadonly状態にする
	document.getElementById("shaho_fuyo").setAttribute("onclick", "return false;");
	// 「年俸－社保扶養」チェックボックスをreadonly状態にする
	document.getElementById("nempo_shaho_fuyo").setAttribute("onclick", "return false;");

	//　データ退避用変数にセットする。”月給””年俸”関係なく
	taihi_tansinfunin_flg = tansinfunin_flg;	//単身赴任フラグ
	taihi_kotei_kihon_kyu = kyuyo_arr["kotei_kihon_kyu"];	//固定基本給
	taihi_chosei_teate = kyuyo_arr["chosei_teate"];	//調整手当
	taihi_tenkin_jutaku_teate = kyuyo_arr["tenkin_jutaku_teate"];	//勤住宅手当
	taihi_tansinfunin_teate = kyuyo_arr["tansinfunin_teate"];	//単身赴任手当
	taihi_nempo_uchi_koteizang_dai = kyuyo_arr["nempo_uchi_koteizang_dai"];	//うち固定残業代
	taihi_nempo_uchi_eigyo_teate = kyuyo_arr["nempo_uchi_eigyo_teate"];	//うち営業手当


}
//****************************************************************************
//　運用管理者ユーザー、システム開発ユーザー、一般ユーザーログイン時、
//　指定したモーダルを表示させるよう設定する。
//*****************************************************************************
function zangyoModalSet(target){

	//以下の項目のタグを作成
	
	//* 通常残業時間・通常残業手当（経理課用モーダル表示機能追加）
	var tujo_zan_jikan = document.getElementById('tujo_zan_jikan');
	tujo_zan_jikan.setAttribute('data-toggle', "modal");
	tujo_zan_jikan.setAttribute('data-target', target);

	var tujo_zan_teate = document.getElementById('tujo_zan_teate');
	tujo_zan_teate.setAttribute('data-toggle', "modal");
	tujo_zan_teate.setAttribute('data-target', target);
	
	//* 深夜残業時間・深夜残業手当（経理課用モーダル表示機能追加）
	var sinya_zan_jikan = document.getElementById('sinya_zan_jikan');
	sinya_zan_jikan.setAttribute('data-toggle', "modal");
	sinya_zan_jikan.setAttribute('data-target', target);

	var sinya_zan_teate = document.getElementById('sinya_zan_teate');
	sinya_zan_teate.setAttribute('data-toggle', "modal");
	sinya_zan_teate.setAttribute('data-target', target);
	
	//* 休日労働時間・休日労働手当（経理課用モーダル表示機能追加）
	var kyujitu_rodo_jikan = document.getElementById('kyujitu_rodo_jikan');
	kyujitu_rodo_jikan.setAttribute('data-toggle', "modal");
	kyujitu_rodo_jikan.setAttribute('data-target', target);

	var kyujitu_rodo_teate = document.getElementById('kyujitu_rodo_teate');
	kyujitu_rodo_teate.setAttribute('data-toggle', "modal");
	kyujitu_rodo_teate.setAttribute('data-target', target);
	
	//* 休日深夜労働時間・休日深夜労働手当（経理課用モーダル表示機能追加）
	var kyujitusinya_rodo_jikan = document.getElementById('kyujitusinya_rodo_jikan');
	kyujitusinya_rodo_jikan.setAttribute('data-toggle', "modal");
	kyujitusinya_rodo_jikan.setAttribute('data-target', target);

	var kyujitusinya_rodo_teate = document.getElementById('kyujitusinya_rodo_teate');
	kyujitusinya_rodo_teate.setAttribute('data-toggle', "modal");
	kyujitusinya_rodo_teate.setAttribute('data-target', target);

}

//****************************************************************************
//　残業時間入力用モーダルを表示させないよう設定する。
//*****************************************************************************
function zangyoModalUnset(){

	
	//* 通常残業時間・通常残業手当（モーダル表示機能削除）
	var tujo_zan_jikan = document.getElementById('tujo_zan_jikan');
	tujo_zan_jikan.setAttribute('data-toggle', "");
	tujo_zan_jikan.setAttribute('data-target', "");

	var tujo_zan_teate = document.getElementById('tujo_zan_teate');
	tujo_zan_teate.setAttribute('data-toggle', "");
	tujo_zan_teate.setAttribute('data-target', "");
	
	//* 深夜残業時間・深夜残業手当（モーダル表示機能削除）
	var sinya_zan_jikan = document.getElementById('sinya_zan_jikan');
	sinya_zan_jikan.setAttribute('data-toggle', "");
	sinya_zan_jikan.setAttribute('data-target', "");

	var sinya_zan_teate = document.getElementById('sinya_zan_teate');
	sinya_zan_teate.setAttribute('data-toggle', "");
	sinya_zan_teate.setAttribute('data-target', "");
	
	//* 休日労働時間・休日労働手当（モーダル表示機能削除）
	var kyujitu_rodo_jikan = document.getElementById('kyujitu_rodo_jikan');
	kyujitu_rodo_jikan.setAttribute('data-toggle', "");
	kyujitu_rodo_jikan.setAttribute('data-target', "");

	var kyujitu_rodo_teate = document.getElementById('kyujitu_rodo_teate');
	kyujitu_rodo_teate.setAttribute('data-toggle', "");
	kyujitu_rodo_teate.setAttribute('data-target', "");
	
	//* 休日深夜労働時間・休日深夜労働手当（モーダル表示機能削除）
	var kyujitusinya_rodo_jikan = document.getElementById('kyujitusinya_rodo_jikan');
	kyujitusinya_rodo_jikan.setAttribute('data-toggle', "");
	kyujitusinya_rodo_jikan.setAttribute('data-target', "");

	var kyujitusinya_rodo_teate = document.getElementById('kyujitusinya_rodo_teate');
	kyujitusinya_rodo_teate.setAttribute('data-toggle', "");
	kyujitusinya_rodo_teate.setAttribute('data-target', "");

}

//****************************************************************************
//　運用管理者ユーザー、システム開発ユーザーログインした場合に
//　編集可能にする項目を編集可能な状態にする。
//*****************************************************************************
function kanriEdit(){

	// 年間休日数（データ更新用モーダルを起動する機能を追加）
	var nenkan_kyujitu = document.getElementById('nenkan_kyujitu');
	nenkan_kyujitu.setAttribute('data-toggle', "modal");
	nenkan_kyujitu.setAttribute('data-target', "#nenkan_kyujitu_modal");

	// １日あたりの労働時間（データ更新用モーダルを起動する機能を追加）
	var rodo_jikan_per_day = document.getElementById('rodo_jikan_per_day');
	rodo_jikan_per_day.setAttribute('data-toggle', "modal");
	rodo_jikan_per_day.setAttribute('data-target', "#nenkan_kyujitu_modal");

	// １日あたりの休憩時間（データ更新用モーダルを起動する機能を追加）
	var kyukei_jikan_per_day = document.getElementById('kyukei_jikan_per_day');
	kyukei_jikan_per_day.setAttribute('data-toggle', "modal");
	kyukei_jikan_per_day.setAttribute('data-target', "#nenkan_kyujitu_modal");

	// 備考
	document.getElementById("biko").readOnly = false;

	//単身赴任フラグの値を取得する
	var tansinfunin_flg = kyuyo_arr["tansinfunin_flg"];
	tansinfunin_flg = tansinfunin_flg - 0;

	//月給の場合、以下の項目のタグを作成し、値をセット
	if(kyuyo_arr["gekkyu_nempo"] == 0){

		// 固定基本給
		document.getElementById("kotei_kihon_kyu").readOnly = false;

		// 調整手当
		document.getElementById("chosei_teate").readOnly = false;

		// 転勤住宅手当
		document.getElementById("tenkin_jutaku_teate").readOnly = false;

		//* 「固定基本給」の値をセット
		document.getElementById("kotei_kihon_kyu").value = numberFormat(kyuyo_arr["kotei_kihon_kyu"]);


		//* 「調整手当」の値をセット
		document.getElementById("chosei_teate").value = numberFormat(kyuyo_arr["chosei_teate"]);

		//* 「転勤住宅手当」の値をセット
		document.getElementById("tenkin_jutaku_teate").value = numberFormat(kyuyo_arr["tenkin_jutaku_teate"]);

		//* 単身赴任かどうかの値をセット
		// document.getElementById("tansinfunin").setAttribute('onclick', "chgTansinfuninVal();");
		if(tansinfunin_flg == 1){
			//「単身赴任」チェックボックスをオン
			document.getElementById("tansinfunin").checked = true;
		}
		document.getElementById("tansinfunin_value").value = tansinfunin_flg;

		//「単身赴任手当」が"無効"の場合は「単身赴任」チェックボックスをreadOnlyにする
		//"有効"の場合は、クリックしたときに実行される処理をセット
		if(mst_tansinfunin_teate_rows[0]["muko_flg"] == 1){
			document.getElementById("tansinfunin").setAttribute("onclick", "return false;");	//readOnlyにする
		}else{
			document.getElementById("tansinfunin").setAttribute('onclick', "chgTansinfuninVal();");	//使用可能（クリック時の処理をセット）
		}

		//* 「単身赴任手当」の値をセット
		document.getElementById("tansinfunin_teate").innerText = numberFormat(kyuyo_arr["tansinfunin_teate"]);

		//「月給－社保扶養」チェックボックス
		//「家族手当」が"無効"の場合は「月給－社保扶養」チェックボックスをreadOnlyにする
		//"有効"の場合は、クリックしたときに実行される処理をセット
		if(mst_kazoku_teate_rows[0]["muko_flg"] == 1){
			document.getElementById("shaho_fuyo").setAttribute("onclick", "return false;");	//readOnlyにする
		}else{
			document.getElementById("shaho_fuyo").setAttribute("onclick", "chgShahoFuyoVal();");	//使用可能（クリック時の処理をセット）
		}

		// 「年俸－社保扶養」チェックボックスをreadonly状態にする
		document.getElementById("nempo_shaho_fuyo").setAttribute("onclick", "return false;");


	//年俸の場合、以下の項目のタグを作成し、値をセット
	}else if(kyuyo_arr["gekkyu_nempo"] == 1){

		// うち固定残業代
		document.getElementById("nempo_uchi_koteizang_dai").readOnly = false;

		// うち営業手当
		document.getElementById("nempo_uchi_eigyo_teate").readOnly = false;

		//* 「うち固定残業代」をセット
		document.getElementById("nempo_uchi_koteizang_dai").value = numberFormat(kyuyo_arr["nempo_uchi_koteizang_dai"]);

		//* 「うち営業手当」をセット
		document.getElementById("nempo_uchi_eigyo_teate").value = numberFormat(kyuyo_arr["nempo_uchi_eigyo_teate"]);

		//* 「単身赴任」チェックボックスをreadonly状態にする
		document.getElementById("tansinfunin").setAttribute("onclick", "return false;");

		//* 「月給－社保扶養」チェックボックスをreadonly状態にする
		document.getElementById("shaho_fuyo").setAttribute("onclick", "return false;");

		//*「年俸－社保扶養」チェックボックス
		//「家族手当」が"無効"の場合は「年俸－社保扶養」チェックボックスをreadOnlyにする
		//"有効"の場合は、クリックしたときに実行される処理をセット
		if(mst_kazoku_teate_rows[0]["muko_flg"] == 1){
			document.getElementById("nempo_shaho_fuyo").setAttribute("onclick", "return false;");	//readOnlyにする
		}else{
			document.getElementById("nempo_shaho_fuyo").setAttribute("onclick", "chgNempoShahoFuyoVal();");	//使用可能（クリック時の処理をセット）
		}
	}

	//　データ退避用変数にセットする。”月給””年俸”関係なく
	taihi_tansinfunin_flg = tansinfunin_flg;	//単身赴任フラグ
	taihi_kotei_kihon_kyu = kyuyo_arr["kotei_kihon_kyu"];	//固定基本給
	taihi_chosei_teate = kyuyo_arr["chosei_teate"];	//調整手当
	taihi_tenkin_jutaku_teate = kyuyo_arr["tenkin_jutaku_teate"];	//転勤住宅手当
	taihi_tansinfunin_teate = kyuyo_arr["tansinfunin_teate"];	//単身赴任手当
	taihi_nempo_uchi_koteizang_dai = kyuyo_arr["nempo_uchi_koteizang_dai"];	//うち固定残業代
	taihi_nempo_uchi_eigyo_teate = kyuyo_arr["nempo_uchi_eigyo_teate"];	//うち営業手当

}


//****************************************************************************
//　対象年度の給与データ（本データ）を画面へセット
//*****************************************************************************
function taishoNendoHonDataSet(){

	//ここに来た時点でここでの対象年度の給与データは存在する。
	//来る前に判定している。

	//月給・年俸
	if(kyuyo_hon_arr["gekkyu_nempo"] == 0){
		document.getElementById("gekkyu_nempo_hon").innerText = "月給";
	}else if(kyuyo_hon_arr["gekkyu_nempo"] == 1){
		document.getElementById("gekkyu_nempo_hon").innerText = "年俸";
	}
	//職責
	document.getElementById("duties_hon").innerText = kyuyo_hon_arr["duties_name"];
	
	//勤務地
	document.getElementById("kimmuchi_hon").innerText = kyuyo_hon_arr["kimmuchi_name"];
	
	//年間休日数
	document.getElementById("nenkan_kyujitu_hon").innerText = kyuyo_hon_arr["nenkan_kyujitu"];
	// document.getElementById("nenkan_kyujitu_hon").innerText = nenkan_kyujitu_hon_row["nenkan_kyujitu"];
	
	//１日あたりの労働時間
	document.getElementById("rodo_jikan_per_day_hon").innerText = kyuyo_hon_arr["rodo_jikan_per_day"] - 0;
	// document.getElementById("rodo_jikan_per_day_hon").innerText = nenkan_kyujitu_hon_row["rodo_jikan_per_day"] - 0;
	
	//１日あたりの休憩時間
	document.getElementById("kyukei_jikan_per_day_hon").innerText = kyuyo_hon_arr["kyukei_jikan_per_day"] - 0;
	// document.getElementById("kyukei_jikan_per_day_hon").innerText = nenkan_kyujitu_hon_row["kyukei_jikan_per_day"] - 0;
	
	//月給の場合
	if(kyuyo_hon_arr["gekkyu_nempo"] == 0){
		//前年度職責給
		document.getElementById("zennen_shokuseki_kyu_hon").innerText = numberFormat(kyuyo_hon_arr["zennen_shokuseki_kyu"]);
		
		//評価点による昇給額
		document.getElementById("shokyu_gaku_hon").innerText = numberFormat(kyuyo_hon_arr["shokyu_gaku"]);
		
		//昇給訂正
		document.getElementById("teisei_gaku_hon").innerText = numberFormat(kyuyo_hon_arr["shokyu_teisei_gaku"]);
		
		//昇給嘆願
		document.getElementById("tangan_gaku_hon").innerText = numberFormat(kyuyo_hon_arr["shokyu_tangan_gaku"]);
		
		//職責給合計
		document.getElementById("shokuseki_kyu_hon").innerText = numberFormat(kyuyo_hon_arr["shokuseki_kyu"]);
		
		//年齢給
		document.getElementById("kihon_kyu_hon").innerText = numberFormat(kyuyo_hon_arr["kihon_kyu"]);
		
		//固定基本給
		document.getElementById("kotei_kihon_kyu_hon").innerText = numberFormat(kyuyo_hon_arr["kotei_kihon_kyu"]);
		
		//皆勤手当
		document.getElementById("kaikin_teate_hon").innerText = numberFormat(kyuyo_hon_arr["kaikin_teate"]);
		
		//都市手当
		document.getElementById("tosi_teate_hon").innerText = numberFormat(kyuyo_hon_arr["tosi_teate"]);
		
		//奨励手当
		document.getElementById("shorei_teate_hon").innerText = numberFormat(kyuyo_hon_arr["shorei_teate"]);
		
		//調整手当
		document.getElementById("chosei_teate_hon").innerText = numberFormat(kyuyo_hon_arr["chosei_teate"]);
		
		//残業代の基礎となる手当－小計
		var zankiso_shokei_hon = calcZanKisoShokei(kyuyo_hon_arr);
		document.getElementById("zankiso_shokei_hon").innerText = numberFormat(zankiso_shokei_hon);
		
		//転勤住宅手当
		document.getElementById("tenkin_jutaku_teate_hon").innerText = numberFormat(kyuyo_hon_arr["tenkin_jutaku_teate"]);
		
		//単身赴任手当
		document.getElementById("tansinfunin_teate_hon").innerText = numberFormat(kyuyo_hon_arr["tansinfunin_teate"]);
		
		//残業代の基礎となる手当ー合計
		var zankiso_total_hon = calcZanKisoGokei(kyuyo_hon_arr);
		document.getElementById("zankiso_total_hon").innerText = numberFormat(zankiso_total_hon);
		
		//月給－家族手当
		document.getElementById("kazoku_teate_hon").innerText = numberFormat(kyuyo_hon_arr["kazoku_teate"]);
		
		//効率残業手当
		document.getElementById("korituzangyo_teate_hon").innerText = numberFormat(kyuyo_hon_arr["korituzangyo_teate"]);
		
		//残業代の基礎とならない手当－合計
		var zan_nonkiso_total_hon = calcNonZanKisoGokei(kyuyo_hon_arr);
		document.getElementById("zan_nonkiso_total_hon").innerText = numberFormat(zan_nonkiso_total_hon);


		//残業単価
		//* 残業代の基礎となる手当の合計(zankiso_total_hon)
		//** 残業単価（金額は小数点第一位を切り上げ）
		var zan_tanka_hon = zankiso_total_hon / rodo_per_month_hon;
		zan_tanka_hon = Math.ceil(zan_tanka_hon);
		document.getElementById("zan_tanka_hon").innerText = numberFormat(zan_tanka_hon);


		//残業時間・残業手当をセット
		var tujo_zan_jikan_hon = 0;
		var tujo_zan_teate_hon = 0;
		var sinya_zan_jikan_hon = 0;
		var sinya_zan_teate_hon = 0;
		var kyujitu_rodo_jikan_hon = 0;
		var kyujitu_rodo_teate_hon = 0;
		var kyujitusinya_rodo_jikan_hon = 0;
		var kyujitusinya_rodo_teate_hon = 0;
		var zangyo_teate_gokei_hon = 0;

		// //* 運用管理者（経理課）、システム管理者（情シス）の場合
		// if(USER_SHUBETU == '4' || USER_SHUBETU == '5'){

		// 	//** 支店別残業時間テーブルから取得したデータが０件の場合
		// 	if(siten_zangyo_jikan_arr == '0'){
		// 		//* 「通常残業時間」をセット
		// 		document.getElementById("tujo_zan_jikan_hon").innerText = 0;
		// 		//* 「通常残業手当」をセット
		// 		document.getElementById("tujo_zan_teate_hon").innerText = 0;
		// 		//* 「深夜残業時間」をセット
		// 		document.getElementById("sinya_zan_jikan_hon").innerText = 0;
		// 		//* 「深夜残業手当」をセット
		// 		document.getElementById("sinya_zan_teate_hon").innerText = 0;
		// 		//* 「休日労働時間」をセット
		// 		document.getElementById("kyujitu_rodo_jikan_hon").innerText = 0;
		// 		//* 「休日労働手当」をセット
		// 		document.getElementById("kyujitu_rodo_teate_hon").innerText = 0;
		// 		//* 「休日深夜労働時間」をセット
		// 		document.getElementById("kyujitusinya_rodo_jikan_hon").innerText = 0;
		// 		//* 「休日深夜労働手当」をセット
		// 		document.getElementById("kyujitusinya_rodo_teate_hon").innerText = 0;
		// 		//* 「残業手当」をセット
		// 		document.getElementById("zangyo_teate_gokei_hon").innerText = 0;

		// 	//支店別残業時間テーブルからデータを取得できた場合
		// 	}else{
		// 		//* 「通常残業時間」をセット
		// 		tujo_zan_jikan_hon = siten_zangyo_jikan_arr["tujo_zan_jikan"] - 0;
		// 		document.getElementById("tujo_zan_jikan_hon").innerText = tujo_zan_jikan_hon;

		// 		//* 「通常残業手当」を算出してセット
		// 		//* （残業単価　×　（１＋0.25）　×　労働時間
		// 		tujo_zan_teate_hon = calcTujoZanTeate(zan_tanka_hon, tujo_zan_jikan_hon);
		// 		document.getElementById("tujo_zan_teate_hon").innerText = numberFormat(tujo_zan_teate_hon);

		// 		//* 「深夜残業時間」をセット
		// 		sinya_zan_jikan_hon = siten_zangyo_jikan_arr["sinya_zan_jikan"] - 0;
		// 		document.getElementById("sinya_zan_jikan_hon").innerText = sinya_zan_jikan_hon;

		// 		//* 「深夜残業手当」をセット
		// 		//* （残業単価　×　（１＋0.25）　×　労働時間
		// 		sinya_zan_teate_hon = calcSinyaZanTeate(zan_tanka_hon, sinya_zan_jikan_hon);
		// 		document.getElementById("sinya_zan_teate_hon").innerText = numberFormat(sinya_zan_teate_hon);

		// 		//* 「休日労働時間」をセット
		// 		kyujitu_rodo_jikan_hon = siten_zangyo_jikan_arr["kyujitu_rodo_jikan"] - 0;
		// 		document.getElementById("kyujitu_rodo_jikan_hon").innerText = kyujitu_rodo_jikan_hon;

		// 		//* 「休日労働手当」をセット
		// 		//* （残業単価　×　（１＋0.35）　×　労働時間
		// 		kyujitu_rodo_teate_hon = calcKyujituRodoTeate(zan_tanka_hon, kyujitu_rodo_jikan_hon);
		// 		document.getElementById("kyujitu_rodo_teate_hon").innerText = numberFormat(kyujitu_rodo_teate_hon);

		// 		//* 「休日深夜労働時間」をセット
		// 		kyujitusinya_rodo_jikan_hon = siten_zangyo_jikan_arr["kyujitusinya_rodo_jikan"] - 0;
		// 		document.getElementById("kyujitusinya_rodo_jikan_hon").innerText = kyujitusinya_rodo_jikan_hon;

		// 		//* 「休日深夜労働手当」をセット
		// 		//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
		// 		kyujitusinya_rodo_teate_hon = calcKyujituSinyaRodoTeate(zan_tanka_hon, kyujitusinya_rodo_jikan_hon);
		// 		document.getElementById("kyujitusinya_rodo_teate_hon").innerText = numberFormat(kyujitusinya_rodo_teate_hon);

		// 		//* 「残業手当」をセット
		// 		zangyo_teate_gokei_hon = tujo_zan_teate_hon + sinya_zan_teate_hon + kyujitu_rodo_teate_hon + kyujitusinya_rodo_teate_hon;
		// 		document.getElementById("zangyo_teate_gokei_hon").innerText = numberFormat(zangyo_teate_gokei_hon);
		// 	}

		// //運用管理者（経理課）、システム管理者（情シス）以外の場合
		// }else{
			//* 「通常残業時間」をセット
			var wk_jikan_nenkei = 0;
			wk_jikan_nenkei = kyuyo_hon_arr["tujo_zan_jikan_nenkei"] - 0;
			tujo_zan_jikan_hon  = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("tujo_zan_jikan_hon").innerText = tujo_zan_jikan_hon;

			//* 「通常残業手当」をセット
			//* （残業単価　×　（１＋0.25）　×　労働時間
			tujo_zan_teate_hon = calcTujoZanTeate(zan_tanka_hon, tujo_zan_jikan_hon);
			document.getElementById("tujo_zan_teate_hon").innerText = numberFormat(tujo_zan_teate_hon);

			//* 「深夜残業時間」をセット
			wk_jikan_nenkei = kyuyo_hon_arr["sinya_zan_jikan_nenkei"] - 0;
			sinya_zan_jikan_hon  = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("sinya_zan_jikan_hon").innerText = sinya_zan_jikan_hon;

			//* 「深夜残業手当」をセット
			//* （残業単価　×　（１＋0.25）　×　労働時間
			sinya_zan_teate_hon = calcSinyaZanTeate(zan_tanka_hon, sinya_zan_jikan_hon);
			document.getElementById("sinya_zan_teate_hon").innerText = numberFormat(sinya_zan_teate_hon);

			//* 「休日労働時間」をセット
			wk_jikan_nenkei = kyuyo_hon_arr["kyujitu_rodo_jikan_nenkei"] - 0;
			kyujitu_rodo_jikan_hon  = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("kyujitu_rodo_jikan_hon").innerText = kyujitu_rodo_jikan_hon;

			//* 「休日労働手当」をセット
			//* （残業単価　×　（１＋0.35）　×　労働時間
			kyujitu_rodo_teate_hon = calcKyujituRodoTeate(zan_tanka_hon, kyujitu_rodo_jikan_hon);
			document.getElementById("kyujitu_rodo_teate_hon").innerText = numberFormat(kyujitu_rodo_teate_hon);

			//* 「休日深夜労働時間」をセット
			wk_jikan_nenkei = kyuyo_hon_arr["kyujitusinya_rodo_jikan_nenkei"] - 0;
			kyujitusinya_rodo_jikan_hon  = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("kyujitusinya_rodo_jikan_hon").innerText = kyujitusinya_rodo_jikan_hon;

			//* 「休日深夜労働手当」をセット
			//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
			kyujitusinya_rodo_teate_hon = calcKyujituSinyaRodoTeate(zan_tanka_hon, kyujitusinya_rodo_jikan_hon);
			document.getElementById("kyujitusinya_rodo_teate_hon").innerText = numberFormat(kyujitusinya_rodo_teate_hon);

			//* 「残業手当」をセット
			zangyo_teate_gokei_hon = tujo_zan_teate_hon + sinya_zan_teate_hon + kyujitu_rodo_teate_hon + kyujitusinya_rodo_teate_hon;
			document.getElementById("zangyo_teate_gokei_hon").innerText = numberFormat(zangyo_teate_gokei_hon);
		// }

	//年俸の場合
	}else if(kyuyo_hon_arr["gekkyu_nempo"] == 1){
		
		//* 「年俸－前年度年額」をセット
		document.getElementById("zennen_nempo_nengaku_hon").innerText = numberFormat(kyuyo_hon_arr["zennen_nempo_nengaku"]);

		//* 「年俸－昇給－評価点による昇給額（月額）」をセット
		document.getElementById("nempo_shokyu_gaku_hon").innerText = numberFormat(kyuyo_hon_arr["shokyu_gaku"]);
		
		//* 「年俸－昇給－昇給訂正（月額）」をセット
		document.getElementById("nempo_teisei_gaku_hon").innerText = numberFormat(kyuyo_hon_arr["shokyu_teisei_gaku"]);
		
		//* 「年俸－昇給－昇給嘆願（月額）」をセット
		document.getElementById("nempo_tangan_gaku_hon").innerText = numberFormat(kyuyo_hon_arr["shokyu_tangan_gaku"]);

		//* 「年俸－昇給－年額」をセット（「年俸－昇給－評価点による昇給額（月額）」を年額に（12倍）したものをセット）
		var nempo_shokyu_nengaku_hon = kyuyo_hon_arr["shokyu_gaku"] - 0;
		nempo_shokyu_nengaku_hon = nempo_shokyu_nengaku_hon * 12;
		document.getElementById("nempo_shokyu_nengaku_hon").innerText = numberFormat(nempo_shokyu_nengaku_hon);

		//「年俸－年額合計（前年度年額＋昇給年額）」をセット
		document.getElementById("nempo_nengaku_hon").innerText = numberFormat(kyuyo_hon_arr["nempo_nengaku"]);
		
		//年俸－12で割った金額／14で割った金額
		var nempo_getugaku_hon = kyuyo_hon_arr["shikyugaku_a"];
		document.getElementById("nempo_getugaku_hon").innerText = numberFormat(nempo_getugaku_hon);
		
		//年俸－家族手当
		document.getElementById("nempo_kazoku_teate_hon").innerText = numberFormat(kyuyo_hon_arr["kazoku_teate"]);

		//* 残業関係は無し。「総支給額」のために合計値をゼロにしておく
		var zangyo_teate_gokei_hon = 0;
		
		//年俸－うち固定残業代
		document.getElementById("nempo_uchi_koteizang_dai_hon").innerText = numberFormat(kyuyo_hon_arr["nempo_uchi_koteizang_dai"]);
		
		//年俸－うち営業手当
		document.getElementById("nempo_uchi_eigyo_teate_hon").innerText = numberFormat(kyuyo_hon_arr["nempo_uchi_eigyo_teate"]);

	}
	
	
	//支給額Ａ
	document.getElementById("shikyugaku_a_hon").innerText = numberFormat(kyuyo_hon_arr["shikyugaku_a"]);
	
	//前年との差[職責給]+[営業手当]+[効率残業手当]+[固定残業手当]
	document.getElementById("sagaku_teate_hon").innerText = numberFormat(kyuyo_hon_arr["sagaku_teate"]);;
	
	//総支給額（支給額Ａ　＋　残業手当）
	var a_zan_total_hon = calcSosikyugaku(kyuyo_hon_arr);
	document.getElementById("a_zan_total_hon").innerText = numberFormat(a_zan_total_hon);
	
	//欠勤控除
	document.getElementById("kekkin_kojo_hon").innerText = numberFormat(kyuyo_hon_arr["kekkin_kojo"]);
	
	//遅刻相対控除
	document.getElementById("chikoku_sotai_kojo_hon").innerText = numberFormat(kyuyo_hon_arr["chikoku_sotai_kojo"]);
	
	//備考
	document.getElementById("biko_hon").innerText = kyuyo_hon_arr["biko"];
}
//****************************************************************************
//　対象年度以前の年度の給与データを画面へセット
//*****************************************************************************
function kakoNendoDataSet(num){

	//ここに来た時点でここでの対象年度の給与データは存在する。
	//来る前に判定している。

	var kyuyo_dt_kako_arr;
	var kyuyo_dt_kako_minus1_arr;
	// var nenkan_kyujitu_kako_row;
	var rodo_per_month_kako;
	// var siten_zangyo_jikan_kako_arr;
	switch(num){
		case 1:
			kyuyo_dt_kako_arr = kyuyo_minus1_arr;
			kyuyo_dt_kako_minus1_arr = kyuyo_minus2_arr;	//対象となる過去データの１年前のデータ
			// nenkan_kyujitu_kako_row = nenkan_kyujitu_minus1_row;
			rodo_per_month_kako = rodo_per_month_minus1;
			// siten_zangyo_jikan_kako_arr = siten_zangyo_jikan_minus1_arr;
			break;
		case 2:
			kyuyo_dt_kako_arr = kyuyo_minus2_arr;
			kyuyo_dt_kako_minus1_arr = kyuyo_minus3_arr;	//対象となる過去データの１年前のデータ
			// nenkan_kyujitu_kako_row = nenkan_kyujitu_minus2_row;
			rodo_per_month_kako = rodo_per_month_minus2;
			// siten_zangyo_jikan_kako_arr = siten_zangyo_jikan_minus2_arr;
			break;
		case 3:
			kyuyo_dt_kako_arr = kyuyo_minus3_arr;
			kyuyo_dt_kako_minus1_arr = kyuyo_minus4_arr;	//対象となる過去データの１年前のデータ
			// nenkan_kyujitu_kako_row = nenkan_kyujitu_minus3_row;
			rodo_per_month_kako = rodo_per_month_minus3;
			// siten_zangyo_jikan_kako_arr = siten_zangyo_jikan_minus3_arr;
			break;
		case 4:
			kyuyo_dt_kako_arr = kyuyo_minus4_arr;
			kyuyo_dt_kako_minus1_arr = kyuyo_minus5_arr;	//対象となる過去データの１年前のデータ
			// nenkan_kyujitu_kako_row = nenkan_kyujitu_minus4_row;
			rodo_per_month_kako = rodo_per_month_minus4;
			// siten_zangyo_jikan_kako_arr = siten_zangyo_jikan_minus4_arr;
			break;
		case 5:
			kyuyo_dt_kako_arr = kyuyo_minus5_arr;
			kyuyo_dt_kako_minus1_arr = kyuyo_minus6_arr;	//対象となる過去データの１年前のデータ
			// nenkan_kyujitu_kako_row = nenkan_kyujitu_minus5_row;
			rodo_per_month_kako = rodo_per_month_minus5;
			// siten_zangyo_jikan_kako_arr = siten_zangyo_jikan_minus5_arr;
			break;
	}
	//月給・年俸
	if(kyuyo_dt_kako_arr["gekkyu_nempo"] == 0){
		document.getElementById("gekkyu_nempo_minus" + num).innerText = "月給";
	}else if(kyuyo_dt_kako_arr["gekkyu_nempo"] == 1){
		document.getElementById("gekkyu_nempo_minus" + num).innerText = "年俸";
	}
	//職責
	document.getElementById("duties_minus" + num).innerText = kyuyo_dt_kako_arr["duties_name"];
	
	//勤務地
	document.getElementById("kimmuchi_minus" + num).innerText = kyuyo_dt_kako_arr["kimmuchi_name"];
	
	//年間休日数
	document.getElementById("nenkan_kyujitu_minus" + num).innerText = kyuyo_dt_kako_arr["nenkan_kyujitu"];
	// document.getElementById("nenkan_kyujitu_minus" + num).innerText = nenkan_kyujitu_kako_row["nenkan_kyujitu"];
	
	//１日あたりの労働時間
	document.getElementById("rodo_jikan_per_day_minus" + num).innerText = kyuyo_dt_kako_arr["rodo_jikan_per_day"] - 0;
	// document.getElementById("rodo_jikan_per_day_minus" + num).innerText = nenkan_kyujitu_kako_row["rodo_jikan_per_day"] - 0;
	
	//１日あたりの休憩時間
	document.getElementById("kyukei_jikan_per_day_minus" + num).innerText = kyuyo_dt_kako_arr["kyukei_jikan_per_day"] - 0;
	// document.getElementById("kyukei_jikan_per_day_minus" + num).innerText = nenkan_kyujitu_kako_row["kyukei_jikan_per_day"] - 0;
	
	//月給の場合
	if(kyuyo_dt_kako_arr["gekkyu_nempo"] == 0){
		//前年度職責給
		document.getElementById("zennen_shokuseki_kyu_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["zennen_shokuseki_kyu"]);
		
		//評価点による昇給額
		document.getElementById("shokyu_gaku_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shokyu_gaku"]);
		
		//昇給訂正
		document.getElementById("teisei_gaku_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shokyu_teisei_gaku"]);
		
		//昇給嘆願
		document.getElementById("tangan_gaku_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shokyu_tangan_gaku"]);
		
		//職責給合計
		document.getElementById("shokuseki_kyu_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shokuseki_kyu"]);
		
		//年齢給
		document.getElementById("kihon_kyu_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["kihon_kyu"]);
		
		//固定基本給
		document.getElementById("kotei_kihon_kyu_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["kotei_kihon_kyu"]);
		
		//皆勤手当
		document.getElementById("kaikin_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["kaikin_teate"]);
		
		//都市手当
		document.getElementById("tosi_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["tosi_teate"]);
		
		//奨励手当
		document.getElementById("shorei_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shorei_teate"]);
		
		//調整手当
		document.getElementById("chosei_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["chosei_teate"]);
		
		//残業代の基礎となる手当－小計
		var zankiso_shokei_minus = calcZanKisoShokei(kyuyo_dt_kako_arr);
		document.getElementById("zankiso_shokei_minus" + num).innerText = numberFormat(zankiso_shokei_minus);
		
		//転勤住宅手当
		document.getElementById("tenkin_jutaku_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["tenkin_jutaku_teate"]);
		
		//単身赴任手当
		document.getElementById("tansinfunin_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["tansinfunin_teate"]);
		
		//残業代の基礎となる手当ー合計
		var zankiso_total_minus = calcZanKisoGokei(kyuyo_dt_kako_arr);
		document.getElementById("zankiso_total_minus" + num).innerText = numberFormat(zankiso_total_minus);
		
		//月給－家族手当
		document.getElementById("kazoku_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["kazoku_teate"]);
		
		//効率残業手当
		document.getElementById("korituzangyo_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["korituzangyo_teate"]);
		
		//残業代の基礎とならない手当－合計
		var zan_nonkiso_total_minus = calcNonZanKisoGokei(kyuyo_dt_kako_arr);
		document.getElementById("zan_nonkiso_total_minus" + num).innerText = numberFormat(zan_nonkiso_total_minus);


		//残業単価
		//* 残業代の基礎となる手当の合計(zankiso_total_minus)
		//** 残業単価（金額は小数点第一位を切り上げ）
		var zan_tanka_minus = zankiso_total_minus / rodo_per_month_kako;
		zan_tanka_minus = Math.ceil(zan_tanka_minus);
		document.getElementById("zan_tanka_minus" + num).innerText = numberFormat(zan_tanka_minus);


		//残業時間・残業手当をセット
		var tujo_zan_jikan_minus = 0;
		var tujo_zan_teate_minus = 0;
		var sinya_zan_jikan_minus = 0;
		var sinya_zan_teate_minus = 0;
		var kyujitu_rodo_jikan_minus = 0;
		var kyujitu_rodo_teate_minus = 0;
		var kyujitusinya_rodo_jikan_minus = 0;
		var kyujitusinya_rodo_teate_minus = 0;
		var zangyo_teate_gokei_minus = 0;

		// //* 運用管理者（経理課）、システム管理者（情シス）の場合
		// if(USER_SHUBETU == '4' || USER_SHUBETU == '5'){

		// 	//** 支店別残業時間テーブルから取得したデータが０件の場合
		// 	if(siten_zangyo_jikan_kako_arr == '0'){
		// 		//* 「通常残業時間」をセット
		// 		document.getElementById("tujo_zan_jikan_minus" + num).innerText = 0;
		// 		//* 「通常残業手当」をセット
		// 		document.getElementById("tujo_zan_teate_minus" + num).innerText = 0;
		// 		//* 「深夜残業時間」をセット
		// 		document.getElementById("sinya_zan_jikan_minus" + num).innerText = 0;
		// 		//* 「深夜残業手当」をセット
		// 		document.getElementById("sinya_zan_teate_minus" + num).innerText = 0;
		// 		//* 「休日労働時間」をセット
		// 		document.getElementById("kyujitu_rodo_jikan_minus" + num).innerText = 0;
		// 		//* 「休日労働手当」をセット
		// 		document.getElementById("kyujitu_rodo_teate_minus" + num).innerText = 0;
		// 		//* 「休日深夜労働時間」をセット
		// 		document.getElementById("kyujitusinya_rodo_jikan_minus" + num).innerText = 0;
		// 		//* 「休日深夜労働手当」をセット
		// 		document.getElementById("kyujitusinya_rodo_teate_minus" + num).innerText = 0;
		// 		//* 「残業手当」をセット
		// 		document.getElementById("zangyo_teate_gokei_minus" + num).innerText = 0;

		// 	//支店別残業時間テーブルからデータを取得できた場合
		// 	}else{
		// 		//* 「通常残業時間」をセット
		// 		tujo_zan_jikan_minus = siten_zangyo_jikan_kako_arr["tujo_zan_jikan"] - 0;
		// 		document.getElementById("tujo_zan_jikan_minus" + num).innerText = tujo_zan_jikan_minus;

		// 		//* 「通常残業手当」を算出してセット
		// 		//* （残業単価　×　（１＋0.25）　×　労働時間
		// 		tujo_zan_teate_minus = calcTujoZanTeate(zan_tanka_minus, tujo_zan_jikan_minus);
		// 		document.getElementById("tujo_zan_teate_minus" + num).innerText = numberFormat(tujo_zan_teate_minus);

		// 		//* 「深夜残業時間」をセット
		// 		sinya_zan_jikan_minus = siten_zangyo_jikan_kako_arr["sinya_zan_jikan"] - 0;
		// 		document.getElementById("sinya_zan_jikan_minus" + num).innerText = sinya_zan_jikan_minus;

		// 		//* 「深夜残業手当」をセット
		// 		//* （残業単価　×　（１＋0.25）　×　労働時間
		// 		sinya_zan_teate_minus = calcSinyaZanTeate(zan_tanka_minus, sinya_zan_jikan_minus);
		// 		document.getElementById("sinya_zan_teate_minus" + num).innerText = numberFormat(sinya_zan_teate_minus);

		// 		//* 「休日労働時間」をセット
		// 		kyujitu_rodo_jikan_minus = siten_zangyo_jikan_kako_arr["kyujitu_rodo_jikan"] - 0;
		// 		document.getElementById("kyujitu_rodo_jikan_minus" + num).innerText = kyujitu_rodo_jikan_minus;

		// 		//* 「休日労働手当」をセット
		// 		//* （残業単価　×　（１＋0.35）　×　労働時間
		// 		kyujitu_rodo_teate_minus = calcKyujituRodoTeate(zan_tanka_minus, kyujitu_rodo_jikan_minus);
		// 		document.getElementById("kyujitu_rodo_teate_minus" + num).innerText = numberFormat(kyujitu_rodo_teate_minus);

		// 		//* 「休日深夜労働時間」をセット
		// 		kyujitusinya_rodo_jikan_minus = siten_zangyo_jikan_kako_arr["kyujitusinya_rodo_jikan"] - 0;
		// 		document.getElementById("kyujitusinya_rodo_jikan_minus" + num).innerText = kyujitusinya_rodo_jikan_minus;

		// 		//* 「休日深夜労働手当」をセット
		// 		//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
		// 		kyujitusinya_rodo_teate_minus = calcKyujituSinyaRodoTeate(zan_tanka_minus, kyujitusinya_rodo_jikan_minus);
		// 		document.getElementById("kyujitusinya_rodo_teate_minus" + num).innerText = numberFormat(kyujitusinya_rodo_teate_minus);

		// 		//* 「残業手当」をセット
		// 		zangyo_teate_gokei_minus = tujo_zan_teate_minus + sinya_zan_teate_minus + kyujitu_rodo_teate_minus + kyujitusinya_rodo_teate_minus;
		// 		document.getElementById("zangyo_teate_gokei_minus" + num).innerText = numberFormat(zangyo_teate_gokei_minus);
		// 	}

		// //運用管理者（経理課）、システム管理者（情シス）以外の場合
		// }else{
			//* 「通常残業時間」をセット
			var wk_jikan_nenkei = 0;
			wk_jikan_nenkei = kyuyo_dt_kako_arr["tujo_zan_jikan_nenkei"] - 0;
			tujo_zan_jikan_minus  = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("tujo_zan_jikan_minus" + num).innerText = tujo_zan_jikan_minus;

			//* 「通常残業手当」をセット
			//* （残業単価　×　（１＋0.25）　×　労働時間
			tujo_zan_teate_minus = calcTujoZanTeate(zan_tanka_minus, tujo_zan_jikan_minus);
			document.getElementById("tujo_zan_teate_minus" + num).innerText = numberFormat(tujo_zan_teate_minus);

			//* 「深夜残業時間」をセット
			wk_jikan_nenkei = kyuyo_dt_kako_arr["sinya_zan_jikan_nenkei"] - 0;
			sinya_zan_jikan_minus  = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("sinya_zan_jikan_minus" + num).innerText = sinya_zan_jikan_minus;

			//* 「深夜残業手当」をセット
			//* （残業単価　×　（１＋0.25）　×　労働時間
			sinya_zan_teate_minus = calcSinyaZanTeate(zan_tanka_minus, sinya_zan_jikan_minus);
			document.getElementById("sinya_zan_teate_minus" + num).innerText = numberFormat(sinya_zan_teate_minus);

			//* 「休日労働時間」をセット
			wk_jikan_nenkei = kyuyo_dt_kako_arr["kyujitu_rodo_jikan_nenkei"] - 0;
			kyujitu_rodo_jikan_minus  = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("kyujitu_rodo_jikan_minus" + num).innerText = kyujitu_rodo_jikan_minus;

			//* 「休日労働手当」をセット
			//* （残業単価　×　（１＋0.35）　×　労働時間
			kyujitu_rodo_teate_minus = calcKyujituRodoTeate(zan_tanka_minus, kyujitu_rodo_jikan_minus);
			document.getElementById("kyujitu_rodo_teate_minus" + num).innerText = numberFormat(kyujitu_rodo_teate_minus);

			//* 「休日深夜労働時間」をセット
			wk_jikan_nenkei = kyuyo_dt_kako_arr["kyujitusinya_rodo_jikan_nenkei"] - 0;
			kyujitusinya_rodo_jikan_minus  = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("kyujitusinya_rodo_jikan_minus" + num).innerText = kyujitusinya_rodo_jikan_minus;

			//* 「休日深夜労働手当」をセット
			//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
			kyujitusinya_rodo_teate_minus = calcKyujituSinyaRodoTeate(zan_tanka_minus, kyujitusinya_rodo_jikan_minus);
			document.getElementById("kyujitusinya_rodo_teate_minus" + num).innerText = numberFormat(kyujitusinya_rodo_teate_minus);

			//* 「残業手当」をセット
			zangyo_teate_gokei_minus = tujo_zan_teate_minus + sinya_zan_teate_minus + kyujitu_rodo_teate_minus + kyujitusinya_rodo_teate_minus;
			document.getElementById("zangyo_teate_gokei_minus" + num).innerText = numberFormat(zangyo_teate_gokei_minus);
		// }

	//年俸の場合
	}else if(kyuyo_dt_kako_arr["gekkyu_nempo"] == 1){
		
		//* 「年俸－前年度年額」をセット
		document.getElementById("zennen_nempo_nengaku_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["zennen_nempo_nengaku"]);

		//* 「年俸－昇給－評価点による昇給額（月額）」をセット
		document.getElementById("nempo_shokyu_gaku_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shokyu_gaku"]);
		
		//* 「年俸－昇給－昇給訂正（月額）」をセット
		document.getElementById("nempo_teisei_gaku_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shokyu_teisei_gaku"]);
		
		//* 「年俸－昇給－昇給嘆願（月額）」をセット
		document.getElementById("nempo_tangan_gaku_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shokyu_tangan_gaku"]);

		//* 「年俸－昇給－年額」をセット（「年俸－昇給－評価点による昇給額（月額）」を年額に（12倍）したものをセット）
		var nempo_shokyu_nengaku_minus = kyuyo_dt_kako_arr["shokyu_gaku"] - 0;
		nempo_shokyu_nengaku_minus = nempo_shokyu_nengaku_minus * 12;
		document.getElementById("nempo_shokyu_nengaku_minus" + num).innerText = numberFormat(nempo_shokyu_nengaku_minus);

		//「年俸－年額合計（前年度年額＋昇給年額）」をセット
		document.getElementById("nempo_nengaku_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["nempo_nengaku"]);
		
		//年俸－12で割った金額／14で割った金額
		var nempo_getugaku_minus = kyuyo_dt_kako_arr["shikyugaku_a"];
		document.getElementById("nempo_getugaku_minus" + num).innerText = numberFormat(nempo_getugaku_minus);
		
		//年俸－家族手当
		document.getElementById("nempo_kazoku_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["kazoku_teate"]);

		//* 残業関係は無し。「総支給額」のために合計値をゼロにしておく
		var zangyo_teate_gokei_minus = 0;
		
		//年俸－うち固定残業代
		document.getElementById("nempo_uchi_koteizang_dai_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["nempo_uchi_koteizang_dai"]);
		
		//年俸－うち営業手当
		document.getElementById("nempo_uchi_eigyo_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["nempo_uchi_eigyo_teate"]);

	}
	
	
	//支給額Ａ
	document.getElementById("shikyugaku_a_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["shikyugaku_a"]);
	
	//前年との差[職責給]+[営業手当]+[効率残業手当]+[固定残業手当]
	document.getElementById("sagaku_teate_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["sagaku_teate"]);;
	
	//総支給額（支給額Ａ　＋　残業手当）
	var a_zan_total_minus = calcSosikyugaku(kyuyo_dt_kako_arr);
	document.getElementById("a_zan_total_minus" + num).innerText = numberFormat(a_zan_total_minus);
	
	//欠勤控除
	document.getElementById("kekkin_kojo_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["kekkin_kojo"]);
	
	//遅刻相対控除
	document.getElementById("chikoku_sotai_kojo_minus" + num).innerText = numberFormat(kyuyo_dt_kako_arr["chikoku_sotai_kojo"]);
	
	//備考
	document.getElementById("biko_minus" + num).innerText = kyuyo_dt_kako_arr["biko"];
}

//****************************************************************************
//　対象年度の給与データを画面へセット
//*****************************************************************************
function taishoNendoDataSet(){

	// 職責セレクトボックス内の値を設定
	//* 対象年度分
	var sel_duties = document.getElementById("sel_duties");
	sel_duties.innerHTML = '';
	//** 先頭の空行を入れる
	var op = document.createElement('option');
	op.setAttribute('value', "");
	op.innerHTML = '';
	sel_duties.appendChild(op);
	//** 中身をセット
	for(var i=0; i<pd_shokuseki_rows.length; i++){
		var op = document.createElement('option');
		op.setAttribute('value', pd_shokuseki_rows[i]["duties_cd"]);
		op.innerHTML = pd_shokuseki_rows[i]["duties_name"];
		sel_duties.appendChild(op);
	}
	//** 選択状態にする
	if(sel_duties){
		for(i = 0; i < sel_duties.options.length; i++){
			if (sel_duties.options[i].value == kyuyo_arr["duties_cd"]){
				sel_duties[i].selected = true;
				break;
			}
		}
	}

	//* 「年間休日数」をセット
	document.getElementById("nenkan_kyujitu").innerText = kyuyo_arr["nenkan_kyujitu"];	//親画面
	document.getElementById("nenkan_kyujitu_input").value = kyuyo_arr["nenkan_kyujitu"];	//モーダルの入力欄
	keep_nenkan_kyujitu = kyuyo_arr["nenkan_kyujitu"];	//退避（登録処理で使用）

	//* 「1日あたりの労働時間」をセット
	document.getElementById("rodo_jikan_per_day").innerText = kyuyo_arr["rodo_jikan_per_day"] - 0;	//親画面
	document.getElementById("rodo_jikan_per_day_input").value = kyuyo_arr["rodo_jikan_per_day"] - 0;	//モーダルの入力欄
	keep_rodo_jikan_per_day = kyuyo_arr["rodo_jikan_per_day"] - 0;	//退避（登録処理で使用）

	//* 「1日あたりの休憩時間」をセット
	document.getElementById("kyukei_jikan_per_day").innerText = kyuyo_arr["kyukei_jikan_per_day"] - 0;	//親画面
	document.getElementById("kyukei_jikan_per_day_input").value = kyuyo_arr["kyukei_jikan_per_day"] - 0;	//モーダルの入力欄
	keep_kyukei_jikan_per_day = kyuyo_arr["kyukei_jikan_per_day"] - 0;	//退避（登録処理で使用）

	//* 「昇給訂正」モーダルの状態を整える
	if(0 < kyuyo_arr["shokyu_teisei_gaku"]){
		//モーダルのチェックボックスをオンにする
		document.getElementById("teisei").checked = true;

		//モーダルの入力欄を入力可能状態にする
		document.getElementById("teisei_ran").disabled = false;
		
		//モーダルの入力欄に値をセット
		document.getElementById("teisei_ran").value = numberFormat(kyuyo_arr["shokyu_teisei_gaku"]);	//モーダルの入力欄
	}

	//* 「昇給嘆願」モーダルの状態を整える
	if(0 < kyuyo_arr["shokyu_tangan_gaku"]){
		//モーダルのチェックボックスをオンにする
		document.getElementById("tangan").checked = true;

		//モーダルの入力欄を入力可能状態にする
		document.getElementById("tangan_ran").disabled = false;
		document.getElementById("tangan_riyu").disabled = false;
		
		//モーダルの入力欄に値をセット
		document.getElementById("tangan_ran").value = numberFormat(kyuyo_arr["shokyu_tangan_gaku"]);
		document.getElementById("tangan_riyu").value =kyuyo_arr["shokyu_tangan_riyu"];
	}

	//社保扶養フラグの値を取得する
	var shaho_fuyo_flg = kyuyo_arr["shaho_fuyo_flg"];
	shaho_fuyo_flg = shaho_fuyo_flg - 0;

	//月給の場合
	if(kyuyo_arr["gekkyu_nempo"] == 0){
		
		// 「勤務地」の［初期値］ボタンを有効化する
		document.getElementById("btn_kimmuchi").disabled = false;
	
		// 勤務地セレクトボックスを作成し、値を設定
		var kimmuchi_box = document.getElementById("kimmuchi_box");
		var sel_kimmuchi = document.createElement('select');
		sel_kimmuchi.setAttribute('class', "form-control");
		sel_kimmuchi.setAttribute('id', "sel_kimmuchi");
		sel_kimmuchi.setAttribute('name', "sel_kimmuchi");
		sel_kimmuchi.setAttribute('onChange', "reCalc()");
		kimmuchi_box.appendChild(sel_kimmuchi);
		sel_kimmuchi.innerHTML = '';
		//* 中身をセット
		for(var i=0; i<pd_kimmuchi_rows.length; i++){
			var op = document.createElement('option');
			op.setAttribute('value', pd_kimmuchi_rows[i]["siten_cd"] + ";" + pd_kimmuchi_rows[i]["todofuken_cd"]);
			op.innerHTML = pd_kimmuchi_rows[i]["siten_name"];
			sel_kimmuchi.appendChild(op);
		}
		//* 選択状態にする
		var siten_cd_arr = [];
		if(sel_kimmuchi){
			for(i = 0; i < sel_kimmuchi.options.length; i++){
				siten_cd_arr = sel_kimmuchi.options[i].value.split(";");
				if (siten_cd_arr[0] == kyuyo_arr["kimmuchi_cd"]){
					sel_kimmuchi[i].selected = true;
					break;
				}
			}
		}

		//* 「前年度職責給」をセット
		document.getElementById("zennen_shokuseki_kyu").innerText = numberFormat(kyuyo_arr["zennen_shokuseki_kyu"]);

		//* 「評価点による昇給額」をセット
		document.getElementById("shokyu_gaku").innerText = numberFormat(kyuyo_arr["shokyu_gaku"]);
		
		//* 「昇給訂正」をセット
		document.getElementById("teisei_gaku").innerText = numberFormat(kyuyo_arr["shokyu_teisei_gaku"]);	//親画面
		
		//* 「昇給嘆願」をセット
		document.getElementById("tangan_gaku").innerText = numberFormat(kyuyo_arr["shokyu_tangan_gaku"]);	//親画面
		
		//* 「職責給－合計」をセット
		document.getElementById("shokuseki_kyu").innerText = numberFormat(kyuyo_arr["shokuseki_kyu"]);

		//* 「年齢給」
		document.getElementById("kihon_kyu").innerText = numberFormat(kyuyo_arr["kihon_kyu"]);

		//* 「皆勤手当」をセット
		document.getElementById("kaikin_teate").innerText = numberFormat(kyuyo_arr["kaikin_teate"]);

		//* 「都市手当」をセット
		document.getElementById("tosi_teate").innerText = numberFormat(kyuyo_arr["tosi_teate"]);

		//* 「奨励手当」をセット
		document.getElementById("shorei_teate").innerText = numberFormat(kyuyo_arr["shorei_teate"]);

		//* 「残業代の基礎となる手当－小計」をセット
		var zankiso_shokei = calcZanKisoShokei(kyuyo_arr);
		document.getElementById("zankiso_shokei").innerText = numberFormat(zankiso_shokei);

		//* 「残業代の基礎となる手当ー合計」をセット
		var zankiso_total = calcZanKisoGokei(kyuyo_arr);
		document.getElementById("zankiso_total").innerText = numberFormat(zankiso_total);

		//* 「月給－社保扶養」チェックボックス設定
		if(shaho_fuyo_flg){
			//「月給－社保扶養」チェックボックスをオン
			document.getElementById("shaho_fuyo").checked = true;
		}
		// document.getElementById("shaho_fuyo_value").value = shaho_fuyo_flg;

		//* 「月給－家族手当」の値をセット
		document.getElementById("kazoku_teate").innerText = numberFormat(kyuyo_arr["kazoku_teate"]);

		//* 「効率残業手当」の値をセット
		document.getElementById("korituzangyo_teate").innerText = numberFormat(kyuyo_arr["korituzangyo_teate"]);
		document.getElementById("korituzangyo_teate_gaitogaku").value = kyuyo_arr["korituzangyo_teate_gaitogaku"];

		//* 「残業代の基礎とならない手当－合計」をセット
		var zan_nonkiso_total = calcNonZanKisoGokei(kyuyo_arr);
		document.getElementById("zan_nonkiso_total").innerText = numberFormat(zan_nonkiso_total);

		//* 「残業単価（割増前の数値を表示するので名称は保留）」をセット
		//** 残業代の基礎となる手当の合計(zankiso_total)
		//*** 残業単価（金額は小数点第一位を切り上げ）
		var zan_tanka = kyuyo_arr["zangyo_tanka"];
		document.getElementById("zan_tanka").innerText = numberFormat(zan_tanka);

		//残業時間・残業手当をセット
		var tujo_zan_jikan = 0;
		var tujo_zan_teate = 0;
		var sinya_zan_jikan = 0;
		var sinya_zan_teate = 0;
		var kyujitu_rodo_jikan = 0;
		var kyujitu_rodo_teate = 0;
		var kyujitusinya_rodo_jikan = 0;
		var kyujitusinya_rodo_teate = 0;
		var zangyo_teate_gokei = 0;


		// //運用管理者（経理課）、システム管理者（情シス）の場合
		// if(USER_SHUBETU == '4' || USER_SHUBETU == '5'){

		// 	//**************************************
		// 	//* 管理者用モーダル「残業単価」をセット
		// 	//**************************************
		// 	document.getElementById("kmd_zangyo_tanka").innerText = numberFormat(zan_tanka);

		// 	//支店別残業時間テーブルから取得したデータが０件の場合
		// 	if(siten_zangyo_jikan_arr == '0'){
		// 		//* 「通常残業時間」をセット
		// 		document.getElementById("tujo_zan_jikan").innerText = 0;
		// 		//* 「通常残業手当」をセット
		// 		document.getElementById("tujo_zan_teate").innerText = 0;
		// 		//* 「深夜残業時間」をセット
		// 		document.getElementById("sinya_zan_jikan").innerText = 0;
		// 		//* 「深夜残業手当」をセット
		// 		document.getElementById("sinya_zan_teate").innerText = 0;
		// 		//* 「休日労働時間」をセット
		// 		document.getElementById("kyujitu_rodo_jikan").innerText = 0;
		// 		//* 「休日労働手当」をセット
		// 		document.getElementById("kyujitu_rodo_teate").innerText = 0;
		// 		//* 「休日深夜労働時間」をセット
		// 		document.getElementById("kyujitusinya_rodo_jikan").innerText = 0;
		// 		//* 「休日深夜労働手当」をセット
		// 		document.getElementById("kyujitusinya_rodo_teate").innerText = 0;
		// 		//* 「残業手当」をセット
		// 		document.getElementById("zangyo_teate_gokei").innerText = 0;

		// 	//支店別残業時間テーブルからデータを取得できた場合
		// 	}else{
		// 		//* 「通常残業時間」をセット
		// 		tujo_zan_jikan = siten_zangyo_jikan_arr["tujo_zan_jikan"] - 0;
		// 		document.getElementById("tujo_zan_jikan").innerText = tujo_zan_jikan;

		// 		//* 「通常残業手当」を算出してセット
		// 		//* （残業単価　×　（１＋0.25）　×　労働時間
		// 		tujo_zan_teate = calcTujoZanTeate(zan_tanka, tujo_zan_jikan);
		// 		document.getElementById("tujo_zan_teate").innerText = numberFormat(tujo_zan_teate);

		// 		//* 「深夜残業時間」をセット
		// 		sinya_zan_jikan = siten_zangyo_jikan_arr["sinya_zan_jikan"] - 0;
		// 		document.getElementById("sinya_zan_jikan").innerText = sinya_zan_jikan;

		// 		//* 「深夜残業手当」をセット
		// 		//* （残業単価　×　（１＋0.25）　×　労働時間
		// 		sinya_zan_teate = calcSinyaZanTeate(zan_tanka, sinya_zan_jikan);
		// 		document.getElementById("sinya_zan_teate").innerText = numberFormat(sinya_zan_teate);

		// 		//* 「休日労働時間」をセット
		// 		kyujitu_rodo_jikan = siten_zangyo_jikan_arr["kyujitu_rodo_jikan"] - 0;
		// 		document.getElementById("kyujitu_rodo_jikan").innerText = kyujitu_rodo_jikan;

		// 		//* 「休日労働手当」をセット
		// 		//* （残業単価　×　（１＋0.35）　×　労働時間
		// 		kyujitu_rodo_teate = calcKyujituRodoTeate(zan_tanka, kyujitu_rodo_jikan);
		// 		document.getElementById("kyujitu_rodo_teate").innerText = numberFormat(kyujitu_rodo_teate);

		// 		//* 「休日深夜労働時間」をセット
		// 		kyujitusinya_rodo_jikan = siten_zangyo_jikan_arr["kyujitusinya_rodo_jikan"] - 0;
		// 		document.getElementById("kyujitusinya_rodo_jikan").innerText = kyujitusinya_rodo_jikan;

		// 		//* 「休日深夜労働手当」をセット
		// 		//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
		// 		kyujitusinya_rodo_teate = calcKyujituSinyaRodoTeate(zan_tanka, kyujitusinya_rodo_jikan);
		// 		document.getElementById("kyujitusinya_rodo_teate").innerText = numberFormat(kyujitusinya_rodo_teate);

		// 		//* 「残業手当」をセット
		// 		zangyo_teate_gokei = tujo_zan_teate + sinya_zan_teate + kyujitu_rodo_teate + kyujitusinya_rodo_teate;
		// 		document.getElementById("zangyo_teate_gokei").innerText = numberFormat(zangyo_teate_gokei);
				
		// 		//*************************************************
		// 		//* 残業時間・手当データを管理者用モーダルにセット
		// 		//*************************************************
		// 		//* 「通常残業時間」をセット
		// 		document.getElementById("kmd_tsujo_zan_jikan").value = tujo_zan_jikan;
		// 		//* 「通常残業手当」を算出してセット
		// 		document.getElementById("kmd_tsujo_zan_teate").innerText = numberFormat(tujo_zan_teate);
		// 		//* 「深夜残業時間」をセット
		// 		document.getElementById("kmd_sinya_zan_jikan").value = sinya_zan_jikan;
		// 		//* 「深夜残業手当」をセット
		// 		document.getElementById("kmd_sinya_zan_teate").innerText = numberFormat(sinya_zan_teate);
		// 		//* 「休日労働時間」をセット
		// 		document.getElementById("kmd_kyujitu_rodo_jikan").value = kyujitu_rodo_jikan;
		// 		//* 「休日労働手当」をセット
		// 		document.getElementById("kmd_kyujitu_rodo_teate").innerText = numberFormat(kyujitu_rodo_teate);
		// 		//* 「休日深夜労働時間」をセット
		// 		document.getElementById("kmd_kyujitusinya_rodo_jikan").value = kyujitusinya_rodo_jikan;
		// 		//* 「休日深夜労働手当」をセット
		// 		document.getElementById("kmd_kyujitusinya_rodo_teate").innerText = numberFormat(kyujitusinya_rodo_teate);
		// 		//* 「残業手当」をセット
		// 		document.getElementById("kmd_zangyo_teate_gokei").innerText = numberFormat(zangyo_teate_gokei);
		// 	}

		// //運用管理者（経理課）、システム管理者（情シス）以外の場合
		// }else{
			//* 「通常残業時間」をセット
			var wk_jikan_nenkei = 0;
			wk_jikan_nenkei = kyuyo_arr["tujo_zan_jikan_nenkei"] - 0;
			tujo_zan_jikan = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("tujo_zan_jikan").innerText = tujo_zan_jikan;

			//* 「通常残業手当」を算出してセット
			//* （残業単価　×　（１＋0.25）　×　労働時間
			tujo_zan_teate = calcTujoZanTeate(zan_tanka, tujo_zan_jikan);
			document.getElementById("tujo_zan_teate").innerText = numberFormat(tujo_zan_teate);

			//* 「深夜残業時間」をセット
			wk_jikan_nenkei = kyuyo_arr["sinya_zan_jikan_nenkei"] - 0;
			sinya_zan_jikan = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("sinya_zan_jikan").innerText = sinya_zan_jikan;

			//* 「深夜残業手当」をセット
			//* （残業単価　×　（１＋0.25）　×　労働時間
			sinya_zan_teate = calcSinyaZanTeate(zan_tanka, sinya_zan_jikan);
			document.getElementById("sinya_zan_teate").innerText = numberFormat(sinya_zan_teate);

			//* 「休日労働時間」をセット
			wk_jikan_nenkei = kyuyo_arr["kyujitu_rodo_jikan_nenkei"] - 0;
			kyujitu_rodo_jikan = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("kyujitu_rodo_jikan").innerText = kyujitu_rodo_jikan;

			//* 「休日労働手当」をセット
			//* （残業単価　×　（１＋0.35）　×　労働時間
			kyujitu_rodo_teate = calcKyujituRodoTeate(zan_tanka, kyujitu_rodo_jikan);
			document.getElementById("kyujitu_rodo_teate").innerText = numberFormat(kyujitu_rodo_teate);

			//* 「休日深夜労働時間」をセット
			wk_jikan_nenkei = kyuyo_arr["kyujitusinya_rodo_jikan_nenkei"] - 0;
			kyujitusinya_rodo_jikan = calcZanJikanTukiHeikin(wk_jikan_nenkei);
			document.getElementById("kyujitusinya_rodo_jikan").innerText = kyujitusinya_rodo_jikan;

			//* 「休日深夜労働手当」をセット
			//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
			kyujitusinya_rodo_teate = calcKyujituSinyaRodoTeate(zan_tanka, kyujitusinya_rodo_jikan);
			document.getElementById("kyujitusinya_rodo_teate").innerText = numberFormat(kyujitusinya_rodo_teate);

			//* 「残業手当」をセット
			zangyo_teate_gokei = tujo_zan_teate + sinya_zan_teate + kyujitu_rodo_teate + kyujitusinya_rodo_teate;
			document.getElementById("zangyo_teate_gokei").innerText = 0;	//初期表示時は「残業手当を適用しない」が選択されるのでここはゼロ
			// document.getElementById("zangyo_teate_gokei").innerText = numberFormat(zangyo_teate_gokei);

			//* 「残業手当」のタイトル部分に、支給される場合の残業手当金額を表示
			document.getElementById("zangyo_teate_disp").innerText = numberFormat(zangyo_teate_gokei);

			//*****************************************
			//* 残業時間・手当データをモーダルにセット
			//*****************************************
			setZangyoModal();
		// }

	//年俸の場合
	}else if(kyuyo_arr["gekkyu_nempo"] == 1){
	
		// 勤務地セレクトボックスを作成し、値を設定
		var kimmuchi_box = document.getElementById("kimmuchi_box");
		var sel_kimmuchi = document.createElement('select');
		sel_kimmuchi.setAttribute('class', "form-control");
		sel_kimmuchi.setAttribute('id', "sel_kimmuchi");
		sel_kimmuchi.setAttribute('name', "sel_kimmuchi");
		kimmuchi_box.appendChild(sel_kimmuchi);
		sel_kimmuchi.innerHTML = '';
		//* 中身をセット
		for(var i=0; i<pd_kimmuchi_rows.length; i++){
			var op = document.createElement('option');
			op.setAttribute('value', pd_kimmuchi_rows[i]["siten_cd"] + ";" + pd_kimmuchi_rows[i]["todofuken_cd"]);
			op.innerHTML = pd_kimmuchi_rows[i]["siten_name"];
			sel_kimmuchi.appendChild(op);
		}
		//* 選択状態にする
		var siten_cd_arr = [];
		if(sel_kimmuchi){
			for(i = 0; i < sel_kimmuchi.options.length; i++){
				siten_cd_arr = sel_kimmuchi.options[i].value.split(";");
				if (siten_cd_arr[0] == kyuyo_arr["kimmuchi_cd"]){
					sel_kimmuchi[i].selected = true;
					break;
				}
			}
		}
		//使用不可状態にする
		sel_kimmuchi.disabled = true;

		//* 「前年度職責給」をセット
		document.getElementById("zennen_shokuseki_kyu").innerText = numberFormat(kyuyo_arr["zennen_shokuseki_kyu"]);

		//**　「前年度職責給」をグレーアウトっぽくする
		document.getElementById("zennen_shokuseki_kyu").setAttribute("class", "font_color_silver");
		
		//* 「年俸－前年度年額」をセット
		document.getElementById("zennen_nempo_nengaku").innerText = numberFormat(kyuyo_arr["zennen_nempo_nengaku"]);
		
		//* 「年俸－評価点による昇給額」をセット
		document.getElementById("nempo_shokyu_gaku").innerText = numberFormat(kyuyo_arr["shokyu_gaku"]);
		
		//* 「年俸－昇給訂正」をセット
		document.getElementById("nempo_teisei_gaku").innerText = numberFormat(kyuyo_arr["shokyu_teisei_gaku"]);
		
		//* 「年俸－昇給嘆願」をセット
		document.getElementById("nempo_tangan_gaku").innerText = numberFormat(kyuyo_arr["shokyu_tangan_gaku"]);

		//* 「年俸－合計」をセット
		document.getElementById("nempo_nengaku").innerText = numberFormat(kyuyo_arr["nempo_nengaku"]);

		//* 「年俸－昇給－年額」をセット（この処理は「１２で割った金額／１４で割った金額」のチェックボックスがセットされてから実施）
		var nempo_shokyu_nengaku = getNempoShokyuNengaku(kyuyo_arr);
		document.getElementById("nempo_shokyu_nengaku").innerText = numberFormat(nempo_shokyu_nengaku);

		//* 「１２で割った金額／１４で割った金額」をセット
		var nempo_getugaku = kyuyo_arr["shikyugaku_a"];
		document.getElementById("nempo_getugaku").innerText = numberFormat(nempo_getugaku);

		//* 「年俸－社保扶養」チェックボックス設定
		if(shaho_fuyo_flg){
			//「年俸－社保扶養」チェックボックスをオン
			document.getElementById("nempo_shaho_fuyo").checked = true;
		}

		//* 「年俸－家族手当」の値をセット
		document.getElementById("nempo_kazoku_teate").innerText = numberFormat(kyuyo_arr["kazoku_teate"]);

		//* 残業関係は無し。「総支給額」のために合計値をゼロにしておく
		var zangyo_teate_gokei = 0;

	}

	//* 「１２で割った金額／１４で割った金額」のチェックボックスのどちらかをオンにする
	if(kyuyo_arr["nempo_12_14"] == 12){
		document.getElementById("cb_nempo_divide_14").checked = false;
		document.getElementById("cb_nempo_divide_12").checked = true;
	}else if(kyuyo_arr["nempo_12_14"] == 14){
		document.getElementById("cb_nempo_divide_12").checked = false;
		document.getElementById("cb_nempo_divide_14").checked = true;
	}

	//* 「社保扶養」の値をセット
	document.getElementById("shaho_fuyo_value").value = shaho_fuyo_flg;

	//* 「支給額Ａ」をセット
	document.getElementById("shikyugaku_a").innerText =  numberFormat(kyuyo_arr["shikyugaku_a"]);

	//* 「前年度との差」をセット
	document.getElementById("sagaku_teate").innerText =  numberFormat(kyuyo_arr["sagaku_teate"]);;

	//* 「総支給額（支給額Ａ　＋　残業手当）」をセット
	//*　※初期表示時は「残業手当」は加算しないので（「残業手当」は”適用しない”なので）、単純に「支給額Ａ」をセットする。
	 document.getElementById("a_zan_total").innerText = numberFormat(kyuyo_arr["shikyugaku_a"]);
	// var a_zan_total = calcSosikyugaku(kyuyo_arr);
	// document.getElementById("a_zan_total").innerText = numberFormat(a_zan_total);

	//* 「欠勤控除」をセット
	document.getElementById("kekkin_kojo").innerText = numberFormat(kyuyo_arr["kekkin_kojo"]);

	//* 「遅刻相対控除」をセット
	document.getElementById("chikoku_sotai_kojo").innerText = numberFormat(kyuyo_arr["chikoku_sotai_kojo"]);

	//* 「備考」をセット
	document.getElementById("biko").innerText = kyuyo_arr["biko"];


	//　データ退避用変数にセットする。”月給””年俸”関係なく
	taihi_shaho_fuyo_flg = shaho_fuyo_flg;	//社保扶養フラグ
	taihi_shokuseki_kyu = kyuyo_arr["shokuseki_kyu"];	//職責給－合計
	taihi_kihon_kyu = kyuyo_arr["kihon_kyu"];	//基本給（年齢給）
	taihi_kaikin_teate = kyuyo_arr["kaikin_teate"];	//皆勤手当
	taihi_tosi_teate = kyuyo_arr["tosi_teate"];	//都市手当
	taihi_shorei_teate = kyuyo_arr["shorei_teate"];	//奨励手当
	taihi_kazoku_teate = kyuyo_arr["kazoku_teate"];	//家族手当
	taihi_korituzangyo_teate = kyuyo_arr["korituzangyo_teate"];	//効率残業手当（支給額）
	taihi_korituzangyo_teate_gaitogaku = kyuyo_arr["korituzangyo_teate_gaitogaku"];	//効率残業手当（該当額）
	taihi_nempo_nengaku = kyuyo_arr["nempo_nengaku"];	//年俸－合計
	taihi_zangyo_tanka = kyuyo_arr["zangyo_tanka"];	//残業単価

}

//****************************************************************************
//　★初期表示用★
//　「年俸－昇給－年額」を取得する
//　【引数】
//　　無し
//*****************************************************************************
function getNempoShokyuNengaku(kyuyo_arr){

	var nengaku = 0;
	var shokyu_gaku = kyuyo_arr["shokyu_gaku"];
	var shokyu_teisei_gaku = kyuyo_arr["shokyu_teisei_gaku"];
	var shokyu_tangan_gaku = kyuyo_arr["shokyu_tangan_gaku"];

	var divide = 12;	//１２倍

	//「昇給訂正額」が入力されている場合は「評価点による昇給額」ではなくそちらを計上する
	//12倍して年額にして加算する
	if(0 < shokyu_teisei_gaku){
		shokyu_teisei_gaku = shokyu_teisei_gaku * divide;
		nengaku = nengaku + shokyu_teisei_gaku;
		return nengaku;
	}
	
	//「昇給嘆願額」が入力されている場合は「評価点による昇給額」でも「昇給訂正額」でもなくそちらを計上する
	//12倍して年額にして加算する
	if(0 < shokyu_tangan_gaku){
		shokyu_tangan_gaku = shokyu_tangan_gaku * divide;
		nengaku = nengaku + shokyu_tangan_gaku;
		return nengaku;
	}
	
	//「評価点による昇給額」がゼロより大きい場合は、12倍して前年度の職責給に加算
	if(0 < shokyu_gaku){
		shokyu_gaku = shokyu_gaku * divide;
		nengaku = nengaku + shokyu_gaku;
	}
	
	return nengaku;
	
}

//****************************************************************************
//　★初期表示用★
//　残業時間入力モーダル（一般ユーザー用）にデータをセットする
//　【引数】
//　　無し
//*****************************************************************************
function setZangyoModal(){

	//* 残業単価（金額は小数点第一位を切り上げ）
	var zan_tanka = kyuyo_arr["zangyo_tanka"];

	//*****************************************
	//* 残業時間・手当データをモーダルにセット
	//*****************************************
	//** 残業単価＿年合計
	document.getElementById("md_zangyo_tanka_year").innerText = numberFormat(zan_tanka);

	//** 通常残業時間＿年合計
	document.getElementById("md_tujo_zan_jikan_year").value = kyuyo_arr["tujo_zan_jikan_nenkei"];

	//** 通常残業手当＿年合計
	//* （残業単価　×　（１＋0.25）　×　労働時間
	var wk_jikan_nenkei = 0;
	wk_jikan_nenkei = kyuyo_arr["tujo_zan_jikan_nenkei"] - 0;
	var tujo_zan_teate_nenkei = calcTujoZanTeate(zan_tanka, wk_jikan_nenkei);
	document.getElementById("md_tujo_zan_teate_year").innerText = numberFormat(tujo_zan_teate_nenkei);

	//** 深夜残業時間＿年合計
	document.getElementById("md_sinya_zan_jikan_year").value = kyuyo_arr["sinya_zan_jikan_nenkei"];

	//** 深夜残業手当＿年合計
	//* （残業単価　×　（１＋0.25）　×　労働時間
	var wk_jikan_nenkei = kyuyo_arr["sinya_zan_jikan_nenkei"] - 0;
	var sinya_zan_teate_nenkei = calcSinyaZanTeate(zan_tanka, wk_jikan_nenkei);
	document.getElementById("md_sinya_zan_teate_year").innerText = numberFormat(sinya_zan_teate_nenkei);

	//** 休日労働時間＿年合計
	document.getElementById("md_kyujitu_rodo_jikan_year").value = kyuyo_arr["kyujitu_rodo_jikan_nenkei"];

	//** 休日労働手当＿年合計
	//* （残業単価　×　（１＋0.35）　×　労働時間
	var wk_jikan_nenkei = kyuyo_arr["kyujitu_rodo_jikan_nenkei"] - 0;
	var kyujitu_rodo_teate_nenkei = calcKyujituRodoTeate(zan_tanka, wk_jikan_nenkei);
	document.getElementById("md_kyujitu_rodo_teate_year").innerText = numberFormat(kyujitu_rodo_teate_nenkei);

	//** 休日深夜労働時間＿年合計
	document.getElementById("md_kyujitusinya_rodo_jikan_year").value = kyuyo_arr["kyujitusinya_rodo_jikan_nenkei"];

	//** 休日深夜労働手当＿年合計
	//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
	var wk_jikan_nenkei = kyuyo_arr["kyujitusinya_rodo_jikan_nenkei"] - 0;
	var kyujitusinya_rodo_teate_nenkei = calcKyujituSinyaRodoTeate(zan_tanka, wk_jikan_nenkei);
	document.getElementById("md_kyujitusinya_rodo_teate_year").innerText = numberFormat(kyujitusinya_rodo_teate_nenkei);

	//** 残業手当＿年合計
	var zangyo_teate_gokei_year = tujo_zan_teate_nenkei	+ sinya_zan_teate_nenkei + kyujitu_rodo_teate_nenkei + kyujitusinya_rodo_teate_nenkei;
	document.getElementById("md_zangyo_teate_gokei_year").innerText = numberFormat(zangyo_teate_gokei_year);

	//** 各月の行に値をセット
	var tujo_zan_jikan_tuki_arr = kyuyo_arr["tujo_zan_jikan_tuki"].split(";");
	var sinya_zan_jikan_tuki_arr = kyuyo_arr["sinya_zan_jikan_tuki"].split(";");
	var kyujitu_rodo_jikan_tuki_arr = kyuyo_arr["kyujitu_rodo_jikan_tuki"].split(";");
	var kyujitusinya_rodo_jikan_tuki_arr = kyuyo_arr["kyujitusinya_rodo_jikan_tuki"].split(";");

	var tujo_zan_jikan_tuki = 0;
	var tujo_zan_teate_tuki = 0;
	var sinya_zan_jikan_tuki = 0;
	var sinya_zan_teate_tuki = 0;
	var kyujitu_rodo_jikan_tuki = 0;
	var kyujitu_rodo_teate_tuki = 0;
	var kyujitusinya_rodo_jikan_tuki = 0;
	var kyujitusinya_rodo_teate_tuki = 0;

	var zangyo_teate_gokei_month = 0;

	var month = 4;
	for(var i=0; i<tujo_zan_jikan_tuki_arr.length; i++){

		tujo_zan_jikan_tuki = 0;
		tujo_zan_teate_tuki = 0;
		sinya_zan_jikan_tuki = 0;
		sinya_zan_teate_tuki = 0;
		kyujitu_rodo_jikan_tuki = 0;
		kyujitu_rodo_teate_tuki = 0;
		kyujitusinya_rodo_jikan_tuki = 0;
		kyujitusinya_rodo_teate_tuki = 0;

		if(tujo_zan_jikan_tuki_arr[i]){
			tujo_zan_jikan_tuki = tujo_zan_jikan_tuki_arr[i];
			tujo_zan_jikan_tuki = tujo_zan_jikan_tuki - 0;
		}
		//* 「通常残業手当」
		//* （残業単価　×　（１＋0.25）　×　労働時間
		tujo_zan_teate_tuki = calcTujoZanTeate(zan_tanka, tujo_zan_jikan_tuki);

		if(sinya_zan_jikan_tuki_arr[i]){
			sinya_zan_jikan_tuki = sinya_zan_jikan_tuki_arr[i];
			sinya_zan_jikan_tuki = sinya_zan_jikan_tuki - 0;
		}

		//* 「深夜残業手当」
		//* （残業単価　×　（１＋0.25）　×　労働時間
		sinya_zan_teate_tuki = calcSinyaZanTeate(zan_tanka, sinya_zan_jikan_tuki);

		if(kyujitu_rodo_jikan_tuki_arr[i]){
			kyujitu_rodo_jikan_tuki = kyujitu_rodo_jikan_tuki_arr[i];
			kyujitu_rodo_jikan_tuki = kyujitu_rodo_jikan_tuki - 0;
		}

		//* 「休日労働手当」
		//* （残業単価　×　（１＋0.35）　×　労働時間
		kyujitu_rodo_teate_tuki = calcKyujituRodoTeate(zan_tanka, kyujitu_rodo_jikan_tuki);

		if(kyujitusinya_rodo_jikan_tuki_arr[i]){
			kyujitusinya_rodo_jikan_tuki = kyujitusinya_rodo_jikan_tuki_arr[i];
			kyujitusinya_rodo_jikan_tuki = kyujitusinya_rodo_jikan_tuki - 0;
		}

		//* 「休日深夜労働手当」
		//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
		kyujitusinya_rodo_teate_tuki = calcKyujituSinyaRodoTeate(zan_tanka, kyujitusinya_rodo_jikan_tuki);

		
		//*** 残業単価＿各月
		document.getElementById("md_zangyo_tanka_" + month).innerText = numberFormat(zan_tanka);
		//*** 通常残業時間＿各月
		document.getElementById("md_tujo_zan_jikan_" + month).value = tujo_zan_jikan_tuki;
		//*** 通常残業手当＿各月
		document.getElementById("md_tujo_zan_teate_" + month).innerText = numberFormat(tujo_zan_teate_tuki);
		//*** 深夜残業時間＿各月
		document.getElementById("md_sinya_zan_jikan_" + month).value = sinya_zan_jikan_tuki;
		//*** 深夜残業手当＿各月
		document.getElementById("md_sinya_zan_teate_" + month).innerText = numberFormat(sinya_zan_teate_tuki);
		//*** 休日労働時間＿各月
		document.getElementById("md_kyujitu_rodo_jikan_" + month).value = kyujitu_rodo_jikan_tuki;
		//*** 休日労働手当＿各月
		document.getElementById("md_kyujitu_rodo_teate_" + month).innerText = numberFormat(kyujitu_rodo_teate_tuki);
		//*** 休日深夜労働時間＿各月
		document.getElementById("md_kyujitusinya_rodo_jikan_" + month).value = kyujitusinya_rodo_jikan_tuki;
		//*** 休日深夜労働手当＿各月
		document.getElementById("md_kyujitusinya_rodo_teate_" + month).innerText = numberFormat(kyujitusinya_rodo_teate_tuki);
		//*** 残業手当＿各月
		zangyo_teate_gokei_month = tujo_zan_teate_tuki + sinya_zan_teate_tuki + kyujitu_rodo_teate_tuki + kyujitusinya_rodo_teate_tuki;
		document.getElementById("md_zangyo_teate_gokei_" + month).innerText = numberFormat(zangyo_teate_gokei_month);
		
		if(12 <= month){
			month = 1;
		}else{
			month++;
		}
		
	}
}

//****************************************************************************
//　残業時間入力モーダル（一般ユーザー用）の入力値、表示値をクリアする
//　【引数】
//　　無し
//*****************************************************************************
function unsetZangyoModal(){

	//** 残業単価＿年合計
	document.getElementById("md_zangyo_tanka_year").innerText = "";

	//** 通常残業時間＿年合計
	document.getElementById("md_tujo_zan_jikan_year").value = "";

	//** 通常残業手当＿年合計
	document.getElementById("md_tujo_zan_teate_year").innerText = "";

	//** 深夜残業時間＿年合計
	document.getElementById("md_sinya_zan_jikan_year").value = "";

	//** 深夜残業手当＿年合計
	document.getElementById("md_sinya_zan_teate_year").innerText = "";

	//** 休日労働時間＿年合計
	document.getElementById("md_kyujitu_rodo_jikan_year").value = "";

	//** 休日労働手当＿年合計
	document.getElementById("md_kyujitu_rodo_teate_year").innerText = "";

	//** 休日深夜労働時間＿年合計
	document.getElementById("md_kyujitusinya_rodo_jikan_year").value = "";

	//** 休日深夜労働手当＿年合計
	document.getElementById("md_kyujitusinya_rodo_teate_year").innerText = "";

	//** 残業手当＿年合計
	document.getElementById("md_zangyo_teate_gokei_year").innerText = "";

	var month = 4;
	for(var i=0; i<12; i++){
//	for(var i=0; i<tujo_zan_jikan_tuki_arr.length; i++){
		
		//*** 残業単価＿各月
		document.getElementById("md_zangyo_tanka_" + month).innerText = "";
		//*** 通常残業時間＿各月
		document.getElementById("md_tujo_zan_jikan_" + month).value = "";
		//*** 通常残業手当＿各月
		document.getElementById("md_tujo_zan_teate_" + month).innerText = "";
		//*** 深夜残業時間＿各月
		document.getElementById("md_sinya_zan_jikan_" + month).value = "";
		//*** 深夜残業手当＿各月
		document.getElementById("md_sinya_zan_teate_" + month).innerText = "";
		//*** 休日労働時間＿各月
		document.getElementById("md_kyujitu_rodo_jikan_" + month).value = "";
		//*** 休日労働手当＿各月
		document.getElementById("md_kyujitu_rodo_teate_" + month).innerText = "";
		//*** 休日深夜労働時間＿各月
		document.getElementById("md_kyujitusinya_rodo_jikan_" + month).value = "";
		//*** 休日深夜労働手当＿各月
		document.getElementById("md_kyujitusinya_rodo_teate_" + month).innerText = "";
		//*** 残業手当＿各月
		document.getElementById("md_zangyo_teate_gokei_" + month).innerText = "";
		
		if(12 <= month){
			month = 1;
		}else{
			month++;
		}
		
	}
}

//****************************************************************************
//　★初期表示用★
//　「残業代の基礎となる手当－小計」を算出する
//　【引数】
//　　１．従業員給与データ（getSalary()で取得したデータ配列）
//*****************************************************************************
function calcZanKisoShokei(kyuyo_arr){
//function calcZanKisoShokei(kyuyo_arr_minus1, kyuyo_arr){

	var wk_shokuseki_kyu = kyuyo_arr["shokuseki_kyu"] - 0;	//職責給
	var wk_kihon_kyu = kyuyo_arr["kihon_kyu"] - 0;			//年齢給
	var wk_kotei_kihon_kyu = kyuyo_arr["kotei_kihon_kyu"] - 0;			//固定基本給
	var wk_kaikin_teate = kyuyo_arr["kaikin_teate"] - 0;	//皆勤手当
	var wk_tosi_teate = kyuyo_arr["tosi_teate"] - 0;		//都市手当
	var wk_shorei_teate = kyuyo_arr["shorei_teate"] - 0;	//奨励手当
	var wk_chosei_teate = kyuyo_arr["chosei_teate"] - 0;	//調整手当
	var zankiso_shokei = wk_shokuseki_kyu + wk_kihon_kyu + wk_kotei_kihon_kyu + wk_kaikin_teate + wk_tosi_teate + wk_shorei_teate + wk_chosei_teate;
	return zankiso_shokei;

}

//****************************************************************************
//　「単身赴任」チェックボックスをクリックしたときの処理
//　hiddenの値を変更する。
//　【引数】
//　　無し
//*****************************************************************************
function chgTansinfuninVal(){
	

	var tansinfunin = document.getElementById("tansinfunin");
	var tansinfunin_value = document.getElementById("tansinfunin_value");
	var tansinfunin_teate = document.getElementById("tansinfunin_teate");
	
	//チェックボックスがオン状態の場合
	if(tansinfunin.checked){
		//「単身赴任フラグ」値を１（単身赴任の状態）にする
		tansinfunin_value.value = 1;

		//「単身赴任手当」に値が入る可能性があるので、再計算処理を実行
		reCalc();

	
	//チェックボックスがオフ状態の場合
	}else{
		if(!confirm("「単身赴任ではない」状態にします。\n単身赴任手当をゼロにしますが、よろしいですか？")){
			tansinfunin.checked = true;
			return;
		}
		//「単身赴任フラグ」値を０（単身赴任ではない）にする
		tansinfunin_value.value = 0;
		//「単身赴任手当」の金額をゼロにする
		tansinfunin_teate.innerText = 0;
		//「単身赴任手当」が変わったことによる再計算
		reCalc();
	}

	//　データ退避用変数にセットする。
	taihi_tansinfunin_flg = tansinfunin_value.value;	//単身赴任フラグ
	taihi_tansinfunin_teate = off_format_val(tansinfunin_teate.innerText);	//単身赴任手当

	return;
}

//****************************************************************************
//　「月給－社保扶養」チェックボックスをクリックしたときの処理
//　hiddenの値を変更する。
//　【引数】
//　　無し
//*****************************************************************************
function chgShahoFuyoVal(){
	
	var shaho_fuyo = document.getElementById("shaho_fuyo");					//「社保扶養」チェックボックス
	var shaho_fuyo_value = document.getElementById("shaho_fuyo_value");		//「社保扶養フラグ」の値を保持するhidden（「月給－者保不要」と「年俸－者保不要」共通で使用する）
	var kazoku_teate = document.getElementById("kazoku_teate");				//「家族手当」
	
	//チェックボックスがオン状態の場合
	if(shaho_fuyo.checked){
		//「社保扶養フラグ」値を１（扶養ありの状態）にする
		shaho_fuyo_value.value = 1;

		//「家族手当」に値が入る可能性があるので、再計算処理を実行
		reCalc();

	
	//チェックボックスがオフ状態の場合
	}else{
		if(!confirm("「社保扶養なし」の状態にします。\n家族手当をゼロにしますが、よろしいですか？")){
			shaho_fuyo.checked = true;
			return;
		}
		//「社保扶養フラグ」値を０（社保扶養なし）にする
		shaho_fuyo_value.value = 0;
		//「家族手当」の金額をゼロにする
		kazoku_teate.innerText = 0;
		//「家族手当」が変わったことによる再計算
		reCalc();
	}

	//　データ退避用変数にセットする。
	taihi_shaho_fuyo_flg = shaho_fuyo_value.value;	//社保扶養フラグ
	taihi_kazoku_teate = off_format_val(kazoku_teate.innerText);	//家族手当

	return;
}

//****************************************************************************
//　「年俸－社保扶養」チェックボックスをクリックしたときの処理
//　hiddenの値を変更する。
//　【引数】
//　　無し
//*****************************************************************************
function chgNempoShahoFuyoVal(){
	
	var shaho_fuyo = document.getElementById("nempo_shaho_fuyo");			//「年俸－社保扶養」チェックボックス
	var shaho_fuyo_value = document.getElementById("shaho_fuyo_value");		//「社保扶養フラグ」の値を保持するhidden（「月給－者保不要」と「年俸－者保不要」共通で使用する）
	var kazoku_teate = document.getElementById("nempo_kazoku_teate");		//「年俸－家族手当」
	
	//チェックボックスがオン状態の場合
	if(shaho_fuyo.checked){
		//「社保扶養フラグ」値を１（扶養ありの状態）にする
		shaho_fuyo_value.value = 1;

		//「家族手当」に値が入る可能性があるので、再計算処理を実行
		reCalc();

	
	//チェックボックスがオフ状態の場合
	}else{
		if(!confirm("「社保扶養なし」の状態にします。\n家族手当をゼロにしますが、よろしいですか？")){
			shaho_fuyo.checked = true;
			return;
		}
		//「社保扶養フラグ」値を０（社保扶養なし）にする
		shaho_fuyo_value.value = 0;
		//「家族手当」の金額をゼロにする
		kazoku_teate.innerText = 0;
		//「家族手当」が変わったことによる再計算
		reCalc();
	}

	//　データ退避用変数にセットする。
	taihi_shaho_fuyo_flg = shaho_fuyo_value.value;	//社保扶養フラグ
	taihi_kazoku_teate = off_format_val(kazoku_teate.innerText);	//家族手当

	return;
}

//****************************************************************************
//　★初期表示用★
//　「残業代の基礎となる手当－合計」を算出する
//　【引数】
//　　１．引数２の前年度分の従業員給与データ（getSalary()で取得したデータ配列）
//　　２．従業員給与データ（getSalary()で取得したデータ配列）
//*****************************************************************************
function calcZanKisoGokei(kyuyo_arr){
//function calcZanKisoGokei(kyuyo_arr_minus1, kyuyo_arr){
	//「残業代の基礎となる手当－小計」を取得
	var wk_zankiso_shokei = calcZanKisoShokei(kyuyo_arr);
	
	var wk_tenkin_jutaku_teate = kyuyo_arr["tenkin_jutaku_teate"] - 0;
	var wk_tansinfunin_teate = kyuyo_arr["tansinfunin_teate"] - 0;
	var zankiso_total = wk_zankiso_shokei + wk_tenkin_jutaku_teate + wk_tansinfunin_teate;
	return zankiso_total;
}

//****************************************************************************
//　★初期表示用★
//　「残業代の基礎とならない手当－合計」を算出する
//　【引数】
//　　１．従業員給与データ（getSalary()で取得したデータ配列）
//*****************************************************************************
function calcNonZanKisoGokei(kyuyo_arr){
	var wk_kazoku_teate = kyuyo_arr["kazoku_teate"] - 0;
	var wk_korituzangyo_teate = kyuyo_arr["korituzangyo_teate"] - 0;
	var zan_nonkiso_total = wk_kazoku_teate + wk_korituzangyo_teate;
	return zan_nonkiso_total;
}

//****************************************************************************
//　★初期表示用★
//　「総支給額」を算出する
//　【引数】
//　　１．従業員給与データ（getSalary()で取得したデータ配列）
//*****************************************************************************
function calcSosikyugaku(kyuyo_arr){

	//年俸の場合はゼロ
	var wk_zangyo_teate_gokei = 0;

	//月給の場合
	if(kyuyo_arr["gekkyu_nempo"] == 0){

		//各残業時間の月平均値を算出
		var wk_tujo_zan_jikan = calcZanJikanTukiHeikin(kyuyo_arr["tujo_zan_jikan_nenkei"]);	//通常残業時間（年合計）
		var wk_sinya_zan_jikan = calcZanJikanTukiHeikin(kyuyo_arr["sinya_zan_jikan_nenkei"]);	//深夜残業時間（年合計）
		var wk_kyujitu_rodo_jikan = calcZanJikanTukiHeikin(kyuyo_arr["kyujitu_rodo_jikan_nenkei"]);	//休日労働時間（年合計）
		var wk_kyujitusinya_rodo_jikan = calcZanJikanTukiHeikin(kyuyo_arr["kyujitusinya_rodo_jikan_nenkei"]);	//休日深夜労働時間（年合計）
		//各残業手当を算出
		var wk_tujo_zan_teate = calcTujoZanTeate(kyuyo_arr["zangyo_tanka"], wk_tujo_zan_jikan);				//通常残業手当
		var wk_sinya_zan_teate = calcSinyaZanTeate(kyuyo_arr["zangyo_tanka"], wk_sinya_zan_jikan);			//深夜残業手当
		var wk_kyujitu_rodo_teate = calcKyujituRodoTeate(kyuyo_arr["zangyo_tanka"], wk_kyujitu_rodo_jikan);			//休日労働手当
		var wk_kyujitusinya_rodo_teate = calcKyujituSinyaRodoTeate(kyuyo_arr["zangyo_tanka"], wk_kyujitusinya_rodo_jikan);	//休日深夜労働手当
		//各残業手当を足す
		wk_zangyo_teate_gokei = wk_tujo_zan_teate + wk_sinya_zan_teate + wk_kyujitu_rodo_teate + wk_kyujitusinya_rodo_teate;

	}

	var wk_shikyugaku_a = kyuyo_arr["shikyugaku_a"] - 0;
	var a_zan_total = wk_zangyo_teate_gokei + wk_shikyugaku_a;
	return a_zan_total;
}

//****************************************************************************
//　★初期表示用★
//　残業時間（年計）から、残業時間の月平均値を算出する。
//　【引数】
//　　１．従業員給与データ（getSalary()で取得したデータ配列）の
//　　　　「**残業時間（年計）」や
//　　　　「**労働時間（年計）」
//*****************************************************************************
function calcZanJikanTukiHeikin(zan_jikan_nenkei){
	var zan_jikan = zan_jikan_nenkei - 0;
	zan_jikan = zan_jikan / 12;
	zan_jikan = zan_jikan * 100;
	zan_jikan = Math.floor(zan_jikan);
	zan_jikan = zan_jikan / 100;
	return zan_jikan;
}

//****************************************************************************
//　★初期表示用★
//　「通常残業手当」を算出する。
//　【引数】
//　　１．残業単価
//　　　従業員給与データ（getSalary()で取得したデータ配列）の「残業単価」等。
//　　２．残業時間
//　　　従業員給与データ（getSalary()で取得したデータ配列）の「通常残業時間（年合計）」や
//　　　それを１２で割ったもの等。
//*****************************************************************************
function calcTujoZanTeate(zan_tanka, zan_jikan){
	//* （残業単価　×　（１＋0.25）　×　労働時間
	var tujo_zan_teate = zan_tanka * (1 + 0.25) * zan_jikan;
	tujo_zan_teate = Math.ceil(tujo_zan_teate);
	return tujo_zan_teate;
}

//****************************************************************************
//　★初期表示用★
//　「深夜残業手当」を算出する。
//　【引数】
//　　１．残業単価
//　　　従業員給与データ（getSalary()で取得したデータ配列）の「残業単価」等。
//　　２．残業時間
//　　　従業員給与データ（getSalary()で取得したデータ配列）の「深夜残業時間（年合計）」や
//　　　それを１２で割ったもの等。
//*****************************************************************************
function calcSinyaZanTeate(zan_tanka, zan_jikan){
	//* （残業単価　×　（１＋0.25）　×　労働時間
	var tujo_zan_teate = zan_tanka * (1 + 0.25) * zan_jikan;
	tujo_zan_teate = Math.ceil(tujo_zan_teate);
	return tujo_zan_teate;
}

//****************************************************************************
//　★初期表示用★
//　「休日労働手当」を算出する。
//　【引数】
//　　１．残業単価
//　　　従業員給与データ（getSalary()で取得したデータ配列）の「残業単価」等。
//　　２．残業時間
//　　　従業員給与データ（getSalary()で取得したデータ配列）の「休日労働時間（年合計）」や
//　　　それを１２で割ったもの等。
//*****************************************************************************
function calcKyujituRodoTeate(zan_tanka, zan_jikan){
	//* （残業単価　×　（１＋0.35）　×　労働時間
	var tujo_zan_teate = zan_tanka * (1 + 0.35) * zan_jikan;
	tujo_zan_teate = Math.ceil(tujo_zan_teate);
	return tujo_zan_teate;
}

//****************************************************************************
//　★初期表示用★
//　「休日深夜労働手当」を算出する。
//　【引数】
//　　　従業員給与データ（getSalary()で取得したデータ配列）の「残業単価」等。
//　　２．残業時間
//　　　従業員給与データ（getSalary()で取得したデータ配列）の「休日深夜労働時間（年合計）」や
//　　　それを１２で割ったもの等。
//*****************************************************************************
function calcKyujituSinyaRodoTeate(zan_tanka, zan_jikan){
	//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
	var tujo_zan_teate = zan_tanka * (1 + 0.35 + 0.25) * zan_jikan;
	tujo_zan_teate = Math.ceil(tujo_zan_teate);
	return tujo_zan_teate;
}

//****************************************************************************
//　プルダウンで選択した従業員の情報に画面表示を切り替える（リロード）
//　【引数】
//　　無し
//*****************************************************************************
function chgStaff(){

	//プルダウンで選択されている年度を取得
	var obj = document.getElementById("sel_jugyoin");
	var wk_sel_jugyoin_value = obj.options[obj.selectedIndex].value;	// プルダウンで選択した社員
	var sel_jugyoin_value = wk_sel_jugyoin_value.split(";");			//セミコロンの左が従業員番号、右が編集可・不可フラグ
	var staff_code = document.getElementById("staff_code");
	staff_code.value = sel_jugyoin_value[0];	//従業員番号をhiddenへ入れる

	transition('kyuyo_input.php');
}

//****************************************************************************
//　「職責給合計」の金額が「職責」セレクトボックスで選択した
//　職責で支給される職責給の範囲内かどうかチェック。
//　範囲内の場合は処理終了。
//　範囲外の場合はその旨のメッセージを表示する。
//　※”年俸”の場合はエラーにしないために、退避用の変数（画面と同じ値を保持している
//　　jsの変数で、"年俸"に切り替えてもＤＢには登録するために、値は保持したままとなる）
//　　ではなく、画面に表示されている値を使用する。
//　【引数】
//　　無し
//*****************************************************************************
function chkShokusekikyu(){

	//「月給・年俸」が"年俸"の場合は処理を抜ける
	if(gekkyu_nempo_val == 1){
		return false;
	}

	//画面上の「職責給合計」を取得
	var shokuseki_kyu = off_format_val(document.getElementById("shokuseki_kyu").innerText);
	shokuseki_kyu = shokuseki_kyu - 0;

	//プルダウンで選択されている職責を取得
	var obj = document.getElementById("sel_duties");
	var sel_duties_value = obj.options[obj.selectedIndex].value;	// プルダウンで選択した職責の値
	
	//職責マスタデータをループ
	for(var i=0; i<pd_shokuseki_rows.length; i++){
		//選択した職責があった場合（必ずある）
		if(pd_shokuseki_rows[i]["duties_cd"] == sel_duties_value){
			//画面上の「前年度職責給」の金額が、その職責の職責給の支給範囲であるか確認
			if((pd_shokuseki_rows[i]["shokusekikyu_kagen"] <= shokuseki_kyu)&&(shokuseki_kyu <= pd_shokuseki_rows[i]["shokusekikyu_jogen"])){
				document.getElementById("shkskkyu_err_msg").style.display = "none";
				err_flg_shokuseki_kyu = 0;
				return false;
			}else{
				//「範囲外ですよ」のメッセージを表示する
				var duties_name = pd_shokuseki_rows[i]["duties_name"];
				var shokusekikyu_kagen = numberFormat(pd_shokuseki_rows[i]["shokusekikyu_kagen"]);
				var shokusekikyu_jogen = numberFormat(pd_shokuseki_rows[i]["shokusekikyu_jogen"]);
				//「職責給－合計」のタイトル部分にコメント挿入
				document.getElementById("shkskkyu_err_msg").innerText = "※" + duties_name + "\nの支給範囲は\n" + shokusekikyu_kagen + "～" + shokusekikyu_jogen + " です。";
				document.getElementById("shkskkyu_err_msg").style.display = "inline";
				//エラーフラグをオン
				err_flg_shokuseki_kyu = 1;
			}
		}
	}
}

//****************************************************************************
//*****　手入力する項目の値を変更した後の処理
//*****　手入力する項目の値を退避用変数に退避する処理と
//*****　再計算処理をコールする
//****************************************************************************
function updateTenyuyokuKomoku(){
	tenyuryokuTaihi();
	reCalc();
}

//****************************************************************************
//*****　手入力する項目の値を退避用変数に退避させる
//*****　以下の項目に対して行う。
//*****　　・固定基本給
//*****　　・調整手当
//*****　　・転勤住宅手当
//*****　　・うち固定残業代
//*****　　・うち営業手当
//*****************************************************************************
function tenyuryokuTaihi(){

	//"月給"の場合
	if(gekkyu_nempo_val == 0){
		taihi_kotei_kihon_kyu = off_format_val(document.getElementById("kotei_kihon_kyu").value);	//固定基本給
		taihi_kotei_kihon_kyu = taihi_kotei_kihon_kyu - 0;
		taihi_chosei_teate = off_format_val(document.getElementById("chosei_teate").value);	//調整手当
		taihi_chosei_teate = taihi_chosei_teate - 0;
		taihi_tenkin_jutaku_teate = off_format_val(document.getElementById("tenkin_jutaku_teate").value);	//転勤住宅手当
		taihi_tenkin_jutaku_teate = taihi_tenkin_jutaku_teate - 0;

	//"年俸"の場合
	}else if(gekkyu_nempo_val == 1){
		taihi_nempo_uchi_koteizang_dai = off_format_val(document.getElementById("nempo_uchi_koteizang_dai").value);	//うち固定残業代
		taihi_nempo_uchi_koteizang_dai = taihi_nempo_uchi_koteizang_dai - 0;
		taihi_nempo_uchi_eigyo_teate = off_format_val(document.getElementById("nempo_uchi_eigyo_teate").value);	//うち営業手当
		taihi_nempo_uchi_eigyo_teate = taihi_nempo_uchi_eigyo_teate - 0;
	}

}

//****************************************************************************
//　★画面上のいずれかの項目の値を手動で変更した場合の処理★
//　画面の再計算が必要な項目を、画面の項目を使用して再計算する
//　【引数】
//　　無し
//*****************************************************************************
function reCalc(){

	//月給の場合
	if(gekkyu_nempo_val == 0){
		reCalcShokusekiKyu();	//「職責給－合計」
		reCalcKaikinTeate();	//「皆勤手当」
		reCalcTosiTeate()		//「都市手当」
		reCalcShoreiTeate();	//「奨励手当」
		reCalcZankisoShokei()	//「残業代の基礎となる手当－小計」

		//「単身赴任手当」
		//チェックボックスがオン状態の場合のみ取得
		var tansinfunin = document.getElementById("tansinfunin");
		if(tansinfunin.checked){
			reCalcTansinfuninTeate()	//「単身赴任手当」
		}

		//「月給－家族手当」
		//「月給－社保扶養」チェックボックスがオン状態の場合のみ取得
		var shaho_fuyo = document.getElementById("shaho_fuyo");
		if(shaho_fuyo.checked){
			reCalcKazokuTeate()	//「月給－家族手当」
		}


		reCalcZankisoTotal()	//「残業代の基礎となる手当－合計」
		
		//残業時間入力モーダルの再計算
		// //システム利用者が運用管理者（経理課）、又はシステム管理者（情シス）の場合
		// if(USER_SHUBETU == '4' || USER_SHUBETU == '5'){
		// 	kanriZangyoModalRecalc();
			
		// //システム利用者が一般ユーザーの場合
		// }else if(USER_SHUBETU == '1'){
			var zangyo_nen_tuki1 = document.getElementById("zangyo_nen_tuki1");
			var zangyo_nen_tuki2 = document.getElementById("zangyo_nen_tuki2");
			//ラジオボタンは「年合計」がオンの場合
			if(zangyo_nen_tuki1.checked){
				zangyoModalNenGokeiRecalc();
			//ラジオボタンは「各月合計」がオンの場合
			}else if(zangyo_nen_tuki2.checked){
				zangyoModalRecalc();
			}
		// }

		
		reCalcTujoZanTeate()		//「通常残業手当」
		reCalcSinyaZanTeate()		//「深夜残業手当」
		reCalcKyujituRodoTeate()	//「休日労働手当」
		reCalcKyujitusinyaRodoTeate()	//「休日深夜労働手当」
		reCalcKorituZangyoTeate()		//「残業手当」※「効率残業手当」も算出
		reCalcZanNonkisoTotal()		//「残業代の基礎とならない手当－合計」
		reCalcShikyugakuA()			//「支給額Ａ」
		reCalcSagakuTeate()			//「前年との差」
		reCalcZAZanTotal()			//「残業単価」


		reCalcAZanTotal()				//「総支給額（支給額Ａ　＋　残業手当）」

	//年俸の場合
	}else if(gekkyu_nempo_val == 1){
		reCalcNempoShokyuNengaku()	//「年俸－昇給－年額」
		reCalcNempoNengaku()		//「年俸ー年額合計（前年度年額＋昇給年額）」
		reCalcNempoGetugaku()		//「年俸－12or14で割った金額」

		//「年俸－家族手当」
		//「年俸－社保扶養」チェックボックスがオン状態の場合のみ取得
		var shaho_fuyo = document.getElementById("nempo_shaho_fuyo");
		if(shaho_fuyo.checked){
			reCalcNempoKazokuTeate()	//「年俸－家族手当」
		}

		reCalcShikyugakuA()			//「支給額Ａ」
		reCalcSagakuTeate()			//「前年との差（固定残業代＋営業手当）」
		reCalcAZanTotal()			//「総支給額（支給額Ａ　＋　残業手当）」
	}
	
	reCalcKekkinKojo();			//「欠勤控除」
	reCalcChikokuSotaiKojo();	//「遅刻早退控除」

	reCalcShokusekiKyuSagaku()			//差額列の「職責給－合計」
	reCalcKihonkyuSagaku()				//差額列の「年齢給」
	reCalcKoteiKihonkyuSagaku()			//差額列の「固定基本給」
	reCalcKaikinTeateSagaku()			//差額列の「皆勤手当」
	reCalcTosiTeateSagaku()				//差額列の「都市手当」
	reCalcShoreiTeateSagaku()			//差額列の「奨励手当」
	reCalcChoseiTeateSagaku()			//差額列の「調整手当」
	reCalcZankisoShokeiSagaku()			//差額列の「残業代の基礎となる手当－小計」
	reCalcTenkinJutakuTeateSagaku()		//差額列の「転勤住宅手当」
	reCalcTansinfuninTeateSagaku()		//差額列の「単身赴任手当」
	reCalcZankisoTotalSagaku()			//差額列の「残業代の基礎となる手当－合計」
	reCalcKazokuTeateSagaku()			//差額列の「月給－家族手当」
	reCalckorituzangyoTeateSagaku()		//差額列の「効率残業手当」
	reCalcZanNonkisoTotalSagaku()		//差額列の「残業代の基礎とならない手当－合計」
	reCalcNempoNengakuSagaku()			//差額列の「年俸－年額合計」
	reCalcNempoGetugakuSagaku()			//差額列の「年俸－12or14で割った金額」
	reCalcNempoKazokuTeateSagaku()		//差額列の「年俸－家族手当」
	reCalcUchiKoteizangyoDaiSagaku()	//差額列の「うち固定残業代」
	reCalcUchiEigyoTeateSagaku()		//差額列の「うち営業手当」
	reCalcShikyugakuASagaku()			//差額列の「支給額Ａ」





	//最後に下記の金額が正しいかチェックする。
	//ＮＧの場合は画面にメッセージが表示される。
	//※"月給"の場合のみチェックする。
	if(gekkyu_nempo_val == 0){
		//「職責給」の金額が選択された職責の範囲内かチェック
		//ＮＧ解消しなくてもＤＢ登録可
		chkShokusekikyu();

		//「転勤住宅手当」規定（支給条件は「主任以下」）に違反していないか確認
		//ＮＧ解消しなくてもＤＢ登録可
		chkTenkinJutakuTeate();
	}

	//「残業代の基礎となる手当－合計」が最低賃金以上になっているかチェック
	//ＮＧ解消しないと登録できない
	chkSaiteichingin();
	
	//データ更新フラグをオン
	update_flg = 1;
}

//****************************************************************************
//　「転勤住宅手当」の支給条件を確認し、
//　規定違反の場合はエラーフラグをオンにする。
//　違反していない場合はオフにする。
//　【引数】
//　　無し
//*****************************************************************************
function chkTenkinJutakuTeate(){

	//規定（支給条件は「主任以下」）に違反していないか確認
	//*　「転勤住宅手当」に入力された値がゼロの場合は処理を抜ける
	var tenkin_jutaku_teate = off_format_val(document.getElementById("tenkin_jutaku_teate").value);
	tenkin_jutaku_teate = tenkin_jutaku_teate - 0;
	if(tenkin_jutaku_teate == 0){
		document.getElementById("tenkin_jutaku_teate_err_msg").style.display = "none";
		err_flg_tenkin_jutaku_teate = 0;
		return;
	}
	//*　プルダウンで選択されている職責を取得
	var obj = document.getElementById("sel_duties");
	var sel_duties_value = obj.options[obj.selectedIndex].value;	// プルダウンで選択した職責の値

	if(sel_duties_value <= DUTIES_SHUNIN_CODE){
		document.getElementById("tenkin_jutaku_teate_err_msg").style.display = "none";
		err_flg_tenkin_jutaku_teate = 0;
	}else{
		//「転勤住宅手当」のタイトル部分にコメント挿入
		document.getElementById("tenkin_jutaku_teate_err_msg").innerText = "※支給条件は主任以下です。";
		document.getElementById("tenkin_jutaku_teate_err_msg").style.display = "inline";
		//エラーフラグをオン
		err_flg_tenkin_jutaku_teate = 1;
	}

	//*　「単身赴任」チェックボックスがオンの場合はエラーメッセージを表示
	var wk_msg = "";
	if(document.getElementById("tansinfunin").checked){
		//**　既にエラーが発生している場合は改行を挿入する
		if(err_flg_tenkin_jutaku_teate){
			wk_msg = document.getElementById("tenkin_jutaku_teate_err_msg").innerText;
			wk_msg = wk_msg + "\n";
		}
		wk_msg = wk_msg + "※「単身赴任」フラグがオン\n　　になっています。単身赴任\n　　の場合は転勤住宅手当は\n　　支給されません。";
		document.getElementById("tenkin_jutaku_teate_err_msg").innerText = wk_msg;
		document.getElementById("tenkin_jutaku_teate_err_msg").style.display = "inline";
		//エラーフラグをオン
		err_flg_tenkin_jutaku_teate = 1;
	}
}




//****************************************************************************
//　★「月給・年俸」切り替え処理の前処理★
//　　"月給"　→　"年俸"　に切り替える場合は必ず「残業手当を適用しない」の
//　　状態にして、そうすることによる他の金額の計算を整えてから
//　　「月給・年俸」切り替え処理を実施する。
//　　なぜなら、"年俸"の状態でＤＢに登録する場合でも、月給関係の手当の金額も
//　　ＤＢに登録されるから。そうすることでＤＢ登録後に再度"月給"に切り替えたときに
//　　正しい値が表示される。
//　　
//　　「残業手当を適用する／しない」のラジオボタンの状態を確認し、
//　　"残業手当を適用する"　が選択されている場合
//　　　　１．"残業手当を適用しない"　が選択された状態に変更する
//　　　　２．再計算処理（reCalc()）を実施
//　　　　３．「月給・年俸」切り替え処理（chgGekkyuNempo()）を実施
//　　"残業手当を適用しない"　が選択されている場合
//　　　　・「月給・年俸」切り替え処理（chgGekkyuNempo()）を実施
//　【引数】
//　　無し
//*****************************************************************************
function beforChgGekkyuNempo(){

    var gekkyu_nempo = document.getElementsByName("cb_gekkyu_nempo");

	//月給／年俸判定用変数に値を入れる
    for(var i = 0; i < gekkyu_nempo.length; i++){
      if(gekkyu_nempo[i].checked) {
		gekkyu_nempo_val = gekkyu_nempo[i].value - 0;
      }
    }

	//"月給"の場合
	if(gekkyu_nempo_val == GEKKYU_CODE){

		var cb_zangyo_tekiyo_y = document.getElementById("cb_zangyo_tekiyo_y");
		var cb_zangyo_tekiyo_n = document.getElementById("cb_zangyo_tekiyo_n");

		//"残業手当を適用する"　が選択されている場合
		if(cb_zangyo_tekiyo_y.checked){
			//"残業手当を適用しない"　が選択された状態に変更する
			cb_zangyo_tekiyo_n.checked;
			//再計算処理（reCalc()）を実施
			reCalc();
		}
	}

	//「月給・年俸」切り替え処理（chgGekkyuNempo()　を実施）
	chgGekkyuNempo();

}

//****************************************************************************
//　★「月給・年俸」を切り替えたときの処理★
//　　画面上の「月給・年俸」の値を内部で保持する。
//　　"月給"　→　"年俸"　の場合、
//　　月給の項目をクリア＆使用不可にして年俸の項目を使用可にする。
//　　"年俸"　→　"月給"　の場合、
//　　年俸の項目をクリア＆使用不可にして月給の項目を使用可にする。
//　【引数】
//　　無し
//*****************************************************************************
function chgGekkyuNempo(){
	
    var gekkyu_nempo = document.getElementsByName("cb_gekkyu_nempo");

	//月給／年俸判定用変数に値を入れる
    for(var i = 0; i < gekkyu_nempo.length; i++){
      if(gekkyu_nempo[i].checked) {
		gekkyu_nempo_val = gekkyu_nempo[i].value - 0;
      }
    }
	
	//"月給"→"年俸"にした場合
	if(gekkyu_nempo_val == NEMPO_CODE){

		//*　１．「年俸ー前年度年額」を黒文字にする（シルバーになっている）
		document.getElementById("zennen_nempo_nengaku").setAttribute("class", "");

		//*　２．「年俸－評価点による昇給額」に該当する値（月給の同項目に表示されていた値）を表示する
		document.getElementById("nempo_shokyu_gaku").innerText = document.getElementById("shokyu_gaku").innerText;

		//*　３．「職責給－昇給訂正」の値を「年俸－昇給訂正」に移動
		document.getElementById('nempo_teisei_gaku').innerText = document.getElementById("teisei_gaku").innerText;

		//*　４．「職責給－昇給嘆願」の値を「年俸－昇給嘆願」に移動
		document.getElementById('nempo_tangan_gaku').innerText = document.getElementById("tangan_gaku").innerText;

		//*　５．「年俸－年額－昇給訂正」をクリックしたら「昇給訂正」モーダルが表示されるようにする
		document.getElementById('nempo_teisei_gaku').setAttribute('data-toggle', "modal");
		document.getElementById('nempo_teisei_gaku').setAttribute('data-target', "#teisei_modal");

		//*　６．「年俸－年額－昇給嘆願」をクリックしたら「昇給嘆願」モーダルが表示されるようにする
		document.getElementById('nempo_tangan_gaku').setAttribute('data-toggle', "modal");
		document.getElementById('nempo_tangan_gaku').setAttribute('data-target', "#tangan_modal");

		//*　７．「１２分割～／１４分割～」ラジオボタンを使用可能な状態にする
		document.getElementById("cb_nempo_divide_12").setAttribute('onclick', "");	//使用可能な状態にする
		document.getElementById("cb_nempo_divide_14").setAttribute('onclick', "");	//使用可能な状態にする

		//*　８．退避エリアから表示欄（入力欄）へ値を入れる
		//**　「社保扶養フラグ」が１の場合は「年俸－社保扶養フラグ」チェックボックスをオンにする
		if(taihi_shaho_fuyo_flg == 1){
			document.getElementById("nempo_shaho_fuyo").checked = true;
		}
		document.getElementById("nempo_kazoku_teate").innerText = numberFormat(taihi_kazoku_teate);				//「年俸－家族手当」
		document.getElementById("nempo_uchi_koteizang_dai").value = numberFormat(taihi_nempo_uchi_koteizang_dai);	//「うち固定残業代」
		document.getElementById("nempo_uchi_eigyo_teate").value = numberFormat(taihi_nempo_uchi_eigyo_teate);		//「うち営業手当」

		//*　９．年俸関係の項目の入力欄を入力可の状態にする
		if((USER_SHUBETU == 4)||(USER_SHUBETU == 5)){
			//「家族手当」が"有効"の場合は、「年俸－社保扶養」チェックボックスを使用可にする
			//"無効"の場合はreadOnlyのまま
			if(mst_kazoku_teate_rows[0]["muko_flg"] == 0){
				document.getElementById("nempo_shaho_fuyo").setAttribute('onclick', "chgNempoShahoFuyoVal();");
			}
			document.getElementById("nempo_uchi_koteizang_dai").readOnly = false;	//「うち固定残業代」
			document.getElementById("nempo_uchi_eigyo_teate").readOnly = false;		//「うち営業手当」
		}

		//*　１０．「前年度職責給」はグレーアウトっぽくする
		document.getElementById("zennen_shokuseki_kyu").setAttribute("class", "font_color_silver");
		
		//*　１１．月給の下記項目の値を退避エリアへコピーする
//		taihi_kihon_kyu = off_format_val(document.getElementById("kihon_kyu").innerText);					//「年齢給」
//		taihi_kotei_kihon_kyu = off_format_val(document.getElementById("kotei_kihon_kyu").value);			//「固定基本給」
//		taihi_kaikin_teate = off_format_val(document.getElementById("kaikin_teate").innerText);				//「皆勤手当」
//		taihi_shorei_teate = off_format_val(document.getElementById("shorei_teate").innerText);				//「奨励手当」
//		taihi_chosei_teate = off_format_val(document.getElementById("chosei_teate").value);					//「調整手当」
//		taihi_tenkin_jutaku_teate = off_format_val(document.getElementById("tenkin_jutaku_teate").value);	//「転勤住宅手当」
//		taihi_tansinfunin_teate = off_format_val(document.getElementById("tansinfunin_teate").innerText);		//「単身赴任手当」
//		taihi_tansinfunin_flg = document.getElementById("tansinfunin_value").value;			//「単身赴任フラグ」hidden
//		taihi_kazoku_teate = off_format_val(document.getElementById("kazoku_teate").innerText);				//「家族手当」
//		taihi_shaho_fuyo_flg = document.getElementById("shaho_fuyo_value").value;			//「社保扶養フラグ」hidden

		//*　１２．月給関係の値をクリア＆使用不可にする（「前年度職責給」以外）

		document.getElementById("btn_kimmuchi").disabled = true;	//「勤務地」の［初期値］ボタン
		document.getElementById("sel_kimmuchi").disabled = true;	//「勤務地」

		document.getElementById("shokyu_gaku").innerText = "";		//「評価点による昇給額」
		document.getElementById("teisei_gaku").innerText = "";		//「昇給訂正」
		document.getElementById("tangan_gaku").innerText = "";		//「昇給嘆願」
		document.getElementById("shokuseki_kyu").innerText = "";	//「職責給－合計」

		document.getElementById("kihon_kyu").innerText = "";		//「年齢給」
		document.getElementById("kotei_kihon_kyu").value = "";		//「固定基本給」
		document.getElementById("kaikin_teate").innerText = "";		//「皆勤手当」
		document.getElementById("shorei_teate").innerText = "";		//「奨励手当」
		document.getElementById("chosei_teate").value = "";			//「調整手当」
		document.getElementById("tenkin_jutaku_teate").value = "";	//「転勤住宅手当」
		document.getElementById("tansinfunin_teate").innerText = "";	//「単身赴任手当」
		document.getElementById("tansinfunin_value").value = 0;		//「単身赴任フラグ」hidden
		document.getElementById("tansinfunin").checked = false;		//「単身赴任手当」checkbox
		// document.getElementById("shaho_fuyo_value").value = 0;		//「社保扶養フラグ」hidden
		document.getElementById("shaho_fuyo").checked = false;		//「月給－社保扶養」checkbox

		//**　運用管理ユーザー、システム管理ユーザーの場合
		if((USER_SHUBETU == 4)||(USER_SHUBETU == 5)){
		
			//***　月給関係の項目の入力欄を入力不可（"disabled"か"readOnly"）にする
			document.getElementById("kotei_kihon_kyu").readOnly = true;			//「固定基本給」
			document.getElementById("chosei_teate").readOnly = true;		//「調整手当」
			document.getElementById("tenkin_jutaku_teate").readOnly = true;	//「転勤住宅手当」
			document.getElementById("tansinfunin").setAttribute("onclick", "return false;");	//「単身赴任」のチェックボックスをreadOnlyにする
			document.getElementById("shaho_fuyo").setAttribute("onclick", "return false;");	//「月給－社保扶養」のチェックボックスをreadOnlyにする

		}

		document.getElementById("tosi_teate").innerText = "";			//「都市手当」
		document.getElementById("zankiso_shokei").innerText = "";		//「残業代の基礎となる－小計」
		document.getElementById("zankiso_total").innerText = "";		//「残業代の基礎となる－合計」
		document.getElementById("kazoku_teate").innerText = "";			//「月給－家族手当」
		document.getElementById("korituzangyo_teate").innerText = "";	//「効率残業手当」
		document.getElementById("zan_nonkiso_total").innerText = "";	//「残業代の基礎とならない－合計」

		//*　１３．「前年度職責給」は給与に計上されないようにする
		//　　→"年俸"が選択されている場合、reCalc()で月給項目は更新されないから問題なし。

		//*　１４．「職責給－昇給訂正」をクリックしても「昇給訂正」モーダルが表示されないようにする
			document.getElementById('teisei_gaku').setAttribute('data-toggle', "");
			document.getElementById('teisei_gaku').setAttribute('data-target', "");

		//*　１５．「職責給－昇給嘆願」をクリックしても「昇給嘆願」モーダルが表示されないようにする
			document.getElementById('tangan_gaku').setAttribute('data-toggle', "");
			document.getElementById('tangan_gaku').setAttribute('data-target', "");

		//*　１６．残業関係の値を退避エリアにコピーする
//		taihi_zan_tanka = off_format_val(document.getElementById("zan_tanka").innerText);		//「残業単価」
		taihi_tujo_zan_jikan = document.getElementById("tujo_zan_jikan").innerText;	//「通常残業時間」
		taihi_tujo_zan_teate = off_format_val(document.getElementById("tujo_zan_teate").innerText);	//「通常残業手当」
		taihi_sinya_zan_jikan = document.getElementById("sinya_zan_jikan").innerText;	//「深夜残業時間」
		taihi_sinya_zan_teate = off_format_val(document.getElementById("sinya_zan_teate").innerText);	//「深夜残業手当」
		taihi_kyujitu_rodo_jikan = document.getElementById("kyujitu_rodo_jikan").innerText;	//「休日労働時間」
		taihi_kyujitu_rodo_teate = off_format_val(document.getElementById("kyujitu_rodo_teate").innerText);	//「休日労働手当」
		taihi_kyujitusinya_rodo_jikan = document.getElementById("kyujitusinya_rodo_jikan").innerText;	//「休日深夜労働時間」
		taihi_kyujitusinya_rodo_teate = off_format_val(document.getElementById("kyujitusinya_rodo_teate").innerText);	//「休日深夜労働手当」
		taihi_zangyo_teate_gokei = off_format_val(document.getElementById("zangyo_teate_gokei").innerText);	//「残業手当」

		//*　１７．残業関係の値をクリアする
		document.getElementById("zan_tanka").innerText = "";		//「残業単価」
		document.getElementById("tujo_zan_jikan").innerText = "";	//「通常残業時間」
		document.getElementById("tujo_zan_teate").innerText = "";	//「通常残業手当」
		document.getElementById("sinya_zan_jikan").innerText = "";	//「深夜残業時間」
		document.getElementById("sinya_zan_teate").innerText = "";	//「深夜残業手当」
		document.getElementById("kyujitu_rodo_jikan").innerText = "";	//「休日労働時間」
		document.getElementById("kyujitu_rodo_teate").innerText = "";	//「休日労働手当」
		document.getElementById("kyujitusinya_rodo_jikan").innerText = "";	//「休日深夜労働時間」
		document.getElementById("kyujitusinya_rodo_teate").innerText = "";	//「休日深夜労働手当」
		document.getElementById("zangyo_teate_gokei").innerText = "";	//「残業手当」
		document.getElementById("zangyo_teate_disp").innerText = "";	//「残業手当」のタイトル部分にの残業手当金額を表示

		//*　１８．残業入力用モーダルが表示されないようにする
		zangyoModalUnset();

		//*　１９．「残業手当を適用する／しない」のラジオボタンを使用不可にする
		document.getElementById("cb_zangyo_tekiyo_y").setAttribute('onclick', "return false;");	//使用不可状態にする
		document.getElementById("cb_zangyo_tekiyo_n").setAttribute('onclick', "return false;");	//使用不可状態にする


	//"年俸"→"月給"にした場合
	}else if(gekkyu_nempo_val == GEKKYU_CODE){
		//*　１．「前年度職責給」の金額を黒にする（シルバーになっている）
		document.getElementById("zennen_shokuseki_kyu").setAttribute("class", "");

		//*　２．「職責給－評価点による昇給額」に該当する値（年俸の同項目に表示されていた値）を表示する
		document.getElementById("shokyu_gaku").innerText = document.getElementById("nempo_shokyu_gaku").innerText;

		//*　３．「年俸－昇給訂正」の値を「職責給－昇給訂正」に移動
		document.getElementById('teisei_gaku').innerText = document.getElementById("nempo_teisei_gaku").innerText;

		//*　４．「年俸－昇給嘆願」の値を「職責給－昇給嘆願」に移動
		document.getElementById('tangan_gaku').innerText = document.getElementById("nempo_tangan_gaku").innerText;

		//*　５．「職責給－昇給訂正」をクリックしたら「昇給訂正」モーダルが表示されるようにする
		document.getElementById('teisei_gaku').setAttribute('data-toggle', "modal");
		document.getElementById('teisei_gaku').setAttribute('data-target', "#teisei_modal");

		//*　６．「職責給－昇給嘆願」をクリックしたら「昇給嘆願」モーダルが表示されるようにする
		document.getElementById('tangan_gaku').setAttribute('data-toggle', "modal");
		document.getElementById('tangan_gaku').setAttribute('data-target', "#tangan_modal");

		//*　７－１．月給関係の項目の入力欄を入力可にする
		document.getElementById("btn_kimmuchi").disabled = false;	// 「勤務地」の［初期値］ボタンを有効化する
		document.getElementById("sel_kimmuchi").disabled = false;	//「勤務地」




		//*　８．退避エリアから表示欄（入力欄）へ値を入れる
		document.getElementById("kihon_kyu").innerText = numberFormat(taihi_kihon_kyu);					//「年齢給」
		document.getElementById("kotei_kihon_kyu").value = numberFormat(taihi_kotei_kihon_kyu);			//「固定基本給」
		document.getElementById("kaikin_teate").innerText = numberFormat(taihi_kaikin_teate);				//「皆勤手当」
		document.getElementById("shorei_teate").innerText = numberFormat(taihi_shorei_teate);				//「奨励手当」
		document.getElementById("chosei_teate").value = numberFormat(taihi_chosei_teate);					//「調整手当」
		document.getElementById("tenkin_jutaku_teate").value = numberFormat(taihi_tenkin_jutaku_teate);	//「転勤住宅手当」
		document.getElementById("tansinfunin_teate").innerText = numberFormat(taihi_tansinfunin_teate);		//「単身赴任手当」
		document.getElementById("tansinfunin_value").value = taihi_tansinfunin_flg;			//「単身赴任フラグ」hidden
		//**　「単身赴任フラグ」が１の場合は「単身赴任フラグ」チェックボックスをオンにする
		if(taihi_tansinfunin_flg == 1){
			document.getElementById("tansinfunin").checked = true;
		}
		document.getElementById("kazoku_teate").innerText = numberFormat(taihi_kazoku_teate);				//「月給－家族手当」
		// document.getElementById("shaho_fuyo_value").value = taihi_shaho_fuyo_flg;			//「社保扶養フラグ」hidden
		//**　「社保扶養フラグ」が１の場合は「社保扶養フラグ」チェックボックスをオンにする
		if(taihi_shaho_fuyo_flg == 1){
			document.getElementById("shaho_fuyo").checked = true;
		}



		//*　７－２．運用管理ユーザー、システム管理ユーザーの場合
		if((USER_SHUBETU == 4)||(USER_SHUBETU == 5)){

			//以下（月給関係の項目）の入力欄を入力可にする
			document.getElementById("kotei_kihon_kyu").readOnly = false;		//「固定基本給」
			document.getElementById("chosei_teate").readOnly = false;		//「調整手当」
			document.getElementById("tenkin_jutaku_teate").readOnly = false;	//「転勤住宅手当」

			//「単身赴任手当」が"有効"の場合は、「単身赴任」チェックボックスを使用可にする
			//"無効"の場合はreadOnlyのまま
			if(mst_tansinfunin_teate_rows[0]["muko_flg"] == 0){
				document.getElementById("tansinfunin").setAttribute('onclick', "chgTansinfuninVal();");
			}

			//「家族手当」が"有効"の場合は、「月給－社保扶養」チェックボックスを使用可にする
			//"無効"の場合はreadOnlyのまま
			if(mst_kazoku_teate_rows[0]["muko_flg"] == 0){
				document.getElementById("shaho_fuyo").setAttribute('onclick', "chgShahoFuyoVal();");
			}
		}

		//*　９．退避エリアから残業関係の表示欄へ値を入れる
		document.getElementById("zan_tanka").innerText = numberFormat(taihi_zangyo_tanka);		//「残業単価」
		document.getElementById("tujo_zan_jikan").innerText = taihi_tujo_zan_jikan;	//「通常残業時間」
		document.getElementById("tujo_zan_teate").innerText = numberFormat(taihi_tujo_zan_teate);	//「通常残業手当」
		document.getElementById("sinya_zan_jikan").innerText = taihi_sinya_zan_jikan;	//「深夜残業時間」
		document.getElementById("sinya_zan_teate").innerText = numberFormat(taihi_sinya_zan_teate);	//「深夜残業手当」
		document.getElementById("kyujitu_rodo_jikan").innerText = taihi_kyujitu_rodo_jikan;	//「休日労働時間」
		document.getElementById("kyujitu_rodo_teate").innerText = numberFormat(taihi_kyujitu_rodo_teate);	//「休日労働手当」
		document.getElementById("kyujitusinya_rodo_jikan").innerText = taihi_kyujitusinya_rodo_jikan;	//「休日深夜労働時間」
		document.getElementById("kyujitusinya_rodo_teate").innerText = numberFormat(taihi_kyujitusinya_rodo_teate);	//「休日深夜労働手当」
		document.getElementById("zangyo_teate_gokei").innerText = numberFormat(taihi_zangyo_teate_gokei);	//「残業手当」
		document.getElementById("zangyo_teate_disp").innerText = numberFormat(taihi_zangyo_teate_gokei);	//「残業手当」のタイトル部分にの残業手当金額を表示

		//*　１０．「年俸ー前年度年額」をグレー文字にする
		document.getElementById("zennen_nempo_nengaku").setAttribute("class", "font_color_silver");
		
		//*　１１．「１２分割～／１４分割～」ラジオボタンを使用不可能な状態にする
		document.getElementById("cb_nempo_divide_12").setAttribute('onclick', "return false;");	//使用不可能な状態にする
		document.getElementById("cb_nempo_divide_14").setAttribute('onclick', "return false;");	//使用不可能な状態にする

		//*　１２．年俸の下記項目の値を退避エリアへコピーする
//		taihi_nempo_uchi_koteizang_dai = off_format_val(document.getElementById("nempo_uchi_koteizang_dai").value);	//「うち固定残業代」
//		taihi_nempo_uchi_eigyo_teate = off_format_val(document.getElementById("nempo_uchi_eigyo_teate").value);		//「うち営業手当」

		//*　１３．年俸関係の項目の入力欄をクリア＆入力不可の状態にする
		document.getElementById("nempo_shokyu_gaku").innerText = "";	//「年俸－昇給－評価点による昇給額」
		document.getElementById("nempo_teisei_gaku").innerText = "";	//「年俸－昇給－昇給訂正額」
		document.getElementById("nempo_tangan_gaku").innerText = "";	//「年俸－昇給－昇給嘆願額」
		document.getElementById("nempo_shokyu_nengaku").innerText = "";		//「年俸－昇給－年額」
		document.getElementById("nempo_nengaku").innerText = "";		//「年俸－年額合計」
		document.getElementById("nempo_getugaku").innerText = "";		//「１２で割った金額」

		document.getElementById("nempo_shaho_fuyo").checked = false;		//「年俸－社保扶養手当」checkbox
		document.getElementById("nempo_kazoku_teate").innerText = "";		//「年俸－家族手当」
		document.getElementById("nempo_uchi_koteizang_dai").value = "";	//「うち固定残業代」
		document.getElementById("nempo_uchi_eigyo_teate").value = "";		//「うち営業手当」

		//**　運用管理ユーザー、システム管理ユーザーの場合
		if((USER_SHUBETU == 4)||(USER_SHUBETU == 5)){
			document.getElementById("nempo_shaho_fuyo").setAttribute("onclick", "return false;");	//「年俸－社保扶養」のチェックボックスをreadOnlyにする
			document.getElementById("nempo_uchi_koteizang_dai").readOnly = true;	//「うち固定残業代」
			document.getElementById("nempo_uchi_eigyo_teate").readOnly = true;		//「うち営業手当」
		}

		//*　１４．「年俸－年額－昇給訂正」をクリックしても「昇給訂正」モーダルが表示されないようにする
		document.getElementById('nempo_teisei_gaku').setAttribute('data-toggle', "");
		document.getElementById('nempo_teisei_gaku').setAttribute('data-target', "");

		//*　１５．「年俸－年額－昇給嘆願」をクリックしても「昇給嘆願」モーダルが表示されないようにする
		document.getElementById('nempo_tangan_gaku').setAttribute('data-toggle', "");
		document.getElementById('nempo_tangan_gaku').setAttribute('data-target', "");

		//*　１６．残業入力用モーダルが表示されるようにする
		// //運用管理ユーザー、システム管理ユーザーの場合
		// if((USER_SHUBETU == 4)||(USER_SHUBETU == 5)){
		// 	zangyoModalSet("#kanri_zangyo_modal");
		// //その他のユーザーの場合
		// }else{
			zangyoModalSet("#zangyo_modal");
		// }

		//*　１７，「残業手当を適用する／しない」ラジオボタンを使用可能な状態にする
		document.getElementById("cb_zangyo_tekiyo_y").setAttribute('onclick', "");
		document.getElementById("cb_zangyo_tekiyo_n").setAttribute('onclick', "");

	}
	
	//画面の値再計算
	reCalc();
	
}

//****************************************************************************
//　★最低賃金チェック★
//　「残業代の基礎となる手当－合計」が最低賃金以上になっているかどうか
//　チェックする
//　【引数】
//　　無し
//　【戻り値】
//　	１．「残業代の基礎となる手当－合計」が最低賃金以上になっている場合
//　　　　true
//　	２．「残業代の基礎となる手当－合計」が最低賃金以上になっていない場合
//　　　　false
//*****************************************************************************
function chkSaiteichingin(){

	//Ａ：最低賃金時間額（各都道府県から出されるもの）　×　１ヶ月の平均所定労働時間
	//Ｂ：残業代の基礎となるものの合計
	//Ａ　<=　Ｂ　になっていればＯＫ（最低賃金以上が支給される状態）


	//　Ａの計算
	//*　プルダウンで選択されている勤務地を取得
	var obj = document.getElementById("sel_kimmuchi");
	var kimmuchi_arr = obj.options[obj.selectedIndex].value.split(";");	// プルダウンで選択した職責の値

	//*　最低賃金マスタデータから、プルダウンで選択されている勤務地の
	//*　最低賃金を取得
	for(var i=0; i<saitei_chingin_mst_dt_rows.length; i++){
		if(saitei_chingin_mst_dt_rows[i]["todofuken_cd"] == kimmuchi_arr[1]){
			sel_saitei_chingin = saitei_chingin_mst_dt_rows[i]["saitei_chingin"];
		}
	}

	//*　計算式（最低賃金時間額（各都道府県から出されるもの）　×　１ヶ月の平均所定労働時間）
	var val_a = sel_saitei_chingin * rodo_per_month;


	//　Ｂの計算
	var val_b = 0;
	//　月給の場合　「残業代の基礎となる手当ー合計」を取得
	if(gekkyu_nempo_val == GEKKYU_CODE){
		val_b = off_format_val(document.getElementById("zankiso_total").innerText);

	//"年俸"の場合　　「年俸年額」を１２または１４（「12分割or14分割」の値）で割った金額から「固定残業代」「営業手当」「家族手当」を引いた金額
	}else if(gekkyu_nempo_val == NEMPO_CODE){
		//年俸月額（年俸年額の１２or１４分割）
		var nempo_getugaku = off_format_val(document.getElementById("nempo_getugaku").innerText);
		nempo_getugaku = nempo_getugaku - 0;
		//うち固定残業代
		var nempo_uchi_koteizang_dai = off_format_val(document.getElementById("nempo_uchi_koteizang_dai").value);
		nempo_uchi_koteizang_dai = nempo_uchi_koteizang_dai - 0;
		//うち営業手当
		var nempo_uchi_eigyo_teate = off_format_val(document.getElementById("nempo_uchi_eigyo_teate").value);
		nempo_uchi_eigyo_teate = nempo_uchi_eigyo_teate - 0;
		//家族手当
		var nempo_kazoku_teate = off_format_val(document.getElementById("nempo_kazoku_teate").innerText);
		nempo_kazoku_teate = nempo_kazoku_teate - 0;
		
		val_b = nempo_getugaku - nempo_uchi_koteizang_dai - nempo_uchi_eigyo_teate - nempo_kazoku_teate;
	}

	if(val_a <= val_b){
		document.getElementById("saiteichingin_err_msg").innerText = "";
		document.getElementById("saiteichingin_err_msg").style.display = "none";
		document.getElementById("saiteichingin_err_msg_top").innerText = "";
		return true;
	}else{
		//「転勤住宅手当」のタイトル部分にコメント挿入
		document.getElementById("saiteichingin_err_msg").innerText = "※「残業代の基礎となる手当－合計」\nが最低賃金(" + numberFormat(Math.ceil(val_a)) + ")を下回っています。\n再度入力してください。";
		document.getElementById("saiteichingin_err_msg").style.display = "inline";
		document.getElementById("saiteichingin_err_msg_top").innerText = "最低賃金割れ";
		return false;
	}	
}


//****************************************************************************
//　★再計算用★
//　「職責給－合計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcShokusekiKyu(){
	var zennen_shokuseki_kyu = off_format_val(document.getElementById("zennen_shokuseki_kyu").innerText);	//「前年度－月給－職責給」
	zennen_shokuseki_kyu = zennen_shokuseki_kyu - 0;
	var shokyu_gaku = off_format_val(document.getElementById("shokyu_gaku").innerText);	//「評価点による昇給額」
	shokyu_gaku = shokyu_gaku - 0;
	var teisei_gaku = off_format_val(document.getElementById("teisei_gaku").innerText);	//「昇給訂正額」
	teisei_gaku = teisei_gaku - 0;
	var tangan_gaku = off_format_val(document.getElementById("tangan_gaku").innerText);	//「昇給嘆願－昇給額」
	tangan_gaku = tangan_gaku - 0;

	//デフォルトは「前年度－月給－職責給」と「評価点による昇給額」の合計値が「職責給－合計」となる
	var gokei = zennen_shokuseki_kyu + shokyu_gaku;
	
	//「昇給訂正額」が入力されている場合は「前年度－月給－職責給」と「昇給訂正額」の合計値が「職責給－合計」となる
	if(0 < teisei_gaku){
		gokei = zennen_shokuseki_kyu + teisei_gaku;

	//「昇給嘆願－昇給額」が入力されている場合は「前年度－月給－職責給」と「昇給嘆願－昇給額」の合計値が「職責給－合計」となる
	}else if(0 < tangan_gaku){
		gokei = zennen_shokuseki_kyu + tangan_gaku;
	}
	
	document.getElementById("shokuseki_kyu").innerText = numberFormat(gokei);

	taihi_shokuseki_kyu = gokei;	//「職責給－合計」を退避
	
}

//****************************************************************************
//　★再計算用★
//　「皆勤手当」を再取得する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKaikinTeate(){
	
	var sikyu_gaku = 0;

	//プルダウンで選択されている職責を取得
	var sel_duties = document.getElementById("sel_duties");
	var sel_duties_cd = sel_duties.options[sel_duties.selectedIndex].value;	// プルダウンで選択した職責の値
	
	//皆勤手当データから金額を取得する。職責で判断する。
	for(var i=0; i<mst_kaikin_teate_rows.length; i++){

		//「皆勤手当」が"無効"の場合は金額をゼロにして処理を抜ける
		if(mst_kaikin_teate_rows[i]["muko_flg"] == 1){
			document.getElementById("kaikin_teate").innerText = 0;
			taihi_kaikin_teate = 0;	//「皆勤手当」を退避
			return;
		}

		if(mst_kaikin_teate_rows[i]["duties_cd"] == sel_duties_cd){
				sikyu_gaku = mst_kaikin_teate_rows[i]["sikyu_gaku"];
		}
	}

	document.getElementById("kaikin_teate").innerText = numberFormat(sikyu_gaku);	//皆勤手当
	taihi_kaikin_teate = sikyu_gaku;	//「皆勤手当」を退避

	return false;
}
//****************************************************************************
//　★再計算用★
//　「都市手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcTosiTeate(){

	//年俸の場合は処理を抜ける
	if(gekkyu_nempo_val == 1){
		return false;
	}

	//都市手当が"無効"の場合は金額をゼロにして処理を抜ける
	if(mst_tosi_teate_rows.length && mst_tosi_teate_rows[0]["muko_flg"] == 1){
		document.getElementById("tosi_teate").innerText = 0;
		taihi_tosi_teate = 0;	//「皆勤手当」を退避
		return;
	}

	var sel_saitei_chingin = 0;
	var sagaku = 0;
	var calc_tosi_teate = 0;

	//プルダウンで選択されている勤務地を取得
	var obj = document.getElementById("sel_kimmuchi");
	var kimmuchi_arr = obj.options[obj.selectedIndex].value.split(";");	// プルダウンで選択した職責の値
	
	//最低賃金マスタデータから、プルダウンで選択されている勤務地の
	//最低賃金を取得
	for(var i=0; i<saitei_chingin_mst_dt_rows.length; i++){
		if(saitei_chingin_mst_dt_rows[i]["todofuken_cd"] == kimmuchi_arr[1]){
			sel_saitei_chingin = saitei_chingin_mst_dt_rows[i]["saitei_chingin"];
		}
	}
	
	var sagaku = sel_saitei_chingin - saitei_chingin_fukuoka_dt_row["saitei_chingin"];

	//（勤務地の最低賃金　ー　福岡の最低賃金　＝　Ａ）がゼロより大きい場合は
	//以下の計算で算出した金額を支給する。小数点第一位を切り上げ。
	//（Ａ　×　1ヶ月の所定労働時間）
	//ゼロ以下の場合は支給しない。
	if(0 < sagaku){
		calc_tosi_teate = sagaku * rodo_per_month;
		calc_tosi_teate = Math.ceil(calc_tosi_teate);

	}

	document.getElementById("tosi_teate").innerText = numberFormat(calc_tosi_teate);	//都市手当

	taihi_tosi_teate = calc_tosi_teate;	//「都市手当」を退避

	return false;
}


//****************************************************************************
//　★再計算用★
//　「奨励手当」を再取得する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcShoreiTeate(){
	
	var sikyu_gaku = 0;

	//プルダウンで選択されている職責を取得
	var sel_duties = document.getElementById("sel_duties");
	var sel_duties_cd = sel_duties.options[sel_duties.selectedIndex].value;	// プルダウンで選択した職責の値
	
	//奨励手当データから金額を取得する。職責で判断する。
	for(var i=0; i<mst_shorei_teate_rows.length; i++){

		//「奨励手当」が"無効"の場合は金額をゼロにして処理を抜ける
		if(mst_shorei_teate_rows[i]["muko_flg"] == 1){
			document.getElementById("shorei_teate").innerText = 0;
			taihi_shorei_teate = 0;	//「奨励手当」を退避
			return;
		}

		if(mst_shorei_teate_rows[i]["duties_cd"] == sel_duties_cd){
				sikyu_gaku = mst_shorei_teate_rows[i]["sikyu_gaku"];
		}
	}

	document.getElementById("shorei_teate").innerText = numberFormat(sikyu_gaku);	//奨励手当
	taihi_shorei_teate = sikyu_gaku;	//「奨励手当」を退避

	return false;
}

//****************************************************************************
//　★再計算用★
//　「単身赴任手当」を再取得する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcTansinfuninTeate(){
	
	var sikyu_gaku = 0;

	//プルダウンで選択されている職責を取得
	var sel_duties = document.getElementById("sel_duties");
	var sel_duties_cd = sel_duties.options[sel_duties.selectedIndex].value;	// プルダウンで選択した職責の値
	
	//単身赴任手当データから金額を取得する。職責で判断する。
	for(var i=0; i<mst_tansinfunin_teate_rows.length; i++){

		//「単身赴任手当」が"無効"の場合、
		//　１．「単身赴任手当」の金額をゼロにする
		//　２．「単身赴任」チェックボックスをオフにする
		//　３．処理を抜ける
		if(mst_tansinfunin_teate_rows[i]["muko_flg"] == 1){
			//
			document.getElementById("tansinfunin_teate").innerText = 0;
			taihi_tansinfunin_teate = 0;	//「単身赴任手当」を退避
			document.getElementById("tansinfunin").checked = false;	//「単身赴任」チェックボックスをオフ状態にする
			document.getElementById("tansinfunin_value").value = 0;	//「単身赴任フラグ」の値を画面にセット
			taihi_tansinfunin_flg = 0;	//「単身赴任フラグ」の値を退避
			return;
		}

		if(mst_tansinfunin_teate_rows[i]["duties_cd"] == sel_duties_cd){
				sikyu_gaku = mst_tansinfunin_teate_rows[i]["sikyu_gaku"];
		}
	}

	document.getElementById("tansinfunin_teate").innerText = numberFormat(sikyu_gaku);	//単身赴任手当
	taihi_tansinfunin_teate = sikyu_gaku;	//「単身赴任手当」を退避

	return false;

}

//****************************************************************************
//　★再計算用★
//　「月給－家族手当」を再取得する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKazokuTeate(){
	
	var sikyu_gaku = 0;

	//家族手当データから金額を取得する。
	if(mst_kazoku_teate_rows.length){
		//「家族手当」が"無効"の場合、
		//　１．「家族手当」の金額をゼロにする
		//　２．「月給－社保扶養」チェックボックスをオフにする
		//　３．処理を抜ける
		if(mst_kazoku_teate_rows[0]["muko_flg"] == 1){
			document.getElementById("kazoku_teate").innerText = 0;
			taihi_kazoku_teate = 0;	//「家族手当」を退避
			document.getElementById("shaho_fuyo").checked = false;	//「社保扶養」チェックボックスをオフにする
			document.getElementById("shaho_fuyo_value").value = 0;	//「社保扶養フラグ」の値を画面にセット
			taihi_shaho_fuyo_flg = 0;	//「社保扶養フラグ」の値を退避
			return;
		}
		sikyu_gaku = mst_kazoku_teate_rows[0]["sikyu_gaku"];
	}

	document.getElementById("kazoku_teate").innerText = numberFormat(sikyu_gaku);	//家族手当
	taihi_kazoku_teate = sikyu_gaku;	//「家族手当」を退避

	return false;
}

//****************************************************************************
//　★再計算用★
//　「年俸－家族手当」を再取得する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcNempoKazokuTeate(){
	
	var sikyu_gaku = 0;

	//家族手当データから金額を取得する。
	if(mst_kazoku_teate_rows.length){
		//「家族手当」が"無効"の場合、
		//　１．「家族手当」の金額をゼロにする
		//　２．「年俸－社保扶養」チェックボックスをオフにする
		//　３．処理を抜ける
		if(mst_kazoku_teate_rows[0]["muko_flg"] == 1){
			document.getElementById("nempo_kazoku_teate").innerText = 0;
			taihi_kazoku_teate = 0;	//「家族手当」を退避
			document.getElementById("nempo_shaho_fuyo").checked = false;	//「社保扶養」チェックボックスをオフにする
			document.getElementById("shaho_fuyo_value").value = 0;	//「社保扶養フラグ」の値を画面にセット
			taihi_shaho_fuyo_flg = 0;	//「社保扶養フラグ」の値を退避
			return;
		}
		sikyu_gaku = mst_kazoku_teate_rows[0]["sikyu_gaku"];
	}

	document.getElementById("nempo_kazoku_teate").innerText = numberFormat(sikyu_gaku);	//年俸－家族手当
	taihi_kazoku_teate = sikyu_gaku;	//「家族手当」を退避

	return false;
}

//****************************************************************************
//　★再計算用★
//　「残業代の基礎となる手当－小計」を画面上の値を用いて計算する。
//　【計算式】
//　　［職責給－合計］＋［年齢給］＋［固定基本給］＋［皆勤手当］＋［都市手当］＋［奨励手当］＋［調整手当］＝［残業代の基礎となる手当－小計］
//　【引数】
//　　無し
//*****************************************************************************
function reCalcZankisoShokei(){

	var calc_zankiso_shokei = 0;

	//［職責給－合計］値取得
	var shokuseki_kyu = off_format_val(document.getElementById("shokuseki_kyu").innerText);
	shokuseki_kyu = shokuseki_kyu - 0;

	//［年齢給］値取得
	var kihon_kyu = off_format_val(document.getElementById("kihon_kyu").innerText);
	kihon_kyu = kihon_kyu - 0;

	//「固定基本給」値取得
	var kotei_kihon_kyu = off_format_val(document.getElementById("kotei_kihon_kyu").value);
	kotei_kihon_kyu = kotei_kihon_kyu - 0;
	
	//［皆勤手当］値取得
	var kaikin_teate = off_format_val(document.getElementById("kaikin_teate").innerText);
	kaikin_teate = kaikin_teate - 0;

	//［都市手当］値取得
	var tosi_teate = off_format_val(document.getElementById("tosi_teate").innerText);
	tosi_teate = tosi_teate - 0;
	
	//［奨励手当］値取得
	var shorei_teate = off_format_val(document.getElementById("shorei_teate").innerText);
	shorei_teate = shorei_teate - 0;

	//［調整手当］値取得
	var chosei_teate = off_format_val(document.getElementById("chosei_teate").value);
//	var chosei_teate = getObjValue("chosei_teate");
	chosei_teate = chosei_teate - 0;
	
	calc_zankiso_shokei = shokuseki_kyu + kihon_kyu + kotei_kihon_kyu + kaikin_teate + tosi_teate + shorei_teate + chosei_teate;
	
	document.getElementById("zankiso_shokei").innerText = numberFormat(calc_zankiso_shokei);
}

//****************************************************************************
//　★再計算用★
//　「残業代の基礎となる手当－合計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcZankisoTotal(){

	var calc_zankiso_total = 0;

	//［残業代の基礎となる手当－小計］値取得
	var zankiso_shokei = off_format_val(document.getElementById("zankiso_shokei").innerText);
	zankiso_shokei = zankiso_shokei - 0;

	//［転勤住宅手当］値取得
	var tenkin_jutaku_teate = off_format_val(document.getElementById("tenkin_jutaku_teate").value);
	tenkin_jutaku_teate = tenkin_jutaku_teate - 0;

	//［単身赴任手当］値取得
	var tansinfunin_teate = off_format_val(document.getElementById("tansinfunin_teate").innerText);
	tansinfunin_teate = tansinfunin_teate - 0;
	
	calc_zankiso_total = zankiso_shokei + tenkin_jutaku_teate + tansinfunin_teate;
	
	document.getElementById("zankiso_total").innerText = numberFormat(calc_zankiso_total);
}

//****************************************************************************
//　★再計算用★
//　「効率残業手当」を画面上の値を用いて計算する。
//　「残業手当」を画面上の値を用いて計算する。
//　職責が主任以下の場合
//　　「効率残業手当」と「残業手当」のどちらかを支給するか判断する。
//　係長以上の場合
//　　「残業手当」も「効率残業手当」も支給しない。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKorituZangyoTeate(){

	//通常残業手当
	var tujo_zan_teate = off_format_val(document.getElementById("tujo_zan_teate").innerText);
	tujo_zan_teate = tujo_zan_teate - 0;
	//深夜残業手当
	var sinya_zan_teate = off_format_val(document.getElementById("sinya_zan_teate").innerText);
	sinya_zan_teate = sinya_zan_teate - 0;
	//休日労働手当
	var kyujitu_rodo_teate = off_format_val(document.getElementById("kyujitu_rodo_teate").innerText);
	kyujitu_rodo_teate = kyujitu_rodo_teate - 0;
	//休日深夜労働手当
	var kyujitusinya_rodo_teate = off_format_val(document.getElementById("kyujitusinya_rodo_teate").innerText);
	kyujitusinya_rodo_teate = kyujitusinya_rodo_teate - 0;
	var zangyo_teate = tujo_zan_teate + sinya_zan_teate + kyujitu_rodo_teate + kyujitusinya_rodo_teate;

	//「残業手当」の金額とタイトル部分のコメントを消す
	document.getElementById("zangyo_teate_gokei").innerText = "";

	document.getElementById("zangyo_teate_msg").style.display = "none";

	//「効率残業手当」の金額とタイトル部分のコメントを消す
	document.getElementById("korituzangyo_teate").innerText = "";
	document.getElementById("korituzangyo_teate_msg").style.display = "none";

	//プルダウンで選択されている職責を取得
	var sel_duties = document.getElementById("sel_duties");
	var sel_duties_cd = sel_duties.options[sel_duties.selectedIndex].value;	// プルダウンで選択した職責の値

	var shikyu_gaku = 0;		//支給額

	//「職責」が”係長”以上の場合
	if(DUTIES_KAKARICHO_CODE <= sel_duties_cd){

		//「残業手当」も「効率残業手当」もゼロ（どちらも支給されない）
		document.getElementById("korituzangyo_teate").innerText = 0;
		document.getElementById("zangyo_teate_gokei").innerText = 0;
		document.getElementById("zangyo_teate_disp").innerText = 0;//「残業手当」のタイトル部分の残業手当金額を表示
		document.getElementById("korituzangyo_teate_gaitogaku").value = 0;	//「効率残業手当（該当額）」hiddenタグ

			//「残業手当」のタイトル部分に「"係長以上なので残業手当は支給しません。"」を表示
			if(0 < zangyo_teate){
				document.getElementById("zangyo_teate_msg").innerText = "※係長以上なので残業手当は支給しません。";
				document.getElementById("zangyo_teate_msg").style.display = "inline";
			}
	
	//「職責」が”主任”以下の場合
	}else{
		var cb_zangyo_tekiyo_y = document.getElementById("cb_zangyo_tekiyo_y");
		var cb_zangyo_tekiyo_n = document.getElementById("cb_zangyo_tekiyo_n");

		//「効率残業手当」の支給額を算出する
		//*　年齢給
		var kihon_kyu = off_format_val(document.getElementById("kihon_kyu").innerText);
		kihon_kyu = kihon_kyu - 0;

		//*　固定基本給
		var kotei_kihon_kyu = off_format_val(document.getElementById("kotei_kihon_kyu").value);
		kotei_kihon_kyu = kotei_kihon_kyu - 0;
		
		//*　職責給
		var shokuseki_kyu = off_format_val(document.getElementById("shokuseki_kyu").innerText);
		shokuseki_kyu = shokuseki_kyu - 0;

		//*　皆勤手当
		var kaikin_teate = off_format_val(document.getElementById("kaikin_teate").innerText);
		kaikin_teate = kaikin_teate - 0;

		//*　奨励手当
		var shorei_teate = off_format_val(document.getElementById("shorei_teate").innerText);
		shorei_teate = shorei_teate - 0;

		//*　基準額（職責給　＋　年齢給　＋　固定基本給　＋　皆勤手当　＋　奨励手当）
		var kijun_gaku = shokuseki_kyu + kihon_kyu + kotei_kihon_kyu + kaikin_teate + shorei_teate;

		for(var i=0; i<mst_koritu_zan_teate_rows.length; i++){

			//手当マスタの「効率残業手当」が"無効"の場合は金額をゼロにする
			if(mst_koritu_zan_teate_rows[i]["muko_flg"] == 1){
				shikyu_gaku = 0;
			}else{
				//*　基準額（年齢給（＋「固定基本給」）＋職責給）を手当マスタの効率残業手当の基準額の範囲に照らして支給額を求める
				if((mst_koritu_zan_teate_rows[i]["nendo"] == nendo)&&(mst_koritu_zan_teate_rows[i]["teate_id"] == KORITU_ZAN_TEATE)&&(mst_koritu_zan_teate_rows[i]["kijungaku_kagen"] <= kijun_gaku)&&(kijun_gaku <= mst_koritu_zan_teate_rows[i]["kijungaku_jogen"])){
					shikyu_gaku = mst_koritu_zan_teate_rows[i]["sikyu_gaku"];
				}
			}
			// //*　基準額（年齢給（＋「固定基本給」）＋職責給）を手当マスタの効率残業手当の基準額の範囲に照らして支給額を求める
			// if((mst_koritu_zan_teate_rows[i]["nendo"] == nendo)&&(mst_koritu_zan_teate_rows[i]["teate_id"] == KORITU_ZAN_TEATE)&&(mst_koritu_zan_teate_rows[i]["kijungaku_kagen"] <= kijun_gaku)&&(kijun_gaku <= mst_koritu_zan_teate_rows[i]["kijungaku_jogen"])){
			// 	shikyu_gaku = mst_koritu_zan_teate_rows[i]["sikyu_gaku"];
			// }
		}

		//ラジオボタン(A1)が”残業手当を適用する”の場合
		if(cb_zangyo_tekiyo_y.checked){

			//「残業手当」＜＝「効率残業手当」の場合
			if(zangyo_teate <= shikyu_gaku){

				//「効率残業手当」は支給額を表示
				document.getElementById("korituzangyo_teate").innerText = numberFormat(shikyu_gaku);

				//「残業手当」はゼロを表示
				document.getElementById("zangyo_teate_gokei").innerText = 0;

				//０＜「残業手当」　の場合
				if(0 < zangyo_teate){
					//「残業手当」のタイトル部分に「"※「効率残業手当」の方が多いので効率残業手当を支給し\n残業手当は支給されません。"」を表示
					document.getElementById("zangyo_teate_msg").innerText = "※「効率残業手当」の方が多いので効率残業手当を支給し\n残業手当は支給されません。";
					document.getElementById("zangyo_teate_msg").style.display = "inline";
				}
   

			//「残業手当」＞「効率残業手当」の場合
			}else{

				//「効率残業手当」はゼロを表示
				document.getElementById("korituzangyo_teate").innerText = 0;
   
				//「残業手当」は支給額を表示
				document.getElementById("zangyo_teate_gokei").innerText = numberFormat(zangyo_teate);
				
					//０＜「効率残業手当」　の場合
					if(0 < shikyu_gaku){
						//「効率残業手当」のタイトル部分に「"※「残業手当」の方が多いので残業手当を支給し\n効率残業手当は支給されません。"」を表示
						document.getElementById("korituzangyo_teate_msg").innerText = "※「残業手当」の方が多いので残業手当を支給し\n効率残業手当は支給されません。";
						document.getElementById("korituzangyo_teate_msg").style.display = "inline";
					}
	   
			}

		//ラジオボタン(A1)が”残業手当を適用しない”の場合
		}else if(cb_zangyo_tekiyo_n.checked){

			//「効率残業手当」は支給額を表示
			document.getElementById("korituzangyo_teate").innerText = numberFormat(shikyu_gaku);

			//「残業手当」はゼロを表示
			document.getElementById("zangyo_teate_gokei").innerText = 0;
		}

		//「残業手当」のタイトル部分に、支給される場合の残業手当金額を表示
		document.getElementById("zangyo_teate_disp").innerText = numberFormat(zangyo_teate);
	}

	//※「効率残業手当ー支給額」の退避用変数に値を退避する
	taihi_korituzangyo_teate = off_format_val(shikyu_gaku);	//効率残業手当（支給額）

	//「効率残業手当ー該当額」の退避用変数に支給額を退避
	taihi_korituzangyo_teate_gaitogaku = shikyu_gaku;	//効率残業手当（該当額）

// 	//通常残業手当
// 	var tujo_zan_teate = off_format_val(document.getElementById("tujo_zan_teate").innerText);
// 	tujo_zan_teate = tujo_zan_teate - 0;
// 	//深夜残業手当
// 	var sinya_zan_teate = off_format_val(document.getElementById("sinya_zan_teate").innerText);
// 	sinya_zan_teate = sinya_zan_teate - 0;
// 	//休日労働手当
// 	var kyujitu_rodo_teate = off_format_val(document.getElementById("kyujitu_rodo_teate").innerText);
// 	kyujitu_rodo_teate = kyujitu_rodo_teate - 0;
// 	//休日深夜労働手当
// 	var kyujitusinya_rodo_teate = off_format_val(document.getElementById("kyujitusinya_rodo_teate").innerText);
// 	kyujitusinya_rodo_teate = kyujitusinya_rodo_teate - 0;
// 	var zangyo_teate = tujo_zan_teate + sinya_zan_teate + kyujitu_rodo_teate + kyujitusinya_rodo_teate;


// 	//「残業手当」タイトル部分のコメントを非表示にする
// 	document.getElementById("zangyo_teate_gokei").innerText = "";
// 	document.getElementById("zangyo_teate_msg").style.display = "none";

// 	//プルダウンで選択されている職責を取得
// 	var sel_duties = document.getElementById("sel_duties");
// 	var sel_duties_cd = sel_duties.options[sel_duties.selectedIndex].value;	// プルダウンで選択した職責の値

// 	var shikyu_gaku = 0;		//支給額

// 	//係長以上の場合は「効率残業手当」も「残業手当」も支給しない
// 	if(DUTIES_KAKARICHO_CODE <= sel_duties_cd){

// 		if(0 < zangyo_teate){
// 			//「残業手当」のタイトル部分にコメント挿入
// 			document.getElementById("zangyo_teate_msg").innerText = "※係長以上なので残業手当は支給しません。";
// 			document.getElementById("zangyo_teate_msg").style.display = "inline";
// 		}

// 		document.getElementById("korituzangyo_teate").innerText = 0;
// 		document.getElementById("zangyo_teate_gokei").innerText = 0;

// 		document.getElementById("korituzangyo_teate_gaitogaku").value = 0;	//「効率残業手当（該当額）」hiddenタグ

// 	//主任以下の場合は「効率残業手当」と「残業手当」のどちらを支給するか判断する
// 	}else{

// 		//年齢給
// 		var kihon_kyu = off_format_val(document.getElementById("kihon_kyu").innerText);
// 		kihon_kyu = kihon_kyu - 0;

// 		//固定基本給
// 		var kotei_kihon_kyu = off_format_val(document.getElementById("kotei_kihon_kyu").value);
// 		kotei_kihon_kyu = kotei_kihon_kyu - 0;
		
// 		//職責給
// 		var shokuseki_kyu = off_format_val(document.getElementById("shokuseki_kyu").innerText);
// 		shokuseki_kyu = shokuseki_kyu - 0;

// 		//皆勤手当
// 		var kaikin_teate = off_format_val(document.getElementById("kaikin_teate").innerText);
// 		kaikin_teate = kaikin_teate - 0;

// 		//奨励手当
// 		var shorei_teate = off_format_val(document.getElementById("shorei_teate").innerText);
// 		shorei_teate = shorei_teate - 0;

// 		//基準額（職責給　＋　年齢給　＋　固定基本給　＋　皆勤手当　＋　奨励手当）
// 		var kijun_gaku = shokuseki_kyu + kihon_kyu + kotei_kihon_kyu + kaikin_teate + shorei_teate;

// 		for(var i=0; i<mst_koritu_zan_teate_rows.length; i++){
// 			//基準額（年齢給（＋「固定基本給」）＋職責給）を手当マスタの効率残業手当の基準額の範囲に照らして支給額を求める
// 			if((mst_koritu_zan_teate_rows[i]["nendo"] == nendo)&&(mst_koritu_zan_teate_rows[i]["teate_id"] == KORITU_ZAN_TEATE)&&(mst_koritu_zan_teate_rows[i]["kijungaku_kagen"] <= kijun_gaku)&&(kijun_gaku <= mst_koritu_zan_teate_rows[i]["kijungaku_jogen"])){
// 				shikyu_gaku = mst_koritu_zan_teate_rows[i]["sikyu_gaku"];
// 			}
// 		}
		
// 		//効率残業手当支給額と、残業手当を比較し、多い方を支給する
// 		//効率残業手当支給額の方が多い場合、こちらを支給し、「残業手当」の欄はゼロにする（そうしないと総支給額に計上されてしまう）
// 		if(zangyo_teate < shikyu_gaku){
// 			document.getElementById("korituzangyo_teate").innerText = numberFormat(shikyu_gaku);
// 			document.getElementById("zangyo_teate_gokei").innerText = 0;
			
// 			//残業手当がゼロより大きい場合は、「効率残業手当」の方が多いので、そちらを支給するから「残業手当」は無しの旨のコメント表示
// 			if(0 < zangyo_teate){
// 				//「残業手当」のタイトル部分にコメント挿入
// 				document.getElementById("zangyo_teate_msg").innerText = "※「効率残業手当」の方が多いので効率残業手当を支給し\n残業手当は支給されません。";
// 				document.getElementById("zangyo_teate_msg").style.display = "inline";
// 			}

// 		//残業手当の方が多い場合、こちらを支給し、「効率残業手当」の欄はゼロにする（そうしないと総支給額に計上されてしまう）
// 		}else{
// 			document.getElementById("korituzangyo_teate").innerText = 0;
// 			document.getElementById("zangyo_teate_gokei").innerText = numberFormat(zangyo_teate);
// 		}
// 		document.getElementById("korituzangyo_teate_gaitogaku").value = shikyu_gaku;	//「効率残業手当（該当額）」hiddenタグ
// 	}
// 	//退避用変数に画面と同じ値を退避する
// 	taihi_korituzangyo_teate = off_format_val(document.getElementById("korituzangyo_teate").innerText);	//効率残業手当（支給額）
// 	taihi_korituzangyo_teate_gaitogaku = shikyu_gaku;	//効率残業手当（該当額）
 }

//****************************************************************************
//　★再計算用★
//　「残業代の基礎とならない手当－合計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcZanNonkisoTotal(){

	var calc_zan_nonkiso_total = 0;
	//［家族手当］値取得
	var kazoku_teate = off_format_val(document.getElementById("kazoku_teate").innerText);
	kazoku_teate = kazoku_teate - 0;
	//［効率残業手当］値取得
	var korituzangyo_teate = off_format_val(document.getElementById("korituzangyo_teate").innerText);
	korituzangyo_teate = korituzangyo_teate - 0;

	calc_zan_nonkiso_total = kazoku_teate + korituzangyo_teate;
	
	document.getElementById("zan_nonkiso_total").innerText = numberFormat(calc_zan_nonkiso_total);
}

//****************************************************************************
//　★再計算用★
//　「年俸－昇給－年額」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcNempoShokyuNengaku(){
//test start
//alert("今から「年俸－昇給－年額」を再計算ね。");
//test end
	var nengaku = 0;
//	var zennen_nempo_nengaku = off_format_val(document.getElementById("zennen_nempo_nengaku").innerText);
//	zennen_nempo_nengaku = zennen_nempo_nengaku - 0;
	var nempo_shokyu_gaku = off_format_val(document.getElementById("nempo_shokyu_gaku").innerText);
	nempo_shokyu_gaku = nempo_shokyu_gaku - 0;
	var nempo_teisei_gaku = off_format_val(document.getElementById("nempo_teisei_gaku").innerText);
	nempo_teisei_gaku = nempo_teisei_gaku - 0;
	var nempo_tangan_gaku = off_format_val(document.getElementById("nempo_tangan_gaku").innerText);
	nempo_tangan_gaku = nempo_tangan_gaku - 0;

	var divide = 12;	//１２倍

	//「昇給訂正額」が入力されている場合は「評価点による昇給額」ではなくそちらを計上する
	//12倍して年額にして加算する
	if(0 < nempo_teisei_gaku){
		nempo_teisei_gaku = nempo_teisei_gaku * divide;
		nengaku = nengaku + nempo_teisei_gaku;
		document.getElementById("nempo_shokyu_nengaku").innerText = numberFormat(nengaku);
		return;
	}
	
	//「昇給嘆願額」が入力されている場合は「評価点による昇給額」でも「昇給訂正額」でもなくそちらを計上する
	//12倍して年額にして加算する
	if(0 < nempo_tangan_gaku){
		nempo_tangan_gaku = nempo_tangan_gaku * divide;
		nengaku = nengaku + nempo_tangan_gaku;
		document.getElementById("nempo_shokyu_nengaku").innerText = numberFormat(nengaku);
		return;
	}
	
	//「評価点による昇給額」がゼロより大きい場合は、12倍して前年度の職責給に加算
	if(0 < nempo_shokyu_gaku){
		nempo_shokyu_gaku = nempo_shokyu_gaku * divide;
		nengaku = nengaku + nempo_shokyu_gaku;
	}
	
	document.getElementById("nempo_shokyu_nengaku").innerText = numberFormat(nengaku);
	
}

//****************************************************************************
//　★再計算用★
//　「年俸－年額－合計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcNempoNengaku(){
	
	var nempo_nengaku = 0;
	
	//年俸－前年度年額
	var zennen_nempo_nengaku = off_format_val(document.getElementById("zennen_nempo_nengaku").innerText);
	zennen_nempo_nengaku = zennen_nempo_nengaku - 0;

	//年俸－昇給－年額
	var nempo_shokyu_nengaku = off_format_val(document.getElementById("nempo_shokyu_nengaku").innerText);
	nempo_shokyu_nengaku = nempo_shokyu_nengaku - 0;
	
	nempo_nengaku = zennen_nempo_nengaku + nempo_shokyu_nengaku;
	
	document.getElementById("nempo_nengaku").innerText = numberFormat(nempo_nengaku);

	//退避用変数に画面と同じ値を退避する
	taihi_nempo_nengaku = nempo_nengaku;

}

//****************************************************************************
//　★再計算用★
//　「12分割～／14分割～」の金額を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcNempoGetugaku(){
	
	var radio_14  = document.getElementById("cb_nempo_divide_14");
	var divide = 12;	//デフォルトは１２分割
	
	//「12分割～／14分割～」ラジオボタンの"１２分割"が選択されている場合
	//「年俸－年額合計（前年度年額＋昇給年額）」の１／１２を「12分割～／14分割～」の金額としてセット
	//※デフォルト値のままのdivideを使用
	
	//「12分割～／14分割～」ラジオボタンの"１４分割"が選択されている場合
	if(radio_14.checked){
		//「年俸－年額合計（前年度年額＋昇給年額）」の１／１４を「12分割～／14分割～」の金額としてセット
		divide = 14;
	}
	
	var nempo_nengaku = off_format_val(document.getElementById("nempo_nengaku").innerText);
	nempo_nengaku = nempo_nengaku - 0;
	var nempo_getugaku = nempo_nengaku / divide;
	nempo_getugaku = Math.ceil(nempo_getugaku);
	document.getElementById("nempo_getugaku").innerText = numberFormat(nempo_getugaku);
}

//****************************************************************************
//　★再計算用★
//　「支給額Ａ」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcShikyugakuA(){

	var calc_shikyugaku_a = 0;
	
	//月給の場合
	if(gekkyu_nempo_val == 0){
//	if(kyuyo_arr["gekkyu_nempo"] == 0){
		var zankiso_total = off_format_val(document.getElementById("zankiso_total").innerText);
		zankiso_total = zankiso_total - 0;
		var zan_nonkiso_total = off_format_val(document.getElementById("zan_nonkiso_total").innerText);
		zan_nonkiso_total = zan_nonkiso_total - 0;
		calc_shikyugaku_a = zankiso_total + zan_nonkiso_total;


	//年俸の場合
	}else if(gekkyu_nempo_val == 1){
//	}else if(kyuyo_arr["gekkyu_nempo"] == 1){
		var nempo_getugaku = off_format_val(document.getElementById("nempo_getugaku").innerText);
		nempo_getugaku = nempo_getugaku - 0;
		calc_shikyugaku_a = nempo_getugaku;
	}
	
	document.getElementById("shikyugaku_a").innerText = numberFormat(calc_shikyugaku_a);
}

//****************************************************************************
//　★再計算用★
//　「前年度との差」を画面上の値を用いて計算する。
//　月給の場合は［職責給］＋［効率残業手当］
//　年俸の場合は［うち固定残業代］＋［うち営業手当］
//　【引数】
//　　無し
//*****************************************************************************
function reCalcSagakuTeate(){
	var sagaku_teate = 0;
	//月給の場合
	if(gekkyu_nempo_val == 0){
		//該当年度分
		var shokuseki_kyu = off_format_val(document.getElementById("shokuseki_kyu").innerText);
		shokuseki_kyu = shokuseki_kyu - 0;
		var korituzangyo_teate = off_format_val(document.getElementById("korituzangyo_teate").innerText);
		korituzangyo_teate = korituzangyo_teate - 0;
		//前年度分
		var shokuseki_kyu_minus1 = off_format_val(document.getElementById("shokuseki_kyu_minus1").innerText);
		shokuseki_kyu_minus1 = shokuseki_kyu_minus1 - 0;
		var korituzangyo_teate_minus1 = off_format_val(document.getElementById("korituzangyo_teate_minus1").innerText);
		korituzangyo_teate_minus1 = korituzangyo_teate_minus1 - 0;

		sagaku_teate = (shokuseki_kyu + korituzangyo_teate) - (shokuseki_kyu_minus1 + korituzangyo_teate_minus1);
		
	//年俸の場合
	}else{
		//該当年度分
		var nempo_uchi_koteizang_dai = off_format_val(document.getElementById("nempo_uchi_koteizang_dai").value);
		nempo_uchi_koteizang_dai = nempo_uchi_koteizang_dai - 0;
		var nempo_uchi_eigyo_teate = off_format_val(document.getElementById("nempo_uchi_eigyo_teate").value);
		nempo_uchi_eigyo_teate = nempo_uchi_eigyo_teate - 0;
		//前年度分
		var nempo_uchi_koteizang_dai_minus1 = off_format_val(document.getElementById("nempo_uchi_koteizang_dai_minus1").innerText);
		nempo_uchi_koteizang_dai_minus1 = nempo_uchi_koteizang_dai_minus1 - 0;
		var nempo_uchi_eigyo_teate_minus1 = off_format_val(document.getElementById("nempo_uchi_eigyo_teate_minus1").innerText);
		nempo_uchi_eigyo_teate_minus1 = nempo_uchi_eigyo_teate_minus1 - 0;

		sagaku_teate = (nempo_uchi_koteizang_dai + nempo_uchi_eigyo_teate) - (nempo_uchi_koteizang_dai_minus1 + nempo_uchi_eigyo_teate_minus1);
	}
	document.getElementById("sagaku_teate").innerText = numberFormat(sagaku_teate);
}

//****************************************************************************
//　★再計算用★
//　「残業単価」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcZAZanTotal(){

	// 「残業代の基礎となる手当－合計」
	var zankiso_total = off_format_val(document.getElementById("zankiso_total").innerText);
	zankiso_total = zankiso_total - 0;

	// 「残業代の基礎となる手当－合計」を「１ヶ月平均所定労働時間」で割る（金額は小数点第一位を切り上げ）
	var zan_tanka = zankiso_total / rodo_per_month;
	zan_tanka = Math.ceil(zan_tanka);
	document.getElementById("zan_tanka").innerText = numberFormat(zan_tanka);
	
	//　残業時間入力モーダルの「残業単価」を更新
	// //*　利用者が運用管理者（経理課）、又はシステム管理者（情シス）の場合
	// if(USER_SHUBETU == '4' || USER_SHUBETU == '5'){
	// 	document.getElementById("kmd_zangyo_tanka").innerText = numberFormat(zan_tanka);

	// //*　利用者が一般ユーザーの場合
	// }else if(USER_SHUBETU == '1'){
		document.getElementById("md_zangyo_tanka_year").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_4").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_5").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_6").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_7").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_8").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_9").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_10").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_11").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_12").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_1").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_2").innerText = numberFormat(zan_tanka);
		document.getElementById("md_zangyo_tanka_3").innerText = numberFormat(zan_tanka);
	// }

	//退避用変数に画面と同じ値を退避する
	taihi_zangyo_tanka = zan_tanka;

}

//****************************************************************************
//　★再計算用★
//　「通常残業手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcTujoZanTeate(){

	//* 「残業単価」
	var zan_tanka = off_format_val(document.getElementById("zan_tanka").innerText);
	zan_tanka = zan_tanka - 0;
	zan_tanka = Math.ceil(zan_tanka);
	//* 「通常残業時間」
	var tujo_zan_jikan = document.getElementById("tujo_zan_jikan").innerText - 0;

	//* 「通常残業手当」を算出してセット
	//* （残業単価　×　（１＋0.25）　×　労働時間
	tujo_zan_teate =  zan_tanka * (1 + 0.25) * tujo_zan_jikan;
	tujo_zan_teate = Math.ceil(tujo_zan_teate);
	document.getElementById("tujo_zan_teate").innerText = numberFormat(tujo_zan_teate);
}

//****************************************************************************
//　★再計算用★
//　「深夜残業手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcSinyaZanTeate(){
	//* 「残業単価」
	var zan_tanka = off_format_val(document.getElementById("zan_tanka").innerText);
	zan_tanka = zan_tanka - 0;
	zan_tanka = Math.ceil(zan_tanka);
	//* 「深夜残業時間」
	sinya_zan_jikan = document.getElementById("sinya_zan_jikan").innerText - 0;

	//* 「深夜残業手当」をセット
	//* （残業単価　×　（１＋0.25）　×　労働時間
	sinya_zan_teate = zan_tanka * (1 + 0.25) * sinya_zan_jikan;
	sinya_zan_teate = Math.ceil(sinya_zan_teate);
	document.getElementById("sinya_zan_teate").innerText = numberFormat(sinya_zan_teate);
}

//****************************************************************************
//　★再計算用★
//　「休日労働手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKyujituRodoTeate(){
	//* 「残業単価」
	var zan_tanka = off_format_val(document.getElementById("zan_tanka").innerText);
	zan_tanka = zan_tanka - 0;
	zan_tanka = Math.ceil(zan_tanka);
	//* 「休日労働時間」をセット
	kyujitu_rodo_jikan = document.getElementById("kyujitu_rodo_jikan").innerText - 0;
	// 「残業代の基礎となる手当－合計」
	var zankiso_total = off_format_val(document.getElementById("zankiso_total").innerText);
	zankiso_total = zankiso_total - 0;

	//* 「休日労働手当」をセット
	//* （残業単価　×　（１＋0.35）　×　労働時間
	kyujitu_rodo_teate = zan_tanka * (1 + 0.35) * kyujitu_rodo_jikan;
	kyujitu_rodo_teate = Math.ceil(kyujitu_rodo_teate);
	document.getElementById("kyujitu_rodo_teate").innerText = numberFormat(kyujitu_rodo_teate);
}

//****************************************************************************
//　★再計算用★
//　「休日深夜労働手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKyujitusinyaRodoTeate(){
	//* 「残業単価」
	var zan_tanka = off_format_val(document.getElementById("zan_tanka").innerText);
	zan_tanka = zan_tanka - 0;
	zan_tanka = Math.ceil(zan_tanka);
	//* 「休日深夜労働時間」をセット
	kyujitusinya_rodo_jikan = document.getElementById("kyujitusinya_rodo_jikan").innerText - 0;
	// 「残業代の基礎となる手当－合計」
	var zankiso_total = off_format_val(document.getElementById("zankiso_total").innerText);
	zankiso_total = zankiso_total - 0;

	//* 「休日深夜労働手当」をセット
	//* （残業単価　×　（１＋0.35＋0.25）　×　労働時間
	kyujitusinya_rodo_teate = zan_tanka * (1 + 0.35 + 0.25) * kyujitusinya_rodo_jikan;
	kyujitusinya_rodo_teate = Math.ceil(kyujitusinya_rodo_teate);
	document.getElementById("kyujitusinya_rodo_teate").innerText = numberFormat(kyujitusinya_rodo_teate);
}


//****************************************************************************
//　★再計算用★
//　「総支給額」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcAZanTotal(){

	var calc_a_zan_total = 0;
	
	//［支給額Ａ］値取得
	var shikyugaku_a = off_format_val(document.getElementById("shikyugaku_a").innerText);
	shikyugaku_a = shikyugaku_a - 0;

	//［残業手当］値取得
	var zangyo_teate_gokei = off_format_val(document.getElementById("zangyo_teate_gokei").innerText);
	zangyo_teate_gokei = zangyo_teate_gokei - 0;
	
	calc_a_zan_total = shikyugaku_a + zangyo_teate_gokei;
	
	document.getElementById("a_zan_total").innerText = numberFormat(calc_a_zan_total);
}

//****************************************************************************
//　★再計算用★
//　「欠勤控除」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKekkinKojo(){

	var kekkin_kojo = 0;

	//月給の場合
	if(gekkyu_nempo_val == 0){

		var zankiso_total = off_format_val(document.getElementById("zankiso_total").innerText);	//残業代の基礎となる手当合計
		zankiso_total = zankiso_total - 0;
		var zan_nonkiso_total = off_format_val(document.getElementById("zan_nonkiso_total").innerText);	//残業代の基礎とならない手当合計
		zan_nonkiso_total = zan_nonkiso_total - 0;
		
		kekkin_kojo = zankiso_total + zan_nonkiso_total;	//「残業代の基礎となる手当合計」＋「残業代の基礎とならない手当」
		kekkin_kojo = kekkin_kojo / 24;						//１ヶ月の平均所定労働日数　で割る
		kekkin_kojo = Math.floor(kekkin_kojo);	//小数以下切り捨て
		
	//年俸の場合
	}else if(gekkyu_nempo_val == 1){
		
		var nempo_nengaku = off_format_val(document.getElementById("nempo_nengaku").innerText);
		var getugaku = nempo_nengaku / 12;
		getugaku = Math.ceil(nempo_nengaku);	//支給月額（小数切り上げ）
		kekkin_kojo = getugaku / 24;			//１ヶ月の平均所定労働日数　で割る
		kekkin_kojo = Math.floor(kekkin_kojo);	//小数以下切り捨て

	}

	document.getElementById("kekkin_kojo").innerText = numberFormat(kekkin_kojo);
}

//****************************************************************************
//　★再計算用★
//　「遅刻早退控除」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcChikokuSotaiKojo(){

	var chikoku_sotai_kojo = 0;

	//月給の場合
	if(gekkyu_nempo_val == 0){

		var zankiso_total = off_format_val(document.getElementById("zankiso_total").innerText);	//残業代の基礎となる手当合計
		zankiso_total = zankiso_total - 0;
		var zan_nonkiso_total = off_format_val(document.getElementById("zan_nonkiso_total").innerText);	//残業代の基礎とならない手当合計
		zan_nonkiso_total = zan_nonkiso_total - 0;
		
		chikoku_sotai_kojo = zankiso_total + zan_nonkiso_total;		//「残業代の基礎となる手当合計」＋「残業代の基礎とならない手当」
		chikoku_sotai_kojo = chikoku_sotai_kojo / rodo_per_month;	//１ヶ月の平均所定労働時間で割る
		chikoku_sotai_kojo = Math.floor(chikoku_sotai_kojo);	//小数以下切り捨て
		
	//年俸の場合
	}else if(gekkyu_nempo_val == 1){
		
		var nempo_nengaku = off_format_val(document.getElementById("nempo_nengaku").innerText);
		var getugaku = nempo_nengaku / 12;		//支給月額
		getugaku = Math.ceil(getugaku);	//支給月額（小数切り上げ）
		chikoku_sotai_kojo = getugaku / rodo_per_month;			//１ヶ月の平均所定労働時間で割る
		chikoku_sotai_kojo = Math.floor(chikoku_sotai_kojo);	//小数以下切り捨て

	}

	document.getElementById("chikoku_sotai_kojo").innerText = numberFormat(chikoku_sotai_kojo);
}

//****************************************************************************
//　★再計算用★
//　差額列の「職責給－合計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcShokusekiKyuSagaku(){
	var shokuseki_kyu = off_format_val(document.getElementById("shokuseki_kyu").innerText);
	var shokuseki_kyu_minus1 = off_format_val(document.getElementById("shokuseki_kyu_minus1").innerText);

	if((shokuseki_kyu == "")||(shokuseki_kyu_minus1 == "")){
		document.getElementById("shokuseki_kyu_sagaku").innerText = "";
		return;
	}

	var shokuseki_kyu_sagaku = shokuseki_kyu - shokuseki_kyu_minus1;
	document.getElementById("shokuseki_kyu_sagaku").innerText = numberFormat(shokuseki_kyu_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「年齢給」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKihonkyuSagaku(){
	var kihon_kyu = off_format_val(document.getElementById("kihon_kyu").innerText);
	var kihon_kyu_minus1 = off_format_val(document.getElementById("kihon_kyu_minus1").innerText);

	if((kihon_kyu == "")||(kihon_kyu_minus1 == "")){
		document.getElementById("kihon_kyu_sagaku").innerText = "";
		return;
	}

	var kihon_kyu_sagaku = kihon_kyu - kihon_kyu_minus1;
	document.getElementById("kihon_kyu_sagaku").innerText = numberFormat(kihon_kyu_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「固定基本給」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKoteiKihonkyuSagaku(){
		var kotei_kihon_kyu = off_format_val(document.getElementById("kotei_kihon_kyu").value);
		var kotei_kihon_kyu_minus1 = off_format_val(document.getElementById("kotei_kihon_kyu_minus1").innerText);
	
		if((kotei_kihon_kyu == "")||(kotei_kihon_kyu_minus1 == "")){
			document.getElementById("kotei_kihon_kyu_sagaku").innerText = "";
			return;
		}
	
		var kotei_kihon_kyu_sagaku = kotei_kihon_kyu - kotei_kihon_kyu_minus1;
		document.getElementById("kotei_kihon_kyu_sagaku").innerText = numberFormat(kotei_kihon_kyu_sagaku);
	}
	
//****************************************************************************
//　★再計算用★
//　差額列の「皆勤手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKaikinTeateSagaku(){
	var kaikin_teate = off_format_val(document.getElementById("kaikin_teate").innerText);
	var kaikin_teate_minus1 = off_format_val(document.getElementById("kaikin_teate_minus1").innerText);

	if((kaikin_teate == "")||(kaikin_teate_minus1 == "")){
		document.getElementById("kaikin_teate_sagaku").innerText = "";
		return;
	}

	var kaikin_teate_sagaku = kaikin_teate - kaikin_teate_minus1;
	document.getElementById("kaikin_teate_sagaku").innerText = numberFormat(kaikin_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「都市手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcTosiTeateSagaku(){
	var tosi_teate = off_format_val(document.getElementById("tosi_teate").innerText);
	var tosi_teate_minus1 = off_format_val(document.getElementById("tosi_teate_minus1").innerText);

	if((tosi_teate == "")||(tosi_teate_minus1 == "")){
		document.getElementById("tosi_teate_sagaku").innerText = "";
		return;
	}

	var tosi_teate_sagaku = tosi_teate - tosi_teate_minus1;
	document.getElementById("tosi_teate_sagaku").innerText = numberFormat(tosi_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「奨励手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcShoreiTeateSagaku(){
	var shorei_teate = off_format_val(document.getElementById("shorei_teate").innerText);
	var shorei_teate_minus1 = off_format_val(document.getElementById("shorei_teate_minus1").innerText);

	if((shorei_teate == "")||(shorei_teate_minus1 == "")){
		document.getElementById("shorei_teate_sagaku").innerText = "";
		return;
	}

	var shorei_teate_sagaku = shorei_teate - shorei_teate_minus1;
	document.getElementById("shorei_teate_sagaku").innerText = numberFormat(shorei_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「調整手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcChoseiTeateSagaku(){
	var chosei_teate = off_format_val(document.getElementById("chosei_teate").value);
	var chosei_teate_minus1 = off_format_val(document.getElementById("chosei_teate_minus1").innerText);

	if((chosei_teate == "")||(chosei_teate_minus1 == "")){
		document.getElementById("chosei_teate_sagaku").innerText = "";
		return;
	}

	var chosei_teate_sagaku = chosei_teate - chosei_teate_minus1;
	document.getElementById("chosei_teate_sagaku").innerText = numberFormat(chosei_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「残業代の基礎となる手当－小計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcZankisoShokeiSagaku(){
	var zankiso_shokei = off_format_val(document.getElementById("zankiso_shokei").innerText);
	var zankiso_shokei_minus1 = off_format_val(document.getElementById("zankiso_shokei_minus1").innerText);

	if((zankiso_shokei == "")||(zankiso_shokei_minus1 == "")){
		document.getElementById("zankiso_shokei_sagaku").innerText = "";
		return;
	}

	var zankiso_shokei_sagaku = zankiso_shokei - zankiso_shokei_minus1;
	document.getElementById("zankiso_shokei_sagaku").innerText = numberFormat(zankiso_shokei_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「転勤住宅手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcTenkinJutakuTeateSagaku(){
	var tenkin_jutaku_teate = off_format_val(document.getElementById("tenkin_jutaku_teate").value);
	var tenkin_jutaku_teate_minus1 = off_format_val(document.getElementById("tenkin_jutaku_teate_minus1").innerText);

	if((tenkin_jutaku_teate == "")||(tenkin_jutaku_teate_minus1 == "")){
		document.getElementById("tenkin_jutaku_teate_sagaku").innerText = "";
		return;
	}

	var tenkin_jutaku_teate_sagaku = tenkin_jutaku_teate - tenkin_jutaku_teate_minus1;
	document.getElementById("tenkin_jutaku_teate_sagaku").innerText = numberFormat(tenkin_jutaku_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「単身赴任手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcTansinfuninTeateSagaku(){
	var tansinfunin_teate = off_format_val(document.getElementById("tansinfunin_teate").innerText);
//	var tansinfunin_teate = off_format_val(document.getElementById("tansinfunin_teate").value);
	var tansinfunin_teate_minus1 = off_format_val(document.getElementById("tansinfunin_teate_minus1").innerText);

	if((tansinfunin_teate == "")||(tansinfunin_teate_minus1 == "")){
		document.getElementById("tansinfunin_teate_sagaku").innerText = "";
		return;
	}

	var tansinfunin_teate_sagaku = tansinfunin_teate - tansinfunin_teate_minus1;
	document.getElementById("tansinfunin_teate_sagaku").innerText = numberFormat(tansinfunin_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「残業代の基礎となる手当－合計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcZankisoTotalSagaku(){
	var zankiso_total = off_format_val(document.getElementById("zankiso_total").innerText);
	var zankiso_total_minus1 = off_format_val(document.getElementById("zankiso_total_minus1").innerText);

	if((zankiso_total == "")||(zankiso_total_minus1 == "")){
		document.getElementById("zankiso_total_sagaku").innerText = "";
		return;
	}

	var zankiso_total_sagaku = zankiso_total - zankiso_total_minus1;
	document.getElementById("zankiso_total_sagaku").innerText = numberFormat(zankiso_total_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「月給－家族手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcKazokuTeateSagaku(){
	var kazoku_teate = off_format_val(document.getElementById("kazoku_teate").innerText);
	var kazoku_teate_minus1 = off_format_val(document.getElementById("kazoku_teate_minus1").innerText);

	if((kazoku_teate == "")||(kazoku_teate_minus1 == "")){
		document.getElementById("kazoku_teate_sagaku").innerText = "";
		return;
	}

	var kazoku_teate_sagaku = kazoku_teate - kazoku_teate_minus1;
	document.getElementById("kazoku_teate_sagaku").innerText = numberFormat(kazoku_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「年俸－家族手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcNempoKazokuTeateSagaku(){
	var kazoku_teate = off_format_val(document.getElementById("nempo_kazoku_teate").innerText);
	var kazoku_teate_minus1 = off_format_val(document.getElementById("nempo_kazoku_teate_minus1").innerText);

	if((kazoku_teate == "")||(kazoku_teate_minus1 == "")){
		document.getElementById("nempo_kazoku_teate_sagaku").innerText = "";
		return;
	}

	var kazoku_teate_sagaku = kazoku_teate - kazoku_teate_minus1;
	document.getElementById("nempo_kazoku_teate_sagaku").innerText = numberFormat(kazoku_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「効率残業手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalckorituzangyoTeateSagaku(){
	var korituzangyo_teate = off_format_val(document.getElementById("korituzangyo_teate").innerText);
	var korituzangyo_teate_minus1 = off_format_val(document.getElementById("korituzangyo_teate_minus1").innerText);

	if((korituzangyo_teate == "")||(korituzangyo_teate_minus1 == "")){
		document.getElementById("korituzangyo_teate_sagaku").innerText = "";
		return;
	}

	var korituzangyo_teate_sagaku = korituzangyo_teate - korituzangyo_teate_minus1;
	document.getElementById("korituzangyo_teate_sagaku").innerText = numberFormat(korituzangyo_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「残業代の基礎とならない手当－合計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcZanNonkisoTotalSagaku(){
	var zan_nonkiso_total = off_format_val(document.getElementById("zan_nonkiso_total").innerText);
	var zan_nonkiso_total_minus1 = off_format_val(document.getElementById("zan_nonkiso_total_minus1").innerText);

	if((zan_nonkiso_total == "")||(zan_nonkiso_total_minus1 == "")){
		document.getElementById("zan_nonkiso_total_sagaku").innerText = "";
		return;
	}

	var zan_nonkiso_total_sagaku = zan_nonkiso_total - zan_nonkiso_total_minus1;
	document.getElementById("zan_nonkiso_total_sagaku").innerText = numberFormat(zan_nonkiso_total_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「年俸－年額合計」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcNempoNengakuSagaku(){
	var nempo_nengaku = off_format_val(document.getElementById("nempo_nengaku").innerText);
	var nempo_nengaku_minus1 = off_format_val(document.getElementById("nempo_nengaku_minus1").innerText);

	if((nempo_nengaku == "")||(nempo_nengaku_minus1 == "")){
		document.getElementById("nempo_nengaku_sagaku").innerText = "";
		return;
	}

	var nempo_nengaku_sagaku = nempo_nengaku - nempo_nengaku_minus1;
	document.getElementById("nempo_nengaku_sagaku").innerText = numberFormat(nempo_nengaku_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「年俸－12で割った金額」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcNempoGetugakuSagaku(){
	var nempo_getugaku = off_format_val(document.getElementById("nempo_getugaku").innerText);
	var nempo_getugaku_minus1 = off_format_val(document.getElementById("nempo_getugaku_minus1").innerText);

	if((nempo_getugaku == "")||(nempo_getugaku_minus1 == "")){
		document.getElementById("nempo_getugaku_sagaku").innerText = "";
		return;
	}

	var nempo_getugaku_sagaku = nempo_getugaku - nempo_getugaku_minus1;
	document.getElementById("nempo_getugaku_sagaku").innerText = numberFormat(nempo_getugaku_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「うち固定残業代」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcUchiKoteizangyoDaiSagaku(){
	var nempo_uchi_koteizang_dai = off_format_val(document.getElementById("nempo_uchi_koteizang_dai").value);
	var nempo_uchi_koteizang_dai_minus1 = off_format_val(document.getElementById("nempo_uchi_koteizang_dai_minus1").innerText);

	if((nempo_uchi_koteizang_dai == "")||(nempo_uchi_koteizang_dai_minus1 == "")){
		document.getElementById("nempo_uchi_koteizang_dai_sagaku").innerText = "";
		return;
	}

	var nempo_uchi_koteizang_dai_sagaku = nempo_uchi_koteizang_dai - nempo_uchi_koteizang_dai_minus1;
	document.getElementById("nempo_uchi_koteizang_dai_sagaku").innerText = numberFormat(nempo_uchi_koteizang_dai_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「うち営業手当」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcUchiEigyoTeateSagaku(){
	var nempo_uchi_eigyo_teate = off_format_val(document.getElementById("nempo_uchi_eigyo_teate").value);
	var nempo_uchi_eigyo_teate_minus1 = off_format_val(document.getElementById("nempo_uchi_eigyo_teate_minus1").innerText);

	if((nempo_uchi_eigyo_teate == "")||(nempo_uchi_eigyo_teate_minus1 == "")){
		document.getElementById("nempo_uchi_eigyo_teate_sagaku").innerText = "";
		return;
	}

	var nempo_uchi_eigyo_teate_sagaku = nempo_uchi_eigyo_teate - nempo_uchi_eigyo_teate_minus1;
	document.getElementById("nempo_uchi_eigyo_teate_sagaku").innerText = numberFormat(nempo_uchi_eigyo_teate_sagaku);
}

//****************************************************************************
//　★再計算用★
//　差額列の「支給額Ａ」を画面上の値を用いて計算する。
//　【引数】
//　　無し
//*****************************************************************************
function reCalcShikyugakuASagaku(){
	var shikyugaku_a = off_format_val(document.getElementById("shikyugaku_a").innerText);
	var shikyugaku_a_minus1 = off_format_val(document.getElementById("shikyugaku_a_minus1").innerText);
	var shikyugaku_a_sagaku = shikyugaku_a - shikyugaku_a_minus1;
	document.getElementById("shikyugaku_a_sagaku").innerText = numberFormat(shikyugaku_a_sagaku);
}

//****************************************************************************
//　★職責給訂正モーダルのチェックボックスをクリックしたときの処理★
//　【引数】
//　　無し
//*****************************************************************************
	function teiseiInputIoChange(){
		var teisei = document.getElementById("teisei");
		var teisei_ran = document.getElementById("teisei_ran");
		if(teisei.checked){
			teisei_ran.disabled = false;
		}else{
			teisei_ran.value = "";
			teisei_ran.disabled = true;
		}
	}

//****************************************************************************
//　★「職責給訂正」モーダルクリア処理★
//　【引数】
//　　無し
//*****************************************************************************
	function clearTeiseiModal(){
		//チェックボックスをオフにする
		document.getElementById("teisei").checked = false;
		//金額入力欄をクリアして使用不可状態にする
		var teisei_ran = document.getElementById("teisei_ran");
		teisei_ran.value = "";
		teisei_ran.disabled = true;
	}

//****************************************************************************
//　★「職責給訂正」モーダルを、親画面に入力されたものと同じ状態にする処理★
//　【引数】
//　　無し
//*****************************************************************************
	function setTeiseiModal(){
		
		var teisei_gaku_val = off_format_val(document.getElementById("teisei_gaku").innerText);	//親画面の「昇給訂正」金額
		teisei_gaku_val = teisei_gaku_val - 0;
		var teisei = document.getElementById("teisei");
		var teisei_ran = document.getElementById("teisei_ran");

		//親画面に金額が設定済みの場合
		if(0 < teisei_gaku_val){
			//モーダルのチェックボックスをオフにする
			document.getElementById("teisei").checked = true;
			//モーダルの金額入力欄を入力可にする
			teisei_ran.disabled = false;
			//モーダルの金額入力欄に親画面の金額を入れる
			teisei_ran.value = teisei_gaku_val;

		//親画面の金額がゼロの場合
		}else{
			//モーダルのチェックボックスをオンにする
			document.getElementById("teisei").checked = false;
			//modalの金額入力欄を空にする
			teisei_ran.value = "";
			//モーダルの金額入力欄を使用不可にする
			teisei_ran.disabled = true;
		}
	}

//****************************************************************************
//　★職責給嘆願モーダルのチェックボックスをクリックしたときの処理★
//　【引数】
//　　無し
//*****************************************************************************
	function tanganInputIoChange(){
		var tangan = document.getElementById("tangan");
		var tangan_ran = document.getElementById("tangan_ran");
		var tangan_riyu = document.getElementById("tangan_riyu");
		if(tangan.checked){
			tangan_riyu.disabled = false;
			tangan_ran.disabled = false;
		}else{
//			alert("「職責給昇給嘆願」の入力内容を削除します。")
			tangan_riyu.innerHTML = "";
			tangan_riyu.value = "";
			tangan_ran.value = "";
			tangan_riyu.disabled = true;
			tangan_ran.disabled = true;
		}
	}

//****************************************************************************
//　★「職責給嘆願」モーダルクリア処理★
//　【引数】
//　　無し
//*****************************************************************************
	function clearTanganModal(){
		//チェックボックスをオフにする
		document.getElementById("tangan").checked = false;
		//「理由」と「昇給額」をクリアして使用不可状態にする
		var tangan_ran = document.getElementById("tangan_ran");
		var tangan_riyu = document.getElementById("tangan_riyu");
		tangan_riyu.innerHTML = "";
		tangan_riyu.value = "";
		tangan_ran.value = "";
		tangan_riyu.disabled = true;
		tangan_ran.disabled = true;
	}

//****************************************************************************
//　★「職責給嘆願」モーダルを、親画面に入力されたものと同じ状態にする処理★
//　【引数】
//　　無し
//*****************************************************************************
	function setTanganModal(){
		
		var tangan_gaku_val = off_format_val(document.getElementById("tangan_gaku").innerText);	//親画面の「昇給嘆願」金額
		tangan_gaku_val = tangan_gaku_val - 0;
		var tangan = document.getElementById("tangan");
		var tangan_ran = document.getElementById("tangan_ran");
		var tangan_riyu = document.getElementById("tangan_riyu");

		//親画面に金額が設定済みの場合
		if(0 < tangan_gaku_val){
			//モーダルのチェックボックスをオフにする
			tangan.checked = true;
			//モーダルの金額入力欄を入力可にする
			tangan_ran.disabled = false;
			//モーダルの金額入力欄に親画面の金額を入れる
			tangan_ran.value = tangan_gaku_val;

		//親画面の金額がゼロの場合
		}else{
			//モーダルのチェックボックスをオンにする
			tangan.checked = false;
			//modalの金額入力欄を空にする
			tangan_ran.value = "";
			//モーダルの金額入力欄を使用不可にする
			tangan_ran.disabled = true;
			//モーダルの「理由」欄を空にする
			tangan_riyu.innerHTML = "";
			tangan_riyu.value = "";
			//モーダルの「理由」欄を使用不可にする
			tangan_riyu.disabled = true;
		}
	}

//****************************************************************************
//　「年間休日日数」「１日あたりの労働時間」「１日あたりの休憩時間」モーダル
//　の優先選択ラジオボタンをクリックしたときの処理
//　【引数】
//　　無し
//*****************************************************************************
function nenkanKyujituYusenChange(){

	var nenkan_kyujitu_input = document.getElementById("nenkan_kyujitu_input");	//モーダルの「年間休日数」
	var rodo_jikan_per_day_input = document.getElementById("rodo_jikan_per_day_input");	//モーダルの「１日あたりの労働時間」
	var kyukei_jikan_per_day_input = document.getElementById("kyukei_jikan_per_day_input");	//モーダルの「１日あたりの休憩時間」

	//「年間休日日数」「１日あたりの労働時間」優先ラジオボタン
	var nissu_jikan_yusen = document.getElementsByName("nissu_jikan_yusen");
	
	//「年間休日日数」優先の場合
	if(nissu_jikan_yusen[0].checked){
		//「年間休日日数」入力欄を入力可能状態にする
		nenkan_kyujitu_input.readOnly = false;
		//「１日あたりの労働時間」入力欄を入力不可（readonly）状態にする
		rodo_jikan_per_day_input.readOnly = true;
		//「１日あたりの休憩時間」入力欄を入力可能状態にする
		kyukei_jikan_per_day_input.readOnly = false;

	//「１日あたりの労働時間」優先の場合
	}else if(nissu_jikan_yusen[1].checked){
		//「年間休日日数」入力欄を入力不可（readonly）状態にする
		nenkan_kyujitu_input.readOnly = true;
		//「１日あたりの労働時間」入力欄を入力可能状態にする
		rodo_jikan_per_day_input.readOnly = false;
		//「１日あたりの休憩時間」入力欄を入力可能状態にする
		kyukei_jikan_per_day_input.readOnly = false;
	}
}

//****************************************************************************
//　「年間休日日数」「１日あたりの労働時間」「１日あたりの休憩時間」モーダルの
//　「年間休日日数」を変更したときの処理
//　【処理内容】
//　　・入力チェック
//　　・「１日あたりの労働時間」を算出してモーダルにセット
//　【引数】
//　　無し
//*****************************************************************************
function chgNenkanNissu(){

	var nenkan_kyujitu_input = document.getElementById("nenkan_kyujitu_input").value;		//年間休日日数（オブジェクト）
	var nenkan_kyujitu_input_val = document.getElementById("nenkan_kyujitu_input").value;	//年間休日日数（値）
	var rodo_jikan_per_day_input = document.getElementById("rodo_jikan_per_day_input");		//１日あたりの労働時間（オブジェクト）

	//*「年間休日日数」が未入力の場合、処理終了
	if(nenkan_kyujitu_input_val == ""){
		return;
	}
	
	//入力チェック
	//（小数ＮＧ。空ＯＫ）
	if(!chkNenkanKyujitu()){
		return;
	}
	
	nenkan_kyujitu_input_val = nenkan_kyujitu_input_val - 0;	//文字列から数値に変換
	
	//* 「１日あたりの労働時間」算出
	//* 式：［週の最大労働時間］÷（（365　－　年間休日日数）÷［１年あたりの週の数］）
	var rodo_jikan = 40 / ((365 - nenkan_kyujitu_input_val) / WEEK_PER_YEAR);
	//* 小数点第三位以降は切り捨て
	rodo_jikan = rodo_jikan * 100;
	rodo_jikan = Math.floor(rodo_jikan);
	rodo_jikan = rodo_jikan / 100;
	//* モーダルに値をセット
	rodo_jikan_per_day_input.value = rodo_jikan;

}

//****************************************************************************
//　「年間休日日数」「１日あたりの労働時間」「１日あたりの休憩時間」モーダルの
//　「１日あたりの労働時間」を変更したときの処理
//　【処理内容】
//　　・入力チェック
//　　・「年間休日数」を算出してモーダルにセット
//　【引数】
//　　無し
//*****************************************************************************
function chgRodoJikanPerDay(){

	var nenkan_kyujitu_input = document.getElementById("nenkan_kyujitu_input");						//年間休日日数（オブジェクト）
	var rodo_jikan_per_day_input = document.getElementById("rodo_jikan_per_day_input");				//１日あたりの労働時間（オブジェクト）
	var rodo_jikan_per_day_input_val = document.getElementById("rodo_jikan_per_day_input").value;	//１日あたりの労働時間（値）
	
	//* 「１日あたりの労働時間」が未入力の場合、処理終了
	if(rodo_jikan_per_day_input_val == ""){
		return;
	}

	//入力チェック
	//１日あたりの労働時間
	//（小数ＯＫ。空ＯＫ）
	if(!chkRodoJikanPerDay()){
		return;
	}

	rodo_jikan_per_day_input_val = rodo_jikan_per_day_input_val - 0;	//文字列から数値に変換

	//* 「年間休日日数」算出
	//* 【式】
	//* 	［週の最大労働時間］÷［１日あたりの労働時間］＝　Ａ（１週間の出勤日数）
	//* 	Ａ　×　［１年あたりの週の数］　＝　Ｂ（年間出勤日数）
	//* 	365　－　Ｂ　＝　年間休日日数
	//* 	※小数は切り上げて整数に
	var nenkan_kyujitu = 365 - ((40 / rodo_jikan_per_day_input_val) * WEEK_PER_YEAR);
	//* モーダルに値をセット
	nenkan_kyujitu_input.value = Math.ceil(nenkan_kyujitu);
	
}

//****************************************************************************
//　「年間休日日数」「１日あたりの労働時間」「１日あたりの休憩時間」モーダルの
//　「１日あたりの休憩時間」を変更したときの処理
//　【引数】
//　　無し
//*****************************************************************************
function chgKyukeiJikanPerDay(){
	
	var kyukei_jikan_per_day_input = document.getElementById("kyukei_jikan_per_day_input");	　　　　　　//１日あたりの休憩時間（オブジェクト）
	var kyukei_jikan_per_day_input_val = document.getElementById("kyukei_jikan_per_day_input").value;	//１日あたりの休憩時間（値）
	kyukei_jikan_per_day_input_val = kyukei_jikan_per_day_input_val - 0;

	//*「１日あたりの休憩時間」が未入力の場合、処理終了
	if(kyukei_jikan_per_day_input_val == ""){
		return;
	}

	//入力チェック
	if(!chkKyukeiJikanPerDay()){
		return;
	}
	
}

//****************************************************************************
//　★「年間休日日数」「１日あたりの労働時間」「１日あたりの休憩時間」入力モーダル
//　　に入力された値を親画面にセットする処理★
//　【引数】
//　　無し
//*****************************************************************************
	function setNenkanNissuModal(){

		var nenkan_kyujitu = document.getElementById("nenkan_kyujitu");	//親画面の「年間休日数」
		var rodo_jikan_per_day = document.getElementById("rodo_jikan_per_day");	//親画面の「１日あたりの労働時間」
		var kyukei_jikan_per_day = document.getElementById("kyukei_jikan_per_day");	//親画面の「１日あたりの休憩時間」

//		//入力チェック
		//「年間休日日数」「１日あたりの労働時間」のチェック
		if(!chkNenkanNissuRodoJikanPerDayControl()){
			//入力チェックＮＧ
			return;
		}

		//「１日あたりの休憩時間」のチェック
		if(!chkKyukeiJikanPerDayControl()){
			//入力チェックＮＧ
			return;
		}
		
		var nenkan_kyujitu_input = document.getElementById("nenkan_kyujitu_input");	//モーダルの「年間休日数」
		var nenkan_kyujitu_input_val = nenkan_kyujitu_input.value;	//モーダルの「年間休日数」値
		var rodo_jikan_per_day_input = document.getElementById("rodo_jikan_per_day_input");	//モーダルの「１日あたりの労働時間」
		var rodo_jikan_per_day_input_val = rodo_jikan_per_day_input.value;	//モーダルの「１日あたりの労働時間」値
		var kyukei_jikan_per_day_input = document.getElementById("kyukei_jikan_per_day_input");	//モーダルの「１日あたりの休憩時間」
		var kyukei_jikan_per_day_input_val = kyukei_jikan_per_day_input.value;	//モーダルの「１日あたりの休憩時間」値

		nenkan_kyujitu_input_val = nenkan_kyujitu_input_val - 0;	//モーダルの「年間休日数」値を数値に変換
		rodo_jikan_per_day_input_val = rodo_jikan_per_day_input_val - 0;	//モーダルの「１日あたりの労働時間」値を数値に変換
		kyukei_jikan_per_day_input_val = kyukei_jikan_per_day_input_val - 0;	//モーダルの「１日あたりの休憩時間」値を数値に変換

		//「１ヶ月平均所定労働時間」を再取得して専用の変数の値を更新する
		rodo_per_month = getHeikinShoteiRodoJikan(nenkan_kyujitu_input_val, rodo_jikan_per_day_input_val);

		//親画面にセット
		nenkan_kyujitu.innerText = nenkan_kyujitu_input_val;
		rodo_jikan_per_day.innerText = rodo_jikan_per_day_input_val;
		kyukei_jikan_per_day.innerText = kyukei_jikan_per_day_input_val;

		//「年間休日日数」「１日あたりの労働時間」「１日あたりの休憩時間」がひとつでも変更されている場合
		//その値を既定値に戻す処理を起動するためのボタンを使用可能状態にする
		if((nenkan_kyujitu_input_val == keep_nenkan_kyujitu)&&
		(rodo_jikan_per_day_input_val == keep_rodo_jikan_per_day)&&
		(kyukei_jikan_per_day_input_val == keep_kyukei_jikan_per_day)){
			document.getElementById("btn_nenkan_kyujitu").disabled = true;
		}else{
			document.getElementById("btn_nenkan_kyujitu").disabled = false;
		}
	
		//画面の項目を再計算
		reCalc();

	}

//****************************************************************************
//　「年間休日数」「１日あたりの労働時間」「１日あたりの休憩時間」の入力チェック
//　　１．「年間休日数」「１日あたりの労働時間」が２つとも空白の場合はＯＫ（値は初期表示時の値に戻す）
//　　２．「年間休日数」「１日あたりの労働時間」が２つとも変更していない場合はＯＫ（どんな値であってもよい）
//　　３．「年間休日数」「１日あたりの労働時間」のどちらか一方が空白の場合はＮＧ
//　　４．「年間休日数」のニューメリックチェック
//　　５．「１日あたりの労働時間」のニューメリックチェック
//　【引数】
//　　無し
//*****************************************************************************
function chkNenkanNissuRodoJikanPerDayControl(){

	var nenkan_kyujitu_input_val = document.getElementById("nenkan_kyujitu_input").value;	//モーダルの「年間休日数」値
	var rodo_jikan_per_day_input_val = document.getElementById("rodo_jikan_per_day_input").value;	//モーダルの「１日あたりの労働時間」値

	//「年間休日数」「１日あたりの労働時間」の片方、もしくは両方が空白の場合はＯＫ（手で変更する前の値に戻す）
	if((nenkan_kyujitu_input_val == "")||(rodo_jikan_per_day_input_val == "")){
		document.getElementById("nenkan_kyujitu_input").value = document.getElementById("nenkan_kyujitu").innerText;
		document.getElementById("rodo_jikan_per_day_input").value = document.getElementById("rodo_jikan_per_day").innerText;
		return true;
	}

	//「年間休日数」「１日あたりの労働時間」が２つとも手入力で変更してない（退避データと一致する）場合はＯＫ
	if((nenkan_kyujitu_input_val == keep_nenkan_kyujitu)&&(rodo_jikan_per_day_input_val == keep_rodo_jikan_per_day)){
		return true;
	}

	//「年間休日数」「１日あたりの労働時間」のどちらか一方が空の場合はＮＧとし、モーダル再表示して入力を促す
	//* 「年間休日数」
	if(nenkan_kyujitu_input_val == ""){
		alert("「年間休日数」に半角の数字を入力してください。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("nenkan_kyujitu_input").focus();
		document.getElementById("nenkan_kyujitu_input").select();
		return false;
	}
	if(!chkNenkanKyujitu()){
		return false;
	}

	//* 「１日あたりの労働時間」
	if(rodo_jikan_per_day_input_val == ""){
		alert("「１日あたりの労働時間」に半角の数字を入力してください。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("rodo_jikan_per_day_input").focus();
		document.getElementById("rodo_jikan_per_day_input").select();
		return false;
	}
	if(!chkRodoJikanPerDay()){
		return false;
	}

	return true;
}

//****************************************************************************
//　「１日あたりの休憩時間」の入力チェック
//　　１．「１日あたりの休憩時間」が空白の場合はＯＫ（初期表示時の値に戻す）
//　　２．「１日あたりの休憩時間」が変更されていない場合はＯＫ（どんな値でもよい）
//　　３．「年間休日数」のニューメリックチェック
//　【引数】
//　　無し
//*****************************************************************************
function chkKyukeiJikanPerDayControl(){

	var kyukei_jikan_per_day_input_val = document.getElementById("kyukei_jikan_per_day_input").value;	//モーダルの「１日あたりの休憩時間」値

	//「１日あたりの休憩時間」が空白の場合は、初期表示時の値に戻す
	if(kyukei_jikan_per_day_input_val == ""){
		document.getElementById("kyukei_jikan_per_day_input").value = document.getElementById("kyukei_jikan_per_day").innerText;
		return true;
	}
	
	//「１日あたりの休憩時間」が変更されていない場合はＯＫ（初期表示時の値に戻す）
	if(kyukei_jikan_per_day_input_val == keep_kyukei_jikan_per_day){
		return true;
	}

	//* 「１日あたりの休憩時間」
	if(kyukei_jikan_per_day_input_val == ""){
		alert("「１日あたりの休憩時間」に半角の数字を入力してください。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("kyukei_jikan_per_day_input").focus();
		document.getElementById("kyukei_jikan_per_day_input").select();
		return false;
	}
	if(!chkKyukeiJikanPerDay()){
		return false;
	}
	
	return true;

}

//****************************************************************************
//　「年間休日数」「１日あたりの労働時間」「１日あたりの休憩時間」を
//　初期表示の状態（給与テーブルの値）に戻す処理
//　親画面とモーダルの２箇所ずつ
//　【引数】
//　　無し
//*****************************************************************************
function reDataNenkanKyujitu(){
	
	if(!confirm("「年間休日数」「１日あたりの労働時間」「１日あたりの休憩時間」\nの値を初期値に戻しますか？")){
		return;
	}

	//「年間休日数」をセット
	document.getElementById("nenkan_kyujitu").innerText = keep_nenkan_kyujitu;	//親画面
	document.getElementById("nenkan_kyujitu_input").value = keep_nenkan_kyujitu;	//モーダルの入力欄
	//「1日あたりの労働時間」をセット
	document.getElementById("rodo_jikan_per_day").innerText = keep_rodo_jikan_per_day;	//親画面
	document.getElementById("rodo_jikan_per_day_input").value = keep_rodo_jikan_per_day;	//モーダルの入力欄
	//「1日あたりの休憩時間」をセット
	document.getElementById("kyukei_jikan_per_day").innerText = keep_kyukei_jikan_per_day;	//親画面
	document.getElementById("kyukei_jikan_per_day_input").value = keep_kyukei_jikan_per_day;	//モーダルの入力欄

	//この処理を起動するボタンをdisabledにする
	document.getElementById("btn_nenkan_kyujitu").disabled = true;

	//画面の項目を再計算
	reCalc();

}

//****************************************************************************
//　「年間休日数」「１日あたりの労働時間」「１日あたりの休憩時間」入力モーダル
//　　の「年間休日数」の入力チェック
//　【引数】
//　　無し
//*****************************************************************************
function chkNenkanKyujitu(){

	var nenkan_kyujitu_input = document.getElementById("nenkan_kyujitu_input").value;

	//年間休日日数
	//（小数ＮＧ。空ＯＫ）
	if(!ckNum2(nenkan_kyujitu_input)){
		alert("「年間休日数」に半角の数字を入力してください。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("nenkan_kyujitu_input").focus();
		document.getElementById("nenkan_kyujitu_input").select();
		return false;
	}
	if(nenkan_kyujitu_input < 0){
		alert("「年間休日数」の入力値が小さすぎます。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("nenkan_kyujitu_input").focus();
		document.getElementById("nenkan_kyujitu_input").select();
		return false;
	}
	if(278 < nenkan_kyujitu_input){
		alert("「年間休日数」の入力値が大きすぎます。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("nenkan_kyujitu_input").focus();
		document.getElementById("nenkan_kyujitu_input").select();
		return false;
	}
	
	return true;
}

//****************************************************************************
//　「年間休日数」「１日あたりの労働時間」「１日あたりの休憩時間」入力モーダル
//　　の「１日あたりの労働時間」の入力チェック
//　【引数】
//　　無し
//*****************************************************************************
function chkRodoJikanPerDay(){

	var rodo_jikan_per_day_input = document.getElementById("rodo_jikan_per_day_input").value;

	//１日あたりの労働時間
	//（小数ＯＫ。空ＯＫ）
	if(!ckDecimal2(rodo_jikan_per_day_input)){
		alert("「１日あたりの労働時間」に半角の数字を入力してください。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("rodo_jikan_per_day_input").focus();
		document.getElementById("rodo_jikan_per_day_input").select();
		return false;
	}
	if(rodo_jikan_per_day_input < 5.8){
		alert("「１日あたりの労働時間」の入力値が小さすぎます。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("rodo_jikan_per_day_input").focus();
		document.getElementById("rodo_jikan_per_day_input").select();
		return false;
	}
	if(24 < rodo_jikan_per_day_input){
		alert("「１日あたりの労働時間」の入力値が多きすぎます。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("rodo_jikan_per_day_input").focus();
		document.getElementById("rodo_jikan_per_day_input").select();
		return false;
	}
	
	return true;
}

//****************************************************************************
//　「年間休日数」「１日あたりの労働時間」「１日あたりの休憩時間」入力モーダル
//　　の「１日あたりの休憩時間」の入力チェック
//　【引数】
//　　無し
//*****************************************************************************
function chkKyukeiJikanPerDay(){

	var kyukei_jikan_per_day_input = document.getElementById("kyukei_jikan_per_day_input").value;

	//１日あたりの休憩時間
	if(!ckNum2(kyukei_jikan_per_day_input)){	//（小数ＮＧ（分で入力だから）。空ＯＫ）
	// if(!ckDecimal2(kyukei_jikan_per_day_input)){	//（小数ＯＫ。空ＯＫ）
		alert("「１日あたりの休憩時間」に半角の整数を入力してください。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("kyukei_jikan_per_day_input").focus();
		document.getElementById("kyukei_jikan_per_day_input").select();
		return false;
	}
	if(1440 < kyukei_jikan_per_day_input){	//1440分＝24時間
	// if(24 < kyukei_jikan_per_day_input){
		alert("「１日あたりの休憩時間」の入力値が多きすぎます。");
		$('#nenkan_kyujitu_modal').modal();
		document.getElementById("kyukei_jikan_per_day_input").focus();
		document.getElementById("kyukei_jikan_per_day_input").select();
		return false;
	}
	
	return true;
}


//****************************************************************************
//　「昇給訂正」モーダル、「昇給嘆願」モーダルを閉じたときの処理
//　モーダルに入力された値を親画面に適用する。
//　（その他細かい制御）
//　【引数】
//　　文字列
//　　　昇給訂正モーダルからの処理の場合："teisei"
//　　　昇給訂正モーダルからの処理の場合："tangan"
//*****************************************************************************
function teiseiTekiyou(teisei_tangan){

	var teisei = document.getElementById("teisei");										//「昇給訂正」モーダルのチェックボックス
	var teisei_ran_val = off_format_val(document.getElementById("teisei_ran").value);	//「昇給訂正」モーダルに入力された金額
	teisei_ran_val = teisei_ran_val - 0;
	var tangan = document.getElementById("tangan");										//「昇給嘆願」モーダルのチェックボックス
	var tangan_ran_val = off_format_val(document.getElementById("tangan_ran").value);	//「昇給嘆願」モーダルに入力された金額
	tangan_ran_val = tangan_ran_val - 0;

	//ここで"月給"と"年俸"とを判定し、どちらにしても同じ変数に値を入れておく（その下の処理をひとつにするため）
	//"月給"の場合
	if(gekkyu_nempo_val == 0){
		var teisei_gaku = document.getElementById("teisei_gaku");		//親画面の「昇給訂正」タグ
		var teisei_gaku_val = off_format_val(teisei_gaku.innerText);	//親画面の「昇給訂正」金額
		var tangan_gaku = document.getElementById("tangan_gaku");		//親画面の「昇給嘆願」タグ
		var tangan_gaku_val = off_format_val(tangan_gaku.innerText);	//親画面の「昇給嘆願」金額

	//"年俸"の場合
	}else if(gekkyu_nempo_val == 1){
		var teisei_gaku = document.getElementById("nempo_teisei_gaku");		//親画面の「年俸－昇給－昇給訂正額（月給）」タグ
		var teisei_gaku_val = off_format_val(nempo_teisei_gaku.innerText);	//親画面の「昇給訂正」金額
		var tangan_gaku = document.getElementById("nempo_tangan_gaku");		//親画面の「年俸－昇給－昇給嘆願額（月給）」タグ
		var tangan_gaku_val = off_format_val(tangan_gaku.innerText);		//親画面の「昇給嘆願」金額
	}
	teisei_gaku_val = teisei_gaku_val - 0;
	tangan_gaku_val = tangan_gaku_val - 0;

	//「昇給訂正」モーダルを閉じたときの処理
	if(teisei_tangan == "teisei"){
		
		//モーダルのチェックボックスがオフ（金額未入力）、若しくはモーダルの金額欄が未入力の場合で
		//親画面の「昇給訂正」に金額が入ってる場合は親画面の「昇給訂正」をクリアする
		if((!teisei.checked)||(!teisei_ran_val)){

			//親画面の「昇給訂正額」に金額が入っている場合
			if(0 < teisei_gaku_val){
				alert("既に入力済みの昇給訂正金額をクリアします。")
				teisei_gaku.innerText = 0;
				//画面の項目を再計算
				reCalc();
			}
			//「昇給訂正」モーダルを未入力状態にする
			clearTeiseiModal();
			return;
		}
		
		//入力チェック
		//金額が空の場合はＯＫ
		teisei_ran_val = teisei_ran_val + "";
		if(!ckNum2(teisei_ran_val)){
			alert("金額欄には半角の数字を入力してください。");
			//モーダルをあるべき状態に戻す
			setTeiseiModal();
			$('#teisei_modal').modal();
			return;
		}

		
		//「昇給嘆願」が入力済みであった場合
		if(0 < tangan_gaku_val){

			//「昇給嘆願」の入力値をクリアし、「昇給訂正」の入力値を画面にセットする
			if(confirm("「昇給訂正」と「昇給嘆願」は、いずれか片方のみ入力可能です。\n「昇給嘆願」の入力値をクリアしますか？")){
				//「昇給嘆願」モーダルを未入力状態にする
				clearTanganModal();
				//「昇給嘆願」をクリアする
				tangan_gaku.innerText = 0;

			//「昇給嘆願」の入力値をそのまま活かし、「昇給訂正」の入力値は破棄する
			}else{
				//「昇給訂正」モーダルを未入力状態にする
				clearTeiseiModal();
				return;
			}
		}

	//「昇給嘆願」を入力した場合
	}else{
		//モーダルのチェックボックスがオフ（金額未入力）、若しくはモーダルの金額欄が未入力の場合で
		//親画面の「昇給嘆願」に金額が入ってる場合は親画面の「昇給嘆願」をクリアする
		if((!tangan.checked)||(!tangan_ran_val)){
			//親画面の「昇給嘆願」に金額が入っている場合
			if(0 < tangan_gaku_val){
				alert("既に入力済みの昇給嘆願金額をクリアします。")
				tangan_gaku.innerText = 0;
				//画面の項目を再計算
				reCalc();
			}
			//「昇給嘆願」モーダルを未入力状態にする
			clearTanganModal();
			return;
		}

		//入力チェック
		//金額が空の場合はＯＫ
		tangan_ran_val = tangan_ran_val + "";
		if(!ckNum2(tangan_ran_val)){
			alert("金額欄には半角の数字を入力してください。");
			//モーダルをあるべき状態に戻す
			setTanganModal();
			$('#tangan_modal').modal();
			return;
		}



		//モーダルのチェックボックスがオフ（金額未入力）の場合は処理終了
		if(!tangan.checked){
			return;
		}
		//モーダルの金額欄が未入力の場合、その旨メッセージ表示して終了
		if(!tangan_ran){
			alert("金額が未入力です");
			//「昇給嘆願」モーダルを未入力状態にする
			clearTanganModal();
			return;
		}
		
		//「昇給訂正」が入力済みであった場合
		if(0 < teisei_gaku_val){

			//「昇給訂正」の入力値をクリアし、「昇給嘆願」の入力値を画面にセットする
			if(confirm("「昇給訂正」と「昇給嘆願」は、いずれか片方のみ入力可能です。\n「昇給訂正」の入力値をクリアしますか？")){
				//「昇給訂正」モーダルを未入力状態にする
				clearTeiseiModal();
				teisei_gaku.innerText = 0;

			//「昇給訂正」の入力値をそのまま活かし、「昇給嘆願」の入力値は破棄する
			}else{
				//「昇給嘆願」モーダルを未入力状態にする
				clearTanganModal();
				return;
			}
		}

	}
	
	var input_kingaku = off_format_val(document.getElementById(teisei_tangan + "_ran").value);
	input_kingaku = input_kingaku - 0;

	//入力金額を親画面に適用
	//月給の場合
	if(gekkyu_nempo_val == 0){
		document.getElementById(teisei_tangan + "_gaku").innerText = numberFormat(input_kingaku);

	//年俸の場合
	}else if(gekkyu_nempo_val == 1){
		document.getElementById("nempo_" + teisei_tangan + "_gaku").innerText = numberFormat(input_kingaku);
	}

	
	//画面の項目を再計算
	reCalc();
}

//****************************************************************************
//　残業時間入力モーダル（一般ユーザー等用）
//　「年合計入力／各月入力」切り替え処理
//　「年合計入力」を選択したときの処理
//　【引数】
//　　無し
//*****************************************************************************
function zangyoChgNen(){

	//「各月毎に入力された残業時間や手当はクリアされますが\nよろしいですか？」をconfirmで
	if(!confirm("各月毎に入力された残業時間や手当はクリアされますが\nよろしいですか？")){
		return;
	}

	//「年合計」の列の値はそのままで各月行の入力欄をゼロにする
	//かつ、各月の行の入力欄を　readOnly　にする
	for(var i=1; i<=12; i++){
		document.getElementById("md_tujo_zan_jikan_" + i).value = 0;		//通常残業時間
		document.getElementById("md_tujo_zan_teate_" + i).innerText = 0;	//通常残業手当
		document.getElementById("md_sinya_zan_jikan_" + i).value = 0;		//深夜残業時間
		document.getElementById("md_sinya_zan_teate_" + i).innerText = 0;	//深夜残業手当
		document.getElementById("md_kyujitu_rodo_jikan_" + i).value = 0;		//休日労働時間
		document.getElementById("md_kyujitu_rodo_teate_" + i).innerText = 0;	//休日労働手当
		document.getElementById("md_kyujitusinya_rodo_jikan_" + i).value = 0;		//休日深夜労働時間
		document.getElementById("md_kyujitusinya_rodo_teate_" + i).innerText = 0;	//休日深夜労働手当
		document.getElementById("md_zangyo_teate_gokei_" + i).innerText = 0;		//残業手当（合計）

		document.getElementById("md_tujo_zan_jikan_" + i).readOnly = true;				//通常残業時間
		document.getElementById("md_sinya_zan_jikan_" + i).readOnly = true;			//深夜残業時間
		document.getElementById("md_kyujitu_rodo_jikan_" + i).readOnly = true;			//休日労働時間
		document.getElementById("md_kyujitusinya_rodo_jikan_" + i).readOnly = true;	//休日深夜労働時間
	}
	
	//「年合計」行の入力欄を入力可能状態にする
	document.getElementById("md_tujo_zan_jikan_year").readOnly = false;			//通常残業時間
	document.getElementById("md_sinya_zan_jikan_year").readOnly = false;			//深夜残業時間
	document.getElementById("md_kyujitu_rodo_jikan_year").readOnly = false;		//休日労働時間
	document.getElementById("md_kyujitusinya_rodo_jikan_year").readOnly = false;	//休日深夜労働時間

	//カーソルを年合計の「通情残業時間」にフォーカス
	document.getElementById("md_tujo_zan_jikan_year").focus();			//「通常残業時間」にフォーカス

}

//****************************************************************************
//　残業時間入力モーダル（一般ユーザー等用）
//　「年合計入力／各月入力」切り替え処理
//　「各月入力」を選択したときの処理
//　【引数】
//　　無し
//*****************************************************************************
function zangyoChgTuki(){

	//「年合計行の値は、各月の合計値に再計算されますが\nよろしいですか？」をconfirm
	if(!confirm("年合計行の値は、各月の合計値に再計算されますが\nよろしいですか？")){
		return;
	}

	//各月の行の入力欄を入力可能状態にする
	for(var j=1; j<=12; j++){
		document.getElementById("md_tujo_zan_jikan_" + j).readOnly = false;			//通常残業時間
		document.getElementById("md_sinya_zan_jikan_" + j).readOnly = false;			//深夜残業時間
		document.getElementById("md_kyujitu_rodo_jikan_" + j).readOnly = false;		//休日労働時間
		document.getElementById("md_kyujitusinya_rodo_jikan_" + j).readOnly = false;	//休日深夜労働時間
	}

	//各月の行の値はそのまま。
	//「年合計」行は入力欄をクリアし、各月行の合計値を計算して入れる。
	document.getElementById("md_tujo_zan_jikan_year").value = 0;		//通常残業時間
	document.getElementById("md_sinya_zan_jikan_year").value = 0;		//深夜残業時間
	document.getElementById("md_kyujitu_rodo_jikan_year").value = 0;		//休日労働時間
	document.getElementById("md_kyujitusinya_rodo_jikan_year").value = 0;		//休日深夜労働時間
	zangyoModalRecalc();
	
	//「年合計」の行の入力欄を　readOnly　にする
	document.getElementById("md_tujo_zan_jikan_year").readOnly = true;				//通常残業時間
	document.getElementById("md_sinya_zan_jikan_year").readOnly = true;			//深夜残業時間
	document.getElementById("md_kyujitu_rodo_jikan_year").readOnly = true;			//休日労働時間
	document.getElementById("md_kyujitusinya_rodo_jikan_year").readOnly = true;	//休日深夜労働時間

	//カーソルを４月の「通情残業時間」にフォーカス
	document.getElementById("md_tujo_zan_jikan_4").focus();			//「通常残業時間」にフォーカス

}

//****************************************************************************
//　残業時間入力モーダル（一般ユーザー等用）の入力チェック
//　【引数】
//　　無し
//*****************************************************************************
function chkZangyoModal(){

	//金額が空の場合はＯＫ
	var tujo_zan_jikan_year = document.getElementById("md_tujo_zan_jikan_year").value;
	var sinya_zan_jikan_year = document.getElementById("md_sinya_zan_jikan_year").value;
	var kyujitu_rodo_jikan_year = document.getElementById("md_kyujitu_rodo_jikan_year").value;
	var kyujitusinya_rodo_jikan_year = document.getElementById("md_kyujitusinya_rodo_jikan_year").value;
	//通常残業時間（年合計）
	if(!ckDecimal2(tujo_zan_jikan_year)){
		alert("半角の数字を入力してください。");
		$('#zangyo_modal').modal();
		return false;
	}
	//深夜残業時間（年合計）
	if(!ckDecimal2(sinya_zan_jikan_year)){
		alert("半角の数字を入力してください。");
		$('#zangyo_modal').modal();
		return false;
	}
	//休日労働時間（年合計）
	if(!ckDecimal2(kyujitu_rodo_jikan_year)){
		alert("半角の数字を入力してください。");
		$('#zangyo_modal').modal();
		return false;
	}
	//休日深夜労働時間（年合計）
	if(!ckDecimal2(kyujitusinya_rodo_jikan_year)){
		alert("半角の数字を入力してください。");
		$('#zangyo_modal').modal();
		return false;
	}

	
	var month = 4;
	for(var i=0; i<12; i++){

		if(!ckDecimal2(document.getElementById("md_tujo_zan_jikan_" + month).value)){
			alert("半角の数字を入力してください。");
			$('#zangyo_modal').modal();
			return false;
		}

		if(!ckDecimal2(document.getElementById("md_sinya_zan_jikan_" + month).value)){
			alert("半角の数字を入力してください。");
			$('#zangyo_modal').modal();
			return false;
		}

		if(!ckDecimal2(document.getElementById("md_kyujitu_rodo_jikan_" + month).value)){
			alert("半角の数字を入力してください。");
			$('#zangyo_modal').modal();
			return false;
		}

		if(!ckDecimal2(document.getElementById("md_kyujitusinya_rodo_jikan_" + month).value)){
			alert("半角の数字を入力してください。");
			$('#zangyo_modal').modal();
			return false;
		}

		if(12 <= month){
			month = 1;
		}else{
			month++;
		}
	}
	
	return true;
}

//****************************************************************************
//　残業時間入力モーダル（一般ユーザー用）の中の
//　年合計行の項目を手で更新した後の処理
//　全項目を再計算する。
//　【引数】
//　　無し
//*****************************************************************************
function zangyoModalNenGokeiRecalc(){

	//入力チェック
	if(!chkZangyoModal()){
		return;
	}

	var zangyo_tanka = 0;
	var tujo_zan_jikan = 0;
	var tujo_zan_teate = 0;
	var sinya_zan_jikan = 0;
	var sinya_zan_teate = 0;
	var kyujitu_rodo_jikan = 0;
	var kyujitu_rodo_teate = 0;
	var kyujitusinya_rodo_jikan = 0;
	var kyujitusinya_rodo_teate = 0;

	var tujo_zan_teate_year = 0;
	var sinya_zan_teate_year = 0;
	var kyujitu_rodo_teate_year = 0;
	var kyujitusinya_rodo_teate_year = 0;
	var zangyo_teate_gokei = 0;

	zangyo_tanka = off_format_val(document.getElementById("md_zangyo_tanka_year").innerText);	//残業単価
	//「通常残業手当」算出
	tujo_zan_jikan = document.getElementById("md_tujo_zan_jikan_year").value;	//通常残業時間
	tujo_zan_teate = zangyo_tanka * ( 1 + 0.25 ) * tujo_zan_jikan;
	tujo_zan_teate = Math.ceil(tujo_zan_teate);
	document.getElementById("md_tujo_zan_teate_year").innerText = numberFormat(tujo_zan_teate);
	
	//「深夜残業手当」算出
	sinya_zan_jikan = document.getElementById("md_sinya_zan_jikan_year").value;	//深夜残業時間
	sinya_zan_teate = zangyo_tanka * ( 1 + 0.25 ) * sinya_zan_jikan;
	sinya_zan_teate = Math.ceil(sinya_zan_teate);
	document.getElementById("md_sinya_zan_teate_year").innerText = numberFormat(sinya_zan_teate);
	
	//「休日労働手当」算出
	kyujitu_rodo_jikan = document.getElementById("md_kyujitu_rodo_jikan_year").value;	//休日労働時間
	kyujitu_rodo_teate = zangyo_tanka * ( 1 + 0.35 ) * kyujitu_rodo_jikan;
	kyujitu_rodo_teate = Math.ceil(kyujitu_rodo_teate);
	document.getElementById("md_kyujitu_rodo_teate_year").innerText = numberFormat(kyujitu_rodo_teate);
	
	//「休日深夜労働手当」算出
	kyujitusinya_rodo_jikan = document.getElementById("md_kyujitusinya_rodo_jikan_year").value;	//休日深夜労働時間
	kyujitusinya_rodo_teate = zangyo_tanka * ( 1 + 0.35 + 0.25 ) * kyujitusinya_rodo_jikan;
	kyujitusinya_rodo_teate = Math.ceil(kyujitusinya_rodo_teate);
	document.getElementById("md_kyujitusinya_rodo_jikan_year").innerText = numberFormat(kyujitusinya_rodo_teate);
	
	//「残業手当」算出（合計）
	zangyo_teate_gokei = tujo_zan_teate + sinya_zan_teate + kyujitu_rodo_teate + kyujitusinya_rodo_teate;
	zangyo_teate_gokei = Math.ceil(zangyo_teate_gokei);
	document.getElementById("md_zangyo_teate_gokei_year").innerText = numberFormat(zangyo_teate_gokei);

}

//****************************************************************************
//　残業時間入力モーダル（一般ユーザー用）の中の
//　各月の行の項目を手で更新した後の処理
//　全項目を再計算する。
//　【引数】
//　　無し
//*****************************************************************************
function zangyoModalRecalc(){

	//入力チェック
	if(!chkZangyoModal()){
		return;
	}

	var zangyo_tanka = 0;
	var tujo_zan_jikan = 0;
	var tujo_zan_teate = 0;
	var sinya_zan_jikan = 0;
	var sinya_zan_teate = 0;
	var kyujitu_rodo_jikan = 0;
	var kyujitu_rodo_teate = 0;
	var kyujitusinya_rodo_jikan = 0;
	var kyujitusinya_rodo_teate = 0;

	var tujo_zan_jikan_year = 0;
	var tujo_zan_teate_year = 0;
	var sinya_zan_jikan_year = 0;
	var sinya_zan_teate_year = 0;
	var kyujitu_rodo_jikan_year = 0;
	var kyujitu_rodo_teate_year = 0;
	var kyujitusinya_rodo_jikan_year = 0;
	var kyujitusinya_rodo_teate_year = 0;
	var zangyo_teate_gokei_year = 0;

	//各月の手当項目を算出する
	for(var i=1; i<=12; i++){

		zangyo_tanka = off_format_val(document.getElementById("md_zangyo_tanka_" + i).innerText);	//残業単価
		//「通常残業手当」算出
		tujo_zan_jikan = document.getElementById("md_tujo_zan_jikan_" + i).value;	//通常残業時間
		tujo_zan_jikan = tujo_zan_jikan - 0;
		tujo_zan_teate = zangyo_tanka * ( 1 + 0.25 ) * tujo_zan_jikan;
		tujo_zan_teate = Math.ceil(tujo_zan_teate);
		document.getElementById("md_tujo_zan_teate_" + i).innerText = numberFormat(tujo_zan_teate);
		//* 年合計に加算
		tujo_zan_jikan_year = tujo_zan_jikan_year + tujo_zan_jikan;
		tujo_zan_teate_year = tujo_zan_teate_year + tujo_zan_teate;
		
		//「深夜残業手当」算出
		sinya_zan_jikan = document.getElementById("md_sinya_zan_jikan_" + i).value;	//深夜残業時間
		sinya_zan_jikan = sinya_zan_jikan - 0;
		sinya_zan_teate = zangyo_tanka * ( 1 + 0.25 ) * sinya_zan_jikan;
		sinya_zan_teate = Math.ceil(sinya_zan_teate);
		document.getElementById("md_sinya_zan_teate_" + i).innerText = numberFormat(sinya_zan_teate);
		//* 年合計に加算
		sinya_zan_jikan_year = sinya_zan_jikan_year + sinya_zan_jikan;
		sinya_zan_teate_year = sinya_zan_teate_year + sinya_zan_teate;
		
		//「休日労働手当」算出
		kyujitu_rodo_jikan = document.getElementById("md_kyujitu_rodo_jikan_" + i).value;	//休日労働時間
		kyujitu_rodo_jikan = kyujitu_rodo_jikan - 0;
		kyujitu_rodo_teate = zangyo_tanka * ( 1 + 0.35 ) * kyujitu_rodo_jikan;
		kyujitu_rodo_teate = Math.ceil(kyujitu_rodo_teate);
		document.getElementById("md_kyujitu_rodo_teate_" + i).innerText = numberFormat(kyujitu_rodo_teate);
		//* 年合計に加算
		kyujitu_rodo_jikan_year = kyujitu_rodo_jikan_year + kyujitu_rodo_jikan;
		kyujitu_rodo_teate_year = kyujitu_rodo_teate_year + kyujitu_rodo_teate;
		
		//「休日深夜労働手当」算出
		kyujitusinya_rodo_jikan = document.getElementById("md_kyujitusinya_rodo_jikan_" + i).value;	//休日深夜労働時間
		kyujitusinya_rodo_jikan = kyujitusinya_rodo_jikan - 0;
		kyujitusinya_rodo_teate = zangyo_tanka * ( 1 + 0.35 + 0.25 ) * kyujitusinya_rodo_jikan;
		kyujitusinya_rodo_teate = Math.ceil(kyujitusinya_rodo_teate);
		document.getElementById("md_kyujitusinya_rodo_teate_" + i).innerText = numberFormat(kyujitusinya_rodo_teate);
		//* 年合計に加算
		kyujitusinya_rodo_jikan_year = kyujitusinya_rodo_jikan_year + kyujitusinya_rodo_jikan;
		kyujitusinya_rodo_teate_year = kyujitusinya_rodo_teate_year + kyujitusinya_rodo_teate;
		
		//「残業手当」算出（各月の合計）
		zangyo_teate_gokei = tujo_zan_teate + sinya_zan_teate + kyujitu_rodo_teate + kyujitusinya_rodo_teate;
		zangyo_teate_gokei = Math.ceil(zangyo_teate_gokei);
		document.getElementById("md_zangyo_teate_gokei_" + i).innerText = numberFormat(zangyo_teate_gokei);
		//* 年合計に加算
		zangyo_teate_gokei_year = zangyo_teate_gokei_year + zangyo_teate_gokei;
		
	}
	
	//** jsでの小数計算で発生してしまう誤差の対応処理
	tujo_zan_jikan_year = jsShosuGosaTaio(tujo_zan_jikan_year);
	sinya_zan_jikan_year = jsShosuGosaTaio(sinya_zan_jikan_year);
	kyujitu_rodo_jikan_year = jsShosuGosaTaio(kyujitu_rodo_jikan_year);
	kyujitusinya_rodo_jikan_year = jsShosuGosaTaio(kyujitusinya_rodo_jikan_year);

	//年合計行に値をセット
	//* 通情残業時間
		document.getElementById("md_tujo_zan_jikan_year").value = tujo_zan_jikan_year;
	//* 深夜残業時間
		document.getElementById("md_sinya_zan_jikan_year").value = sinya_zan_jikan_year;
	//* 休日労働時間
		document.getElementById("md_kyujitu_rodo_jikan_year").value = kyujitu_rodo_jikan_year;
	//* 休日深夜労働時間
		document.getElementById("md_kyujitusinya_rodo_jikan_year").value = kyujitusinya_rodo_jikan_year;

	//* 通情残業手当
		document.getElementById("md_tujo_zan_teate_year").innerText = numberFormat(tujo_zan_teate_year);
	//* 深夜残業手当
		document.getElementById("md_sinya_zan_teate_year").innerText = numberFormat(sinya_zan_teate_year);
	//* 休日労働手当
		document.getElementById("md_kyujitu_rodo_teate_year").innerText = numberFormat(kyujitu_rodo_teate_year);
	//* 休日深夜労働手当
		document.getElementById("md_kyujitusinya_rodo_teate_year").innerText = numberFormat(kyujitusinya_rodo_teate_year);
	//* 残業手当（合計）
		document.getElementById("md_zangyo_teate_gokei_year").innerText = numberFormat(zangyo_teate_gokei_year);
	
}

//****************************************************************************
//　残業時間入力モーダル（一般ユーザー等用）を閉じたときの処理
//　モーダルで入力された値を親画面に適用する。
//　【引数】
//　　無し
//*****************************************************************************
function zangyoTekiyou(){

	//入力チェック
	if(!chkZangyoModal()){
		return;
	}

	//入力された値を親画面にセット
	//* モーダルの「通常残業時間」を12で割る。小数点第三位以降は切り捨て。
	var tujo_zan_jikan_year = document.getElementById("md_tujo_zan_jikan_year").value;
	tujo_zan_jikan_year = tujo_zan_jikan_year - 0;
	tujo_zan_jikan_year = tujo_zan_jikan_year / 12;
	tujo_zan_jikan_year = tujo_zan_jikan_year * 100;
	tujo_zan_jikan_year = Math.floor(tujo_zan_jikan_year);
	tujo_zan_jikan_year = tujo_zan_jikan_year / 100;

	//* モーダルの「通常残業手当」を12で割る。小数は切り上げ
	var tujo_zan_teate_year = off_format_val(document.getElementById("md_tujo_zan_teate_year").innerText);
	tujo_zan_teate_year = tujo_zan_teate_year - 0;
	tujo_zan_teate_year = tujo_zan_teate_year / 12;
	tujo_zan_teate_year = Math.ceil(tujo_zan_teate_year);

	//* モーダルの「深夜残業時間」を12で割る。小数点第三位以降は切り捨て。
	var sinya_zan_jikan_year = document.getElementById("md_sinya_zan_jikan_year").value;
	sinya_zan_jikan_year = sinya_zan_jikan_year - 0;
	sinya_zan_jikan_year = sinya_zan_jikan_year / 12;
	sinya_zan_jikan_year = sinya_zan_jikan_year * 100;
	sinya_zan_jikan_year = Math.floor(sinya_zan_jikan_year);
	sinya_zan_jikan_year = sinya_zan_jikan_year / 100;

	//* モーダルの「深夜残業手当」を12で割る。小数は切り上げ
	var sinya_zan_teate_year = off_format_val(document.getElementById("md_sinya_zan_teate_year").innerText);
	sinya_zan_teate_year = sinya_zan_teate_year - 0;
	sinya_zan_teate_year = sinya_zan_teate_year / 12;
	sinya_zan_teate_year = Math.ceil(sinya_zan_teate_year);

	//* モーダルの「休日労働時間」を12で割る。小数点第三位以降は切り捨て。
	var kyujitu_rodo_jikan_year = document.getElementById("md_kyujitu_rodo_jikan_year").value;
	kyujitu_rodo_jikan_year = kyujitu_rodo_jikan_year - 0;
	kyujitu_rodo_jikan_year = kyujitu_rodo_jikan_year / 12;
	kyujitu_rodo_jikan_year = kyujitu_rodo_jikan_year * 100;
	kyujitu_rodo_jikan_year = Math.floor(kyujitu_rodo_jikan_year);
	kyujitu_rodo_jikan_year = kyujitu_rodo_jikan_year / 100;

	//* モーダルの「休日労働手当」を12で割る。小数は切り上げ
	var kyujitu_rodo_teate_year = off_format_val(document.getElementById("md_kyujitu_rodo_teate_year").innerText);
	kyujitu_rodo_teate_year = kyujitu_rodo_teate_year - 0;
	kyujitu_rodo_teate_year = kyujitu_rodo_teate_year / 12;
	kyujitu_rodo_teate_year = Math.ceil(kyujitu_rodo_teate_year);

	//* モーダルの「休日深夜労働時間」を12で割る。小数点第三位以降は切り捨て。
	var kyujitusinya_rodo_jikan_year = document.getElementById("md_kyujitusinya_rodo_jikan_year").value;
	kyujitusinya_rodo_jikan_year = kyujitusinya_rodo_jikan_year - 0;
	kyujitusinya_rodo_jikan_year = kyujitusinya_rodo_jikan_year / 12;
	kyujitusinya_rodo_jikan_year = kyujitusinya_rodo_jikan_year * 100;
	kyujitusinya_rodo_jikan_year = Math.floor(kyujitusinya_rodo_jikan_year);
	kyujitusinya_rodo_jikan_year = kyujitusinya_rodo_jikan_year / 100;

	//* モーダルの「休日深夜労働手当」を12で割る。小数は切り上げ
	var kyujitusinya_rodo_teate_year = off_format_val(document.getElementById("md_kyujitusinya_rodo_teate_year").innerText);
	kyujitusinya_rodo_teate_year = kyujitusinya_rodo_teate_year - 0;
	kyujitusinya_rodo_teate_year = kyujitusinya_rodo_teate_year / 12;
	kyujitusinya_rodo_teate_year = Math.ceil(kyujitusinya_rodo_teate_year);

	//* モーダルの「残業手当」を12で割る。小数は切り上げ
	var zangyo_teate_gokei_year = off_format_val(document.getElementById("md_zangyo_teate_gokei_year").innerText);
	zangyo_teate_gokei_year = zangyo_teate_gokei_year - 0;
	zangyo_teate_gokei_year = zangyo_teate_gokei_year / 12;
	zangyo_teate_gokei_year = Math.ceil(zangyo_teate_gokei_year);

	document.getElementById("tujo_zan_jikan").innerText = tujo_zan_jikan_year;					//通常残業時間
	document.getElementById("sinya_zan_jikan").innerText = sinya_zan_jikan_year;				//深夜残業時間
	document.getElementById("kyujitu_rodo_jikan").innerText = kyujitu_rodo_jikan_year					//休日労働時間
	document.getElementById("kyujitusinya_rodo_jikan").innerText = kyujitusinya_rodo_jikan_year			//休日深夜労働時間
	document.getElementById("zangyo_teate_gokei").innerText = numberFormat(zangyo_teate_gokei_year);			//残業手当

	//「残業手当」のタイトル部分に、支給される場合の残業手当金額を表示
	document.getElementById("zangyo_teate_disp").innerText = numberFormat(zangyo_teate_gokei_year);

	//画面の項目を再計算
	reCalc();
}

//****************************************************************************
//　残業時間入力モーダル（管理ユーザー等用）の入力チェック
//　【引数】
//　　無し
//*****************************************************************************
// function chkKanriZangyoModal(){

// 	var kmd_tsujo_zan_jikan = document.getElementById("kmd_tsujo_zan_jikan").value;
// 	var kmd_sinya_zan_jikan = document.getElementById("kmd_sinya_zan_jikan").value;
// 	var kmd_kyujitu_rodo_jikan = document.getElementById("kmd_kyujitu_rodo_jikan").value;
// 	var kmd_kyujitusinya_rodo_jikan = document.getElementById("kmd_kyujitusinya_rodo_jikan").value;
// 	//通常残業時間
// 	if(!ckDecimal2(kmd_tsujo_zan_jikan)){
// 		alert("半角の数字を入力してください。");
// 		$('#kanri_zangyo_modal').modal();
// 		return false;
// 	}
// 	//深夜残業時間
// 	if(!ckDecimal2(kmd_sinya_zan_jikan)){
// 		alert("半角の数字を入力してください。");
// 		$('#kanri_zangyo_modal').modal();
// 		return false;
// 	}
// 	//休日労働時間
// 	if(!ckDecimal2(kmd_kyujitu_rodo_jikan)){
// 		alert("半角の数字を入力してください。");
// 		$('#kanri_zangyo_modal').modal();
// 		return false;
// 	}
// 	//休日深夜労働時間
// 	if(!ckDecimal2(kmd_kyujitusinya_rodo_jikan)){
// 		alert("半角の数字を入力してください。");
// 		$('#kanri_zangyo_modal').modal();
// 		return false;
// 	}

	
// 	return true;
// }

//****************************************************************************
//　残業時間入力モーダル（管理ユーザー用）の中の項目を手で更新した後の処理
//　モーダル内の項目を再計算する。
//　【引数】
//　　無し
//*****************************************************************************
// function kanriZangyoModalRecalc(){

// 	//入力チェック
// 	if(!chkKanriZangyoModal()){
// 		return;
// 	}


// 	var zangyo_tanka = 0;
// 	var tujo_zan_jikan = 0;
// 	var tujo_zan_teate = 0;
// 	var sinya_zan_jikan = 0;
// 	var sinya_zan_teate = 0;
// 	var kyujitu_rodo_jikan = 0;
// 	var kyujitu_rodo_teate = 0;
// 	var kyujitusinya_rodo_jikan = 0;
// 	var kyujitusinya_rodo_teate = 0;

// 	var tujo_zan_teate_year = 0;
// 	var sinya_zan_teate_year = 0;
// 	var kyujitu_rodo_teate_year = 0;
// 	var kyujitusinya_rodo_teate_year = 0;
// 	var zangyo_teate_gokei = 0;

// 	zangyo_tanka = off_format_val(document.getElementById("kmd_zangyo_tanka").innerText);	//残業単価

// 	//「通常残業手当」算出
// 	tujo_zan_jikan = document.getElementById("kmd_tsujo_zan_jikan").value;	//通常残業時間
// 	tujo_zan_jikan = tujo_zan_jikan - 0;
// 	tujo_zan_teate = zangyo_tanka * ( 1 + 0.25 ) * tujo_zan_jikan;
// 	tujo_zan_teate = Math.ceil(tujo_zan_teate);
// 	document.getElementById("kmd_tsujo_zan_teate").innerText = numberFormat(tujo_zan_teate);
	
// 	//「深夜残業手当」算出
// 	sinya_zan_jikan = document.getElementById("kmd_sinya_zan_jikan").value;	//深夜残業時間
// 	sinya_zan_jikan = sinya_zan_jikan - 0;
// 	sinya_zan_teate = zangyo_tanka * ( 1 + 0.25 ) * sinya_zan_jikan;
// 	sinya_zan_teate = Math.ceil(sinya_zan_teate);
// 	document.getElementById("kmd_sinya_zan_teate").innerText = numberFormat(sinya_zan_teate);
	
// 	//「休日労働手当」算出
// 	kyujitu_rodo_jikan = document.getElementById("kmd_kyujitu_rodo_jikan").value;	//休日労働時間
// 	kyujitu_rodo_jikan = kyujitu_rodo_jikan - 0;
// 	kyujitu_rodo_teate = zangyo_tanka * ( 1 + 0.35 ) * kyujitu_rodo_jikan;
// 	kyujitu_rodo_teate = Math.ceil(kyujitu_rodo_teate);
// 	document.getElementById("kmd_kyujitu_rodo_teate").innerText = numberFormat(kyujitu_rodo_teate);
	
// 	//「休日深夜労働手当」算出
// 	kyujitusinya_rodo_jikan = document.getElementById("kmd_kyujitusinya_rodo_jikan").value;	//休日深夜労働時間
// 	kyujitusinya_rodo_jikan = kyujitusinya_rodo_jikan - 0;
// 	kyujitusinya_rodo_teate = zangyo_tanka * ( 1 + 0.35 + 0.25 ) * kyujitusinya_rodo_jikan;
// 	kyujitusinya_rodo_teate = Math.ceil(kyujitusinya_rodo_teate);
// 	document.getElementById("kmd_kyujitusinya_rodo_teate").innerText = numberFormat(kyujitusinya_rodo_teate);

// 	//「残業手当」算出（合計）
// 	zangyo_teate_gokei = tujo_zan_teate + sinya_zan_teate + kyujitu_rodo_teate + kyujitusinya_rodo_teate;
// 	zangyo_teate_gokei = Math.ceil(zangyo_teate_gokei);
// 	document.getElementById("kmd_zangyo_teate_gokei").innerText = numberFormat(zangyo_teate_gokei);

// }


//****************************************************************************
//　残業時間入力モーダル（管理者用用）を閉じたときの処理
//　モーダルで入力された値を親画面に適用する。
//　【引数】
//　　無し
//*****************************************************************************
// function kanriZangyoTekiyou(){

// 	//入力チェック
// 	if(!chkKanriZangyoModal()){
// 		return;
// 	}
	
// 	//入力された値を親画面にセット
// 	var tujo_zan_jikan_year = document.getElementById("kmd_tsujo_zan_jikan").value;
// 	tujo_zan_jikan_year = tujo_zan_jikan_year - 0;

// 	//* モーダルの「深夜残業時間」を12で割る。小数点第三位以降は切り捨て。
// 	var sinya_zan_jikan_year = document.getElementById("kmd_sinya_zan_jikan").value;
// 	sinya_zan_jikan_year = sinya_zan_jikan_year - 0;

// 	//* モーダルの「休日労働時間」を12で割る。小数点第三位以降は切り捨て。
// 	var kyujitu_rodo_jikan_year = document.getElementById("kmd_kyujitu_rodo_jikan").value;
// 	kyujitu_rodo_jikan_year = kyujitu_rodo_jikan_year - 0;

// 	//* モーダルの「休日深夜労働時間」を12で割る。小数点第三位以降は切り捨て。
// 	var kyujitusinya_rodo_jikan_year = document.getElementById("kmd_kyujitusinya_rodo_jikan").value;
// 	kyujitusinya_rodo_jikan_year = kyujitusinya_rodo_jikan_year - 0;

// 	document.getElementById("tujo_zan_jikan").innerText = tujo_zan_jikan_year;					//通常残業時間
// 	document.getElementById("sinya_zan_jikan").innerText = sinya_zan_jikan_year;				//深夜残業時間
// 	document.getElementById("kyujitu_rodo_jikan").innerText = kyujitu_rodo_jikan_year					//休日労働時間
// 	document.getElementById("kyujitusinya_rodo_jikan").innerText = kyujitusinya_rodo_jikan_year			//休日深夜労働時間
	
// 	//画面の項目を再計算
// 	reCalc();
// }

//**********************************************************
//***** 「勤務地」を初期表示時の選択値に戻す処理
//**********************************************************
function kimmuchiShokichi(){
	
	if(!confirm("初期値に戻しますか？")){
		return;
	}
	
	var sel_kimmuchi = document.getElementById("sel_kimmuchi");
	
	//* 選択値を変更する
	var siten_cd_arr = [];
	if(sel_kimmuchi){
		for(i = 0; i < sel_kimmuchi.options.length; i++){
			siten_cd_arr = sel_kimmuchi.options[i].value.split(";");
			if (siten_cd_arr[0] == taihi_siten_cd){
				sel_kimmuchi[i].selected = true;
				break;
			}
		}
	}
	
	//再計算
	reCalc();
}

/***************************************************************/
/***** 画面を印刷                                           *****/
/***** 画面上部（スクロールしても動かない部分）は印刷できない   *****/
/***** ので画面とは違う印刷用のタグを印刷                     *****/
/***************************************************************/
function gamen_image_print(){
	//画面表示のヘッダーを非表示にする
	document.getElementById("headerArea").setAttribute("class", "display_none");

	//ボタンエリアを非表示にする
	// document.getElementById("edit_tbl_karikakutei_btn_box").style.visibility = "hidden";
	// document.getElementById("sansho_tbl_karikakutei_btn_box").style.visibility = "hidden";

	//印刷用のタグを表示する
	document.getElementById("prt_header").setAttribute("class", "");

	//印刷用タグ内に確定表示関係の文字を入れる
	var nendo_kakutei_jotai = document.getElementById("nendo_kakutei_jotai").innerText;
	var kyuyodata_kakutei_jotai = document.getElementById("kyuyodata_kakutei_jotai").innerText;
	var saiteichingin_err_msg_top = document.getElementById("saiteichingin_err_msg_top").innerText;
	var prt_nendo_kakutei_jotai = document.getElementById("prt_nendo_kakutei_jotai");
	var prt_kyuyodata_kakutei_jotai = document.getElementById("prt_kyuyodata_kakutei_jotai");
	var prt_saiteichingin_err_msg_top = document.getElementById("prt_saiteichingin_err_msg_top");
	prt_nendo_kakutei_jotai.innerText = "";
	prt_kyuyodata_kakutei_jotai.innerText = "";
	prt_saiteichingin_err_msg_top.innerText = "";
	if(nendo_kakutei_jotai){
		prt_nendo_kakutei_jotai.innerText = nendo_kakutei_jotai;
	}
	if(kyuyodata_kakutei_jotai){
		prt_kyuyodata_kakutei_jotai.innerText = kyuyodata_kakutei_jotai;
	}
	if(saiteichingin_err_msg_top){
		prt_saiteichingin_err_msg_top.innerText = saiteichingin_err_msg_top;
	}



	//ヘッダーの高さ調整
	//headAreaが非表示になっているのでbody部分の高さ調整してheadAreaの領域分上に詰める
	//そうしないと印刷時に上にスペースが空く。
	disp_head_body();

	//印刷
	window.print();

	//印刷用タグ内に確定表示関係の文字を削除
	prt_nendo_kakutei_jotai.innerText = "";
	prt_kyuyodata_kakutei_jotai.innerText = "";
	prt_saiteichingin_err_msg_top.innerText = "";


	//印刷用のタグを非表示にする
	document.getElementById("prt_header").setAttribute("class", "display_none");

	//画面表示のヘッダーを表示する（もとに戻すのさ）
	document.getElementById("headerArea").setAttribute("class", "navbar navbar-default navbar-fixed-top back_color_02");

	//ボタンエリアを表示する
	// document.getElementById("edit_tbl_karikakutei_btn_box").style.visibility = "visible";
	// document.getElementById("sansho_tbl_karikakutei_btn_box").style.visibility = "visible";

	
	//ヘッダーの高さ調整
	disp_head_body();

}

//**********************************************************
//***** 表示中の社員を仮確定状態にする処理
//**********************************************************
function kariKakutei(){

	var sel_jugyoin = document.getElementById("sel_jugyoin");
	var wk_sel_jugyoin_value = sel_jugyoin.options[sel_jugyoin.selectedIndex].value;
	var sel_jugyoin_value = wk_sel_jugyoin_value.split(";");

	if(confirm("表示中の社員の給与データを仮確定します。\nよろしいですか？")){
		var SendParam = "val_parm=" + nendo + "~" + sel_jugyoin_value[0];
		sendRequest(SendParam, 'POST', 'server_kyuyo_input_karikakutei_update.php', true);
	}

}

//**********************************************************
//***** 表示中の社員を仮確定解除（「未確定」状態に）する処理
//**********************************************************
function kariKakuteiKaijo(){

	var sel_jugyoin = document.getElementById("sel_jugyoin");
	var wk_sel_jugyoin_value = sel_jugyoin.options[sel_jugyoin.selectedIndex].value;
	var sel_jugyoin_value = wk_sel_jugyoin_value.split(";");

	if(confirm("表示中の社員の給与データを「未確定」状態にします。\nよろしいですか？")){
		var SendParam = "val_parm=" + nendo + "~" + sel_jugyoin_value[0];
		sendRequest(SendParam, 'POST', 'server_kyuyo_input_karikakutei_kaijo_update.php', true);
	}

}

//**********************************************************
//***** 表示中の社員を確定状態にする処理
//**********************************************************
function kakutei(){

	var sel_jugyoin = document.getElementById("sel_jugyoin");
	var wk_sel_jugyoin_value = sel_jugyoin.options[sel_jugyoin.selectedIndex].value;
	var sel_jugyoin_value = wk_sel_jugyoin_value.split(";");

	if(confirm("表示中の社員の給与データを確定します。\nよろしいですか？")){
		var SendParam = "val_parm=" + nendo + "~" + sel_jugyoin_value[0];
		sendRequest(SendParam, 'POST', 'server_kyuyo_input_kakutei_update.php', true);
	}
}

//**********************************************************
//***** 表示中の社員を確定解除（「仮確定」状態に）する処理
//**********************************************************
function kakuteiKaijo(){

	var sel_jugyoin = document.getElementById("sel_jugyoin");
	var wk_sel_jugyoin_value = sel_jugyoin.options[sel_jugyoin.selectedIndex].value;
	var sel_jugyoin_value = wk_sel_jugyoin_value.split(";");

	if(confirm("表示中の社員の給与データを「仮確定」状態にします。\nよろしいですか？")){
		var SendParam = "val_parm=" + nendo + "~" + sel_jugyoin_value[0];
		sendRequest(SendParam, 'POST', 'server_kyuyo_input_kakutei_kaijo_update.php', true);
	}

}

//****************************************************************************
//　画面に入力した値をデータベースに登録する
//　規定に反している項目がある場合は、その旨のメッセージを表示する。
//　【引数】
//　　
//*****************************************************************************
function regist(){
	
	//一般ユーザーログイン時、「勤務地」が変更されている場合はＤＢに登録されている値（初期値）
	//に戻さないとここでの登録処理はできない。
	//「勤務地」はシミュレーションでのみ変更可能な項目で、他の項目の値も変更される。
	//だからもとに戻さないとここでは登録処理ＮＧ。
	//運用管理ユーザー、システム管理ユーザーの場合は登録可。
	var obj = document.getElementById("sel_kimmuchi");
	var sel_kimmuchi = obj.options[obj.selectedIndex].value.split(";"); // プルダウンで選択してある勤務地
	if(USER_SHUBETU == 1){
		if(sel_kimmuchi[0] !== taihi_siten_cd){
			alert("「勤務地」が変更されているため、登録できません。\n登録する場合は「初期値」ボタンで値を戻してください。\n※「都市手当」に影響します。");
			return;
		}
	}

	//ラジオボタンで「残業手当を適用する」が選択されている場合はＤＢ登録不可。
	var cb_zangyo_tekiyo_y = document.getElementById("cb_zangyo_tekiyo_y");
	var cb_zangyo_tekiyo_n = document.getElementById("cb_zangyo_tekiyo_n");
	if(cb_zangyo_tekiyo_y.checked){
		//”ＯＫ”がクリックされた場合、
		//（「職責」が主任以下で（「効率残業手当」＜「残業手当」）の場合でも「残業手当」ではなく「効率残業手当」が支給される形で再計算してＤＢへ登録する処理を続行）
		if(confirm("『残業手当を適用する』が選択された状態では登録できません。\n『残業手当を適用しない』の状態に変更して登録しますがよろしいですか？")){
			//ラジオボタンで”残業手当を適用しない”が選択された状態にする
			cb_zangyo_tekiyo_n.checked = true;
			//再計算処理
			reCalc();
			
		//”キャンセル”がクリックされた場合
		}else{
			//処理を抜ける
			return false;
		}
	}


	//以下３項目が画面上で変更されている場合、登録できない。
	//「年間休日」
	//「１日あたりの労働時間」
	//「１にちあたりの休憩時間」
	var nenkan_kyujitu_input = document.getElementById("nenkan_kyujitu_input").value;
	var rodo_jikan_per_day_input = document.getElementById("rodo_jikan_per_day_input").value
	var kyukei_jikan_per_day_input = document.getElementById("kyukei_jikan_per_day_input").value

	if((nenkan_kyujitu_input != keep_nenkan_kyujitu)||
	 (rodo_jikan_per_day_input != keep_rodo_jikan_per_day)||
	 (kyukei_jikan_per_day_input != keep_kyukei_jikan_per_day)){
		alert("「年間休日」「１日あたりの労働時間」「１にちあたりの休憩時間」\nの３項目のいずれかひとつでも変更されている場合は登録できません。\n「規定値に戻す」ボタンで値を戻してから登録してください。")
		return;
	}
	
	//最低賃金未満の場合は登録不可
	if(!chkSaiteichingin()){

		if(gekkyu_nempo_val == GEKKYU_CODE){
			alert("「残業代の基礎となる手当ー合計」が最低賃金を下回っています。\n入力をやり直してください。");
		}else if(gekkyu_nempo_val == NEMPO_CODE){
			alert("最低賃金を下回っています。\n入力をやり直してください。");
		}
		return;
	}

	//画面の「月給・年俸」で"月給"を選択している場合、入力値が社内規定に反していないか確認
	if((gekkyu_nempo_val == 0)&&((err_flg_shokuseki_kyu == 1)||(err_flg_tenkin_jutaku_teate == 1))){
		if(!confirm("規定に反する入力値があります。画面の赤文字を確認してください。\nこのまま登録しますか？")){
			return false;
		}
	}
		
	//登録処理
	if(confirm("表示内容を登録します。よろしいですか？")){
		
		var reg_data_kyuyo_tbl = "";	//従業員給与テーブル更新データ
		var reg_data_zangyo_tbl = "";	//支店別残業時間テーブル更新データ（運用管理者ユーザー、システム管理者ユーザーの場合に使用）

		//☆０
		//職責コード
		var sel_duties = document.getElementById("sel_duties");
		reg_data_kyuyo_tbl += sel_duties.options[sel_duties.selectedIndex].value;	// プルダウンで選択した職責の値

		//☆１
		//勤務地コード
		reg_data_kyuyo_tbl += "~" + sel_kimmuchi[0];

		//☆２
		//単身赴任フラグ
		reg_data_kyuyo_tbl += "~" + taihi_tansinfunin_flg;

		//☆３
		//社保扶養フラグ
		reg_data_kyuyo_tbl += "~" + taihi_shaho_fuyo_flg;

		//☆４
		//月給・年俸（「月給・年俸」を更新する）
		//変数「gekkyu_nempo_val」に値（月給：０、年俸：１）が入っている。画面でラジオ切り替える度にchgGekkyuNempo()でセットされる。
		reg_data_kyuyo_tbl += "~" + gekkyu_nempo_val;


		//「年俸－賞与１」と「年俸－賞与２」に入れるデータを準備する
		var nempo_shoyo = 0;
		var divide_14 = document.getElementById("cb_nempo_divide_14");	//「１２割１４割」ラジオボタンのオブジェクト取得
		var shikyugaku_a = off_format_val(document.getElementById("shikyugaku_a").innerText);	//画面上の「支給額＿Ａ」の値を取得
		shikyugaku_a = shikyugaku_a - 0;

		//"月給"の場合
		if(gekkyu_nempo_val == 0){
			var teisei_gaku = off_format_val(document.getElementById("teisei_gaku").innerText);	//「昇給訂正額」
			var tangan_gaku = off_format_val(document.getElementById("tangan_gaku").innerText);	//「昇給嘆願額」
		//"年俸"の場合
		}else if(gekkyu_nempo_val == 1){
			var teisei_gaku = off_format_val(document.getElementById("nempo_teisei_gaku").innerText);	//「昇給訂正額」
			var tangan_gaku = off_format_val(document.getElementById("nempo_tangan_gaku").innerText);	//「昇給嘆願額」
			//１４分割の場合、
			if(divide_14.checked){
				nempo_shoyo = shikyugaku_a;
			}
		}

		//☆５
		//昇給訂正（「昇給訂正額」を更新する）
		teisei_gaku = teisei_gaku - 0;
		reg_data_kyuyo_tbl += "~" + teisei_gaku;

		//☆６
		//職責給－昇給嘆願（「昇給嘆願－昇給額」を更新する）
		tangan_gaku = tangan_gaku - 0;
		if(tangan_gaku){
			//職責昇給嘆願モーダルの「理由」（「昇給嘆願－理由」を更新する）
			reg_data_kyuyo_tbl += "~" + document.getElementById("tangan_riyu").value;
		}else{
			//職責昇給嘆願モーダルの「理由」（「昇給嘆願－理由」を更新する）
			reg_data_kyuyo_tbl += "~" 
		}
		//☆７
		//職責給－昇給嘆願（「昇給嘆願－昇給額」を更新する）
		reg_data_kyuyo_tbl += "~" + tangan_gaku;


		//☆８
		//職責給－合計（「月給－職責給」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_shokuseki_kyu;

		//☆９
		//年齢給（「月給－基本給」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_kihon_kyu;

		//☆１０
		//固定基本給（「月給－固定基本給」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_kotei_kihon_kyu;

		//☆１１
		//皆勤手当
		reg_data_kyuyo_tbl += "~" + taihi_kaikin_teate;
		
		//☆１２
		//都市手当（「月給－都市手当」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_tosi_teate;

		//☆１３
		//奨励手当
		reg_data_kyuyo_tbl += "~" + taihi_shorei_teate;

		//☆１４
		//調整手当（「月給－調整手当」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_chosei_teate;

		//☆１５
		//転勤住宅手当（「月給－転勤住宅手当」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_tenkin_jutaku_teate;

		//☆１６
		//単身赴任手当（「月給－単身赴任手当」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_tansinfunin_teate;

		//☆１７
		//家族手当（「月給－家族手当」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_kazoku_teate;

		//☆１８
		//効率残業手当（「月給－効率残業手当＿支給額」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_korituzangyo_teate;

		//☆１９
		//効率残業手当（「月給－効率残業手当＿該当額」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_korituzangyo_teate_gaitogaku;

		//☆２０
		//年俸－年額－合計（「年俸ー年額」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_nempo_nengaku;

		//☆２１
		//１２分割して毎月支給／１４分割して毎月と賞与時期に支給（「年俸－１２割１４割」を更新する）
		var divide = 12;
		if(divide_14.checked){
			divide = 14;
		}
		reg_data_kyuyo_tbl += "~" + divide;

		//☆２２
		//うち固定残業代（「年俸ーうち固定残業代」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_nempo_uchi_koteizang_dai;

		//☆２３
		//うち営業手当（「年俸ーうち営業手当」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_nempo_uchi_eigyo_teate;
		
		//☆２４
		//年俸－賞与１
		//"年俸"で"１４分割"の場合、画面上の「支給額＿Ａ」の値、それ以外の場合はゼロを登録する（上の方でデータ作成済み）
		reg_data_kyuyo_tbl += "~" + nempo_shoyo;
		
		//☆２５
		//年俸－賞与２
		//"年俸"で"１４分割"の場合、画面上の「支給額＿Ａ」の値、それ以外の場合はゼロを登録する（上の方でデータ作成済み）
		reg_data_kyuyo_tbl += "~" + nempo_shoyo;

		//☆２６
		//支給額Ａ（毎月の支給額）（「支給額Ａ（毎月の支給額）」を更新する）
		reg_data_kyuyo_tbl += "~" + shikyugaku_a;

		//☆２７
		//前年との差（「前年度との差額（手当）」を更新する）
		var sagaku_teate = off_format_val(document.getElementById("sagaku_teate").innerText);
		sagaku_teate = sagaku_teate - 0;
		reg_data_kyuyo_tbl += "~" + sagaku_teate;

		//☆２８
		//残業単価（「残業単価」を更新する）
		reg_data_kyuyo_tbl += "~" + taihi_zangyo_tanka;


		//☆２９
		//欠勤控除（「欠勤控除」を更新する）
		var kekkin_kojo = off_format_val(document.getElementById("kekkin_kojo").innerText);
		kekkin_kojo = kekkin_kojo - 0;
		reg_data_kyuyo_tbl += "~" + kekkin_kojo;

		//☆３０
		//遅刻早退控除（「遅刻早退控除」を更新する）
		var chikoku_sotai_kojo = off_format_val(document.getElementById("chikoku_sotai_kojo").innerText);
		chikoku_sotai_kojo = chikoku_sotai_kojo - 0;
		reg_data_kyuyo_tbl += "~" + chikoku_sotai_kojo;

		//☆３１
		//備考（「備考」を更新する）
		reg_data_kyuyo_tbl += "~" + document.getElementById("biko").value;
		
		//------------------------------------------------------------------------------------------------
		//*残業時間と手当は、ユーザー種別４or５の場合、支店別残業時間テーブルに登録（更新）する。
		//*ユーザー種別１の場合は従業員給与テーブル登録（更新）。
		//*ログインユーザーが一般ユーザー（ユーザー種別：１）の場合
		// if(USER_SHUBETU == 1){
			//☆３２
			//残業時間入力モーダルの４～３月の「通常残業時間」をセミコロン区切りでひとつの値にまとめる（「通常残業時間（月別）」を更新する）
			var md_tujo_zan_jikan_value = "";
			var i = 4;
			while(i !== 0){
				md_tujo_zan_jikan_value += ";" + document.getElementById("md_tujo_zan_jikan_" + i).value;
				if(i == 12){
					i = 1;
				}else if(i == 3){
					i = 0;
				}else{
					i++;
				}
			}
			reg_data_kyuyo_tbl += "~" + md_tujo_zan_jikan_value;

			//☆３３
			//残業時間入力モーダルの年合計の「通常残業時間」（「通常残業時間（年合計）」を更新する）
			var md_tujo_zan_jikan_year = document.getElementById("md_tujo_zan_jikan_year").value;
			md_tujo_zan_jikan_year = md_tujo_zan_jikan_year - 0;
			reg_data_kyuyo_tbl += "~" + md_tujo_zan_jikan_year;

			//☆３４
			//残業時間入力モーダルの４～３月の「深夜残業時間」をセミコロン区切りでひとつの値にまとめる（「深夜残業時間（月別）」を更新する）
			var md_sinya_zan_jikan_value = "";
			i=4;
			while(i !== 0){
				md_sinya_zan_jikan_value += ";" + document.getElementById("md_sinya_zan_jikan_" + i).value;
				if(i == 12){
					i = 1;
				}else if(i == 3){
					i = 0;
				}else{
					i++;
				}
			}
			reg_data_kyuyo_tbl += "~" + md_sinya_zan_jikan_value;

			//☆３５
			//残業時間入力モーダルの年合計の「深夜残業時間」（「深夜残業時間（年合計）」を更新する）
			var md_sinya_zan_jikan_year = document.getElementById("md_sinya_zan_jikan_year").value;
			md_sinya_zan_jikan_year = md_sinya_zan_jikan_year - 0;
			reg_data_kyuyo_tbl += "~" + md_sinya_zan_jikan_year;

			//☆３６
			//残業時間入力モーダルの４～３月の「休日労働時間」をセミコロン区切りでひとつの値にまとめる（「休日労働時間（月別）」を更新する）
			var md_kyujitu_rodo_jikan_value = "";
			i=4;
			while(i !== 0){
				md_kyujitu_rodo_jikan_value += ";" + document.getElementById("md_kyujitu_rodo_jikan_" + i).value;
				if(i == 12){
					i = 1;
				}else if(i == 3){
					i = 0;
				}else{
					i++;
				}
			}
			reg_data_kyuyo_tbl += "~" + md_kyujitu_rodo_jikan_value;

			//☆３７
			//残業時間入力モーダルの年合計の「休日労働時間」（「休日労働時間（年合計）」を更新する）
			var md_kyujitu_rodo_jikan_year = document.getElementById("md_kyujitu_rodo_jikan_year").value;
			md_kyujitu_rodo_jikan_year = md_kyujitu_rodo_jikan_year - 0;
			reg_data_kyuyo_tbl += "~" + md_kyujitu_rodo_jikan_year;

			//☆３８
			//残業時間入力モーダルの４～３月の「休日深夜労働時間」をセミコロン区切りでひとつの値にまとめる（「休日深夜労働時間（月別）」を更新する）
			var md_kyujitusinya_rodo_jikan_value = "";
			i=4;
			while(i !== 0){
				md_kyujitusinya_rodo_jikan_value += ";" + document.getElementById("md_kyujitusinya_rodo_jikan_" + i).value;
				if(i == 12){
					i = 1;
				}else if(i == 3){
					i = 0;
				}else{
					i++;
				}
			}
			reg_data_kyuyo_tbl += "~" + md_kyujitusinya_rodo_jikan_value;

			//☆３９
			//残業時間入力モーダルの年合計の「休日深夜労働時間」（「休日深夜労働時間（年合計）」を更新する）
			var md_kyujitusinya_rodo_jikan_year = document.getElementById("md_kyujitusinya_rodo_jikan_year").value;
			md_kyujitusinya_rodo_jikan_year = md_kyujitusinya_rodo_jikan_year - 0;
			reg_data_kyuyo_tbl += "~" + md_kyujitusinya_rodo_jikan_year;


		// //*ログインユーザーが運用管理者ユーザー（ユーザー種別：４）かシステム管理者ユーザー（ユーザー種別：５）の場合
		// }else if((USER_SHUBETU == 4) || (USER_SHUBETU == 5)){

		// 	//通常残業時間（支店別残業時間テーブルの「通常残業時間」を更新する）
		// 	var kmd_tsujo_zan_jikan = document.getElementById("kmd_tsujo_zan_jikan").value;
		// 	kmd_tsujo_zan_jikan = kmd_tsujo_zan_jikan - 0;
		// 	reg_data_zangyo_tbl = kmd_tsujo_zan_jikan;

		// 	//深夜残業時間（支店別残業時間テーブルの「深夜残業時間」を更新する）
		// 	var kmd_sinya_zan_jikan = document.getElementById("kmd_sinya_zan_jikan").value;
		// 	kmd_sinya_zan_jikan = kmd_sinya_zan_jikan - 0;
		// 	reg_data_zangyo_tbl += "~" + kmd_sinya_zan_jikan;

		// 	//休日労働時間（支店別残業時間テーブルの「休日労働時間」を更新する）
		// 	var kmd_kyujitu_rodo_jikan = document.getElementById("kmd_kyujitu_rodo_jikan").value;
		// 	kmd_kyujitu_rodo_jikan = kmd_kyujitu_rodo_jikan - 0;
		// 	reg_data_zangyo_tbl += "~" + kmd_kyujitu_rodo_jikan;

		// 	//休日深夜労働時間（支店別残業時間テーブルの「休日深夜労働時間」を更新する）
		// 	var kmd_kyujitusinya_rodo_jikan = document.getElementById("kmd_kyujitusinya_rodo_jikan").value;
		// 	kmd_kyujitusinya_rodo_jikan = kmd_kyujitusinya_rodo_jikan - 0;
		// 	reg_data_zangyo_tbl += "~" + kmd_kyujitusinya_rodo_jikan;

		// }


		//※「確定状態」は、［仮確定］ボタンや［確定］ボタンクリック時にリアルタイムでＤＢ更新するので、ここでは処理不要
		
		//従業員コード
		staff_code = document.getElementById("staff_code").value;
		
		//従業員マスタ更新データ　と　従業員給与テーブル更新データ　と　支店別残業時間テーブル更新データ　をサーカムフレックス（"^"）で結合する
		//※下でユーザー種別４or５の場合の残業時間データ（reg_data_zangyo_tbl）を渡しているが、server_kyuyo_input_kyuyodata_update.php で
		//※支店別残業時間テーブル（siten_zangyo_jikan_tbl）に登録はしていない。登録しない仕様。仕様変更に対応できるようにここでは渡している。
		//※ユーザー種別４or５（経理ユーザー）が画面で変更した残業時間は画面でのシミュレーションのみで、登録はしない仕様。
		//※ユーザー種別４or５（経理ユーザー）が画面で使う支店別の残照時間は、マスタメンテでのみ登録可能。
		var SendParam = "val_parm=" + nendo + "^" + staff_code + "^" + reg_data_kyuyo_tbl + "^" + reg_data_zangyo_tbl;
		sendRequest(SendParam, 'POST', 'server_kyuyo_input_kyuyodata_update.php', true);
	}
}

//##################################################
// Ajax
// Serverへデータを送信
//##################################################
function sendRequest(data, method, url, async) {

	objReq = createHttpRequest();
	if (objReq == false) {
		return null;
	}
	objReq.onreadystatechange = procReqChange;
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
function procReqChange() {
	if (objReq.readyState == 4) {				//4 = complete（読み込んだデータの解析完了、または失敗した。つまり処理が終わった） 

		if (objReq.status == 200) {				//成功!!
///			alert("データ削除 " + objReq.responseText);
			var svr_rtn = objReq.responseText;
///			SERVER_RETURN = eval(objReq.responseText);
///			var svr_rtn = eval(objReq.responseText);
			rtn = svr_rtn.split(";");

			if(rtn[0] == '0'){
				alert(rtn[1]);
				transition("kyuyo_input.php");
			}else{
				alert(svr_rtn);
			}

		} else {
			alert("ERROR: " + objReq.statusText);
		}
	}
}
