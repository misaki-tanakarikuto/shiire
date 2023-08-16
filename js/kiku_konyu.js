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

//========================================================================================================
//Ajax
//========================================================================================================
$(function(){
    //小窓の「作成」ボタンをクリックしてajax通信を行う
    $('#btn_make_newdate').click(function(){
		/** 日付入力チェック */
		var new_date = $('#new_chumon_konyu_date').val();
		if(!new_date){
			alert("注文購入日を選択してください。");
		}else{
			/** サーバーへ送るデータを作成する */
			var send_data = {'chumon_konyu_date' : new_date};
			// var send_data = [{'chumon_konyu_date' : new_date}];
			// let send_data = new Array();
			// send_data["chumon_konyu_date"] = $("#new_chumon_konyu_date").val();

			$.ajax({
				url: 'server_kiku_chumon_newdate_insert.php',
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
					alert(new_date + " の注文データが作成されました。");
					transition("kiku_chumon.php");	//菊注文画面をリロード
				}else{
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



	
//========================================================================================================
//菊購入データを画面にセット
//========================================================================================================
if(konyu_rows != 0){
	// kikuIrisuSet();
	konyuDataSet();
}


//****************************************************************************
//　輪菊のサイズごとの入数（箱に入っている花の本数）を画面へセット
//*****************************************************************************
function kikuIrisuSet(){
	
	mst_himmoku_size_kiku_irisu_arr.forEach(function(kiku_irisu_row){
		if(kiku_irisu_row["himmoku_size_cd"] == 1){
			document.getElementById("disp_2l_honsu").innerText = kiku_irisu_row["irisu"]
		}else if(kiku_irisu_row["himmoku_size_cd"] == 2){
			document.getElementById("disp_l_honsu").innerText = kiku_irisu_row["irisu"]
		}else if(kiku_irisu_row["himmoku_size_cd"] == 3){
			document.getElementById("disp_m_honsu").innerText = kiku_irisu_row["irisu"]
		}
	});

}

//****************************************************************************
//　菊購入データを画面へセット
//*****************************************************************************
function konyuDataSet(){
	//ここに来た時点でここで購入データは存在する。
	//来る前に判定している。

	var tbl = document.getElementById("tbl_lst");	//テーブルオブジェクト取得
	//一覧表のタイトル行を作る ------------------------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();

	//--「選択」セル（thタグ）を追加
	var cell_sentaku_title = document.createElement('th');
	tbl_row.appendChild(cell_sentaku_title);
	cell_sentaku_title.setAttribute('class', "table-secondary vertical_middle");
	cell_sentaku_title.innerText = "選択";

	//--「搬入先」セル（thタグ）を追加
	var cell_hannyusaki_title = document.createElement('th');
	tbl_row.appendChild(cell_hannyusaki_title);
	cell_hannyusaki_title.setAttribute('class', "table-secondary vertical_middle");
	cell_hannyusaki_title.innerText = "搬入先";

	//--「品種」セル（thタグ）を追加
	var cell_hinshu_title = document.createElement('th');
	tbl_row.appendChild(cell_hinshu_title);
	cell_hinshu_title.setAttribute('class', "table-secondary vertical_middle");
	cell_hinshu_title.innerText = "品種";

	//--「産地」セル（thタグ）を追加
	var cell_sanchi_title = document.createElement('th');
	tbl_row.appendChild(cell_sanchi_title);
	cell_sanchi_title.setAttribute('class', "table-secondary vertical_middle");
	cell_sanchi_title.innerText = "産地";

	//--「等級」セル（thタグ）を追加
	var cell_tokyu_title = document.createElement('th');
	tbl_row.appendChild(cell_tokyu_title);
	cell_tokyu_title.setAttribute('class', "table-secondary vertical_middle");
	cell_tokyu_title.innerText = "等級";

	//--「備考」セル（thタグ）を追加
	var cell_biko_title = document.createElement('th');
	tbl_row.appendChild(cell_biko_title);
	cell_biko_title.setAttribute('class', "table-secondary vertical_middle");
	cell_biko_title.innerText = "備考";

	//--「サイズ」セル（thタグ）を追加
	var cell_size_title = document.createElement('th');
	tbl_row.appendChild(cell_size_title);
	cell_size_title.setAttribute('class', "table-secondary vertical_middle");
	cell_size_title.innerText = "サイズ";

	//--「入数」セル（thタグ）を追加
	var cell_irisu_title = document.createElement('th');
	tbl_row.appendChild(cell_irisu_title);
	cell_irisu_title.setAttribute('class', "table-secondary vertical_middle");
	cell_irisu_title.innerText = "入数";

	//--「口数」セル（thタグ）を追加
	var cell_kuchisu_title = document.createElement('th');
	tbl_row.appendChild(cell_kuchisu_title);
	cell_kuchisu_title.setAttribute('class', "table-secondary vertical_middle");
	cell_kuchisu_title.innerText = "口数";

	//--「単価」セル（thタグ）を追加
	var cell_tanka_title = document.createElement('th');
	tbl_row.appendChild(cell_tanka_title);
	cell_tanka_title.setAttribute('class', "table-secondary vertical_middle");
	cell_tanka_title.innerText = "単価";

	//--「合計本数」セル（thタグ）を追加
	var cell_gokeihonsu_title = document.createElement('th');
	tbl_row.appendChild(cell_gokeihonsu_title);
	cell_gokeihonsu_title.setAttribute('class', "table-secondary vertical_middle");
	cell_gokeihonsu_title.innerText = "合計本数";

	//--「口数残」セル（thタグ）を追加
	var cell_kuchisuzan_title = document.createElement('th');
	tbl_row.appendChild(cell_kuchisuzan_title);
	cell_kuchisuzan_title.setAttribute('class', "table-secondary vertical_middle");
	cell_kuchisuzan_title.innerText = "口数残";

	//--部署名セル（thタグ）を追加（部署マスタ配列をループ）
	var old_busho_cd = "";
	mst_busho_arr.forEach(function(busho_row){
		//--「部署名」セル（thタグ）を追加
		let cell_busho_nm_title = document.createElement('th');
		tbl_row.appendChild(cell_busho_nm_title);
		cell_busho_nm_title.setAttribute('class', "table-secondary");
		cell_busho_nm_title.innerText = busho_row["disp_busho_nm"];
	});

	//データ行を作成 ---------------------------
	konyu_rows.forEach(function(konyu_row){

	});



	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「２Ｌ」セル（thタグ）を追加
	var cell_chumon_kuchisu_2l_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_kuchisu_2l_title);
	cell_chumon_kuchisu_2l_title.setAttribute('class', "table-primary");
	cell_chumon_kuchisu_2l_title.innerText = "２Ｌ";

	//--各支店の口数表示セルを作成
	var wk_2l_chumonsu_gokei = 0;
	//---注文データ（配列）をループ
	chumon_arr.forEach(function(chumon_row){
		//品目サイズが「２Ｌ」
		if(chumon_row["himmoku_size_cd"] == 1){
			let cell_2l_chumon_kuchisu = tbl_row.insertCell();
			// cell_2l_chumon_kuchisu.setAttribute('onclick', "beforViewInputModal2(event)");
			cell_2l_chumon_kuchisu.setAttribute('class', "text_align_center");
			cell_2l_chumon_kuchisu.setAttribute('id', chumon_row['busho_cd'] + "_" + chumon_row["himmoku_size_cd"] + "_kuchisu");
			cell_2l_chumon_kuchisu.setAttribute('data-bs-toggle', "modal");	//セルをクリックしたら注文口数入力用モーダルを開くようにするための設定（bootstrap5）
			cell_2l_chumon_kuchisu.setAttribute('data-bs-target', "#input_chumon_modal");	//セルをクリックしたら注文口数入力用モーダルを開くようにするための設定（bootstrap5）
			cell_2l_chumon_kuchisu.innerText = numberFormat(chumon_row["chumonsu"] - 0);
			wk_2l_chumonsu_gokei += chumon_row["chumonsu"] - 0;
		}
	});	

	//--２Ｌの「合計」セルを作成
	var cell_2l_gokei = tbl_row.insertCell();
	cell_2l_gokei.setAttribute('class', "text_align_center");
	cell_2l_gokei.setAttribute('id', "size_cd_1_kuchisugokei");
	cell_2l_gokei.innerText = numberFormat(wk_2l_chumonsu_gokei - 0);

	
















	


	//「注文口数」のタイトル行を作る -------------------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加

	//--「注文口数」セル（thタグ）を追加
	var cell_chumon_kuchisu_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_kuchisu_title);
	cell_chumon_kuchisu_title.setAttribute('rowspan', "4");
	cell_chumon_kuchisu_title.setAttribute('class', "table-primary vertical_middle");
	cell_chumon_kuchisu_title.innerText = "注文口数";

	//--「サイズ」セル（thタグ）を追加
	var cell_chumon_size_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_size_title);
	cell_chumon_size_title.setAttribute('class', "table-secondary");
	cell_chumon_size_title.innerText = "サイズ";

	//--部署名セル（thタグ）を追加（菊注文データ配列をループ）
	var old_busho_cd = "";
	chumon_arr.forEach(function(chumon_row){
		if(old_busho_cd !== chumon_row["busho_cd"]){
			//--「部署名」セル（thタグ）を追加
			let cell_busho_nm_title = document.createElement('th');
			tbl_row.appendChild(cell_busho_nm_title);
			cell_busho_nm_title.setAttribute('class', "table-secondary");
			cell_busho_nm_title.innerText = chumon_row["disp_busho_nm"];
			old_busho_cd = chumon_row["busho_cd"];
		}
	});

	//--「合計」セル（thタグ）を追加
	var cell_chumon_gokei_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_gokei_title);
	cell_chumon_gokei_title.setAttribute('class', "table-warning");
	cell_chumon_gokei_title.innerText = "合計";


	//「注文口数」のデータ１行目（２Ｌ）を作成 ---------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「２Ｌ」セル（thタグ）を追加
	var cell_chumon_kuchisu_2l_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_kuchisu_2l_title);
	cell_chumon_kuchisu_2l_title.setAttribute('class', "table-primary");
	cell_chumon_kuchisu_2l_title.innerText = "２Ｌ";

	//--各支店の口数表示セルを作成
	var wk_2l_chumonsu_gokei = 0;
	//---注文データ（配列）をループ
	chumon_arr.forEach(function(chumon_row){
		//品目サイズが「２Ｌ」
		if(chumon_row["himmoku_size_cd"] == 1){
			let cell_2l_chumon_kuchisu = tbl_row.insertCell();
			// cell_2l_chumon_kuchisu.setAttribute('onclick', "beforViewInputModal2(event)");
			cell_2l_chumon_kuchisu.setAttribute('class', "text_align_center");
			cell_2l_chumon_kuchisu.setAttribute('id', chumon_row['busho_cd'] + "_" + chumon_row["himmoku_size_cd"] + "_kuchisu");
			cell_2l_chumon_kuchisu.setAttribute('data-bs-toggle', "modal");	//セルをクリックしたら注文口数入力用モーダルを開くようにするための設定（bootstrap5）
			cell_2l_chumon_kuchisu.setAttribute('data-bs-target', "#input_chumon_modal");	//セルをクリックしたら注文口数入力用モーダルを開くようにするための設定（bootstrap5）
			cell_2l_chumon_kuchisu.innerText = numberFormat(chumon_row["chumonsu"] - 0);
			wk_2l_chumonsu_gokei += chumon_row["chumonsu"] - 0;
		}
	});	

	//--２Ｌの「合計」セルを作成
	var cell_2l_gokei = tbl_row.insertCell();
	cell_2l_gokei.setAttribute('class', "text_align_center");
	cell_2l_gokei.setAttribute('id', "size_cd_1_kuchisugokei");
	cell_2l_gokei.innerText = numberFormat(wk_2l_chumonsu_gokei - 0);


	//「注文口数」のデータ２行目（Ｌ）を作成 -----------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「Ｌ」セル（thタグ）を追加
	var cell_chumon_kuchisu_l_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_kuchisu_l_title);
	cell_chumon_kuchisu_l_title.setAttribute('class', "table-primary");
	cell_chumon_kuchisu_l_title.innerText = "Ｌ";

	//--各支店の口数表示セルを作成
	var wk_l_chumonsu_gokei = 0;
	//---注文データ（配列）をループ
	chumon_arr.forEach(function(chumon_row){
		//品目サイズが「Ｌ」
		if(chumon_row["himmoku_size_cd"] == 2){
			let cell_l_chumon_kuchisu = tbl_row.insertCell();
			// cell_l_chumon_kuchisu.setAttribute('onclick', "clickTrasition");
			cell_l_chumon_kuchisu.setAttribute('class', "text_align_center");
			cell_l_chumon_kuchisu.setAttribute('id', chumon_row['busho_cd'] + "_" + chumon_row["himmoku_size_cd"] + "_kuchisu");
			cell_l_chumon_kuchisu.setAttribute('data-bs-toggle', "modal");	//セルをクリックしたら注文口数入力用モーダルを開くようにするための設定（bootstrap5）
			cell_l_chumon_kuchisu.setAttribute('data-bs-target', "#input_chumon_modal");	//セルをクリックしたら注文口数入力用モーダルを開くようにするための設定（bootstrap5）
			cell_l_chumon_kuchisu.innerText = numberFormat(chumon_row["chumonsu"] - 0);
			wk_l_chumonsu_gokei += chumon_row["chumonsu"] - 0;
		}
	});	

	//--Ｌの「合計」セルを作成
	var cell_l_gokei = tbl_row.insertCell();
	cell_l_gokei.setAttribute('class', "text_align_center");
	cell_l_gokei.setAttribute('id', "size_cd_2_kuchisugokei");
	cell_l_gokei.innerText = numberFormat(wk_l_chumonsu_gokei - 0);


	//「注文口数」のデータ３行目（Ｍ）を作成 -----------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「Ｍ」セル（thタグ）を追加
	var cell_chumon_kuchisu_m_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_kuchisu_m_title);
	cell_chumon_kuchisu_m_title.setAttribute('class', "table-primary");
	cell_chumon_kuchisu_m_title.innerText = "Ｍ";

	//--各支店の口数表示セルを作成
	var wk_m_chumonsu_gokei = 0;
	//---注文データ（配列）をループ
	chumon_arr.forEach(function(chumon_row){
		//品目サイズが「Ｍ」
		if(chumon_row["himmoku_size_cd"] == 3){
			let cell_m_chumon_kuchisu = tbl_row.insertCell();
			// cell_m_chumon_kuchisu.setAttribute('onclick', "clickTrasition");
			cell_m_chumon_kuchisu.setAttribute('class', "text_align_center");
			cell_m_chumon_kuchisu.setAttribute('id', chumon_row['busho_cd'] + "_" + chumon_row["himmoku_size_cd"] + "_kuchisu");
			cell_m_chumon_kuchisu.setAttribute('data-bs-toggle', "modal");	//セルをクリックしたら注文口数入力用モーダルを開くようにするための設定（bootstrap5）
			cell_m_chumon_kuchisu.setAttribute('data-bs-target', "#input_chumon_modal");	//セルをクリックしたら注文口数入力用モーダルを開くようにするための設定（bootstrap5）
			cell_m_chumon_kuchisu.innerText = numberFormat(chumon_row["chumonsu"] - 0);
			wk_m_chumonsu_gokei += chumon_row["chumonsu"] - 0;
		}
	});	

	//--Ｍの「合計」セルを作成
	var cell_m_gokei = tbl_row.insertCell();
	cell_m_gokei.setAttribute('class', "text_align_center");
	cell_m_gokei.setAttribute('id', "size_cd_3_kuchisugokei");
	cell_m_gokei.innerText = numberFormat(wk_m_chumonsu_gokei - 0);



	//「注文本数」のタイトル行を作る -------------------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「注文本数」セル（thタグ）を追加
	var cell_chumon_kuchisu_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_kuchisu_title);
	cell_chumon_kuchisu_title.setAttribute('rowspan', "5");
	cell_chumon_kuchisu_title.setAttribute('class', "table-success vertical_middle");
	cell_chumon_kuchisu_title.innerText = "注文本数";

	//--「サイズ」セル（thタグ）を追加
	var cell_chumon_size_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_size_title);
	cell_chumon_size_title.setAttribute('class', "table-secondary");
	cell_chumon_size_title.innerText = "サイズ";

	//--部署名セル（thタグ）を追加（菊注文データ配列をループ）
	var old_busho_cd = "";
	chumon_arr.forEach(function(chumon_row){
		if(old_busho_cd !== chumon_row["busho_cd"]){
			//--「部署名」セル（thタグ）を追加
			let cell_busho_nm_title = document.createElement('th');
			tbl_row.appendChild(cell_busho_nm_title);
			cell_busho_nm_title.setAttribute('class', "table-secondary");
			cell_busho_nm_title.innerText = chumon_row["disp_busho_nm"];
			old_busho_cd = chumon_row["busho_cd"];
		}
	});

	//--「合計」セル（thタグ）を追加
	var cell_chumon_gokei_title = document.createElement('th');
	tbl_row.appendChild(cell_chumon_gokei_title);
	cell_chumon_gokei_title.setAttribute('class', "table-warning");
	cell_chumon_gokei_title.innerText = "合計";


	//「注文本数」のデータ１行目（２Ｌ）を作成 ---------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「２Ｌ」セル（thタグ）を追加
	var cell_honsu_2l_title = document.createElement('th');
	tbl_row.appendChild(cell_honsu_2l_title);
	cell_honsu_2l_title.setAttribute('class', "table-success");
	cell_honsu_2l_title.innerText = "２Ｌ";

	//--各支店の本数表示セルを作成
	var wk_2l_honsu_gokei = 0;
	//---注文データ（配列）をループ
	chumon_arr.forEach(function(chumon_row){
		//品目サイズが「２Ｌ」
		if(chumon_row["himmoku_size_cd"] == 1){
			let cell_2l_honsu = tbl_row.insertCell();
			// cell_2l_honsu.setAttribute('onclick', "clickTrasition");
			cell_2l_honsu.setAttribute('id', chumon_row['busho_cd'] + "_" + chumon_row["himmoku_size_cd"] + "_honsu");
			cell_2l_honsu.setAttribute('class', "text_align_center");
			cell_2l_honsu.innerText = numberFormat(chumon_row["honsu"] - 0);
			wk_2l_honsu_gokei += chumon_row["honsu"] - 0;
		}
	});	

	//--２Ｌの「合計」セルを作成
	var cell_2l_gokei = tbl_row.insertCell();
	cell_2l_gokei.setAttribute('class', "text_align_center");
	cell_2l_gokei.setAttribute('id', "size_cd_1_honsugokei");
	cell_2l_gokei.innerText = numberFormat(wk_2l_honsu_gokei - 0);


	//「注文本数」のデータ２行目（Ｌ）を作成 -----------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「Ｌ」セル（thタグ）を追加
	var cell_honsu_l_title = document.createElement('th');
	tbl_row.appendChild(cell_honsu_l_title);
	cell_honsu_l_title.setAttribute('class', "table-success");
	cell_honsu_l_title.innerText = "Ｌ";

	//--各支店の本数表示セルを作成
	var wk_l_honsu_gokei = 0;
	//---注文データ（配列）をループ
	chumon_arr.forEach(function(chumon_row){
		//品目サイズが「Ｌ」
		if(chumon_row["himmoku_size_cd"] == 2){
			let cell_l_honsu = tbl_row.insertCell();
			// cell_l_honsu.setAttribute('onclick', "clickTrasition");
			cell_l_honsu.setAttribute('id', chumon_row['busho_cd'] + "_" + chumon_row["himmoku_size_cd"] + "_honsu");
			cell_l_honsu.setAttribute('class', "text_align_center");
			cell_l_honsu.innerText = numberFormat(chumon_row["honsu"] - 0);
			wk_l_honsu_gokei += chumon_row["honsu"] - 0;
		}
	});	

	//--Ｌの「合計」セルを作成
	var cell_l_gokei = tbl_row.insertCell();
	cell_l_gokei.setAttribute('class', "text_align_center");
	cell_l_gokei.setAttribute('id', "size_cd_2_honsugokei");
	cell_l_gokei.innerText = numberFormat(wk_l_honsu_gokei - 0);


	//「注文本数」のデータ３行目（Ｍ）を作成 -----------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「Ｍ」セル（thタグ）を追加
	var cell_honsu_m_title = document.createElement('th');
	tbl_row.appendChild(cell_honsu_m_title);
	cell_honsu_m_title.setAttribute('class', "table-success");
	cell_honsu_m_title.innerText = "Ｍ";

	//--各支店の本数表示セルを作成
	var wk_m_honsu_gokei = 0;
	//---注文データ（配列）をループ
	chumon_arr.forEach(function(chumon_row){
		//品目サイズが「Ｍ」
		if(chumon_row["himmoku_size_cd"] == 3){
			let cell_m_honsu = tbl_row.insertCell();
			// cell_m_honsu.setAttribute('onclick', "clickTrasition");
			cell_m_honsu.setAttribute('id', chumon_row['busho_cd'] + "_" + chumon_row["himmoku_size_cd"] + "_honsu");
			cell_m_honsu.setAttribute('class', "text_align_center");
			cell_m_honsu.innerText = numberFormat(chumon_row["honsu"] - 0);
			wk_m_honsu_gokei += chumon_row["honsu"] - 0;
		}
	});	

	//--Ｍの「合計」セルを作成
	var cell_m_gokei = tbl_row.insertCell();
	cell_m_gokei.setAttribute('class', "text_align_center");
	cell_m_gokei.setAttribute('id', "size_cd_3_honsugokei");
	cell_m_gokei.innerText = numberFormat(wk_m_honsu_gokei - 0);


	//「注文本数」の合計行（４行目）を作成 -----------------------------------
	//-テーブルに１行追加する
	var tbl_row = tbl.insertRow();	//テーブルに１行追加
	tbl_row.setAttribute('class', "record");

	//--「合計」セル（thタグ）を追加
	var cell_honsu_gokei_title = document.createElement('th');
	tbl_row.appendChild(cell_honsu_gokei_title);
	cell_honsu_gokei_title.setAttribute('class', "table-warning");
	cell_honsu_gokei_title.innerText = "合計";

	//--各支店の合計本数表示セルを作成
	var wk_all_honsu_gokei = 0;
	//---注文データ（配列）をループ
	chumon_arr.forEach(function(chumon_row){
		//部署毎の注文本数合計レコード（品目サイズがゼロ）の場合
		if(chumon_row["himmoku_size_cd"] == 0){
			let cell_gokei_honsu = tbl_row.insertCell();
			// cell_gokei_honsu.setAttribute('onclick', "clickTrasition");
			cell_gokei_honsu.setAttribute('class', "text_align_center");
			cell_gokei_honsu.setAttribute('id', chumon_row["busho_cd"] + "_honsugokei");
			cell_gokei_honsu.innerText = numberFormat(chumon_row["busho_honsu_gokei"] - 0);
			wk_all_honsu_gokei += chumon_row["busho_honsu_gokei"] - 0;
		}
	});	
