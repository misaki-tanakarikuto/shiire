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
  $hikaku = pg_query("SELECT  himmoku_nm, iro_nm, tokyu,  SUM(irisu * B.kuchisu) AS  honsu, SUM(B.kingaku/irisu * B.kuchisu) AS ave,SUM(B.kingaku) as kingaku
            FROM (((chumon_tbl as A
      INNER join konyu_himmoku_tbl as B
       on A.chumon_id = B.chumon_id)
      INNER join mst_himmoku AS C
       on B.himmoku_cd = C.himmoku_cd)
      INNER join mst_himmoku_color as F
      on  F.himmoku_color_cd = B.iro_cd)
      WHERE A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'
      GROUP BY himmoku_nm,iro_nm,tokyu");
    }
      
//  品目名、色、階級ごとに二つ目の選択された範囲内のデータを取得
    if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
      $hikaku2 = pg_query("SELECT  himmoku_nm, iro_nm, tokyu,  SUM(irisu * B.kuchisu) AS  honsu2, SUM(B.kingaku/irisu * B.kuchisu) AS ave2,SUM(B.kingaku) as kingaku2
      FROM (((chumon_tbl as A
       INNER join konyu_himmoku_tbl as B
            on A.chumon_id = B.chumon_id)
       INNER join mst_himmoku AS C
            on B.himmoku_cd = C.himmoku_cd)
       INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
       WHERE A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."'
       GROUP BY himmoku_nm,iro_nm,tokyu");
      }
       //  表示に使う配列の作成
      $hikaku_arr = array();
      $i = 0;
          //  取得したデータを配列に入れる
          if(!empty($hikaku)){
          $hikaku_row = pg_fetch_all($hikaku);
          }
          if(!empty($hikaku2)){
          $hikaku_row2 = pg_fetch_all($hikaku2);
          }
            // 値が存在して配列であるかチェック
      if(isset($hikaku_row)  and  isset($hikaku_row2) and is_array($hikaku_row) and is_array($hikaku_row2)){
            foreach($hikaku_row as $hikaku_rows){
                foreach($hikaku_row2 as $hikaku_row2s){
                  // 品目名、色、階級が同じならば配列に入れていく
                  if($hikaku_rows['himmoku_nm'] == $hikaku_row2s['himmoku_nm'] and  $hikaku_rows['iro_nm'] == $hikaku_row2s['iro_nm'] and $hikaku_rows['tokyu']== $hikaku_row2s['tokyu'] ){
                    $hikaku_arr[$i]['himmoku_nm1'] = $hikaku_rows['himmoku_nm'];  
                    $hikaku_arr[$i]['himmoku_color_nm1'] = $hikaku_rows['iro_nm'];
                    $hikaku_arr[$i]['kaikyu1'] = $hikaku_rows['tokyu'];
                    $hikaku_arr[$i]['honsu1'] = $hikaku_rows['honsu'];
                    $hikaku_arr[$i]['ave1'] = $hikaku_rows['ave'];   
                    $hikaku_arr[$i]['kingaku1'] = $hikaku_rows['kingaku'];
                    $hikaku_arr[$i]['honsu22'] = $hikaku_row2s['honsu2'];
                    $hikaku_arr[$i]['ave22'] = $hikaku_row2s['ave2'];
                    $hikaku_arr[$i]['kingaku22'] = $hikaku_row2s['kingaku2'];
                    $hikaku_arr[$i]['hikaku_a'] = $hikaku_rows['honsu'] - $hikaku_row2s['honsu2'];
                    // $hikaku_arr[$i]['hikaku_ave'] = ($hikaku_rows['kingaku'] - $hikaku_row2s['kingaku22'])  / ($hikaku_rows['irisu'] - $hikaku_row2s['ichiba_irisu2']  );
                    $hikaku_arr[$i]['hikaku_ichiba_kingaku'] = $hikaku_rows['kingaku'] - $hikaku_row2s['kingaku2'];
                    $i++;
                  }
                }
            }
      }
              //指定範囲内の菊の購入データ取得                 
      if(($chumon_konyu_date) and ($chumon_konyu_date2)){
       $kiku  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as kiku_honsu, SUM(B.kingaku/irisu * B.kuchisu) as  kiku_avg,SUM(B.kingaku) as kiku_ichiba_kingaku
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE    C.himmoku_cd = 1 and A.chumon_konyu_date  BETWEEN '".$chumon_konyu_date."'  and  '".$chumon_konyu_date2."' 
            GROUP BY himmoku_nm");
          }

              //指定範囲内の菊の購入データ取得 
          if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
           $kiku2  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as kiku_honsu2, SUM(B.kingaku/irisu * B.kuchisu) as  kiku_avg2,SUM(B.kingaku) as kiku_ichiba_kingaku2
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE  C.himmoku_cd = 1 and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."' 
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
                $kiku_arr[$k]['tokyu'] = '-';
                $kiku_arr[$k]['kiku_totals'] = $kiku_rows['kiku_honsu'];
                $kiku_arr[$k]['kiku_avgs'] = $kiku_rows['kiku_avg'];
                $kiku_arr[$k]['kiku_ichiba_kingakus'] = $kiku_rows['kiku_ichiba_kingaku'];
                $kiku_arr[$k]['kiku_totals2'] = $kiku_row2s['kiku_honsu2'];
                $kiku_arr[$k]['kiku_avgs2'] = $kiku_row2s['kiku_avg2'];
                $kiku_arr[$k]['kiku_ichiba_kingakus2'] = $kiku_row2s['kiku_ichiba_kingaku2'];
                $kiku_arr[$k]['kiku_sagaku'] = $kiku_rows['kiku_honsu'] - $kiku_row2s['kiku_honsu2'];
                // $kiku_arr[$k]['kiku_heikinsa'] = ($kiku_rows['kiku_ichiba_kingaku'] - $kiku_row2s['kiku_ichiba_kingaku2']) /  ($kiku_rows['kiku_honsu'] - $kiku_row2s['kiku_honsu']);
                $kiku_arr[$k]['kiku_ichiba_kingakusa'] = $kiku_rows['kiku_ichiba_kingaku'] - $kiku_row2s['kiku_ichiba_kingaku2'];
                $k++;
               }
            }
          }

                  //指定範囲内のSマムの購入データ取得 
          if(($chumon_konyu_date) and ($chumon_konyu_date2)){
            $Smamu  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as smamu_honsu , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg ,SUM(B.kingaku) as smamu_ichiba_kingaku
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE  C.himmoku_cd = 3  and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'   
            GROUP BY himmoku_nm");
          }
                //指定範囲内のSマムの購入データ取得 
          if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
            $Smamu2  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as smamu_honsu2, SUM(B.kingaku/irisu * B.kuchisu) as  smamu_avg2,SUM(B.kingaku) as smamu_ichiba_kingaku2
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE C.himmoku_cd = 3 and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."'
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
                $Smamu_arr[$s]['tokyu'] = '';
                $Smamu_arr[$s]['Smamu_totals'] = $Smamu_rows['smamu_honsu'];
                $Smamu_arr[$s]['Smamu_avgs'] = $Smamu_rows['smamu_avg'];
                $Smamu_arr[$s]['Smamu_ichiba_kingakus'] = $Smamu_rows['smamu_ichiba_kingaku'];
                $Smamu_arr[$s]['Smamu_totals2'] = $Smamu_row2s['smamu_honsu2'];
                $Smamu_arr[$s]['Smamu_avgs2'] = $Smamu_row2s['smamu_avg2'];
                $Smamu_arr[$s]['Smamu_ichiba_kingakus2'] = $Smamu_row2s['smamu_ichiba_kingaku2'];
                $Smamu_arr[$s]['Smamu_sagaku'] = $Smamu_rows['smamu_honsu'] - $Smamu_row2s['smamu_honsu2'];
                // $Smamu_arr[$s]['Smamu_heikinsa'] = ($Smamu_rows['smamu_ichiba_kingaku'] - $Smamu_row2s['smamu_ichiba_kingaku2']) / ($Smamu_rows['smamu_total'] - $Smamu_row2s['smamu_total2']);
                $Smamu_arr[$s]['Smamu_ichiba_kingakusa'] = $Smamu_rows['smamu_ichiba_kingaku'] - $Smamu_row2s['smamu_ichiba_kingaku2'];
                $s++;
               }
            }
          }


                    //指定範囲内のユリの購入データ取得 
          if(($chumon_konyu_date) and ($chumon_konyu_date2)){
            $yuri  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as yuri_honsu, SUM(B.kingaku/irisu * B.kuchisu) as  yuri_avg,SUM(B.kingaku) as yuri_ichiba_kingaku
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE   C.himmoku_cd = 11 and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'  
            GROUP BY himmoku_nm");
          }
                       //指定範囲内のユリの購入データ取得 
          if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
            $yuri2  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as yuri_honsu, SUM(B.kingaku/irisu * B.kuchisu) as  yuri_avg2,SUM(B.kingaku) as yuri_ichiba_kingaku2
      FROM (((chumon_tbl as A
            INNER join konyu_himmoku_tbl as B
             on A.chumon_id = B.chumon_id)
            INNER join mst_himmoku AS C
             on B.himmoku_cd = C.himmoku_cd)
            INNER join mst_himmoku_color as F
            on  F.himmoku_color_cd = B.iro_cd)
            WHERE C.himmoku_cd = 11 and A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."' 
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
                $yuri_arr[$y]['tokyu'] = '';
                $yuri_arr[$y]['yuri_totals'] = $yuri_rows['yuri_honsu'];
                $yuri_arr[$y]['yuri_avgs'] = $yuri_rows['yuri_avg'];
                $yuri_arr[$y]['yuri_ichiba_kingakus'] = $yuri_rows['yuri_ichiba_kingaku'];
                $yuri_arr[$y]['yuri_totals2'] = $yuri_row2s['yuri_honsu2'];
                $yuri_arr[$y]['yuri_avgs2'] = $yuri_row2s['yuri_avg2'];
                $yuri_arr[$y]['yuri_ichiba_kingakus2'] = $yuri_row2s['yuri_ichiba_kingaku2'];
                $yuri_arr[$y]['yuri_sagaku'] = $yuri_rows['yuri_honsu'] - $yuri_row2s['yuri_honsu2'];
                // $yuri_arr[$y]['yuri_heikinsa'] = ($yuri_rows['yuri_ichiba_kingaku'] - $yuri_row2s['yuri_ichiba_kingaku2']) /  ($yuri_rows['yuri_total'] - $yuri_row2s['yuri_total2']);
                $yuri_arr[$y]['yuri_ichiba_kingakusa'] = $yuri_rows['yuri_ichiba_kingaku'] - $yuri_row2s['yuri_ichiba_kingaku2'];
                $y++;
               }
            }
          }

                  //指定範囲内の色がWのSマム購入データ取得 
                  if(($chumon_konyu_date) and ($chumon_konyu_date2)){
                    $Smamu_w  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as smamu_honsu_w , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg_w ,SUM(B.kingaku) as smamu_ichiba_kingaku_w
              FROM (((chumon_tbl as A
                    INNER join konyu_himmoku_tbl as B
                     on A.chumon_id = B.chumon_id)
                    INNER join mst_himmoku AS C
                     on B.himmoku_cd = C.himmoku_cd)
                    INNER join mst_himmoku_color as F
                    on  F.himmoku_color_cd = B.iro_cd)
                    WHERE  C.himmoku_cd = 3  and F.himmoku_color_cd = 2 and  A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'   
                    GROUP BY himmoku_nm");
                  }          



                      //指定範囲内の色がWのSマム購入データ取得 
                  if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
                    $Smamu_w2  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as smamu_honsu_w2 , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg_w2 ,SUM(B.kingaku) as smamu_ichiba_kingaku_w2
              FROM (((chumon_tbl as A
                    INNER join konyu_himmoku_tbl as B
                     on A.chumon_id = B.chumon_id)
                    INNER join mst_himmoku AS C
                     on B.himmoku_cd = C.himmoku_cd)
                    INNER join mst_himmoku_color as F
                    on  F.himmoku_color_cd = B.iro_cd)
                    WHERE  C.himmoku_cd = 3  and F.himmoku_color_cd = 2 and  A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."'   
                    GROUP BY himmoku_nm");
                  }          

                  // 表示に使う配列の作成
          $Smamu_arr_w = array();
          $sw = 0;
           //  取得したデータを配列に入れる
          if(!empty($Smamu_w)){
            $Smamu_row_w  =  pg_fetch_all($Smamu_w);
          }
          if(!empty($Smamu_w2)){
            $Smamu_row_w2  =  pg_fetch_all($Smamu_w2);
          }


                   // 取得した二つの配列を繋げる
                   if(isset($Smamu_row_w)  and  isset($Smamu_row_w2) and is_array($Smamu_row_w) and is_array($Smamu_row_w2)){
                    foreach($Smamu_row_w as  $Smamu_row_ws){
                       foreach($Smamu_row_w2 as $Smamu_row_w2s){
                        $Smamu_arr_w[$sw]['hinmoku'] = 'Sマム';
                        $Smamu_arr_w[$sw]['iro'] = 'w';
                        $Smamu_arr_w[$sw]['tokyu'] = '';
                        $Smamu_arr_w[$sw]['Smamu_totals_w'] = $Smamu_row_ws['smamu_honsu_w'];
                        $Smamu_arr_w[$sw]['Smamu_avgs_w'] = $Smamu_row_ws['smamu_avg_w'];
                        $Smamu_arr_w[$sw]['Smamu_ichiba_kingakus_w'] = $Smamu_row_ws['smamu_ichiba_kingaku_w'];
                        $Smamu_arr_w[$sw]['Smamu_totals2_w'] = $Smamu_row_w2s['smamu_honsu_w2'];
                        $Smamu_arr_w[$sw]['Smamu_avgs2_w'] = $Smamu_row_w2s['smamu_avg_w2'];
                        $Smamu_arr_w[$sw]['Smamu_ichiba_kingakus2_w'] = $Smamu_row_w2s['smamu_ichiba_kingaku_w2'];
                        $Smamu_arr_w[$sw]['Smamu_sagaku_w'] = $Smamu_row_ws['smamu_honsu_w'] - $Smamu_row_w2s['smamu_honsu_w2'];
                        // $Smamu_arr[$s]['Smamu_heikinsa'] = ($Smamu_rows['smamu_ichiba_kingaku'] - $Smamu_row2s['smamu_ichiba_kingaku2']) / ($Smamu_rows['smamu_total'] - $Smamu_row2s['smamu_total2']);
                        $Smamu_arr_w[$sw]['Smamu_ichiba_kingakusa_w'] = $Smamu_row_ws['smamu_ichiba_kingaku_w'] - $Smamu_row_w2s['smamu_ichiba_kingaku_w2'];
                        $sw++;
                       }
                    }
                  }
        
 //指定範囲内の色がPのSマム購入データ取得 
 if(($chumon_konyu_date) and ($chumon_konyu_date2)){
  $Smamu_p  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as smamu_honsu_p , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg_p ,SUM(B.kingaku) as smamu_ichiba_kingaku_p
FROM (((chumon_tbl as A
  INNER join konyu_himmoku_tbl as B
   on A.chumon_id = B.chumon_id)
  INNER join mst_himmoku AS C
   on B.himmoku_cd = C.himmoku_cd)
  INNER join mst_himmoku_color as F
  on  F.himmoku_color_cd = B.iro_cd)
  WHERE  C.himmoku_cd = 3  and F.himmoku_color_cd = 3 and  A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'   
  GROUP BY himmoku_nm");
}          



    //指定範囲内の色がPのSマム購入データ取得 
