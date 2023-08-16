<?php


   if($_POST['val_parm']) {
    $data_array = $_POST['val_parm'];	
    
    $arrayDayList = explode (";",$data_array);
     
   $csv = $arrayDayList[0];
    $csvdata    =  substr($csv, 12); 

   $kounyu_saki = $arrayDayList[1];


   // csvファイルを開いて、読み込みモードに設定する
// $fp = fopen($csvdata, 'r');
 
// // fgetcsvでファイルのデータを読み込む
// $data = fgetcsv($fp);
// mb_convert_variables('UTF-8','SJIS-win',$data);

// var_dump($data);
 
$fp = fopen($csvdata, 'r');

// 1行ずつ読み込む
while($line = fgetcsv($fp)){
   mb_convert_variables('UTF-8','SJIS-win',$line);
   var_dump($line);
}
// ファイルを閉じる
fclose($fp);   
}
?>