//test start
// console.log("wk_all_honsu_gokei= " + wk_all_honsu_gokei);
//test end

	//--部署別合計行の「合計」セルを作成
	var cell_all_gokei = tbl_row.insertCell();
	cell_all_gokei.setAttribute('class', "text_align_center");
	cell_all_gokei.setAttribute('id', "honsugokei");
	cell_all_gokei.innerText = numberFormat(wk_all_honsu_gokei - 0);

}

//****************************************************************************
//　プルダウンで選択した注文購入日の情報に画面表示を切り替える（リロード）
//　【引数】
//　　無し
//*****************************************************************************
function chgDate(){

	var obj = document.getElementById("disp_chumon_konyu_date");	//注文購入日プルダウンのオブジェクト
	var disp_chumon_konyu_date = obj.options[obj.selectedIndex].value;	// 注文購入日プルダウンで選択した注文購入日
	var hd_chumon_konyu_date = document.getElementById("hd_chumon_konyu_date");
	hd_chumon_konyu_date.value = disp_chumon_konyu_date;	//プルダウンで選択された注文購入日をhiddenへ入れる

	var obj_konyusaki = document.getElementById("disp_konyusaki");	//購入先プルダウンのオブジェクト
	var disp_konyusaki = obj_konyusaki.options[obj_konyusaki.selectedIndex].value;	// 購入先プルダウンで選択した購入先
	var hd_konyusaki_cd = document.getElementById("hd_konyusaki_cd");
	hd_konyusaki_cd.value = disp_konyusaki;	//プルダウンで選択された購入先コードをhiddenへ入れる

	transition('kiku_konyu.php');
}