if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
  $Smamu_p2  =  pg_query("SELECT  himmoku_nm, SUM(irisu * B.kuchisu) as smamu_honsu_p2 , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg_p2 ,SUM(B.kingaku) as smamu_ichiba_kingaku_p2
FROM (((chumon_tbl as A
  INNER join konyu_himmoku_tbl as B
   on A.chumon_id = B.chumon_id)
  INNER join mst_himmoku AS C
   on B.himmoku_cd = C.himmoku_cd)
  INNER join mst_himmoku_color as F
  on  F.himmoku_color_cd = B.iro_cd)
  WHERE  C.himmoku_cd = 3  and F.himmoku_color_cd = 3 and  A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."'   
  GROUP BY himmoku_nm");
}          

// 表示に使う配列の作成
$Smamu_arr_p = array();
$sp = 0;
//  取得したデータを配列に入れる
if(!empty($Smamu_p)){
$Smamu_row_p  =  pg_fetch_all($Smamu_p);
}
if(!empty($Smamu_p2)){
$Smamu_row_p2  =  pg_fetch_all($Smamu_p2);
}


 // 取得した二つの配列を繋げる
 if(isset($Smamu_row_p)  and  isset($Smamu_row_p2) and is_array($Smamu_row_p) and is_array($Smamu_row_p2)){
  foreach($Smamu_row_p as  $Smamu_row_ps){
     foreach($Smamu_row_p2 as $Smamu_row_p2s){
      $Smamu_arr_p[$sp]['hinmoku'] = 'Sマム';
      $Smamu_arr_p[$sp]['iro'] = 'P';
      $Smamu_arr_p[$sp]['tokyu'] = '';
      $Smamu_arr_p[$sp]['Smamu_totals_p'] = $Smamu_row_ps['smamu_honsu_p'];
      $Smamu_arr_p[$sp]['Smamu_avgs_p'] = $Smamu_row_ps['smamu_avg_p'];
      $Smamu_arr_p[$sp]['Smamu_ichiba_kingakus_p'] = $Smamu_row_ps['smamu_ichiba_kingaku_p'];
      $Smamu_arr_p[$sp]['Smamu_totals2_p'] = $Smamu_row_p2s['smamu_honsu_p2'];
      $Smamu_arr_p[$sp]['Smamu_avgs2_p'] = $Smamu_row_p2s['smamu_avg_p2'];
      $Smamu_arr_p[$sp]['Smamu_ichiba_kingakus2_p'] = $Smamu_row_p2s['smamu_ichiba_kingaku_p2'];
      $Smamu_arr_p[$sp]['Smamu_sagaku_p'] = $Smamu_row_ps['smamu_honsu_p'] - $Smamu_row_p2s['smamu_honsu_p2'];
      // $Smamu_arr[$s]['Smamu_heikinsa'] = ($Smamu_rows['smamu_ichiba_kingaku'] - $Smamu_row2s['smamu_ichiba_kingaku2']) / ($Smamu_rows['smamu_total'] - $Smamu_row2s['smamu_total2']);
      $Smamu_arr_p[$sp]['Smamu_ichiba_kingakusa_p'] = $Smamu_row_ps['smamu_ichiba_kingaku_p'] - $Smamu_row_p2s['smamu_ichiba_kingaku_p2'];
      $sp++;
     }
  }
}

   
 //指定範囲内の色がYのSマム購入データ取得 
 if(($chumon_konyu_date) and ($chumon_konyu_date2)){
  $Smamu_y  =  pg_query("SELECT  himmoku_nm, SUM(irisu) as smamu_honsu_y , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg_y ,SUM(B.kingaku) as smamu_ichiba_kingaku_y
FROM (((chumon_tbl as A
  INNER join konyu_himmoku_tbl as B
   on A.chumon_id = B.chumon_id)
  INNER join mst_himmoku AS C
   on B.himmoku_cd = C.himmoku_cd)
  INNER join mst_himmoku_color as F
  on  F.himmoku_color_cd = B.iro_cd)
  WHERE  C.himmoku_cd = 3  and F.himmoku_color_cd = 4 and  A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'   
  GROUP BY himmoku_nm");
}          



    //指定範囲内の色がYのSマム購入データ取得 
if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
  $Smamu_y2  =  pg_query("SELECT  himmoku_nm, SUM(irisu) as smamu_honsu_y2 , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg_y2 ,SUM(B.kingaku) as smamu_ichiba_kingaku_y2
FROM (((chumon_tbl as A
  INNER join konyu_himmoku_tbl as B
   on A.chumon_id = B.chumon_id)
  INNER join mst_himmoku AS C
   on B.himmoku_cd = C.himmoku_cd)
  INNER join mst_himmoku_color as F
  on  F.himmoku_color_cd = B.iro_cd)
  WHERE  C.himmoku_cd = 3  and F.himmoku_color_cd = 4 and  A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."'   
  GROUP BY himmoku_nm");
}          

// 表示に使う配列の作成
$Smamu_arr_y = array();
$sy = 0;
//  取得したデータを配列に入れる
if(!empty($Smamu_y)){
$Smamu_row_y  =  pg_fetch_all($Smamu_y);
}
if(!empty($Smamu_y2)){
$Smamu_row_y2  =  pg_fetch_all($Smamu_y2);
}


 // 取得した二つの配列を繋げる
 if(isset($Smamu_row_y)  and  isset($Smamu_row_y2) and is_array($Smamu_row_y) and is_array($Smamu_row_y2)){
  foreach($Smamu_row_y as  $Smamu_row_ys){
     foreach($Smamu_row_y2 as $Smamu_row_y2s){
      $Smamu_arr_y[$sy]['hinmoku'] = 'Sマム';
      $Smamu_arr_y[$sy]['iro'] = 'Y';
      $Smamu_arr_y[$sy]['tokyu'] = '';
      $Smamu_arr_y[$sy]['Smamu_totals_y'] = $Smamu_row_ys['smamu_honsu_y'];
      $Smamu_arr_y[$sy]['Smamu_avgs_y'] = $Smamu_row_ys['smamu_avg_y'];
      $Smamu_arr_y[$sy]['Smamu_ichiba_kingakus_y'] = $Smamu_row_ys['smamu_ichiba_kingaku_y'];
      $Smamu_arr_y[$sy]['Smamu_totals2_y'] = $Smamu_row_y2s['smamu_honsu_y2'];
      $Smamu_arr_y[$sy]['Smamu_avgs2_y'] = $Smamu_row_y2s['smamu_avg_y2'];
      $Smamu_arr_y[$sy]['Smamu_ichiba_kingakus2_y'] = $Smamu_row_y2s['smamu_ichiba_kingaku_y2'];
      $Smamu_arr_y[$sy]['Smamu_sagaku_y'] = $Smamu_row_ys['smamu_honsu_y'] - $Smamu_row_y2s['smamu_honsu_y2'];
      // $Smamu_arr[$s]['Smamu_heikinsa'] = ($Smamu_rows['smamu_ichiba_kingaku'] - $Smamu_row2s['smamu_ichiba_kingaku2']) / ($Smamu_rows['smamu_total'] - $Smamu_row2s['smamu_total2']);
      $Smamu_arr_y[$sy]['Smamu_ichiba_kingakusa_y'] = $Smamu_row_ys['smamu_ichiba_kingaku_y'] - $Smamu_row_y2s['smamu_ichiba_kingaku_y2'];
      $sy++;
     }
  }
}




 //指定範囲内の色がmixのSマム購入データ取得 
 if(($chumon_konyu_date) and ($chumon_konyu_date2)){
  $Smamu_mix  =  pg_query("SELECT  himmoku_nm, SUM(irisu) as smamu_honsu_mix , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg_mix ,SUM(B.kingaku) as smamu_ichiba_kingaku_mix
FROM (((chumon_tbl as A
  INNER join konyu_himmoku_tbl as B
   on A.chumon_id = B.chumon_id)
  INNER join mst_himmoku AS C
   on B.himmoku_cd = C.himmoku_cd)
  INNER join mst_himmoku_color as F
  on  F.himmoku_color_cd = B.iro_cd)
  WHERE  C.himmoku_cd = 3  and F.himmoku_color_cd = 5 and  A.chumon_konyu_date BETWEEN '".$chumon_konyu_date."' and  '".$chumon_konyu_date2."'   
  GROUP BY himmoku_nm");
}          



    //指定範囲内の色がmixのSマム購入データ取得 
if(($chumon_konyu_date3) and ($chumon_konyu_date4)){
  $Smamu_mix2  =  pg_query("SELECT  himmoku_nm, SUM(irisu) as smamu_honsu_mix2 , SUM(B.kingaku/irisu * B.kuchisu) as smamu_avg_mix2 ,SUM(B.kingaku) as smamu_ichiba_kingaku_mix2
FROM (((chumon_tbl as A
  INNER join konyu_himmoku_tbl as B
   on A.chumon_id = B.chumon_id)
  INNER join mst_himmoku AS C
   on B.himmoku_cd = C.himmoku_cd)
  INNER join mst_himmoku_color as F
  on  F.himmoku_color_cd = B.iro_cd)
  WHERE  C.himmoku_cd = 3  and F.himmoku_color_cd = 5 and  A.chumon_konyu_date BETWEEN '".$chumon_konyu_date3."' and  '".$chumon_konyu_date4."'   
  GROUP BY himmoku_nm");
}          

// 表示に使う配列の作成
$Smamu_arr_mix = array();
$smix = 0;
//  取得したデータを配列に入れる
if(!empty($Smamu_mix)){
$Smamu_row_mix  =  pg_fetch_all($Smamu_mix);
}
if(!empty($Smamu_mix2)){
$Smamu_row_mix2  =  pg_fetch_all($Smamu_mix2);
}


 // 取得した二つの配列を繋げる
 if(isset($Smamu_row_mix)  and  isset($Smamu_row_mix2) and is_array($Smamu_row_mix) and is_array($Smamu_row_mix2)){
  foreach($Smamu_row_mix as  $Smamu_row_mixs){
     foreach($Smamu_row_mix2 as $Smamu_row_mix2s){
      $Smamu_arr_mix[$smix]['hinmoku'] = 'Sマム';
      $Smamu_arr_mix[$smix]['iro'] = 'mix';
      $Smamu_arr_mix[$smix]['tokyu'] = '';
      $Smamu_arr_mix[$smix]['Smamu_totals_mix'] = $Smamu_row_mixs['smamu_honsu_mix'];
      $Smamu_arr_mix[$smix]['Smamu_avgs_mix'] = $Smamu_row_mixs['smamu_avg_mix'];
      $Smamu_arr_mix[$smix]['Smamu_ichiba_kingakus_mix'] = $Smamu_row_mixs['smamu_ichiba_kingaku_mix'];
      $Smamu_arr_mix[$smix]['Smamu_totals2_mix'] = $Smamu_row_mix2s['smamu_honsu_mix2'];
      $Smamu_arr_mix[$smix]['Smamu_avgs2_mix'] = $Smamu_row_mix2s['smamu_avg_mix2'];
      $Smamu_arr_mix[$smix]['Smamu_ichiba_kingakus2_mix'] = $Smamu_row_mix2s['smamu_ichiba_kingaku_mix2'];
      $Smamu_arr_mix[$smix]['Smamu_sagaku_mix'] = $Smamu_row_mixs['smamu_honsu_mix'] - $Smamu_row_mix2s['smamu_honsu_mix2'];
      // $Smamu_arr[$s]['Smamu_heikinsa'] = ($Smamu_rows['smamu_ichiba_kingaku'] - $Smamu_row2s['smamu_ichiba_kingaku2']) / ($Smamu_rows['smamu_total'] - $Smamu_row2s['smamu_total2']);
      $Smamu_arr_mix[$smix]['Smamu_ichiba_kingakusa_mix'] = $Smamu_row_mixs['smamu_ichiba_kingaku_mix'] - $Smamu_row_mix2s['smamu_ichiba_kingaku_mix2'];
      $smix++;
     }
  }
}

          // CSVで出力するための項目作成
          $koumoku = array (
            array('品目名', '色', '階級', '本数１','平均単価１','金額１','本数２','平均単価２','本数３','金額３')
        );    
        // 取得したデータを一つの配列にまとめる
        $Smamu_arr_y_mix = array_merge($Smamu_arr_y , $Smamu_arr_mix);

        $Smamu_arr_p_y_mix = array_merge($Smamu_arr_p , $Smamu_arr_y_mix);

        $Smamu_arr_w_p_y_mix = array_merge($Smamu_arr_w , $Smamu_arr_p_y_mix);

        $yuri_Smamu_arr_w_p_y_mix = array_merge($yuri_arr , $Smamu_arr_w_p_y_mix);

        $kobetu = array_merge($Smamu_arr, $yuri_Smamu_arr_w_p_y_mix);

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
            <td rowspan="2" style="background:   #f5f5f5" class="border-start text-center">品目名
            <td rowspan="2" style="background:   #f5f5f5" class="border-start border-end text-center">色
            <td rowspan="2" style="background:   #f5f5f5" class="border-start border-end text-center">階級
            <td rowspan="2" style="background:   #f5f5f5" class="border-start border-end text-center">本数
            <td rowspan="2" style="background:   #f5f5f5" class="border-end text-center">平均単価 
            <td rowspan="2" style="background:   #f5f5f5" class="border-end text-center">金額
            <td rowspan="2" style="background:   #f5f5f5" class="border-end text-center">本数
            <td rowspan="2" style="background:   #f5f5f5" class="border-end text-center">平均単価
            <td rowspan="2" style="background:   #f5f5f5" class="border-end text-center">金額
            <td colspan="3" style="background:   #f5f5f5" class="border-end text-center">「日付範囲指定１」と「日付範囲指定２」の差</td>
          <tr>
              <td style="background:   #f5f5f5" class="border-end text-center">本数</td>  
              <td style="background:   #f5f5f5" class="border-end text-center">金額</td>
              <? if ($hikaku_arr != null){foreach($hikaku_arr as $hikaku_arrs){?>
        <tr>    
          <td  class="border-start text-center"><?print_r($hikaku_arrs['himmoku_nm1'])?></td>
          <td  class="border-start text-center"><?print_r($hikaku_arrs['himmoku_color_nm1'])?></td>
          <td  class="border-start text-center"><?print_r($hikaku_arrs['kaikyu1'])?></td>
          <td style="background: #b5eeb5" class="border-start text-center"><?print_r($hikaku_arrs['honsu1'])?></td>
          <td style="background: #b5eeb5" class="border-start text-center"><?print_r($hikaku_arrs['ave1'])?></td>
          <td style="background: #b5eeb5" class="border-start text-center"><?print_r($hikaku_arrs['kingaku1'])?></td>
          <td style="background: #9faaf3" class="border-start text-center"><?print_r($hikaku_arrs['honsu22'])?></td>
          <td style="background: #9faaf3" class="border-start text-center"><?print_r($hikaku_arrs['ave22'])?></td>
          <td style="background: #9faaf3" class="border-start text-center"><?print_r($hikaku_arrs['kingaku22'])?></td>
          <td style="background: #ffff00" class="border-start text-center"><?print_r($hikaku_arrs['hikaku_a'])?></td>
          <!-- <td style="background: #ffff00" class="border-start text-center"><?print_r($hikaku_arrs['hikaku_ave'])?></td> -->
          <td style="background: #ffff00" class="border-start border-end text-center"><?print_r($hikaku_arrs['hikaku_ichiba_kingaku'])?></td>
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
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_ichiba_kingakus'])?></td>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_totals2'])?></td>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_avgs2'])?></td>
            <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_ichiba_kingakus2'])?></td>
            <td class="border-start text-center"><?print_r($kiku_arrs['kiku_sagaku'])?></td>
            <!-- <td  class="border-start text-center"><?print_r($kiku_arrs['kiku_heikinsa'])?></td> -->
            <td  class="border-start border-end text-center"><?print_r($kiku_arrs['kiku_ichiba_kingakusa'])?></td>
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
            <td  class="border-start text-center "><?print_r($Smamu_arrs['Smamu_totals'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_avgs'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_ichiba_kingakus'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_totals2'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_avgs2'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_ichiba_kingakus2'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_sagaku'])?></td>
            <!-- <td  class="border-start text-center"><?print_r($Smamu_arrs['Smamu_heikinsa'])?></td> -->
            <td  class="border-start border-end text-center"><?print_r($Smamu_arrs['Smamu_ichiba_kingakusa'])?></td>
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
            <td  class="border-start text-center border-top"><?print_r($yuri_arrs['yuri_totals'])?></td>
            <td  class="border-start text-center border-top"><?print_r($yuri_arrs['yuri_avgs'])?></td>
            <td  class="border-start text-center border-top"><?print_r($yuri_arrs['yuri_ichiba_kingakus'])?></td>
            <td  class="border-start text-center border-top"><?print_r($yuri_arrs['yuri_totals2'])?></td>
            <td  class="border-start text-center border-top"><?print_r($yuri_arrs['yuri_avgs2'])?></td>
            <td  class="border-start text-center border-top"><?print_r($yuri_arrs['yuri_ichiba_kingakus2'])?></td>
            <td  class="border-start text-center border-top"><?print_r($yuri_arrs['yuri_sagaku'])?></td>
            <!-- <td  class="border-start text-center"><?print_r($yuri_arrs['yuri_heikinsa'])?></td> -->
            <td  class="border-start border-end text-center border-top"><?print_r($yuri_arrs['yuri_ichiba_kingakusa'])?></td>
            <?
           }
          }
        ?>
             <tr>
          <td class="border-start text-center">Sマム
          <td class="border-start text-center">W
          <td class="border-start text-center border-end ">
          <? if ($Smamu_arr_w != null){
            foreach($Smamu_arr_w as $Smamu_arr_ws){
              ?>
            <td  class="border-start text-center "><?print_r($Smamu_arr_ws['Smamu_totals_w'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arr_ws['Smamu_avgs_w'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arr_ws['Smamu_ichiba_kingakus_w'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arr_ws['Smamu_totals_w'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arr_ws['Smamu_avgs2_w'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arr_ws['Smamu_ichiba_kingakus2_w'])?></td>
            <td  class="border-start text-center"><?print_r($Smamu_arr_ws['Smamu_sagaku_w'])?></td>
            <!-- <td  class="border-start text-center"><?print_r($Smamu_arr_ws['Smamu_heikinsa_w'])?></td> -->
            <td  class="border-start border-end text-center"><?print_r($Smamu_arr_ws['Smamu_ichiba_kingakusa_w'])?></td>
            <?
           }
          }
        ?>     
      <tr>
        <td class="border-start text-center">Sマム
        <td class="border-start text-center">P
        <td class="border-start text-center border-end ">
        <? if ($Smamu_arr_p != null){
          foreach($Smamu_arr_p as $Smamu_arr_ps){
            ?>
          <td  class="border-start text-center "><?print_r($Smamu_arr_ps['Smamu_totals_p'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ps['Smamu_avgs_p'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ps['Smamu_ichiba_kingakus_p'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ps['Smamu_totals_p'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ps['Smamu_avgs2_p'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ps['Smamu_ichiba_kingakus2_p'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ps['Smamu_sagaku_p'])?></td>
          <!-- <td  class="border-start text-center"><?print_r($Smamu_arr_ps['Smamu_heikinsa_p'])?></td> -->
          <td  class="border-start border-end text-center"><?print_r($Smamu_arr_ps['Smamu_ichiba_kingakusa_p'])?></td>
          <?
         }
        }
      ?>
         <tr>
        <td class="border-start text-center">Sマム
        <td class="border-start text-center">Y
        <td class="border-start text-center border-end ">
        <? if ($Smamu_arr_y != null){
          foreach($Smamu_arr_y as $Smamu_arr_ys){
            ?>
          <td  class="border-start text-center "><?print_r($Smamu_arr_ys['Smamu_totals_y'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ys['Smamu_avgs_y'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ys['Smamu_ichiba_kingakus_y'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ys['Smamu_totals_y'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ys['Smamu_avgs2_y'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ys['Smamu_ichiba_kingakus2_y'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_ys['Smamu_sagaku_y'])?></td>
          <!-- <td  class="border-start text-center"><?print_r($Smamu_arr_ys['Smamu_heikinsa_p'])?></td> -->
          <td  class="border-start border-end text-center"><?print_r($Smamu_arr_ys['Smamu_ichiba_kingakusa_y'])?></td>
          <?
         }
        }
      ?>
            <tr>
        <td class="border-start text-center">Sマム
        <td class="border-start text-center">mix
        <td class="border-start text-center border-end ">
        <? if ($Smamu_arr_mix != null){
          foreach($Smamu_arr_mix as $Smamu_arr_mixs){
            ?>
          <td  class="border-start text-center "><?print_r($Smamu_arr_mixs['Smamu_totals_mix'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_mixs['Smamu_avgs_mix'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_mixs['Smamu_ichiba_kingakus_mix'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_mixs['Smamu_totals_mix'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_mixs['Smamu_avgs2_mix'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_mixs['Smamu_ichiba_kingakus2_mix'])?></td>
          <td  class="border-start text-center"><?print_r($Smamu_arr_mixs['Smamu_sagaku_mix'])?></td>
          <!-- <td  class="border-start text-center"><?print_r($Smamu_arr_mixs['Smamu_heikinsa_mix'])?></td> -->
          <td  class="border-start border-end text-center"><?print_r($Smamu_arr_mixs['Smamu_ichiba_kingakusa_mix'])?></td>
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