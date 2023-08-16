<?php
include "./tcpdf/tcpdf.php"; // tcpdf.phpを読み込む
$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());
}
  $busyo = pg_query(" SELECT disp_busho_nm	, busho_cd
  FROM mst_busho
  where mst_busho.use_kiku_chumon = '1'");
  $busyo_row = pg_fetch_all($busyo);



$tcpdf = new TCPDF();
$tcpdf->AddPage('L'); // 新しいpdfページを追加



$tcpdf->SetFont("kozgopromedium", "", 10); // デフォルトで用意されている日本語フォント
 
$html = <<< EOF

<table>
 <tr>
   <th>購入先</th>
   <th>品種</th>
   <th>産地</th>
   <th>等級</th>
   <th>サイズ</th>
   <th>入数</th>
   <th>口数</th>  
   <th>合計本数</th>
   <th>{$busyo}</th>
 </tr>
</table>
EOF;


$tcpdf->writeHTML($html); // 表示htmlを設定
$tcpdf->Output('samurai.pdf', 'I'); // pdf表示設定
?>