//****************************************************************************
//　「注文口数入力」ボタンクリック時に呼ばれる関数
//　注文口数入力小窓１の部署プルダウンで選択されている部署の値（元画面の）を
//　小窓の入力欄へセットする。
//　小窓表示前の処理
//	（これは注文口数入力用小窓（input_chumon_modal）を表示するイベント（show.bs.modal）
//	　が実行されるたびに呼び出される関数）
//　【引数】
//　　無し
//*****************************************************************************
var input_chumon_modal = document.getElementById('input_chumon_modal')
input_chumon_modal.addEventListener('show.bs.modal', function (event) {

	var sell = event.relatedTarget;	//「注文口数入力ボタン」or 一覧中の注文口数のセル
	var busho_size = sell.getAttribute('id');
	var busho_size_arr = busho_size.split("_");

	if(busho_size_arr == "btn"){
		//「注文口数入力」ボタンクリックされたときの処理
		//元画面の値を小窓の入力欄にセットする
		inputModal1ValueSet();
	}else{
		//一覧中の注文口数のセルをクリックした時の処理
		//部署プルダウンで該当部署を選択
		var busho_sel1 = document.getElementById("busho_sel1");	//select要素を取得
		var options = busho_sel1.options	//select要素のoption要素を取得

		//HTMLCollectionを配列に変換してループ
		Array.from(options).forEach(function(option) {
			if(option.value == busho_size_arr[0]){
				option.selected = true;
			}
		});

		//元画面の値を小窓の入力欄にセットする
		inputModal1ValueSet();
	}

})

