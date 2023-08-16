<?php
$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());
}

//購入日のデータを取得
if (isset($_POST["example"])) {
    $kounyubi = $_POST["example"];
}
//市場のデータを取得
if (isset($_POST["sijou"])) {
    $sijou = $_POST["sijou"];
}

//
$kensaku = pg_query("SELECT	hinshu_nm, sanchi_nm1,  tokyu, himmoku_size_nm, B.irisu, kuchisu,tanka, konyu_su,B.busho_cd,B.konyu_himmoku_id
FROM (((((chumon_tbl as A
      INNER join konyu_himmoku_tbl as B
       on A.chumon_id = B.chumon_id)
      INNER join mst_himmoku AS C
       on B.himmoku_cd = C.himmoku_cd)
       INNER join mst_konyusaki as D
       on  D.konyusaki_cd = B.konyusaki_cd)
      INNER join mst_himmoku_size as E
       on E.himmoku_size_cd  =  B.size_cd)
      INNER join konyu_himmoku_siten_tbl as F
       on F.chumon_id = B.chumon_id AND F.konyu_himmoku_id = B.konyu_himmoku_id)
       where A.chumon_konyu_date =  '".$kounyubi."'  AND konyusaki_nm = '".$sijou."' ");    

      $busyo = pg_query(" SELECT disp_busho_nm	, busho_cd
            FROM mst_busho
    where del_flg = '0'");

    $hinshu = '';
    $santi = '';
    $toukyu ='';
    $B_arr = array();
    $i = 0;
   

?>
<html lang="ja"><!DOCTYPE html>
 <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="bootstrap\css\bootstrap.min.css">
    <script src="js/main.js"></script>
    <title>菊購入先別印刷</title>
 </head>
    
<body>
    <div class="container-fluid">
<form name="form1">
    <div class="btn-lg d-md-flex justify-content-md-end">
     <div class="display-none">
     <input type="button"  value="戻る" onclick="transition('insatu_menu.php')">
      <input type="button"  value="印刷" onclick="window.print();">
     </div>
    </div>
</form>

<div class="child "><?print($sijou)?> 御中</div>
<div class="d-md-flex justify-content-md-end">
  <?print($kounyubi)?>
</div>
    <table class="table table-bordered">
        <tr>
          <td class="text-center header ">品種</td>
          <td class="text-center header ">産地</td>
          <td class="text-center header ">等級</td>
          <td class="text-center header ">サイズ</td>
          <td class="text-center header ">入数</td>
          <td class="text-center header ">口数</td>
          <td class="text-center header ">単価</td>
          <?php
          //部署を配列に入れて表示
        $busyo_row = pg_fetch_all($busyo);
        if($busyo_row != NULL and  is_array($busyo_row)){ 
         foreach($busyo_row as  $busyo_rows){?>
            <td class="text-center header"><?=$busyo_rows['disp_busho_nm'] ?></td>
        <?}
      }?>
      <tr>
<?php 
//印刷内容を配列に入れる
$kensakuarr  =  pg_fetch_all($kensaku);
//データがあるか配列であるかチェック 
if ($kensakuarr != null && is_array($kensakuarr)){
  //品種、産地、等級が変わったらB＿arrに入れる
    foreach($kensakuarr AS $kensakuarrs ){
  if(($kensakuarrs['ichiba_hinshu_nm'] != $hinshu ) or ($kensakuarrs['ichiba_sanchi_nm1'] != $santi ) or  ($kensakuarrs['ichiba_tokyu'] != $toukyu)){
    $B_arr[$i]['ichiba_hinshu_nms'] = $kensakuarrs['ichiba_hinshu_nm'];
    $B_arr[$i]['ichiba_sanchi_nm1s'] = $kensakuarrs['ichiba_sanchi_nm1'];
    $B_arr[$i]['ichiba_tokyus'] = $kensakuarrs['ichiba_tokyu'];
    $B_arr[$i]['himmoku_size_nms'] = $kensakuarrs['himmoku_size_nm'];
    $B_arr[$i]['ichiba_irisus'] = $kensakuarrs['ichiba_irisu'];
    $B_arr[$i]['ichiba_kuchisus'] = $kensakuarrs['ichiba_kuchisu'];
    $B_arr[$i]['ichiba_tankas'] = $kensakuarrs['ichiba_tanka'];
    $B_arr[$i]['konyu_himmoku_ids'] = $kensakuarrs['konyu_himmoku_id'];    
  }
  $hinshu = $kensakuarrs['ichiba_hinshu_nm'];
  $santi =  $kensakuarrs['ichiba_sanchi_nm1'];
  $toukyu = $kensakuarrs['ichiba_tokyu'];
  $i++;
 }
}

      

foreach($B_arr as $vas){?>
   <tr>
      <td class="text-center header"><?print_r($vas['ichiba_hinshu_nms'])?></td>
      <td class="text-center header"><?print_r($vas['ichiba_sanchi_nm1s'])?></td>
      <td class="text-center header"><?print_r($vas['ichiba_tokyus'])?></td>
      <td class="text-center header"><?print_r($vas['himmoku_size_nms'])?></td>
      <td class="text-center header"><?print_r($vas['ichiba_irisus'])?></td>
      <td class="text-center header"><?print_r($vas['ichiba_kuchisus'])?></td>
      <td class="text-center header"><?print_r($vas['ichiba_tankas'])?></td>
        <?
          foreach($busyo_row as $A_row){?>
           <td class="text-center header">
             <?   
              foreach($kensakuarr as $B_row){
               if($A_row['busho_cd'] == $B_row['busho_cd'] && $vas['konyu_himmoku_ids'] == $B_row['konyu_himmoku_id'] ){
                 if($B_row['konyu_su'] >= 0){
                  print_r($B_row['konyu_su']);
             }
            }
           }  
          }
         ?>
     </tr>  
    <?
   }
  ?> 
</div>
</table>
</body> 
</html>