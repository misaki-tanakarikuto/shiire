<?php
// db接続
$conn = "host=localhost dbname=shiiredb user=postgres password=41916";
$link = pg_connect($conn);
if (!$link) {
    die('接続失敗です。'.pg_last_error());
}
// 範囲指定する日付を取得する
$chumon_konyu_date = "";
if(!empty($_POST["date"])){
	$chumon_konyu_date = $_POST["date"];
}

$chumon_konyu_date2 = "";
if(!empty($_POST["date2"])){
	$chumon_konyu_date2 = $_POST["date2"];
}
$chumon_konyu_date3 = "";
if(!empty($_POST["date3"])){
	$chumon_konyu_date3 = $_POST["date3"];
}
$chumon_konyu_date4 = "";
if(!empty($_POST["date4"])){
	$chumon_konyu_date4 = $_POST["date4"];
}
    



//  品目名、色、階級ごとに選択された範囲内のデータを取得
if(($chumon_konyu_date) and ($chumon_konyu_date2)){
  $hikaku = pg_query("SELECT  himmoku_nm, iro_nm, kaikyu,  SUM(irisu * B.kuchisu) AS  konyu_total, SUM(B.kingaku/B.konyu_total) AS ave,SUM(B.kingaku) as kingaku
            FROM (((chumon_tbl as A
      INNER join konyu_himmoku_tbl as B
       on A.chumon_id = B.chumon_id)
      INNER join mst_himmoku AS C
       on B.himmoku_cd = C.himmoku_cd)
      INNER join mst_himmoku_color as F
      on  F.himmoku_color_cd = B.iro_cd)
      WHERE A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'
      GROUP BY himmoku_nm,iro_nm,kaikyu");
    }
      
//  品目名、色、階級ごとに二つ目の選択された範囲内のデータを取得
    if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
      $hikaku2 = pg_query("SELECT  himmoku_nm, iro_nm, kaikyu,  SUM(irisu * B.kuchisu) AS  konyu_total2, SUM(B.kingaku/B.konyu_total) AS ave2,SUM(B.kingaku) as kingaku2
      FROM (((chumon_tbl as A
       INNER join konyu_himmoku_tbl as B
            on A.chumon_id = B.chumon_id)
       INNER join mst_himmoku AS C
            on B.himmoku_cd = C.himmoku_cd)
       INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
       WHERE A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."'
       GROUP BY himmoku_nm,iro_nm,kaikyu");
      }
       //  表示に使う配列の作成
      $hikaku_arr = array();
      $i = 0;
          //  取得したデータを配列に入れる
      if(!empty($hikaku)){
      $hikaku_row  =  pg_fetch_all($hikaku);
      }
      if(!empty($hikaku2)){
      $hikaku_row2  =  pg_fetch_all($hikaku2);
      }
            // 値が存在して配列であるかチェック
      if(isset($hikaku_row)  and  isset($hikaku_row2) and is_array($hikaku_row) and is_array($hikaku_row2)){
            foreach($hikaku_row as $hikaku_rows){
                foreach($hikaku_row2 as $hikaku_row2s){
                  // 品目名、色、階級が同じならば配列に入れていく
                  if($hikaku_rows['himmoku_nm'] == $hikaku_row2s['himmoku_nm'] and  $hikaku_rows['iro_nm'] == $hikaku_row2s['iro_nm'] and $hikaku_rows['kaikyu']== $hikaku_row2s['kaikyu'] ){
                    $hikaku_arr[$i]['himmoku_nm1'] = $hikaku_rows['himmoku_nm'];  
                    $hikaku_arr[$i]['himmoku_color_nm1'] = $hikaku_rows['iro_nm'];
                    $hikaku_arr[$i]['kaikyu1'] = $hikaku_rows['kaikyu'];
                    $hikaku_arr[$i]['konyu_total1'] = $hikaku_rows['konyu_total'];
                    $hikaku_arr[$i]['ave1'] = $hikaku_rows['ave'];   
                    $hikaku_arr[$i]['kingaku1'] = $hikaku_rows['kingaku'];
                    $hikaku_arr[$i]['konyu_total22'] = $hikaku_row2s['konyu_total2'];
                    $hikaku_arr[$i]['ave22'] = $hikaku_row2s['ave2'];
                    $hikaku_arr[$i]['kingaku2'] = $hikaku_row2s['kingaku2'];
                    $hikaku_arr[$i]['hikaku_a'] = $hikaku_rows['konyu_total'] - $hikaku_row2s['konyu_total2'];
                    // $hikaku_arr[$i]['hikaku_ave'] = ($hikaku_rows['kingaku'] - $hikaku_row2s['kingaku2'])  / ($hikaku_rows['konyu_total'] - $hikaku_row2s['konyu_total2']  );
                    $hikaku_arr[$i]['hikaku_kingaku'] = $hikaku_rows['kingaku'] - $hikaku_row2s['kingaku2'];
                    $i++;
                  }
                }
            }
      }
              //指定範囲内の菊の購入データ取得                 
      if(($chumon_konyu_date) and ($chumon_konyu_date2)){
       $kiku  =  pg_query("SELECT  himmoku_nm, SUM(konyu_total) as kiku_total, SUM(B.kingaku/B.konyu_total) as  kiku_avg,SUM(B.kingaku) as kiku_kingaku
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE    himmoku_nm LIKE '%菊%' and A.chumon_konyu_date  BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."' 
            GROUP BY himmoku_nm");
          }

              //指定範囲内の菊の購入データ取得 
          if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
           $kiku2  =  pg_query("SELECT  himmoku_nm, SUM(konyu_total) as kiku_total2, SUM(B.kingaku/B.konyu_total) as  kiku_avg2,SUM(B.kingaku) as kiku_kingaku2
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE  himmoku_nm LIKE '%菊%' and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."' 
            GROUP BY himmoku_nm");
          }
              //  表示に使う配列の作成
          $kiku_arr = array();
          $k = 0;
           //  取得したデータを配列に入れる
          if(!empty($kiku)){
            $kiku_row  =  pg_fetch_all($kiku);
          }
          if(!empty($kiku2)){
            $kiku_row2  =  pg_fetch_all($kiku2);
          }
            
                  // 取得した二つの配列を繋げる
          if(isset($kiku_row)and isset($kiku_row2) and is_array($kiku_row) and is_array($kiku_row2)){
            foreach($kiku_row as  $kiku_rows){
               foreach($kiku_row2 as $kiku_row2s){
                $kiku_arr[$k]['hinmoku'] = '菊';
                $kiku_arr[$k]['iro'] = '';
                $kiku_arr[$k]['kaikyu'] = '-';
                $kiku_arr[$k]['kiku_totals'] = $kiku_rows['kiku_total'];
                $kiku_arr[$k]['kiku_avgs'] = $kiku_rows['kiku_avg'];
                $kiku_arr[$k]['kiku_kingakus'] = $kiku_rows['kiku_kingaku'];
                $kiku_arr[$k]['kiku_totals2'] = $kiku_row2s['kiku_total2'];
                $kiku_arr[$k]['kiku_avgs2'] = $kiku_row2s['kiku_avg2'];
                $kiku_arr[$k]['kiku_kingakus2'] = $kiku_row2s['kiku_kingaku2'];
                $kiku_arr[$k]['kiku_sagaku'] = $kiku_rows['kiku_total'] - $kiku_row2s['kiku_total2'];
                // $kiku_arr[$k]['kiku_heikinsa'] = ($kiku_rows['kiku_kingaku'] - $kiku_row2s['kiku_kingaku2']) /  ($kiku_rows['kiku_total'] - $kiku_row2s['kiku_total2']);
                $kiku_arr[$k]['kiku_kingakusa'] = $kiku_rows['kiku_kingaku'] - $kiku_row2s['kiku_kingaku2'];
                $k++;
               }
            }
          }

                  //指定範囲内のSマムの購入データ取得 
          if(($chumon_konyu_date) and ($chumon_konyu_date2)){
            $Smamu  =  pg_query("SELECT  himmoku_nm, SUM(konyu_total) as smamu_total , SUM(B.kingaku/B.konyu_total) as smamu_avg ,SUM(B.kingaku) as smamu_kingaku
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE  himmoku_nm LIKE '%Sマム%' and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'   
            GROUP BY himmoku_nm");
          }
                //指定範囲内のSマムの購入データ取得 
          if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
            $Smamu2  =  pg_query("SELECT  himmoku_nm, SUM(konyu_total) as smamu_total2, SUM(B.kingaku/B.konyu_total) as  smamu_avg2,SUM(B.kingaku) as smamu_kingaku2
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE himmoku_nm LIKE '%Sマム%'and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."'
            GROUP BY himmoku_nm");
          }
        // 表示に使う配列の作成
          $Smamu_arr = array();
          $s = 0;
           //  取得したデータを配列に入れる
          if(!empty($Smamu)){
            $Smamu_row  =  pg_fetch_all($Smamu);
          }
          if(!empty($Smamu2)){
            $Smamu_row2  =  pg_fetch_all($Smamu2);
          }
            
                 // 取得した二つの配列を繋げる
          if(isset($Smamu_row)  and  isset($Smamu_row2) and is_array($Smamu_row) and is_array($Smamu_row2)){
            foreach($Smamu_row as  $Smamu_rows){
               foreach($Smamu_row2 as $Smamu_row2s){
                $Smamu_arr[$s]['hinmoku'] = 'Sマム';
                $Smamu_arr[$s]['iro'] = '-';
                $Smamu_arr[$s]['kaikyu'] = '';
                $Smamu_arr[$s]['Smamu_totals'] = $Smamu_rows['smamu_total'];
                $Smamu_arr[$s]['Smamu_avgs'] = $Smamu_rows['smamu_avg'];
                $Smamu_arr[$s]['Smamu_kingakus'] = $Smamu_rows['smamu_kingaku'];
                $Smamu_arr[$s]['Smamu_totals2'] = $Smamu_row2s['smamu_total2'];
                $Smamu_arr[$s]['Smamu_avgs2'] = $Smamu_row2s['smamu_avg2'];
                $Smamu_arr[$s]['Smamu_kingakus2'] = $Smamu_row2s['smamu_kingaku2'];
                $Smamu_arr[$s]['Smamu_sagaku'] = $Smamu_rows['smamu_total'] - $Smamu_row2s['smamu_total2'];
                // $Smamu_arr[$s]['Smamu_heikinsa'] = ($Smamu_rows['smamu_kingaku'] - $Smamu_row2s['smamu_kingaku2']) / ($Smamu_rows['smamu_total'] - $Smamu_row2s['smamu_total2']);
                $Smamu_arr[$s]['Smamu_kingakusa'] = $Smamu_rows['smamu_kingaku'] - $Smamu_row2s['smamu_kingaku2'];
                $s++;
               }
            }
          }


                    //指定範囲内のユリの購入データ取得 
          if(($chumon_konyu_date) and ($chumon_konyu_date2)){
            $yuri  =  pg_query("SELECT  himmoku_nm, SUM(konyu_total) as yuri_total, SUM(B.kingaku/B.konyu_total) as  yuri_avg,SUM(B.kingaku) as yuri_kingaku
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE  himmoku_nm LIKE  '%ユリ%' and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'  
            GROUP BY himmoku_nm");
          }
                       //指定範囲内のユリの購入データ取得 
          if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
            $yuri2  =  pg_query("SELECT  himmoku_nm, SUM(konyu_total) as yuri_total2, SUM(B.kingaku/B.konyu_total) as  yuri_avg2,SUM(B.kingaku) as yuri_kingaku2
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE himmoku_nm LIKE  '%ユリ%' and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."' 
            GROUP BY himmoku_nm");
          }
               // 表示に使う配列の作成
          $yuri_arr = array();
          $y = 0;
          //  取得したデータを配列に入れる
          if(!empty($yuri)){
            $yuri_row  =  pg_fetch_all($yuri);
          }
          if(!empty($yuri2)){
            $yuri_row2  =  pg_fetch_all($yuri2);
          }
            
                  // 取得した二つの配列を繋げる
          if(isset($yuri_row)  and  isset($yuri_row2) and is_array($yuri_row) and is_array(  $yuri_row2 )){
            foreach($yuri_row as  $yuri_rows){
               foreach($yuri_row2 as $yuri_row2s){
                $yuri_arr[$y]['hinmoku'] = 'ユリ';
                $yuri_arr[$y]['iro'] = '';
                $yuri_arr[$y]['kaikyu'] = '';
                $yuri_arr[$y]['yuri_totals'] = $yuri_rows['yuri_total'];
                $yuri_arr[$y]['yuri_avgs'] = $yuri_rows['yuri_avg'];
                $yuri_arr[$y]['yuri_kingakus'] = $yuri_rows['yuri_kingaku'];
                $yuri_arr[$y]['yuri_totals2'] = $yuri_row2s['yuri_total2'];
                $yuri_arr[$y]['yuri_avgs2'] = $yuri_row2s['yuri_avg2'];
                $yuri_arr[$y]['yuri_kingakus2'] = $yuri_row2s['yuri_kingaku2'];
                $yuri_arr[$y]['yuri_sagaku'] = $yuri_rows['yuri_total'] - $yuri_row2s['yuri_total2'];
                // $yuri_arr[$y]['yuri_heikinsa'] = ($yuri_rows['yuri_kingaku'] - $yuri_row2s['yuri_kingaku2']) /  ($yuri_rows['yuri_total'] - $yuri_row2s['yuri_total2']);
                $yuri_arr[$y]['yuri_kingakusa'] = $yuri_rows['yuri_kingaku'] - $yuri_row2s['yuri_kingaku2'];
                $y++;
               }
            }
          }
          // CSVで出力するための項目作成
          $koumoku = array (
            array('品目名', '色', '階級', '本数１','平均単価１','金額１','本数２','平均単価２','本数３','金額３')
        );    
        // 取得したデータを一つの配列にまとめる
        $kobetu = array_merge($Smamu_arr, $yuri_arr);
        $kobetu_to = array_merge($kiku_arr, $kobetu);
        $csv_arr = array_merge($hikaku_arr,  $kobetu_to);
        $koumokus = array_merge( $koumoku, $csv_arr);
          //  まとめた配列をSCVとして出力
          if (isset($_POST['approve'])) {
          $file  =   new SplFileObject('hikaku.csv', 'w');
             foreach ( $koumokus as $fields) {
                         $file->fputcsv($fields);
                }
              }

?>
<!DOCTYPE html>
<html lang="ja">
<head>


    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>品目任意期間比較表</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="bootstrap\css\bootstrap.min.css">
    <script src="js/main.js"></script>

   
</head>
<body>

    <div class="title_bar"> 品目任意期間比較表</div>
    <div class="container-fluid">
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <div class="display-none">
            <button class="btn btn-primary me-md-2 btn-lg" type="button">メインメニュー</button>
            </div>
          </div>
    <table  class="table">
        <tr>
            <td rowspan="2" class="border-bottom-0"></td>
            <td rowspan="2" class="border-bottom-0"></td>
            <td rowspan="2" class="border-bottom-0"></td>
            <td colspan="3" class="text-center border-bottom-0 border-top border-end border-start">日付範囲指定１</td>
            <td colspan="3" class="text-center border-bottom-0 border-top border-end border-start">日付範囲指定2</td>   
        <tr>  
        <tr>
            <td rowspan="2">
            <td rowspan="2">
            <td rowspan="2" class="border-end">
            <td colspan="3" class="example text-center border-end">
            <form name="form1" method="POST">
                <input id="date" type="date" name="date" onchange="transition('hikaku_kanri.php')" value="<?print($chumon_konyu_date);?>">~
                <input type="date" onchange="transition('hikaku_kanri.php')" name="date2" value="<?print($chumon_konyu_date2);?>"></td> 
            <td colspan="3" class="example text-center">
                <input type="date" onchange="transition('hikaku_kanri.php')" name="date3" value="<?print($chumon_konyu_date3);?>">~
                <input type="date" onchange="transition('hikaku_kanri.php')" name="date4" value="<?print($chumon_konyu_date4);?>"/>
              </td>  
            
            <td colspan="3" class="border-start ">
               <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                 <div class="display-none">
                  <button type="submit" name="approve" class="btn btn-primary me-md-2 btn-lg" >データのエクスポート  </button>
                  <input type="button" class="btn btn-primary me-md-2 btn-lg" value="このページを印刷する" onclick="window.print();">
                 </div>
               </div>
            </td>            
        <tr>
        <tr>
            <td rowspan="2" class="border-start text-center">品目名
            <td rowspan="2" class="border-start border-end text-center">色
            <td rowspan="2" class="border-start border-end text-center">階級
            <td rowspan="2" class="border-start border-end text-center">本数
            <td rowspan="2" class="border-end text-center">平均単価 
            <td rowspan="2" class="border-end text-center">金額
            <td rowspan="2" class="border-end text-center">本数
            <td rowspan="2" class="border-end text-center">平均単価
            <td rowspan="2" class="border-end text-center">金額
            <td colspan="3" class="border-end text-center">「日付範囲指定１」と「日付範囲指定２」の差</td>
          <tr>
              <td class="border-end text-center">本数</td>  
              <td class="border-end text-center">金額</td>
              <? if ($hikaku_arr != null){foreach($hikaku_arr as $hikaku_arrs){?>
        <tr>    
          <td  class="border-start text-center"><?print_r($hikaku_arrs['himmoku_nm1'])?></td>
          <td  class="border-start text-center"><?print_r($hikaku_arrs['himmoku_color_nm1'])?></td>
          <td  class="border-start text-center"><?print_r($hikaku_arrs['kaikyu1'])?></td>
          <td style="background: #b5eeb5" class="border-start text-center"><?print_r($hikaku_arrs['konyu_total1'])?></td>
          <td style="background: #b5eeb5" class="border-start text-center"><?print_r($hikaku_arrs['ave1'])?></td>
          <td style="background: #b5eeb5" class="border-start text-center"><?print_r($hikaku_arrs['kingaku1'])?></td>
          <td style="background: #9faaf3" class="border-start text-center"><?print_r($hikaku_arrs['konyu_total22'])?></td>
          <td style="background: #9faaf3" class="border-start text-center"><?print_r($hikaku_arrs['ave22'])?></td>
          <td style="background: #9faaf3" class="border-start text-center"><?print_r($hikaku_arrs['kingaku2'])?></td>
          <td style="background: #ffff00" class="border-start text-center"><?print_r($hikaku_arrs['hikaku_a'])?></td>
          <!-- <td style="background: #ffff00" class="border-start text-center"><?print_r($hikaku_arrs['hikaku_ave'])?></td> -->
          <td style="background: #ffff00" class="border-start border-end text-center"><?print_r($hikaku_arrs['hikaku_kingaku'])?></td>
            <?
            }
           }
         ?>
         <tr>
          <td class="border-start text-center">菊
          <td class="border-start text-center">
          <td class="border-start text-center border-end ">-
          <? if ($kiku_arr != null){
            foreach($kiku_arr as $kiku_arrs){
              ?>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_totals'])?></td>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_avgs'])?></td>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_kingakus'])?></td>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_totals2'])?></td>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_avgs2'])?></td>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_kingakus2'])?></td>
            <td class="border-start text-center"><?print_r($kiku_arrs['kiku_sagaku'])?></td>
            <!-- <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_heikinsa'])?></td> -->
            <td  class="border-start border-end text-center"><?print_r($kiku_arrs['kiku_kingakusa'])?></td>
            <?
           }
          }
        ?>

          <tr>
          <td class="border-start text-center">Sマム
          <td class="border-start text-center">-
          <td class="border-start text-center border-end ">
          <? if ($Smamu_arr != null){
            foreach($Smamu_arr as $Smamu_arrs){
              ?>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_totals'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_avgs'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_kingakus'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_totals2'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_avgs2'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_kingakus2'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_sagaku'])?></td>
            <!-- <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_heikinsa'])?></td> -->
            <td  class="border-start border-end text-center"><?print_r($Smamu_arrs['Smamu_kingakusa'])?></td>
            <?
           }
          }
        ?>
          <tr>
          <td class="border-start text-center">ユリ
          <td class="border-start text-center">
          <td class="border-start text-center border-end ">
          <? if($yuri_arr != null){
            foreach($yuri_arr as $yuri_arrs){
              ?>
            <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_totals'])?></td>
            <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_avgs'])?></td>
            <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_kingakus'])?></td>
            <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_totals2'])?></td>
            <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_avgs2'])?></td>
            <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_kingakus2'])?></td>
            <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_sagaku'])?></td>
            <!-- <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_heikinsa'])?></td> -->
            <td  class="border-start border-end text-center"><?print_r($yuri_arrs['yuri_kingakusa'])?></td>
            <?
           }
          }
        ?>
      </table>
    </div> 
</div>
</form>
</body>
</html>