//****************************************************************************
//　注文口数入力小窓１が表示された後、「２Ｌ」入力欄にフォーカスする。
//　小窓表示後の処理
//	（これは注文口数入力用小窓（input_chumon_modal）を表示するイベント（shown.bs.modal）
//	　が実行されるたびに呼び出される関数）
//　【引数】
//　　無し
//*****************************************************************************
input_chumon_modal.addEventListener('shown.bs.modal', function (event) {
	//「２Ｌ」入力欄にフォーカスする
	//注文口数入力小窓１の「２Ｌ」にフォーカスし、値を選択した状態にする
	inputModal12lFocus();
	// document.getElementById("input_chumonsu_2l").select();
	// document.getElementById("input_chumonsu_2l").focus();
})

//****************************************************************************
//　注文口数入力小窓１の部署（ブルダウン）変更時に呼ばれる関数
//　注文口数入力小窓１の部署プルダウンで選択されている部署の値（元画面の）を
//　小窓の入力欄へセットする。
//　【引数】
//　　無し
//*****************************************************************************
function inputModal1ValueSet(){
	//プルダウンで選択されている部署のコード取得
	var busho_cd = document.getElementById("busho_sel1").value;
	//元画面の２Ｌサイズの値を小窓の２Ｌ入力欄にセット
	document.getElementById("input_chumonsu_2l").value = document.getElementById(busho_cd + "_1_kuchisu").innerText;
	//元画面のＬサイズの値を小窓のＬ入力欄にセット
	document.getElementById("input_chumonsu_l").value = document.getElementById(busho_cd + "_2_kuchisu").innerText;
	//元画面のＭサイズの値を小窓のＭ入力欄にセット
	document.getElementById("input_chumonsu_m").value = document.getElementById(busho_cd + "_3_kuchisu").innerText;
	//注文口数入力小窓１の「２Ｌ」にフォーカスし、値を選択した状態にする
	inputModal12lFocus();
}

//****************************************************************************
//　注文口数入力小窓１の「２Ｌ」にフォーカスし、
//　「２Ｌ」の値を選択した状態にする
//　【引数】
//　　無し
//*****************************************************************************
function inputModal12lFocus(){
	//「２Ｌ」入力欄にフォーカスする
	document.getElementById("input_chumonsu_2l").select();
	document.getElementById("input_chumonsu_2l").focus();
}

  
//****************************************************************************
//　注文口数入力用小窓１の「入力」ボタンクリック時に呼ばれる関数
//　小窓に入力した値を元画面へセットする。
//　【引数】
//　　無し
//*****************************************************************************
function inputChumonKuchiSu(){
	//プルダウンで選択されている部署のコード取得
	var busho_cd = document.getElementById("busho_sel1").value;
	//２Ｌサイズの入力値を元画面の該当セルにセット
	var input_chumonsu_2l = document.getElementById("input_chumonsu_2l").value;
	if(!input_chumonsu_2l){
		input_chumonsu_2l = 0;
	}
	document.getElementById(busho_cd + "_1_kuchisu").innerText = input_chumonsu_2l;
	//Ｌサイズの入力値を元画面の該当セルにセット
	var input_chumonsu_l = document.getElementById("input_chumonsu_l").value;
	if(!input_chumonsu_l){
		input_chumonsu_l = 0;		
	}
	document.getElementById(busho_cd + "_2_kuchisu").innerText = input_chumonsu_l;
	//Ｍサイズの入力値を元画面の該当セルにセット
	var input_chumonsu_m = document.getElementById("input_chumonsu_m").value;
	if(!input_chumonsu_m){
		input_chumonsu_m = 0;
	}
	document.getElementById(busho_cd + "_3_kuchisu").innerText = input_chumonsu_m;

	//元画面の一覧表の値をＤＢに登録する（Ajax）
	/** ここにＤＢ更新処理を記述する必要がある */

	/** サーバーへ送るデータを作成する */
	var chumon_konyu_date = $('#new_chumon_konyu_date').val();
	var send_data = new Array;
	// var send_data = {};
	var send_data = {
		'chumon_id' : document.getElementById("hd_chumon_id").value
		,'busho_cd' : busho_cd
		,'chumonsu_2l' : input_chumonsu_2l
		,'chumonsu_l' : input_chumonsu_l
		,'chumonsu_m' : input_chumonsu_m
	};

	// send_data["chumon_id"] = document.getElementById("hd_chumon_id").value;
	// // send_data["chumon_konyu_date"] = chumon_konyu_date;
	// send_data["busho_cd"] = busho_cd;
	// send_data["chumonsu_2l"] = input_chumonsu_2l;
	// send_data["chumonsu_l"] = input_chumonsu_l;
	// send_data["chumonsu_m"] = input_chumonsu_m;
//test start
// alert("chumon_id = " . document.getElementById('hd_chumon_id').value);
// alert("chumon_id = " + send_data['chumon_id']);
//test end

	$.ajax({
		url: 'server_kiku_chumon_toroku.php',
		// url: '/ajax/test.html',
		/* 自サイトのドメインであれば、https://kinocolog.com/ajax/test.html というURL指定も可 */
		type: 'POST',
		data: {
			val_parm: send_data
		},
		dataType: 'html'  //
		//   dataType: 'json'  //SON形式のデータとして評価しJavaScriptのオブジェクトに変換
	}).done(function(data){
		/* 通信成功時 */
		if(data == 0){
			// alert(new_date + " の注文データが作成されました。");
			// transition("kiku_chumon.php");	//菊注文画面をリロード
			//注文本数（一覧の下半分）を再計算する
			calcChumonHonsu(busho_cd, input_chumonsu_2l, input_chumonsu_l, input_chumonsu_m);
			calcGokei();
		}else{
			alert(data);
		}
		// $("#return").append('<p>'+data.id+' : '+data.school+' : '+data.skill+'</p>');
//            $('.result').html(data); //取得したHTMLを.resultに反映
		
	}).fail(function(jqXHR, textStatus, errorThrown){
	// }).fail(function(data){
		/* 通信失敗時 */
		// alert('通信失敗！:'+ data);
		alert('菊注文口数の登録に失敗しました。');
		console.log("ajax通信に失敗しました");
		console.log("jqXHR          : " + jqXHR.status); // HTTPステータスが取得
		console.log("textStatus     : " + textStatus);    // タイムアウト、パースエラー
		console.log("errorThrown    : " + errorThrown.message); // 例外情報
		// console.log("URL            : " + url);
				
	}).always(function(data) {
		/*　通信成功失敗問わず行う処理　*/
	});

}

//****************************************************************************
//　指定した部署の注文本数（一覧の下半分）を再計算する関数
//　引数に渡された部署の各サイズの注文本数を再計算した値を画面に表示する。
//　【引数】
//		１．部署コード
//		２．２Ｌサイズの注文数（口数）
//		３．Ｌサイズの注文数（口数）
//		４．Ｍサイズの注文数（口数）
//*****************************************************************************
function calcChumonHonsu(busho_cd, input_chumonsu_2l, input_chumonsu_l, input_chumonsu_m){
	var himmoku_size_2l = mst_himmoku_size_kiku_irisu_arr[0]["himmoku_size_cd"];	//品目サイズコード（２Ｌ）
	var himmoku_size_l = mst_himmoku_size_kiku_irisu_arr[1]["himmoku_size_cd"];	//品目サイズコード（Ｌ）
	var himmoku_size_m = mst_himmoku_size_kiku_irisu_arr[2]["himmoku_size_cd"];	//品目サイズコード（Ｍ）
	var irisu_2l = mst_himmoku_size_kiku_irisu_arr[0]["irisu"];	//２Ｌサイズの箱内の本数
	var irisu_l = mst_himmoku_size_kiku_irisu_arr[1]["irisu"];	//Ｌサイズの箱内の本数
	var irisu_m = mst_himmoku_size_kiku_irisu_arr[2]["irisu"];	//Ｍサイズの箱内の本数

	input_chumonsu_2l = input_chumonsu_2l - 0;
	input_chumonsu_l = input_chumonsu_l - 0;
	input_chumonsu_m = input_chumonsu_m - 0;
	irisu_2l = irisu_2l - 0;
	irisu_l = irisu_l - 0;
	irisu_m = irisu_m - 0;

	var honsu_2l = input_chumonsu_2l * irisu_2l;
	var honsu_l = input_chumonsu_l * irisu_l;
	var honsu_m = input_chumonsu_m * irisu_m;

	document.getElementById(busho_cd + "_" + himmoku_size_2l + "_honsu").innerText = numberFormat(honsu_2l);
	document.getElementById(busho_cd + "_" + himmoku_size_l + "_honsu").innerText = numberFormat(honsu_l);
	document.getElementById(busho_cd + "_" + himmoku_size_m + "_honsu").innerText = numberFormat(honsu_m);

}


//****************************************************************************
//　一覧の各合計を再計算して表示させる関数
//		・注文口数の２Ｌサイズの合計、注文口数のＬサイズの合計、注文口数のＭサイズの合計
//		・注文本数の２Ｌサイズの合計、注文本数のＬサイズの合計、注文本数のＭサイズの合計
//		・注文本数の各部署の合計
//　【引数】
//		なし
//*****************************************************************************
function calcGokei(){
	var kuchisu_2l_gokei = 0; //注文口数２Ｌサイズ行の合計
	var kuchisu_l_gokei = 0; //注文口数Ｌサイズ行の合計
	var kuchisu_m_gokei = 0; //注文口数Ｍサイズ行の合計
	var honsu_2l_gokei = 0; //注文本数２Ｌサイズ行の合計
	var honsu_l_gokei = 0; //注文本数Ｌサイズ行の合計
	var honsu_m_gokei = 0; //注文本数Ｍサイズ行の合計
	var honsu_busho_gokei = 0;  //注文本数の各部署合計
	var honsu_gokei = 0;  //注文本数の合計
	var wk_kuchisu_2l_gokei = 0;
	var wk_kuchisu_l_gokei = 0;
	var wk_kuchisu_m_gokei = 0;
	var wk_honsu_2l_gokei = 0;
	var wk_honsu_l_gokei = 0;
	var wk_honsu_m_gokei = 0;
	var old_busho_cd = "";

	$i = 0;
	chumon_arr.forEach(function(chumon_row){
		//部署毎の注文口数合計を部署が変わったときに初期化する
		if(old_busho_cd !== chumon_row["busho_cd"]){
			honsu_gokei += honsu_busho_gokei;	//注文本数の総合計をセット
			honsu_busho_gokei = 0;
			old_busho_cd = chumon_row["busho_cd"];
		}
		//品目サイズが「２Ｌ」
		if(chumon_row["himmoku_size_cd"] == 1){
			//注文口数の２Ｌ行の合計加算
			wk_kuchisu_2l_gokei = off_format_val(document.getElementById(chumon_row["busho_cd"] + "_" + chumon_row["himmoku_size_cd"] + "_kuchisu").innerText);
			wk_kuchisu_2l_gokei = wk_kuchisu_2l_gokei - 0;
			kuchisu_2l_gokei += wk_kuchisu_2l_gokei;
			//注文本数の２Ｌ行の合計加算
			wk_honsu_2l_gokei = off_format_val(document.getElementById(chumon_row["busho_cd"] + "_" + chumon_row["himmoku_size_cd"] + "_honsu").innerText);
			wk_honsu_2l_gokei = wk_honsu_2l_gokei - 0;
			honsu_2l_gokei += wk_honsu_2l_gokei;
			honsu_busho_gokei += wk_honsu_2l_gokei;
		}
		//品目サイズが「Ｌ」
		if(chumon_row["himmoku_size_cd"] == 2){
			//注文口数のＬ行の合計加算
			wk_kuchisu_l_gokei = off_format_val(document.getElementById(chumon_row["busho_cd"] + "_" + chumon_row["himmoku_size_cd"] + "_kuchisu").innerText);
			wk_kuchisu_l_gokei = wk_kuchisu_l_gokei - 0;
			kuchisu_l_gokei += wk_kuchisu_l_gokei;
			//注文本数のＬ行の合計加算
			wk_honsu_l_gokei = off_format_val(document.getElementById(chumon_row["busho_cd"] + "_" + chumon_row["himmoku_size_cd"] + "_honsu").innerText);
			wk_honsu_l_gokei = wk_honsu_l_gokei - 0;
			honsu_l_gokei += wk_honsu_l_gokei;
			honsu_busho_gokei += wk_honsu_l_gokei;
		}
		//品目サイズが「Ｍ」
		if(chumon_row["himmoku_size_cd"] == 3){
			//注文口数のＭ行の合計加算
			wk_kuchisu_m_gokei = off_format_val(document.getElementById(chumon_row["busho_cd"] + "_" + chumon_row["himmoku_size_cd"] + "_kuchisu").innerText);
			wk_kuchisu_m_gokei = wk_kuchisu_m_gokei - 0;
			kuchisu_m_gokei += wk_kuchisu_m_gokei;
			//注文本数のＭ行の合計加算
			wk_honsu_m_gokei = off_format_val(document.getElementById(chumon_row["busho_cd"] + "_" + chumon_row["himmoku_size_cd"] + "_honsu").innerText);
			wk_honsu_m_gokei = wk_honsu_m_gokei - 0;
			honsu_m_gokei += wk_honsu_m_gokei;
			honsu_busho_gokei += wk_honsu_m_gokei;
		}
		//注文本数の部署毎の合計
		document.getElementById(chumon_row["busho_cd"] + "_honsugokei").innerText = numberFormat(honsu_busho_gokei);
	  });	
	
	//注文口数の２Ｌ行の合計セット
	document.getElementById("size_cd_1_kuchisugokei").innerText = numberFormat(kuchisu_2l_gokei);
	//注文口数のＬ行の合計セット
	document.getElementById("size_cd_2_kuchisugokei").innerText = numberFormat(kuchisu_l_gokei);
	//注文口数のＭ行の合計セット
	document.getElementById("size_cd_3_kuchisugokei").innerText = numberFormat(kuchisu_m_gokei);
	//注文本数の２Ｌ行の合計セット
	document.getElementById("size_cd_1_honsugokei").innerText = numberFormat(honsu_2l_gokei);
	//注文本数のＬ行の合計セット
	document.getElementById("size_cd_2_honsugokei").innerText = numberFormat(honsu_l_gokei);
	//注文本数のＭ行の合計セット
	document.getElementById("size_cd_3_honsugokei").innerText = numberFormat(honsu_m_gokei);
	//注文本数の総合計セット
	document.getElementById("honsugokei").innerText = numberFormat(honsu_2l_gokei + honsu_l_gokei + honsu_m_gokei);
	// document.getElementById("honsugokei").innerText = numberFormat(honsu_gokei);
  }













// //読み込み時のチェックボックスのセット処理
// function setCheckBox(){
//   var checkboxes = document.getElementsByClassName("checkbox");

//   checkData.forEach(function(el){
//     [].forEach.call(checkboxes,function(e){
//       if(e.value == el){
//         e.checked = true;
//       }
//     });
//   });
// }
// //メインメニューへ遷移
// function mainmanuTransition(){
//   var frm = document.createElement('form');
//   frm.action = "./main_menu.php";
//   document.body.appendChild(frm);
//   frm.submit();
// }
// //ログアウト処理＆遷移
// function logout(){
//   if(confirm('ログアウトします。')){
//     var frm = document.createElement('form');
//     frm.action = "./logout.php";
//     document.body.appendChild(frm);
//     frm.submit();
//   }
// }
// //クリックした従業員の給与入力画面での遷移
// function clickTrasition(staff_code){

//   //クッキーの書き込み
//   var nendo = document.getElementById("nendo").value;
//   var staff_cd_array = [];
//   var checkboxes = document.getElementsByClassName("checkbox");
//   [].forEach.call(checkboxes,function(e){
//     if(e.checked == true){
//       staff_cd_array.push(e.value);
//     }
//   });
//   var data = JSON.stringify({'nendo':nendo,'checkboxes':JSON.stringify(staff_cd_array)});

//   document.cookie = 'nemp_taishosha_lst=' + data;

//   var frm = document.createElement('form');
//   frm.action = "./nempo_input.php";
//   frm.method = "post";

//   var input1 = document.createElement('input');
//   input1.type = "hidden";
//   input1.name = "staff_code";
//   input1.value = staff_code;

//   frm.appendChild(input1);

//   var input2 = document.createElement('input');
//   input2.type = "hidden";
//   input2.name = "nendo";
//   input2.value = document.getElementById("nendo").value;

//   frm.appendChild(input2);

//   document.body.appendChild(frm);

//   frm.submit();
// }
// //プルダウンを変更したときの自分自身に遷移
// function pullChangeTrasition(obj){
//   var frm = document.createElement('form');
//   frm.action = "./nempo_taishosha_lst.php";
//   frm.method = "get";

//   var input = document.createElement('input');
//   input.type = "hidden";
//   input.name = "nendo";
//   input.value = obj.value;

//   frm.appendChild(input);

//   document.body.appendChild(frm);

//   frm.submit();
// }
// //チェックボックスの全選択
// function allCheckOn(){
//   var checkboxes = document.getElementsByClassName("checkbox");
//   [].forEach.call(checkboxes,function(e){
//     e.checked = true;
//   });

// }
// //チェックボックスの全解除
// function allCheckOff(){
//   var checkboxes = document.getElementsByClassName("checkbox");
//   [].forEach.call(checkboxes,function(e){
//     e.checked = false;
//   });

// }
// function KakuteiKaijo(){
//   //確定を仮確定へ更新
//   updateKakutei(2,1);
// }
// function KarikakuteiKaijo(){
//   //仮確定を未確定へ更新
//   updateKakutei(1,0);
// }
// function KariKakutei(){
//   //未確定を仮確定へ更新
//   updateKakutei(0,1);
// }
// //チェックしたデータの確定状態を変更する
// //updateKakutei:変更前の確定状態
// //updatedKakutei:変更後の確定状態
// function updateKakutei(updateKakutei,updatedKakutei){

//   var staff_cd_array = [];
//   var obj_pull = document.getElementById("nendo");
//   var nendo = obj_pull.value;

//   var checkboxes = document.getElementsByClassName("checkbox");
//   [].forEach.call(checkboxes,function(e){
//     if(e.checked == true && e.dataset.kakutei == updateKakutei){
//       staff_cd_array.push(e.value);
//     }
//   });

//   if(staff_cd_array.length == "0"){
//     return false;
//   }

//   const req = new XMLHttpRequest();
//   req.onreadystatechange = function() {
//     if(req.readyState == 4){ //通信完了時
//       if(req.status == 200){ //通信が成功した時
//         var data = req.responseText;
//         if(data == -1){
//           alert("データ更新できませんでした。");
//         }
//       } else { //通信に失敗した時
//         alert("失敗");
//       }
//     } else { //通信が完了する前
//     }
//   }
//   req.open('GET','server_nempo_taishosah_lst_kakutei_update.php?staff_code=' + encodeURIComponent(JSON.stringify(staff_cd_array))
//                                                            + '&updated_kakutei=' + encodeURIComponent(updatedKakutei)
//                                                            + '&nendo=' + encodeURIComponent(nendo),false);
//   req.send(null);

//   //再表示
//   redisplay();
// }
// //確定状態系のボタン押下時の再表示処理
// function redisplay(){

//   var staff_cd_array = [];
//   var checkboxes = document.getElementsByClassName("checkbox");
//   [].forEach.call(checkboxes,function(e){
//     if(e.checked == true){
//       staff_cd_array.push(e.value);
//     }
//   });

//   var frm = document.createElement('form');
//   frm.action = "./nempo_taishosha_lst.php";
//   frm.method = "get";

//   var input1 = document.createElement('input');
//   input1.type = "hidden";
//   input1.name = "nendo";
//   input1.value = document.getElementById("nendo").value;

//   frm.appendChild(input1);

//   var input2 = document.createElement('input');
//   input2.type = "hidden";
//   input2.name = "checkboxes";
//   input2.value = JSON.stringify(staff_cd_array);

//   frm.appendChild(input2);

//   document.body.appendChild(frm);

//   frm.submit();
// }
