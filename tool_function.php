<?php
//シングルコーテーションで囲う
//空文字、nullの場合”null”を文字列で返す
function setCulumnSingleQuotation($str) {
  if(!isset($str) || $str == ""){
    return "null";
  } else {
    return pg_escape_literal($str);
  }
}

////////////////////////////////////////////////////
//年度マスタから一番大きい年度を取得する。
////////////////////////////////////////////////////
function getMaxNendo(){
  $sql = <<< EOM
  SELECT
   MAX(nendo) as nendo
  FROM
   mst_nendo;
EOM;

  $result = pg_query($sql);

  //1レコードの取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows['nendo'];

}
////////////////////////////////////////////////////
//年度マスタから年度を取得する。
////////////////////////////////////////////////////
function getNendo(){
  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_nendo
  ORDER BY
   nendo DESC;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;

}

////////////////////////////////////////////////////
//年度マスタから指定した年度の確定状態を取得する。
////////////////////////////////////////////////////
function getKakuteiJotaiForNendomasta($nendo){
  $sql = <<< EOM
  SELECT
   kakutei_jotai
  FROM
   mst_nendo
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  //レコードの取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows["kakutei_jotai"];

}

//////////////////////////////////////////////////////////////
//年度マスタから指定した給与確定状態と評価確定状態を取得する。
//////////////////////////////////////////////////////////////
function getKakuteiJotaiKyuyoHyoka($nendo){
  $sql = <<< EOM
  SELECT
   kakutei_jotai
   ,kaki_hyoka_kakutei_jotai
   ,toki_hyoka_kakutei_jotai
  FROM
   mst_nendo
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  //レコードの取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows;

}

////////////////////////////////////////////////////
// 部署マスタから部署情報を取得する。
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function getBusho($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_busho
  WHERE
   nendo = '{$nendo}'
  ORDER BY
   busho_cd;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
// 職責マスタを取得する。
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function getShokuseki($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_shokuseki
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}
////////////////////////////////////////////////////
// 職責マスタを取得する。
// 本データ用
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function getShokusekiHon($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   hon_mst_shokuseki
  WHERE
   nendo = '{$nendo}'
   AND duties_cd <= 9700;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
// 勤務地マスタを取得する。
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function getKimmuchi($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_kimmuchi
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//従業員マスタから指定の年度、従業員番号のレコードを取得する
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function getJugyoinForJugyoinUserKanri($nendo,$staff_cd){
  if(empty($nendo) || empty($staff_cd)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_jugyoin
  WHERE
   nendo = '{$nendo}'
   AND staff_code = '{$staff_cd}';
EOM;

  $result = pg_query($sql);

  //レコードの取得
  return pg_fetch_array($result, NULL, PGSQL_ASSOC);
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//従業員マスタから指定の年度、従業員番号のレコードを更新する
//$nendo:年度
//$staff_cd:従業員番号
//$busho_cd：所属部署コード
//$duties_cd:職責コード
//$kimmuchi_cd:勤務地コード
//$tansinfunin_flg:単身赴任フラグ
//$shaho_fuyo_flg：社保扶養フラグ
//$status：退職不落
////////////////////////////////////////////////////
function updateJugyoinForJugyoinUserKanri($nendo,$staff_cd,$busho_cd,$duties_cd,$kimmuchi_cd,$tansinfunin_flg,$shaho_fuyo_flg,$status){

  //トランザクション開始
  pg_query("BEGIN");

  if($status == "1"){//退職の場合
    $set = " kanrisha_staff_code = null, user_shubetu = '0',";
  } else if($duties_cd < 5000){//職責コードが５０００以下になった場合
    $set = " user_shubetu = '0',";
  } else {
    $set = "";
  }

  $sql = <<< EOM
  UPDATE
   mst_jugyoin
  SET
   {$set}
   busho_cd        = '{$busho_cd}',
   duties_cd       = '{$duties_cd}',
   kimmuchi_cd     = '{$kimmuchi_cd}',
   tansinfunin_flg = '{$tansinfunin_flg}',
   shaho_fuyo_flg  = '{$shaho_fuyo_flg}',
   status          = '{$status}'
  WHERE
   nendo = '{$nendo}'
   AND staff_code = '{$staff_cd}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    pg_query("ROLLBACK");
    return 'クエリーが失敗しました。(従業員情報更処理)'.pg_last_error();
  }

  if($status == "1" || $duties_cd < 5000){//退職,または職責コードが５０００以下になった場合
    /////////////////////////////////////////////////////////////////////////////////
    //退職,または職責コードが５０００より小さい場合、
    //管理している部署、管理している従業員になっていた場合、対象のレコードの項目をnullにする。
    /////////////////////////////////////////////////////////////////////////////////
    $sql = <<< EOM
    UPDATE
     mst_jugyoin
    SET
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND kanrisha_staff_code = '{$staff_cd}';
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(従業員情報更処理ー管理している従業員の管理者コードをnullにする)'.pg_last_error();
    }

    $sql = <<< EOM
    UPDATE
     mst_busho
    SET
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND kanrisha_staff_code = '{$staff_cd}';
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(従業員情報更処理ー管理している部署の管理者コードをnullにする)'.pg_last_error();
    }

  }

  pg_query("COMMIT");
  return 1;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//年度を指定して、給与管理システムの従業員情報を取得する。(従業員情報更新ボタン用)
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function selectJugyoinKyuyoKanriSysytemForJugyoinUserKanri($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_jugyoin
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//従業員情報を更新する。(従業員情報更新ボタン用)
//$nendo:年度
//$both_jugyoin:給与システム、従業員管理に両方にあるデータの更新データ
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function updateJugyoinInfoForJugyoinUserKanri($nendo,$both_jugyoin){

  function update_sql($nendo,$array){
    $sql = <<< EOM
    UPDATE
     mst_jugyoin
    SET
     busho_cd = '{$array["busho_cd"]}',
     duties_cd = '{$array["duties_cd"]}',
     kimmuchi_cd = '{$array["kimmuchi_cd"]}',
     shaho_fuyo_flg = '{$array["shaho_fuyo_flg"]}',
     status = '0'
    WHERE
     nendo = '{$nendo}'
     AND staff_code = '{$array["staff_code"]}';
EOM;

    return $sql;
  }

  foreach ($both_jugyoin as $array) {

    $result = pg_query(update_sql($nendo,$array));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(従業員情報更新処理失敗)'.pg_last_error();
    }
  }
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//指定の従業員情報の退職フラグをtrue:1に更新する。(従業員情報更新ボタン用)
//$nendo:年度
//$delete_jugyoin_array:論理削除する従業員コードの配列
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function deleteJugyoinInfoForJugyoinUserKanri($nendo,$delete_jugyoin_array){

  function ronridelete_sql($nendo,$staff_cd){
    $sql = <<< EOM
    UPDATE
     mst_jugyoin
    SET
     status = '1',
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND staff_code = '{$staff_cd}';
EOM;

    return $sql;
  }

  foreach ($delete_jugyoin_array as $staff_cd) {

    $result = pg_query(ronridelete_sql($nendo,$staff_cd));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(従業員情報論理削除処理失敗)'.pg_last_error();
    }
  }
}
////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//削除対象の従業員が従業員の管理者になっている場合、管理者になっている従業員情報
//の管理者コードをすべてnullの状態にupdateする。
//$nendo:年度
//$delete_jugyoin_array：削除対象の従業員
////////////////////////////////////////////////////
function updateJugyoinInfoKanrishaNull($nendo,$delete_jugyoin_array){
  function sql_ujikn($nendo,$staff_cd){
    $sql = <<< EOM
    UPDATE
     mst_jugyoin
    SET
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND kanrisha_staff_code = '{$staff_cd}';
EOM;

    return $sql;
  }

  foreach ($delete_jugyoin_array as $staff_cd) {

    $result = pg_query(sql_ujikn($nendo,$staff_cd));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(削除対象が管理者だった場合に、管理対象の従業員情報の「管理者コード」をnullにする)'.pg_last_error();
    }
  }
}
////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//削除対象の従業員が部署の管理者になっている場合、管理者になっている部署
//の管理者コードをすべてnullの状態にupdateする。
//$nendo:年度
//$delete_jugyoin_array：削除対象の従業員
////////////////////////////////////////////////////
function updateBushoKanrishaNull($nendo,$delete_jugyoin_array){
  function sql_ubkn($nendo,$staff_cd){
    $sql = <<< EOM
    UPDATE
     mst_busho
    SET
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND kanrisha_staff_code = '{$staff_cd}';
EOM;

    return $sql;
  }

  foreach ($delete_jugyoin_array as $staff_cd) {

    $result = pg_query(sql_ubkn($nendo,$staff_cd));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(削除対象が管理者だった場合に、管理対象の部署の「管理者コード」をnullにする)'.pg_last_error();
    }
  }
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//従業員情報を新規登録する。(従業員情報更新ボタン用)
//$nendo:年度
//$insert_data_array:登録する従業員情報の配列
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function insertJugyoinInfoForJugyoinUserKanri($nendo,$insert_data_array){

  function insert_sql($nendo,$insert_data){
    $sql = <<< EOM
    INSERT INTO
     mst_jugyoin
     (
      nendo,
      staff_code,
      staff_name,
      busho_cd,
      duties_cd,
      birthday,
      kimmuchi_cd,
      tansinfunin_flg,
      shaho_fuyo_flg,
      status,
      kanrisha_staff_code,
      user_shubetu
       )
    VALUES(
      '{$nendo}',
      '{$insert_data["staff_code"]}',
      '{$insert_data["staff_name"]}',
      '{$insert_data["busho_cd"]}',
      '{$insert_data["duties_cd"]}',
      '{$insert_data["birthday"]}',
      '{$insert_data["kimmuchi_cd"]}',
      '0',
      '{$insert_data["shaho_fuyo_flg"]}',
      '0',
      null,
      '0'
    );
EOM;

    return $sql;
  }

  foreach ($insert_data_array as $insert_data) {

    $result = pg_query(insert_sql($nendo,$insert_data));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(従業員情報新規登録処理失敗)'.pg_last_error();
    }
  }
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//年度を指定して、従業員管理システムから従業員情報を取得する。
//(従業員情報更新ボタン用)
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
// function selectJugyoinJugyoinKanriSysytemForJugyoinUserKanri($nendo){
//   if(empty($nendo)){
//     return -1;
//   }
//
//   $sql = <<< EOM
//   SELECT
//    *
//   FROM
// EOM;
//
//   $result = pg_query($sql);
//
//   $output_array = array();
//
//   //レコードの取得
//   while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
//     array_push($output_array,$rows);
//   }
//
//   return $output_array;
// }


////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//年度を指定して、従業員情報を取得する。(使用できるユーザー情報)
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function selectUserIchiranForJugyoinUserKanri($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   a.staff_code,
   a.staff_name,
   a.user_shubetu,
   a.duties_cd,
   CASE a.user_shubetu
    WHEN '0' THEN '非ユーザー'
    WHEN '1' THEN '一般ユーザー'
    WHEN '2' THEN '一般参照ユーザー'
    WHEN '3' THEN '参照ユーザー'
    WHEN '4' THEN '運用管理者'
    WHEN '5' THEN 'システム管理者'
   END user_shubetsu,
   COALESCE(b.kanri_jugyoin_su,'0') as kanri_jugyoin_su,
   COALESCE(c.kanri_busho_su,'0') as kanri_busho_su,
   d.duties_name,
   e.busho_name
  FROM
   mst_jugyoin a
   LEFT JOIN (
     SELECT
       nendo,
       kanrisha_staff_code,
       COUNT(*) as kanri_jugyoin_su
     FROM
       mst_jugyoin
     WHERE
       status = '0'
     GROUP BY
       nendo,
       kanrisha_staff_code
     ) b
   ON a.nendo = b.nendo
   AND a.staff_code = b.kanrisha_staff_code
   AND a.status = '0'
   LEFT JOIN (
     SELECT
       nendo,
       kanrisha_staff_code,
       COUNT(*) as kanri_busho_su
     FROM
       mst_busho
     GROUP BY
       nendo,
       kanrisha_staff_code
     ) c
   ON a.nendo = c.nendo
   AND a.staff_code = c.kanrisha_staff_code
   LEFT JOIN mst_shokuseki d
   ON a.nendo = d.nendo
   AND a.duties_cd = d.duties_cd
   LEFT JOIN mst_busho e
   ON a.nendo = e.nendo
   AND a.busho_cd = e.busho_cd
  WHERE
   a.nendo = '{$nendo}'
   AND a.status = '0'
   AND a.user_shubetu <> '0'
  ORDER BY
   a.user_shubetu,
   a.duties_cd,
   a.busho_cd,
   a.staff_code;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//年度を指定して、従業員情報を取得する。(在職中、退職済どちらもとる)
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function selectJugyoinStatusForJugyoinUserKanri($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   a.busho_cd,
   b.busho_name,
   a.staff_name,
   c.staff_name as kanrisha_name,
   a.staff_code,
   a.status
  FROM
   mst_jugyoin a
   LEFT JOIN mst_busho b
   ON a.nendo = b.nendo
   AND a.busho_cd = b.busho_cd
   LEFT JOIN mst_jugyoin c
   ON a.nendo = c.nendo
   AND a.kanrisha_staff_code = c.staff_code
   AND c.status = '0'
   LEFT JOIN mst_shokuseki d
   ON a.nendo = d.nendo
   AND a.duties_cd = d.duties_cd
  WHERE
   a.nendo = '{$nendo}'
  ORDER BY
   b.busho_cd,
   a.duties_cd,
   a.staff_code;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//年度を指定して、従業員情報を取得する。
//$nendo:年度
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function selectJugyoinForJugyoinUserKanri($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   a.busho_cd,
   b.busho_name,
   a.staff_name,
   c.staff_name as kanrisha_name,
   a.staff_code,
   d.duties_name,
   a.duties_cd,
   a.user_shubetu
  FROM
   mst_jugyoin a
   LEFT JOIN mst_busho b
   ON a.nendo = b.nendo
   AND a.busho_cd = b.busho_cd
   LEFT JOIN mst_jugyoin c
   ON a.nendo = c.nendo
   AND a.kanrisha_staff_code = c.staff_code
   AND c.status = '0'
   LEFT JOIN mst_shokuseki d
   ON a.nendo = d.nendo
   AND a.duties_cd = d.duties_cd
  WHERE
   a.nendo = '{$nendo}'
   AND a.status = '0'
  ORDER BY
   b.busho_cd,
   a.duties_cd,
   a.staff_code;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//給与按分情報を取得する。
//$nendo:年度
//$staff_cd:従業員コード
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function selectAnbunForJugyoinUserKanri($nendo,$staff_cd){
  if(empty($nendo) || empty($staff_cd)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_kyuyo_ambun
  WHERE
   nendo = '{$nendo}'
   AND staff_code = '{$staff_cd}';
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//給与按分情報を更新する。
//$nendo:年度
//$staff_cd:従業員コード
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function DeleteInsertAnbunForJugyoinUserKanri($nendo,$staff_cd,$anbun_info_list){
  if(empty($nendo) || empty($staff_cd)){
    return -1;
  }

  //トランザクション開始
  pg_query("BEGIN");

  //まずデータを全削除///////////////////////////////////////////////////////////
  $sql = <<< EOM
  DELETE FROM
   mst_kyuyo_ambun
  WHERE
   nendo = '{$nendo}'
   AND staff_code = '{$staff_cd}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    pg_query("ROLLBACK");
    return 'クエリーが失敗しました。(削除処理失敗)'.pg_last_error();
  }
  //////////////////////////////////////////////////////////////////////////////

  //次にデータをインサートしていく/////////////////////////////////////////////////
  function sqlInsert($nendo,$staff_cd,$busho_cd,$anbunritsu){
    $sql = <<< EOM
    INSERT INTO
     mst_kyuyo_ambun
     (
       nendo,
       staff_code,
       ambun_busho_cd,
       kyuyo_ambun_ritu
       )
     VALUES
     (
       '{$nendo}',
       '{$staff_cd}',
       '{$busho_cd}',
       '{$anbunritsu}'
     );
EOM;
   return $sql;
  }

  foreach($anbun_info_list as $anbun_info){
    $result = pg_query(sqlInsert($nendo,$staff_cd,$anbun_info["busho"],$anbun_info["anbunritsu"]));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(追加処理失敗)'.pg_last_error();
    }
  }
  //////////////////////////////////////////////////////////////////////////////

  pg_query("COMMIT");
  return 1;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//従業員情報のユーザー種別を更新する。
//但し、管理する部署・従業員がある従業員が非ユーザーになった場合、
//管理する情報を削除する(正確には更新)
//$nendo:年度
//$staff_cd:従業員コード
//$kanri_busho_delete_flg:管理する部署の情報を削除するかの判断
//$kanri_jugyoin_delete_flg:管理する従業員の情報を削除するかの判断
//$user_shubetsu:変更するユーザー種別
////////////////////////////////////////////////////
function UpdateUserShubetsuForJugyoinUserKanri($nendo,$staff_cd,$kanri_busho_delete_flg,$kanri_jugyoin_delete_flg,$user_shubetsu){
  if(empty($nendo) || empty($staff_cd) || empty($kanri_busho_delete_flg) || empty($kanri_jugyoin_delete_flg)){
    return -1;
  }

  //トランザクション開始
  pg_query("BEGIN");

  //ユーザー種別の更新:start/////////////////////////////////////////////////////
  $sql = <<< EOM
  UPDATE
   mst_jugyoin
  SET
   user_shubetu = '{$user_shubetsu}'
  WHERE
   nendo = '{$nendo}'
   AND staff_code = '{$staff_cd}';
EOM;

  $result = pg_query($sql);

  if(!$result) {
    pg_query("ROLLBACK");
    return 'クエリーが失敗しました。(ユーザー種別変更失敗)'.pg_last_error();
  }
  //ユーザー種別の更新:end///////////////////////////////////////////////////////

  //管理する部署・従業員の更新:start//////////////////////////////////////////////
  if($kanri_jugyoin_delete_flg == "true"){
    $sql = <<< EOM
    UPDATE
     mst_jugyoin
    SET
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND kanrisha_staff_code = '{$staff_cd}';
EOM;

    $result = pg_query($sql);

    if(!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(管理する従業員変更失敗)'.pg_last_error();
    }
  }

  if($kanri_busho_delete_flg == "true"){
    $sql = <<< EOM
    UPDATE
     mst_busho
    SET
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND kanrisha_staff_code = '{$staff_cd}';
EOM;

    $result = pg_query($sql);

    if(!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(管理する部署変更失敗)'.pg_last_error();
    }
  }
  //管理する部署・従業員の更新:end////////////////////////////////////////////////

  pg_query("COMMIT");
  return 1;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//管理者が設定されていない&自身が管理する部署を取得する。
//$nendo:年度
//$staff_cd:従業員番号
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function selectBushoWithoutKanriStaffForJugyoinUserKanri($nendo,$staff_cd){
  if(empty($nendo) || empty($staff_cd)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   busho_cd,
   busho_name,
   CASE
    WHEN kanrisha_staff_code = '{$staff_cd}' THEN '1'
    WHEN kanrisha_staff_code IS NULL THEN '0'
   END as check
  FROM
   mst_busho
  WHERE
   nendo = '{$nendo}'
   AND (
     kanrisha_staff_code ='{$staff_cd}'
     OR kanrisha_staff_code IS NULL
   )
  ORDER BY
   busho_cd;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//管理する部署に管理従業員をセットする。
//$nendo:年度
//$staff_cd:従業員番号
//$kanribusho_array:管理部署の配列(チェックしたかの判断も持つ)
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function updateKanribushoForJugyoinUserKanri($nendo,$staff_cd,$kanribusho_array){
  function sql($nendo,$busho_cd,$staff_cd){
    $sql = <<< EOM
    UPDATE
     mst_busho
    SET
     kanrisha_staff_code = '{$staff_cd}'
    WHERE
     nendo = '{$nendo}'
     AND busho_cd = '{$busho_cd}';
EOM;

    return $sql;
  }

  function sqlNull($nendo,$busho_cd){
    $sql = <<< EOM
    UPDATE
     mst_busho
    SET
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND busho_cd = '{$busho_cd}';
EOM;

    return $sql;
  }

  if(empty($nendo) || empty($staff_cd)){
    return -1;
  }

  //トランザクション開始
  pg_query("BEGIN");

  foreach ($kanribusho_array as $value) {
    $sql = "";
    if($value["check"]){
      $sql = sql($nendo,$value["busho_cd"],$staff_cd);
    }else {
      $sql = sqlNull($nendo,$value["busho_cd"]);
    }

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。'.pg_last_error();
    }
  }

  pg_query("COMMIT");
  return "1";
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//管理者が設定されていない&自身が管理する従業員を取得する。
//$nendo:年度
//$staff_cd:従業員番号
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function selectKanriJugyoinForJugyoinUserKanri($nendo,$staff_cd){
  if(empty($nendo) || empty($staff_cd)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   a.staff_code,
   a.staff_name,
   a.busho_cd,
   b.busho_name,
   CASE
    WHEN a.kanrisha_staff_code = '{$staff_cd}' THEN '1'
    WHEN a.kanrisha_staff_code IS NULL THEN '0'
   END as before_check,
   CASE
    WHEN a.kanrisha_staff_code = '{$staff_cd}' THEN '1'
    WHEN a.kanrisha_staff_code IS NULL THEN '0'
   END as after_check
  FROM
   mst_jugyoin a
   LEFT JOIN mst_busho b
   ON a.nendo = b.nendo
   AND a.busho_cd = b.busho_cd
  WHERE
   a.nendo='{$nendo}'
   AND a.status = '0'
   AND (
     a.kanrisha_staff_code = '{$staff_cd}'
     OR a.kanrisha_staff_code is null
     )
   AND a.staff_code <> '{$staff_cd}'
  ORDER BY
   a.busho_cd ASC,
   a.staff_code ASC;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜従業員・ユーザー管理＞＞
//管理する従業員に管理従業員をセットする。
//$nendo:年度
//$staff_cd:従業員番号
//$kanrijugyoin_array:管理従業員の配列(チェックしたかの判断も持つ)
//レコードが存在しない場合は空のarrayを返す
////////////////////////////////////////////////////
function updateKanrijugyoinForJugyoinUserKanri($nendo,$staff_cd,$kanrijugyoin_array){
  function sql($nendo,$staff_cd,$kanri_staff_cd){
    $sql = <<< EOM
    UPDATE
     mst_jugyoin
    SET
     kanrisha_staff_code = '{$kanri_staff_cd}'
    WHERE
     nendo = '{$nendo}'
     AND staff_code = '{$staff_cd}';
EOM;

    return $sql;
  }

  function sqlNull($nendo,$staff_cd){
    $sql = <<< EOM
    UPDATE
     mst_jugyoin
    SET
     kanrisha_staff_code = null
    WHERE
     nendo = '{$nendo}'
     AND staff_code = '{$staff_cd}';
EOM;

    return $sql;
  }

  if(empty($nendo) || empty($staff_cd)){
    return -1;
  }

  //トランザクション開始
  pg_query("BEGIN");

  foreach ($kanrijugyoin_array as $value) {
    $sql = "";
    if($value["check"] == "1"){
      $sql = sql($nendo,$value["staff_cd"],$staff_cd);
    }else {
      $sql = sqlNull($nendo,$value["staff_cd"]);
    }

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。'.pg_last_error();
    }
  }

  pg_query("COMMIT");
  return "1";
}

////////////////////////////////////////////////////
//＜＜部署管理＞＞
//年度指定して部署管理データを取得する。
//nendo:年度
//レコードが存在しない場合は空のarrayを返す。
////////////////////////////////////////////////////
function getBushoList($nendo){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   a.busho_cd,
   a.busho_name,
   a.oya_busho_cd,
   b.busho_name as oya_busho_name,
   e.staff_name,
   COALESCE(c.ninzu,'-1') as shozoku,
   COALESCE(a.kanrisha_staff_code,'-1') as kanrisha,
   COALESCE(d.oya_busho_cd,'-1') as oyabusho_settei
  FROM
   mst_busho a
   LEFT JOIN mst_busho b
   ON a.nendo = b.nendo
   AND a.oya_busho_cd = b.busho_cd
   LEFT JOIN (
    SELECT
      nendo,
      busho_cd,
      COUNT(*) as ninzu
     FROM
      mst_jugyoin
     WHERE
      status = '0'
     GROUP BY
      nendo,
      busho_cd
     ) c
   ON a.nendo = c.nendo
   AND a.busho_cd = c.busho_cd
   LEFT JOIN (
     SELECT
      DISTINCT nendo,
      oya_busho_cd
     FROM
      mst_busho
     ) d
   ON a.nendo = d.nendo
   AND a.busho_cd = d.oya_busho_cd
   LEFT JOIN mst_jugyoin e
   ON a.nendo = e.nendo
   AND a.kanrisha_staff_code = e.staff_code
   AND e.status = '0'
  WHERE
   a.nendo = '{$nendo}'
  ORDER BY
   a.busho_cd ASC;
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;
}

////////////////////////////////////////////////////
//＜＜部署管理＞＞
//部署マスタを更新する。
//nendo:年度
//$busho_cd:部署コード
//$busho_name:部署名
//$oya_buso_cd:親コード
////////////////////////////////////////////////////
function updateBusho($nendo,$busho_cd,$busho_name,$oya_busho_cd){

  //部署の小孫の部署を取得する。
  function check($nendo,$busho_cd,&$komagobusho_array){
    $sql = <<< EOM
    SELECT
     busho_cd
    FROM
     mst_busho
    WHERE
     nendo = '{$nendo}'
     AND oya_busho_cd = '{$busho_cd}'
EOM;
    $result = pg_query($sql);

    if(pg_num_rows($result) == '0'){
      return;
    } else {
      while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
        array_push($komagobusho_array,$rows["busho_cd"]);
        check($nendo,$rows["busho_cd"],$komagobusho_array);
      }
    }
  }

  //指定した親部署が自分の小孫に設定している場合エラーにする。
  $komagobusho_array = array();
  check($nendo,$busho_cd,$komagobusho_array);
  foreach($komagobusho_array as $komago){
    if($komago == (int)$oya_busho_cd){
      return 2;
    }
  }

  $oya_busho_cd = ($oya_busho_cd != "") ? "'" . $oya_busho_cd . "'" : "null" ;
  $busho_name = pg_escape_literal($busho_name);

  $sql = <<< EOM
  UPDATE
   mst_busho
  SET
   busho_name   = {$busho_name},
   oya_busho_cd = {$oya_busho_cd}
  WHERE
   nendo        = '{$nendo}'
   AND busho_cd = '{$busho_cd}'
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }

  return 1;
}

////////////////////////////////////////////////////
//＜＜部署管理＞＞
//部署マスタに新規登録する。
//nendo:年度
//$busho_name:部署名
//$oya_buso_cd:親コード
////////////////////////////////////////////////////
function insertBusho($nendo,$busho_name,$oya_busho_cd){

  //部署コードの作成////////////////////////////////////////
  $sql = <<< EOM
  SELECT
   busho_cd as max_busho_cd
  FROM
   mst_code_kanri
  WHERE
   nendo = '{$nendo}'
EOM;

  $result = pg_query($sql);

  if (!$result) {
   return 'クエリーが失敗しました。'.pg_last_error();
  }

  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);
  $incre_busho_cd = (int)$rows['max_busho_cd'] + 1;//取得した部署コードをインクリメント
  /////////////////////////////////////////////////////////

  $oya_busho_cd = ($oya_busho_cd != "") ? "'" . $oya_busho_cd . "'" : "null" ;
  $busho_name = pg_escape_literal($busho_name);

  //トランザクション開始
  pg_query("BEGIN");

  //部署データの作成////////////////////////////////////////
  $sql = <<< EOM
  INSERT INTO
   mst_busho
   (
     nendo,
     busho_cd,
     busho_name,
     oya_busho_cd
   )
   VALUES
   (
    '{$nendo}',
    '{$incre_busho_cd}',
    {$busho_name},
    {$oya_busho_cd}
  );
EOM;

  $result = pg_query($sql);

  if (!$result) {
    pg_query("ROLLBACK");
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  //////////////////////////////////////////////////

  //部署コードをインクリメントしておく/////////////////
  $sql = <<< EOM
  UPDATE
   mst_code_kanri
  SET
   busho_cd = '{$incre_busho_cd}'
  WHERE
   nendo = '{$nendo}'
EOM;

  $result = pg_query($sql);

  if (!$result) {
    pg_query("ROLLBACK");
   return 'クエリーが失敗しました。'.pg_last_error();
  }
  //////////////////////////////////////////////////

  //トランザクション終了
  pg_query("COMMIT");
  return 1;
}

////////////////////////////////////////////////////
//＜＜部署管理＞＞
//部署マスタのデータを削除する。
//nendo:年度
//$busho_cds:部署コードの配列
////////////////////////////////////////////////////
function deleteBusho($nendo,$busho_cds){
  function sql($nendo,$busho_cd){
    $sql = <<< EOM
    DELETE
    FROM
     mst_busho
    WHERE
     nendo = '{$nendo}'
     AND busho_cd = '{$busho_cd}';
EOM;

    return $sql;
  }

  //トランザクション開始
  pg_query("BEGIN");

  foreach ($busho_cds as $busho_cd) {
    $sql = sql($nendo,$busho_cd);

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。'.pg_last_error();
    }
  }

  pg_query("COMMIT");
  return "1";
}

////////////////////////////////////////////////////
//＜＜職責別給与比較画面＞＞
//職責別の給与情報を取得する。(データ複数)
//$nendo:年度
//$duties_cd:職責コード
//引数が空の場合は-1を返す。
//レコードが存在しない場合は空のarrayを返す。
//配列に連想配列(従業員マスタ、職責・職責給マスタ、従業員給与テーブル、年間休日マスタ、勤務地マスタのすべてのカラム)が入ったものを返す
////////////////////////////////////////////////////
function getJobTypeSalaryList($nendo,$duties_cd){
  //引数が正しくない場合はエラー
  if(empty($nendo) || !isset($duties_cd)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
  *
 FROM
  mst_jugyoin a
  LEFT JOIN mst_shokuseki b
  ON a.nendo = b.nendo
  AND a.duties_cd = b.duties_cd
  LEFT JOIN mst_kimmuchi mk
  ON a.nendo = mk.nendo
  AND a.kimmuchi_cd = mk.siten_cd
  INNER JOIN hon_kyuyo_tbl c
  ON a.nendo = c.nendo
  AND a.staff_code = c.staff_code
 WHERE
  a.nendo = '{$nendo}'
  AND a.duties_cd = {$duties_cd}
  AND a.status = '0'
EOM;

  $result = pg_query($sql);

  $output_array = array();

  //レコードの取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($output_array,$rows);
  }

  return $output_array;

}
////////////////////////////////////////////////////
//＜＜年俸制対象者の給与入力＞＞
//年俸制対象者の情報を取得する。
//申請系テーブルから取得する。
//$nendo:年度
//$staff_code：従業員番号
//引数が空の場合は-1を返す。
//レコードが存在しない場合は-2を返す。
//gekkyu_nempo,duties_name,nempo_nengaku,nempo_shoyo_1,nempo_shoyo_2,
//maitsukishikyugaku,bikoの連想配列を返す。
//月給・年俸が０(月給)の場合は、すべてのカラム(月給・年俸以外)がブランクのレコードを返す。
////////////////////////////////////////////////////
function getAnnualSalarySinsei($nendo,$staff_code){
  //引数が正しくない場合はエラー
  if(empty($nendo) || empty($staff_code)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   c.gekkyu_nempo,
   b.duties_name,
   c.nempo_nengaku,
   c.nempo_shoyo_1,
   c.nempo_shoyo_2,
   c.shikyugaku_a as maitsukishikyugaku,
   c.biko
  FROM
   mst_jugyoin a
   LEFT JOIN sinsei_mst_shokuseki b
   ON a.nendo = b.nendo
   AND a.duties_cd = b.duties_cd
   INNER JOIN sinsei_kyuyo_tbl c
   ON a.nendo = c.nendo
   AND a.staff_code = c.staff_code
  WHERE
   a.nendo = '{$nendo}'
   AND a.staff_code = '{$staff_code}'
   AND a.status = '0';
EOM;

  $result = pg_query($sql);

  //レコードが0件の場合はエラー
  if(pg_num_rows($result) == 0){
    return -2;
  }

  //レコード1件取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows;

}

////////////////////////////////////////////////////
//＜＜年俸制対象者の給与入力＞＞
//年俸制対象者の確定状態を取得する。
//申請系テーブルから取得する。
//$nedo:年度
//$staff_cd:従業員番号
////////////////////////////////////////////////////
function getAnnualSalaryKakuteiJotaiSinsei($nendo,$staff_cd){
  //引数が正しくない場合はエラー
  if(empty($nendo) || empty($staff_cd)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   kakutei_jotai
  FROM
   sinsei_kyuyo_tbl
  WHERE
   nendo = '{$nendo}'
  AND staff_code = '{$staff_cd}';
EOM;

  $result = pg_query($sql);

  //レコードが0件の場合はエラー
  if(pg_num_rows($result) == 0){
   return -2;
  }

  //レコード1件取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows;
}

////////////////////////////////////////////////////
//＜＜年俸制対象者の給与入力＞＞
//年俸制対象者の給与を更新する。
//申請系テーブルを更新する。
//$nedo:年度
//$staff_cd:従業員番号
//$soshikyu:年俸
//$syoyo_1:賞与１
//$syoyo_2:賞与２
//$maitukishikyu:毎月の支給額
//$biko:備考
////////////////////////////////////////////////////
function getAnnualSalaryUpdateSinsei($nendo,$staff_cd,$soshikyu,$syoyo_1,$syoyo_2,$maitukishikyu,$biko,$user){

  $escape_biko = pg_escape_literal($biko);

  $sql = <<< EOM
    UPDATE
     sinsei_kyuyo_tbl
    SET
     nempo_nengaku = '{$soshikyu}',
     nempo_shoyo_1 = '{$syoyo_1}',
     nempo_shoyo_2 = '{$syoyo_2}',
     shikyugaku_a  = '{$maitukishikyu}',
     biko          = {$escape_biko},
     update_user   = '{$user}',
     update_date   = current_timestamp
    WHERE
     nendo = '{$nendo}'
     AND staff_code = '{$staff_cd}'
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return -1;
  }

  return "1";
}

////////////////////////////////////////////////////
//＜＜年俸制対象者の給与入力＞＞
//年俸制対象者の確定状態を更新する。
//申請系テーブルを更新する。
//$nendo:年度
//$staff_code:従業員番号
//$kakutei：更新する確定状態
//$user:更新ユーザー
//引数が空の場合は-1を返す。
//レコードがない場合は-2を返す。
//それ以外の場合は、1レコード分の配列を返す。
////////////////////////////////////////////////////
function getAnnualSalaryUpdateKakuteiJotaiSinsei($nendo,$staff_cd,$kakutei,$user){

  $sql = <<< EOM
  UPDATE
   sinsei_kyuyo_tbl
  SET
   kakutei_jotai = '{$kakutei}',
   update_user = '{$user}',
   update_date = current_timestamp
  WHERE
   nendo = '{$nendo}'
   AND staff_code = '{$staff_cd}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }

  return 1;
}

////////////////////////////////////////////////////
//＜＜年俸制対象者一覧＞＞
//年俸制対象者の情報を取得する。
//申請系テーブルから取得する
//$nedo:年度
//引数が空の場合は-1を返す。
//レコードが存在しない場合は空のarrayを返す。
//配列の中にbusho_name,staff_name,staff_code,nempo_nengaku,kakutei_jotaiの連想配列
//が入っている
////////////////////////////////////////////////////
function getAnnualSalaryTargetListSinsei($nendo){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   b.busho_name
   , a.staff_name
   , a.staff_code
   , c.nempo_nengaku
   ,CASE c.kakutei_jotai
         WHEN '0' THEN '未確定'
         WHEN '1' THEN '仮確定'
         WHEN '2' THEN '確定'
    END as kakutei_jotai_name
    , c.kakutei_jotai
  FROM
   mst_jugyoin a
   LEFT JOIN mst_busho b
     ON a.nendo = b.nendo
     AND a.busho_cd = b.busho_cd
   INNER JOIN sinsei_kyuyo_tbl c
     ON a.nendo = c.nendo
     AND a.staff_code = c.staff_code
  WHERE
   a.status = '0' AND
   a.nendo = '{$nendo}' AND
   a.duties_cd >= 9000
  ORDER BY
   a.busho_cd ASC,
   a.staff_code ASC;
EOM;

  $result = pg_query($sql);

  $array = array();

  //レコード取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
////////////////////////////////////////////////////
//＜＜年俸制対象者一覧＞＞
//年俸制対象者の確定状態を更新する。
//申請系テーブルを更新する
//$nedo:年度
//$staff_cd_array:従業員番号の配列
//$kakutei:変更後の確定状態
//$update_user:更新ユーザー
////////////////////////////////////////////////////
function getAnnualSalaryTargetListUpdateSinsei($nendo,$staff_cd_array,$kakutei,$update_user){

  function sql($kakutei,$nendo,$staff_code,$user){
    $sql = <<< EOM
    UPDATE
     sinsei_kyuyo_tbl
    SET
     kakutei_jotai = '{$kakutei}',
     update_user = '{$user}',
     update_date = current_timestamp
    WHERE
     nendo = '{$nendo}'
     AND staff_code = '{$staff_code}'
EOM;

    return $sql;
  }

  //トランザクション開始
  pg_query("BEGIN");

  foreach ($staff_cd_array as $staff_code) {
    $sql = sql($kakutei,$nendo,$staff_code,$update_user);

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。'.pg_last_error();
    }
  }

  pg_query("COMMIT");
  return "1";
}

////////////////////////////////////////////////////
//＜＜給与入力画面用＞＞
//申請用給与テーブルを取得する
//$nendo:年度
//$staff_code:従業員番号
//引数が空の場合は-1を返す。
//レコードがない場合は-2を返す。
//それ以外の場合は、1レコード分の配列を返す。
////////////////////////////////////////////////////
function getSalarySinsei($nendo,$staff_code){

  //引数が正しくない場合はエラー
  if(empty($nendo) || empty($staff_code)){
    return -1;
  }

  $sql = <<< EOM
   SELECT
    skt.nendo
    ,skt.nenkan_kyujitu
    ,skt.rodo_jikan_per_day
    ,skt.kyukei_jikan_per_day
    ,skt.tansinfunin_flg
    ,skt.shaho_fuyo_flg
    ,skt.gekkyu_nempo
    ,skt.shokyu_gaku
    ,skt.shokyu_teisei_gaku
    ,skt.shokyu_tangan_riyu
    ,skt.shokyu_tangan_gaku
    ,skt.zennen_shokuseki_kyu
    ,skt.shokuseki_kyu
    ,skt.kihon_kyu
    ,skt.kotei_kihon_kyu
    ,skt.kaikin_teate
    ,skt.tosi_teate
    ,skt.shorei_teate
    ,skt.chosei_teate
    ,skt.tenkin_jutaku_teate
    ,skt.tansinfunin_teate
    ,skt.kazoku_teate
    ,skt.korituzangyo_teate
    ,skt.korituzangyo_teate_gaitogaku
    ,skt.teate_yobi_01
    ,skt.teate_yobi_02
    ,skt.teate_yobi_03
    ,skt.teate_yobi_04
    ,skt.teate_yobi_05
    ,skt.zennen_nempo_nengaku
    ,skt.nempo_nengaku
    ,skt.nempo_12_14
    ,skt.nempo_uchi_koteizang_dai
    ,skt.nempo_uchi_eigyo_teate
    ,skt.nempo_shoyo_1
    ,skt.nempo_shoyo_2
    ,skt.shikyugaku_a
    ,skt.sagaku_teate
    ,skt.zangyo_tanka
    ,skt.tujo_zan_jikan_tuki
    ,skt.tujo_zan_jikan_nenkei
    ,skt.sinya_zan_jikan_tuki
    ,skt.sinya_zan_jikan_nenkei
    ,skt.kyujitu_rodo_jikan_tuki
    ,skt.kyujitu_rodo_jikan_nenkei
    ,skt.kyujitusinya_rodo_jikan_tuki
    ,skt.kyujitusinya_rodo_jikan_nenkei
    ,skt.kekkin_kojo
    ,skt.chikoku_sotai_kojo
    ,skt.biko
    ,skt.keiri_upd_flg_shokuseki
    ,skt.keiri_upd_flg_shokyu_teisei
    ,skt.keiri_upd_flg_shokyu_tangan
    ,skt.keiri_upd_flg_tsujo_zan
    ,skt.keiri_upd_flg_sinya_zan
    ,skt.keiri_upd_flg_kyujitu_rodo
    ,skt.keiri_upd_flg_kyujitu_sinya
    ,skt.kakutei_jotai
    ,skt.delete_flg
    ,sms.duties_cd
    ,sms.duties_name
    ,mj.busho_cd
    ,skt.kimmuchi_cd
    ,mk.siten_name kimmuchi_name
   FROM
    ((sinsei_kyuyo_tbl as skt
    LEFT JOIN mst_jugyoin as mj
    ON skt.staff_code = mj.staff_code
    AND skt.nendo = mj.nendo)
    LEFT JOIN sinsei_mst_shokuseki as sms
    ON skt.duties_cd = sms.duties_cd
    AND skt.nendo = sms.nendo)
    LEFT JOIN mst_kimmuchi as mk
    ON skt.kimmuchi_cd = mk.siten_cd
    AND skt.nendo = mk.nendo
   WHERE
    skt.nendo = '{$nendo}' AND
    skt.staff_code = '{$staff_code}' AND
    mj.status = '0'
EOM;

  $result = pg_query($sql);

  //レコードが0件の場合はエラー
  if(pg_num_rows($result) == 0){
    return -2;
  }

  //レコード1件取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows;
}

////////////////////////////////////////////////////
//＜＜給与入力画面用＞＞
//本データ用給与テーブルを取得する
//$nendo:年度
//$staff_code:従業員番号
//引数が空の場合は-1を返す。
//レコードがない場合は-2を返す。
//それ以外の場合は、1レコード分の配列を返す。
////////////////////////////////////////////////////
function getSalaryHon($nendo,$staff_code){

  //引数が正しくない場合はエラー
  if(empty($nendo) || empty($staff_code)){
    return -1;
  }

  $sql = <<< EOM
   SELECT
    hkt.nendo
    ,hkt.nenkan_kyujitu
    ,hkt.rodo_jikan_per_day
    ,hkt.kyukei_jikan_per_day
    ,hkt.tansinfunin_flg
    ,hkt.shaho_fuyo_flg
    ,hkt.gekkyu_nempo
    ,hkt.shokyu_gaku
    ,hkt.shokyu_teisei_gaku
    ,hkt.shokyu_tangan_riyu
    ,hkt.shokyu_tangan_gaku
    ,hkt.zennen_shokuseki_kyu
    ,hkt.shokuseki_kyu
    ,hkt.kihon_kyu
    ,hkt.kotei_kihon_kyu
    ,hkt.kaikin_teate
    ,hkt.tosi_teate
    ,hkt.shorei_teate
    ,hkt.chosei_teate
    ,hkt.tenkin_jutaku_teate
    ,hkt.tansinfunin_teate
    ,hkt.kazoku_teate
    ,hkt.korituzangyo_teate
    ,hkt.korituzangyo_teate_gaitogaku
    ,hkt.teate_yobi_01
    ,hkt.teate_yobi_02
    ,hkt.teate_yobi_03
    ,hkt.teate_yobi_04
    ,hkt.teate_yobi_05
    ,hkt.zennen_nempo_nengaku
    ,hkt.nempo_nengaku
    ,hkt.nempo_12_14
    ,hkt.nempo_uchi_koteizang_dai
    ,hkt.nempo_uchi_eigyo_teate
    ,hkt.nempo_shoyo_1
    ,hkt.nempo_shoyo_2
    ,hkt.shikyugaku_a
    ,hkt.sagaku_teate
    ,hkt.zangyo_tanka
    ,hkt.tujo_zan_jikan_tuki
    ,hkt.tujo_zan_jikan_nenkei
    ,hkt.sinya_zan_jikan_tuki
    ,hkt.sinya_zan_jikan_nenkei
    ,hkt.kyujitu_rodo_jikan_tuki
    ,hkt.kyujitu_rodo_jikan_nenkei
    ,hkt.kyujitusinya_rodo_jikan_tuki
    ,hkt.kyujitusinya_rodo_jikan_nenkei
    ,hkt.kekkin_kojo
    ,hkt.chikoku_sotai_kojo
    ,hkt.biko
    ,hkt.keiri_upd_flg_shokuseki
    ,hkt.keiri_upd_flg_shokyu_teisei
    ,hkt.keiri_upd_flg_shokyu_tangan
    ,hkt.keiri_upd_flg_tsujo_zan
    ,hkt.keiri_upd_flg_sinya_zan
    ,hkt.keiri_upd_flg_kyujitu_rodo
    ,hkt.keiri_upd_flg_kyujitu_sinya
    ,hkt.kakutei_jotai
    ,hkt.delete_flg
    ,hms.duties_cd
    ,hms.duties_name
    ,mj.busho_cd
    ,hkt.kimmuchi_cd
    ,mk.siten_name kimmuchi_name
   FROM
    ((hon_kyuyo_tbl as hkt
    LEFT JOIN mst_jugyoin as mj
    ON hkt.staff_code = mj.staff_code
    AND hkt.nendo = mj.nendo)
    LEFT JOIN hon_mst_shokuseki as hms
    ON hkt.duties_cd = hms.duties_cd
    AND hkt.nendo = hms.nendo)
    LEFT JOIN mst_kimmuchi as mk
    ON hkt.kimmuchi_cd = mk.siten_cd
    AND hkt.nendo = mk.nendo
   WHERE
    hkt.nendo = '{$nendo}' AND
    hkt.staff_code = '{$staff_code}' AND
    mj.status = '0'
EOM;

  $result = pg_query($sql);

  //レコードが0件の場合はエラー
  if(pg_num_rows($result) == 0){
    return -2;
  }

  //レコード1件取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows;
}

////////////////////////////////////////////////////
//＜＜給与シミュレーションデータ入力画面用＞＞
//シミュレーション給与テーブルを取得する
//$nendo:年度
//$pattern_id:パターンｉｄ
//$staff_code:従業員番号
//引数が空の場合は-1を返す。
//レコードがない場合は-2を返す。
//それ以外の場合は、1レコード分の配列を返す。
////////////////////////////////////////////////////
function getSalarySimu($nendo, $pattern_id, $staff_code){

  //引数が正しくない場合はエラー
  if(empty($nendo) || empty($pattern_id) || empty($staff_code)){
    return -1;
  }

  $sql = <<< EOM
   SELECT
    skt.nendo
    ,skt.nenkan_kyujitu
    ,skt.rodo_jikan_per_day
    ,skt.kyukei_jikan_per_day
    ,skt.tansinfunin_flg
    ,skt.shaho_fuyo_flg
    ,skt.gekkyu_nempo
    ,skt.shokyu_gaku
    ,skt.shokyu_teisei_gaku
    ,skt.shokyu_tangan_riyu
    ,skt.shokyu_tangan_gaku
    ,skt.zennen_shokuseki_kyu
    ,skt.shokuseki_kyu
    ,skt.kihon_kyu
    ,skt.kotei_kihon_kyu
    ,skt.kaikin_teate
    ,skt.tosi_teate
    ,skt.shorei_teate
    ,skt.chosei_teate
    ,skt.tenkin_jutaku_teate
    ,skt.tansinfunin_teate
    ,skt.kazoku_teate
    ,skt.korituzangyo_teate
    ,skt.korituzangyo_teate_gaitogaku
    ,skt.teate_yobi_01
    ,skt.teate_yobi_02
    ,skt.teate_yobi_03
    ,skt.teate_yobi_04
    ,skt.teate_yobi_05
    ,skt.zennen_nempo_nengaku
    ,skt.nempo_nengaku
    ,skt.nempo_12_14
    ,skt.nempo_uchi_koteizang_dai
    ,skt.nempo_uchi_eigyo_teate
    ,skt.nempo_shoyo_1
    ,skt.nempo_shoyo_2
    ,skt.shikyugaku_a
    ,skt.sagaku_teate
    ,skt.zangyo_tanka
    ,skt.tujo_zan_jikan_tuki
    ,skt.tujo_zan_jikan_nenkei
    ,skt.sinya_zan_jikan_tuki
    ,skt.sinya_zan_jikan_nenkei
    ,skt.kyujitu_rodo_jikan_tuki
    ,skt.kyujitu_rodo_jikan_nenkei
    ,skt.kyujitusinya_rodo_jikan_tuki
    ,skt.kyujitusinya_rodo_jikan_nenkei
    ,skt.kekkin_kojo
    ,skt.chikoku_sotai_kojo
    ,skt.biko
    ,skt.keiri_upd_flg_shokuseki
    ,skt.keiri_upd_flg_shokyu_teisei
    ,skt.keiri_upd_flg_shokyu_tangan
    ,skt.keiri_upd_flg_tsujo_zan
    ,skt.keiri_upd_flg_sinya_zan
    ,skt.keiri_upd_flg_kyujitu_rodo
    ,skt.keiri_upd_flg_kyujitu_sinya
    ,skt.kakutei_jotai
    ,skt.delete_flg
    ,sms.duties_cd
    ,sms.duties_name
    ,mj.busho_cd
    ,skt.kimmuchi_cd
    ,mk.siten_name kimmuchi_name
   FROM
    ((simu_kyuyo_tbl as skt
    LEFT JOIN mst_jugyoin as mj
    ON skt.staff_code = mj.staff_code
    AND skt.nendo = mj.nendo)

    LEFT JOIN simu_mst_shokuseki as sms
    ON skt.duties_cd = sms.duties_cd
    AND skt.nendo = sms.nendo
    AND skt.pattern_id = sms.pattern_id)

    LEFT JOIN mst_kimmuchi as mk
    ON skt.kimmuchi_cd = mk.siten_cd
    AND skt.nendo = mk.nendo

    WHERE
    skt.nendo = '{$nendo}' AND
    skt.pattern_id = '{$pattern_id}' AND
    skt.staff_code = '{$staff_code}' AND
    mj.status = '0'
EOM;

  $result = pg_query($sql);

  //レコードが0件の場合はエラー
  if(pg_num_rows($result) == 0){
    return -2;
  }

  //レコード1件取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows;
}

////////////////////////////////////////////////////
//＜＜給与シミュレーション用＞＞
//シミュレーション給与テーブルを取得する
//$nendo:年度
//$staff_code:従業員番号
//$pattern_id：パターンid
//引数が空の場合は-1を返す。
//レコードがない場合は-2を返す。
//それ以外の場合は、1レコード分の配列を返す。
////////////////////////////////////////////////////
function getSimuSalary($nendo,$staff_code,$pattern_id){

  //引数が正しくない場合はエラー
  if(empty($nendo) || empty($staff_code) || empty($pattern_id)){
    return -1;
  }

  $sql = <<< EOM
   SELECT
    *
   FROM
    simu_kyuyo_tbl
   WHERE
    nendo = '{$nendo}' AND
    staff_code = '{$staff_code}' AND
    pattern_id = '{$pattern_id}';
EOM;

  $result = pg_query($sql);

  //レコードが0件の場合はエラー
  if(pg_num_rows($result) == 0){
    return -2;
  }

  //レコード1件取得
  $rows = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  return $rows;
}

// ////////////////////////////////////////////////////////////////////////////////////////////
// //＜＜人件費参照画面(休日ベース)用＞＞
// //年間人件費総額を取得する。
// //$nendo:年度
// //引数が空の場合は-1を返す
// /////////////////////////////////////////////////////////////////////////////////////////////
// function getTotalSalaryForYearKyujitsu($nendo){
//   //引数が正しくない場合はエラー
//   if(empty($nendo)){
//     return -1;
//   }
//
//   $sql = <<< EOM
//   SELECT
//    休日日数,
//    人件費総額
//   FROM
//
// EOM;
// }

////////////////////////////////////////////////////////////////////////////////////////////
//＜＜人件費参照画面(残業ベース)用＞＞
//部署ごとの年間人件費総額(月間、年間(賞与含まず)、年間賞与、年間(賞与含む))を
//『　本データ用給与テーブル　』から取得する。
//$nendo:年度
//引数が空の場合は-1を返す
//返り値:
//配列の中に
// *busho_cd             -> 部署コード
// *busho_name           -> 部署名
// *month_total_kyuyo    -> 月間トータル
// *year_total_kyuyonomi -> 年間トータル(給与のみ)
// *year_total_shoyonomi -> 年間賞与トータル
// *year_total           -> 年間トータル(賞与含む)
//が入っている
/////////////////////////////////////////////////////////////////////////////////////////////
function getTotalSalaryDepartmentalForYearHon($nendo){
  return getTotalSalaryDepartmentalHon($nendo);
}

/////////////////////////////////////////////////////////////////////////////////////////////
//＜＜従業員一覧画面用＞＞
//部署ごとの月間、年間人件費総額(月間、年間(賞与含まず)、年間賞与、年間(賞与含む))を
//　『　申請用給与テーブル　』又は『　本データ用給与テーブル　』から取得する。
//
//1.引数で指定した従業員の管理する部署と、その部署の小孫(下の階層すべて)をすべて取得する。
//  引数３で「申請用給与テーブル」か「本データ用給与テーブル」か、取得先を判断する。
//2.1で取得した部署が親部署となっているか、なっていないか分ける。
//3.親部署になっていないものはその部署の月間,年間人件費総額を取得して、返却用配列にプッシュする。
//4.親部署になっているものは、その部署の小孫(下の階層すべて)の月間,年間人件費総額を合計して、
//返却用配列にプッシュする。
//$nendo:年度
//$staff_code:絞り込むために使用する従業員番号
//$sel_tbl
//　　"sinsei"：『申請用給与テーブル』から取得
//　　"hon"：『本データ用給与テーブル』から取得
//
//引数が空の場合は-1を返す
//レコードが存在しない場合は空のarrayを返す。
//
//返り値:
//配列の中に
// *busho_cd             -> 部署コード
// *busho_name           -> 部署名
// *month_total_kyuyo    -> 月収トータル
// *year_total_kyuyonomi -> 年収トータル(給与のみ)
// *year_total_shoyonomi -> 年間賞与トータル
// *year_total           -> 年収トータル(賞与含む)
//の連想配列
//が入っている
//////////////////////////////////////////////////////////////////////////////////////////////
function getTotalSalaryDepartmentalForMonthYear($nendo, $staff_code, $sel_tbl){
  //引数が正しくない場合はエラー
  if(empty($nendo) || empty($staff_code)){
    return -1;
  }
  if(($sel_tbl !== "sinsei")&&($sel_tbl !== "hon")){
    return -1;
  }

  //返却用配列
  $output_array = array();

  //指定年度の部署毎の月間、年間人件費総額の配列を取得
  if($sel_tbl == "sinsei"){
	$array = getTotalSalaryDepartmentalSinsei($nendo);
  }else if($sel_tbl == "hon"){
	$array = getTotalSalaryDepartmentalHon($nendo);
  }

  //引数２の従業員が管理する部署を取得
  $sql = <<< EOM
  SELECT
   b.busho_cd,
   b.busho_name,
   CASE
    WHEN oya IS NULL THEN '0'
    ELSE '1'
   END as oya_flg
  FROM
   mst_jugyoin a
   INNER JOIN mst_busho b
   ON a.staff_code = b.kanrisha_staff_code
   AND a.nendo = b.nendo
   LEFT JOIN (
     SELECT
      DISTINCT oya_busho_cd as oya
     FROM
      mst_busho
     WHERE
      nendo = '{$nendo}'
     ) c
   ON b.busho_cd = c.oya
  WHERE
   a.staff_code = '{$staff_code}'
   AND a.status = '0'
   AND a.nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  //レコードが0件の場合
  if(pg_num_rows($result) == 0){
    return array();
  }

  $bushos = array();
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($bushos,$rows);
  }

  //従業員が管理する部署と、その小孫(下の階層すべて)の部署をリストアップする
  //※部署には親部署になっているか、なっていないかの情報ももっている。
  $busho_array = array();
  nest_busho($nendo,$busho_array,$bushos);

  //リストアップした部署配列の重複を削除
  $tmp = array();
  $busho_unique_array = array();
  foreach ($busho_array as $value){
    if (!in_array($value["busho_cd"], $tmp)) {
      $tmp[] = $value["busho_cd"];
      $busho_unique_array[] = $value;
    }
  }

  //リストアップした部署配列のソート(部署コード順)
  usort($busho_unique_array, function ($a, $b) {
    return $a['busho_cd'] < $b['busho_cd'] ? -1 : 1;
  });

  //リストアップした部署配列を元に返却用配列に部署の月間、年間人件費総額
  //をプッシュしていく。
  foreach($busho_unique_array as $busho){
    if($busho['oya_flg'] == '0'){//親部署になっていない部署
      foreach($array as $value){
        if($value['busho_cd'] == $busho["busho_cd"]){
          array_push($output_array,$value);
        }
      }
    } else if($busho["oya_flg"] == "1"){//親部署になっている部署

      //親部署になっている部署は自分自身の小孫(下の階層すべて)の部署
      //の月間、年間人件費総額を足していく。

      $busho_name = $busho["busho_name"];
      $busho_cd   = $busho["busho_cd"];

      //合計値の初期値(※これを参照渡しして足していく)
      $total_month            = 0;
      $total_year_kyuyonomi   = 0;
      $total_year_shoyonomi   = 0;
      $total_year_all         = 0;

      //合計値の算出処理(再帰処理)
      oyaBusho_nest(
        $nendo,
        $array,
        $busho,
        $total_month,
        $total_year_kyuyonomi,
        $total_year_shoyonomi,
        $total_year_all
      );

      //算出後連想配列を作成
      $tmp = array(
        "busho_name"           => $busho_name,
        "busho_cd"             => $busho_cd,
        "month_total_kyuyo"    => $total_month,
        "year_total_kyuyonomi" => $total_year_kyuyonomi,
        "year_total_shoyonomi" => $total_year_shoyonomi,
        "year_total"           => $total_year_all
      );

      array_push($output_array,$tmp);
    }
  }

  return $output_array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜従業員一覧画面用＞＞
//部署階層構造上にある部署をすべて配列にプッシュする。(内部ファンクション)
//※再帰処理になっているので注意
//$nendo:年度
//$busho_array:プッシュする配列(これに部署が入る※参照渡しになっているので注意)
//$bushos:部署情報の配列
//返り値:なし
//////////////////////////////////////////////////////////////////////////////////////////////
function nest_busho($nendo,&$busho_array,$bushos){
  foreach($bushos as $busho){

    array_push($busho_array,$busho);

    $sql = <<< EOM
    SELECT
     a.busho_cd,
     a.busho_name,
     CASE
      WHEN oya IS NULL THEN '0'
      ELSE '1'
     END as oya_flg
    FROM
     mst_busho a
     LEFT JOIN (
       SELECT
        DISTINCT oya_busho_cd as oya
       FROM
        mst_busho
       WHERE
        nendo = '{$nendo}'
       ) b
      ON a.busho_cd = b.oya
    WHERE
     nendo = '{$nendo}'
     AND oya_busho_cd = '{$busho['busho_cd']}'
EOM;
    $result = pg_query($sql);

    if(pg_num_rows($result) != '0'){
      $tmp_array = array();
      while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
        array_push($tmp_array,$rows);
      }
      //自分自身を実行する(再帰処理)
      nest_busho($nendo,$busho_array,$tmp_array);
    }
  }
}

/////////////////////////////////////////////////////////////////////////////////////////////
//＜＜従業員一覧画面用＞＞
//子の部署の子の部署みたいに下におりていって、合計値を算出する。(内部ファンクション)
//※再帰処理になっているので注意
//$nendo  :年度
//$array  :部署毎の人件費の配列   ※参照渡しになっているので注意
//$t_m    :月収トータル          ※参照渡しになっているので注意
//$t_y_k  :年収トータル(給与のみ) ※参照渡しになっているので注意
//$t_y_s  :年間賞与トータル       ※参照渡しになっているので注意
//$t_y_a  :年収トータル(賞与含む) ※参照渡しになっているので注意
//返り値:なし
//////////////////////////////////////////////////////////////////////////////////////////////
function oyaBusho_nest($nendo,$array,$busho,&$t_m,&$t_y_k,&$t_y_s,&$t_y_a){

  $sql = <<< EOM
  SELECT
   a.busho_cd,
   CASE
    WHEN oya IS NULL THEN '0'
    ELSE '1'
   END as oya_flg
  FROM
   mst_busho a
   LEFT JOIN (
     SELECT
      DISTINCT oya_busho_cd as oya
     FROM
      mst_busho
     WHERE
      nendo = '{$nendo}'
     ) b
    ON a.busho_cd = b.oya
  WHERE
   nendo = '{$nendo}'
   AND oya_busho_cd = '{$busho["busho_cd"]}'
EOM;

  $result = pg_query($sql);

  //レコードが0件の場合
  if(pg_num_rows($result) == 0){
    return;
  }

  $bushos_1 = array();//親部署になっている部署
  $bushos_0 = array();//親部署になっていない部署

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    if($rows["oya_flg"] == "0"){
      array_push($bushos_0,$rows);
    } else if($rows["oya_flg"] == "1"){
      array_push($bushos_1,$rows);
    }
  }

  //親部署になっていないものを足す
  foreach($bushos_0 as $bs){
    foreach($array as $rd){
      if($rd['busho_cd'] == $bs["busho_cd"]){
        $t_m     = $t_m   + $rd['month_total_kyuyo'   ];
        $t_y_k   = $t_y_k + $rd['year_total_kyuyonomi'];
        $t_y_s   = $t_y_s + $rd['year_total_shoyonomi'];
        $t_y_a   = $t_y_a + $rd['year_total'];
      }
    }
  }

  if(count($bushos_1) != 0){
    foreach($bushos_1 as $bsh){
      //親部署自体に紐づいている従業員の給与を足しとく
      foreach($array as $rd){
        if($rd['busho_cd'] == $bsh["busho_cd"]){
          $t_m     = $t_m   + $rd['month_total_kyuyo'   ];
          $t_y_k   = $t_y_k + $rd['year_total_kyuyonomi'];
          $t_y_s   = $t_y_s + $rd['year_total_shoyonomi'];
          $t_y_a   = $t_y_a + $rd['year_total'];
        }
      }
      //自分自身を実行する(再帰処理)
      oyaBusho_nest($nendo,$array,$bsh,$t_m,$t_y_k,$t_y_s,$t_y_a);
    }
  } else {
    //親部署がない場合は終了
    return;
  }
}

////////////////////////////////////////////////////////////////////////////////////////////
//＜＜従業員一覧画面用＞＞
//会社の月間、年間総人件費(月間、年間(賞与含まず)、年間賞与、年間(賞与含む))を
//『　申請用給与テーブル　』又は『　本データ用給与テーブル　』から取得する。
//(部署ごとの人件費を足すだけ）
//
//$nendo:年度
//$sel_tbl
//　　"sinsei"：『申請用給与テーブル』から取得
//　　"hon"：『本データ用給与テーブル』から取得
//
//引数が空の場合は-1を返す
//返り値:
//配列の中に
//*month_total_kyuyo    -> 会社の月の人件費
//*year_total_kyuyonomi -> 会社の年の人件費(賞与含まず)
//*year_total_shoyonomi -> 会社の年の賞与総額
//*year_total           -> 会社の年の人件費(賞与含む)
//が入っている
/////////////////////////////////////////////////////////////////////////////////////////////
function getTotalSalaryCompanyForMonthYear($nendo, $sel_tbl){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }
  if(($sel_tbl !== "sinsei")&&($sel_tbl !== "hon")){
    return -1;
  }

  //部署ごとの人件費総額を取得する。
  if($sel_tbl == "sinsei"){
$array = getTotalSalaryDepartmentalSinsei($nendo);
  }else if($sel_tbl == "hon"){
	$array = getTotalSalaryDepartmentalHon($nendo);
  }

  $total_month            = 0;
  $total_year_kyuyonomi   = 0;
  $total_year_shoyonomi   = 0;
  $total_yaer_all         = 0;

  foreach ($array as $rd) {//全部足す
    $total_month            = $total_month            + $rd['month_total_kyuyo'   ];
    $total_year_kyuyonomi   = $total_year_kyuyonomi   + $rd['year_total_kyuyonomi'];
    $total_year_shoyonomi   = $total_year_shoyonomi   + $rd['year_total_shoyonomi'];
    $total_yaer_all         = $total_yaer_all         + $rd['year_total'];
  }

  return array(
    "month_total_kyuyo"    =>$total_month,
    "year_total_kyuyonomi" =>$total_year_kyuyonomi,
    "year_total_shoyonomi" =>$total_year_shoyonomi,
    "year_total"           =>$total_yaer_all
  );
}

////////////////////////////////////////////////////////////////////////////////////////////
//＜＜給与シミュレーションデータ一覧画面用＞＞
//会社の月間、年間総人件費(月間、年間(賞与含まず)、年間賞与、年間(賞与含む))を
//『　シミュレーション給与テーブル　』から取得する。
//(部署ごとの人件費を足すだけ）
//
//$nendo:年度
//$pattern_id
//
//引数が空の場合は-1を返す
//返り値:
//配列の中に
//*month_total_kyuyo    -> 会社の月の人件費
//*year_total_kyuyonomi -> 会社の年の人件費(賞与含まず)
//*year_total_shoyonomi -> 会社の年の賞与総額
//*year_total           -> 会社の年の人件費(賞与含む)
//が入っている
/////////////////////////////////////////////////////////////////////////////////////////////
function getTotalSalaryCompanyForMonthYearSimu($nendo, $pattern_id){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }
  // if(($sel_tbl !== "sinsei")&&($sel_tbl !== "hon")){
  //   return -1;
  // }

  //部署ごとの人件費総額を取得する。
  $array = getTotalSalaryDepartmentalSimu($nendo, $pattern_id);

  $total_month            = 0;
  $total_year_kyuyonomi   = 0;
  $total_year_shoyonomi   = 0;
  $total_yaer_all         = 0;

  foreach ($array as $rd) {//全部足す
    $total_month            = $total_month            + $rd['month_total_kyuyo'   ];
    $total_year_kyuyonomi   = $total_year_kyuyonomi   + $rd['year_total_kyuyonomi'];
    $total_year_shoyonomi   = $total_year_shoyonomi   + $rd['year_total_shoyonomi'];
    $total_yaer_all         = $total_yaer_all         + $rd['year_total'];
  }

  return array(
    "month_total_kyuyo"    =>$total_month,
    "year_total_kyuyonomi" =>$total_year_kyuyonomi,
    "year_total_shoyonomi" =>$total_year_shoyonomi,
    "year_total"           =>$total_yaer_all
  );
}

//////////////////////////////////////////////////////////////////////////////////////////////
//部署ごとの人件費総額(月間、年間(賞与含まず)、年間賞与、年間(賞与含む))を
//『　シミュレーション給与テーブル　』から取得する。(内部function)
//$nendo:年度
//$pattern_id：パターンｉｄ
//引数が空の場合は-1を返す。
//
//返り値:
//配列の中に
// *busho_cd             -> 部署コード
// *busho_name           -> 部署名
// *month_total_kyuyo    -> 月間トータル
// *year_total_kyuyonomi -> 年間トータル(給与のみ)
// *year_total_shoyonomi -> 年間賞与トータル
// *year_total           -> 年間トータル(賞与含む)
//の連想配列
//が入っている
//////////////////////////////////////////////////////////////////////////////////////////////
function getTotalSalaryDepartmentalSimu($nendo, $pattern_id){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   c.busho_name
   , c.busho_cd
   , COALESCE(d.month_total_kyuyo, 0) as month_total_kyuyo
   , COALESCE(d.year_total_kyuyonomi, 0) as year_total_kyuyonomi
   , COALESCE(d.year_total_shoyonomi, 0) as year_total_shoyonomi
   , COALESCE(d.year_total, 0) as year_total
  FROM
   mst_busho c
   LEFT JOIN (
    SELECT
      a.nendo
      , a.busho_cd
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN c.shikyugaku_kyu_teate
          WHEN c.gekkyu_nempo = '1' THEN c.shikyugaku_a
         END
        ) as month_total_kyuyo
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN c.shikyugaku_kyu_teate * 12
          WHEN c.gekkyu_nempo = '1' THEN c.shikyugaku_a * 12
         END
        ) as year_total_kyuyonomi
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 2)
          WHEN c.gekkyu_nempo = '1' THEN (c.nempo_shoyo_1 + c.nempo_shoyo_2)
          ELSE '0'
         END
         ) as year_total_shoyonomi
      , SUM(
        CASE
         WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 14)
         WHEN c.gekkyu_nempo = '1' THEN (c.shikyugaku_a * 12 + (c.nempo_shoyo_1 + c.nempo_shoyo_2))
         ELSE '0'
        END
        ) as  year_total
    FROM
      mst_jugyoin a
      LEFT JOIN mst_kyuyo_ambun b
        ON a.nendo = b.nendo
        AND a.staff_code = b.staff_code
      LEFT JOIN (
        SELECT
         nendo,
         staff_code,
         gekkyu_nempo,
         (
          COALESCE(shokuseki_kyu,0) +
          COALESCE(kihon_kyu,0) +
          COALESCE(kotei_kihon_kyu,0) +
          COALESCE(kaikin_teate,0) +
          COALESCE(tosi_teate,0) +
          COALESCE(shorei_teate,0) +
          COALESCE(chosei_teate,0) +
          COALESCE(tenkin_jutaku_teate,0) +
          COALESCE(tansinfunin_teate,0) +
          COALESCE(kazoku_teate,0) +
          COALESCE(korituzangyo_teate_gaitogaku,0)
          ) as shikyugaku_kyu_teate,
         COALESCE(shikyugaku_a,0) as shikyugaku_a,
         COALESCE(nempo_shoyo_1,0) as nempo_shoyo_1,
         COALESCE(nempo_shoyo_2,0) as nempo_shoyo_2
        FROM
         simu_kyuyo_tbl
        WHERE
         pattern_id = {$pattern_id}
      ) c
        ON a.nendo = c.nendo
        AND a.staff_code = c.staff_code
    WHERE
      b.nendo IS NULL
      AND a.status = '0'
    GROUP BY
      a.nendo
      , a.busho_cd
   ) d
    ON c.busho_cd = d.busho_cd
    AND c.nendo = d.nendo
  WHERE
   c.nendo = '{$nendo}'
  ORDER BY
   busho_cd ASC;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  $sql = <<< EOM
  SELECT
   b.ambun_busho_cd
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil(c.shikyugaku_kyu_teate * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil(c.shikyugaku_a * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as month_shikyugaku
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 12) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil((c.shikyugaku_a * 12) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_shikyugaku_kyuyonomi
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 2) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil((c.nempo_shoyo_1 + c.nempo_shoyo_2) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_shikyugaku_shoyonomi
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 14) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil(((c.shikyugaku_a * 12) + (c.nempo_shoyo_1 + c.nempo_shoyo_2)) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_total_all
  FROM
   mst_jugyoin a
   INNER JOIN mst_kyuyo_ambun b
     ON a.nendo = b.nendo
     AND a.staff_code = b.staff_code
   LEFT JOIN (
    SELECT
     nendo,
     staff_code,
     gekkyu_nempo,
     (
      COALESCE(shokuseki_kyu,0) +
      COALESCE(kihon_kyu,0) +
      COALESCE(kotei_kihon_kyu,0) +
      COALESCE(kaikin_teate,0) +
      COALESCE(tosi_teate,0) +
      COALESCE(shorei_teate,0) +
      COALESCE(chosei_teate,0) +
      COALESCE(tenkin_jutaku_teate,0) +
      COALESCE(tansinfunin_teate,0) +
      COALESCE(kazoku_teate,0) +
      COALESCE(korituzangyo_teate_gaitogaku,0)
      ) as shikyugaku_kyu_teate,
      COALESCE(shikyugaku_a,0) as shikyugaku_a,
      COALESCE(nempo_shoyo_1,0) as nempo_shoyo_1,
      COALESCE(nempo_shoyo_2,0) as nempo_shoyo_2
    FROM
     simu_kyuyo_tbl
    WHERE
     pattern_id = {$pattern_id}
) c
     ON a.staff_code = c.staff_code
     AND a.nendo = c.nendo
  WHERE
   a.nendo = '{$nendo}'
   AND a.status = '0';
EOM;

  $result = pg_query($sql);

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    foreach($array as &$rd){
      if($rd['busho_cd'] == $rows['ambun_busho_cd']){

        $rd['month_total_kyuyo'   ] = $rd['month_total_kyuyo']    + $rows['month_shikyugaku'];
        $rd['year_total_kyuyonomi'] = $rd['year_total_kyuyonomi'] + $rows['year_shikyugaku_kyuyonomi'];
        $rd['year_total_shoyonomi'] = $rd['year_total_shoyonomi'] + $rows['year_shikyugaku_shoyonomi'];
        $rd['year_total']           = $rd['year_total']           + $rows['year_total_all'];

      }
    }
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//部署ごとの人件費総額(月間、年間(賞与含まず)、年間賞与、年間(賞与含む))を
//『　申請用給与テーブル　』から取得する。(内部function)
//$nendo:年度
//引数が空の場合は-1を返す。
//
//返り値:
//配列の中に
// *busho_cd             -> 部署コード
// *busho_name           -> 部署名
// *month_total_kyuyo    -> 月間トータル
// *year_total_kyuyonomi -> 年間トータル(給与のみ)
// *year_total_shoyonomi -> 年間賞与トータル
// *year_total           -> 年間トータル(賞与含む)
//の連想配列
//が入っている
//////////////////////////////////////////////////////////////////////////////////////////////
function getTotalSalaryDepartmentalSinsei($nendo){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   c.busho_name
   , c.busho_cd
   , COALESCE(d.month_total_kyuyo, 0) as month_total_kyuyo
   , COALESCE(d.year_total_kyuyonomi, 0) as year_total_kyuyonomi
   , COALESCE(d.year_total_shoyonomi, 0) as year_total_shoyonomi
   , COALESCE(d.year_total, 0) as year_total
  FROM
   mst_busho c
   LEFT JOIN (
    SELECT
      a.nendo
      , a.busho_cd
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN c.shikyugaku_kyu_teate
          WHEN c.gekkyu_nempo = '1' THEN c.shikyugaku_a
         END
        ) as month_total_kyuyo
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN c.shikyugaku_kyu_teate * 12
          WHEN c.gekkyu_nempo = '1' THEN c.shikyugaku_a * 12
         END
        ) as year_total_kyuyonomi
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 2)
          WHEN c.gekkyu_nempo = '1' THEN (c.nempo_shoyo_1 + c.nempo_shoyo_2)
          ELSE '0'
         END
         ) as year_total_shoyonomi
      , SUM(
        CASE
         WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 14)
         WHEN c.gekkyu_nempo = '1' THEN (c.shikyugaku_a * 12 + (c.nempo_shoyo_1 + c.nempo_shoyo_2))
         ELSE '0'
        END
        ) as  year_total
    FROM
      mst_jugyoin a
      LEFT JOIN mst_kyuyo_ambun b
        ON a.nendo = b.nendo
        AND a.staff_code = b.staff_code
      LEFT JOIN (
        SELECT
         nendo,
         staff_code,
         gekkyu_nempo,
         (
          COALESCE(shokuseki_kyu,0) +
          COALESCE(kihon_kyu,0) +
          COALESCE(kotei_kihon_kyu,0) +
          COALESCE(kaikin_teate,0) +
          COALESCE(tosi_teate,0) +
          COALESCE(shorei_teate,0) +
          COALESCE(chosei_teate,0) +
          COALESCE(tenkin_jutaku_teate,0) +
          COALESCE(tansinfunin_teate,0) +
          COALESCE(kazoku_teate,0) +
          COALESCE(korituzangyo_teate_gaitogaku,0)
          ) as shikyugaku_kyu_teate,
         COALESCE(shikyugaku_a,0) as shikyugaku_a,
         COALESCE(nempo_shoyo_1,0) as nempo_shoyo_1,
         COALESCE(nempo_shoyo_2,0) as nempo_shoyo_2
        FROM
         sinsei_kyuyo_tbl
      ) c
        ON a.nendo = c.nendo
        AND a.staff_code = c.staff_code
    WHERE
      b.nendo IS NULL
      AND a.status = '0'
    GROUP BY
      a.nendo
      , a.busho_cd
   ) d
    ON c.busho_cd = d.busho_cd
    AND c.nendo = d.nendo
  WHERE
   c.nendo = '{$nendo}'
  ORDER BY
   busho_cd ASC;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  $sql = <<< EOM
  SELECT
   b.ambun_busho_cd
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil(c.shikyugaku_kyu_teate * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil(c.shikyugaku_a * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as month_shikyugaku
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 12) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil((c.shikyugaku_a * 12) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_shikyugaku_kyuyonomi
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 2) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil((c.nempo_shoyo_1 + c.nempo_shoyo_2) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_shikyugaku_shoyonomi
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 14) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil(((c.shikyugaku_a * 12) + (c.nempo_shoyo_1 + c.nempo_shoyo_2)) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_total_all
  FROM
   mst_jugyoin a
   INNER JOIN mst_kyuyo_ambun b
     ON a.nendo = b.nendo
     AND a.staff_code = b.staff_code
   LEFT JOIN (
    SELECT
     nendo,
     staff_code,
     gekkyu_nempo,
     (
      COALESCE(shokuseki_kyu,0) +
      COALESCE(kihon_kyu,0) +
      COALESCE(kotei_kihon_kyu,0) +
      COALESCE(kaikin_teate,0) +
      COALESCE(tosi_teate,0) +
      COALESCE(shorei_teate,0) +
      COALESCE(chosei_teate,0) +
      COALESCE(tenkin_jutaku_teate,0) +
      COALESCE(tansinfunin_teate,0) +
      COALESCE(kazoku_teate,0) +
      COALESCE(korituzangyo_teate_gaitogaku,0)
      ) as shikyugaku_kyu_teate,
      COALESCE(shikyugaku_a,0) as shikyugaku_a,
      COALESCE(nempo_shoyo_1,0) as nempo_shoyo_1,
      COALESCE(nempo_shoyo_2,0) as nempo_shoyo_2
    FROM
     sinsei_kyuyo_tbl
    ) c
     ON a.staff_code = c.staff_code
     AND a.nendo = c.nendo
  WHERE
   a.nendo = '{$nendo}'
   AND a.status = '0';
EOM;

  $result = pg_query($sql);

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    foreach($array as &$rd){
      if($rd['busho_cd'] == $rows['ambun_busho_cd']){

        $rd['month_total_kyuyo'   ] = $rd['month_total_kyuyo']    + $rows['month_shikyugaku'];
        $rd['year_total_kyuyonomi'] = $rd['year_total_kyuyonomi'] + $rows['year_shikyugaku_kyuyonomi'];
        $rd['year_total_shoyonomi'] = $rd['year_total_shoyonomi'] + $rows['year_shikyugaku_shoyonomi'];
        $rd['year_total']           = $rd['year_total']           + $rows['year_total_all'];

      }
    }
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//部署ごとの人件費総額(月間、年間(賞与含まず)、年間賞与、年間(賞与含む))を
//『　本データ用給与テーブル　』から取得する。(内部function)
//$nendo:年度
//引数が空の場合は-1を返す。
//
//返り値:
//配列の中に
// *busho_cd             -> 部署コード
// *busho_name           -> 部署名
// *month_total_kyuyo    -> 月間トータル
// *year_total_kyuyonomi -> 年間トータル(給与のみ)
// *year_total_shoyonomi -> 年間賞与トータル
// *year_total           -> 年間トータル(賞与含む)
//の連想配列
//が入っている
//////////////////////////////////////////////////////////////////////////////////////////////
function getTotalSalaryDepartmentalHon($nendo){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   c.busho_name
   , c.busho_cd
   , COALESCE(d.month_total_kyuyo, 0) as month_total_kyuyo
   , COALESCE(d.year_total_kyuyonomi, 0) as year_total_kyuyonomi
   , COALESCE(d.year_total_shoyonomi, 0) as year_total_shoyonomi
   , COALESCE(d.year_total, 0) as year_total
  FROM
   mst_busho c
   LEFT JOIN (
    SELECT
      a.nendo
      , a.busho_cd
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN c.shikyugaku_kyu_teate
          WHEN c.gekkyu_nempo = '1' THEN c.shikyugaku_a
         END
        ) as month_total_kyuyo
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN c.shikyugaku_kyu_teate * 12
          WHEN c.gekkyu_nempo = '1' THEN c.shikyugaku_a * 12
         END
        ) as year_total_kyuyonomi
      , SUM(
         CASE
          WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 2)
          WHEN c.gekkyu_nempo = '1' THEN (c.nempo_shoyo_1 + c.nempo_shoyo_2)
          ELSE '0'
         END
         ) as year_total_shoyonomi
      , SUM(
        CASE
         WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 14)
         WHEN c.gekkyu_nempo = '1' THEN (c.shikyugaku_a * 12 + (c.nempo_shoyo_1 + c.nempo_shoyo_2))
         ELSE '0'
        END
        ) as  year_total
    FROM
      mst_jugyoin a
      LEFT JOIN mst_kyuyo_ambun b
        ON a.nendo = b.nendo
        AND a.staff_code = b.staff_code
      LEFT JOIN (
        SELECT
         nendo,
         staff_code,
         gekkyu_nempo,
         (
          COALESCE(shokuseki_kyu,0) +
          COALESCE(kihon_kyu,0) +
          COALESCE(kotei_kihon_kyu,0) +
          COALESCE(kaikin_teate,0) +
          COALESCE(tosi_teate,0) +
          COALESCE(shorei_teate,0) +
          COALESCE(chosei_teate,0) +
          COALESCE(tenkin_jutaku_teate,0) +
          COALESCE(tansinfunin_teate,0) +
          COALESCE(kazoku_teate,0) +
          COALESCE(korituzangyo_teate_gaitogaku,0)
          ) as shikyugaku_kyu_teate,
         COALESCE(shikyugaku_a,0) as shikyugaku_a,
         COALESCE(nempo_shoyo_1,0) as nempo_shoyo_1,
         COALESCE(nempo_shoyo_2,0) as nempo_shoyo_2
        FROM
         hon_kyuyo_tbl
      ) c
        ON a.nendo = c.nendo
        AND a.staff_code = c.staff_code
    WHERE
      b.nendo IS NULL
      AND a.status = '0'
    GROUP BY
      a.nendo
      , a.busho_cd
   ) d
    ON c.busho_cd = d.busho_cd
    AND c.nendo = d.nendo
  WHERE
   c.nendo = '{$nendo}'
  ORDER BY
   busho_cd ASC;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  $sql = <<< EOM
  SELECT
   b.ambun_busho_cd
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil(c.shikyugaku_kyu_teate * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil(c.shikyugaku_a * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as month_shikyugaku
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 12) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil((c.shikyugaku_a * 12) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_shikyugaku_kyuyonomi
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 2) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil((c.nempo_shoyo_1 + c.nempo_shoyo_2) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_shikyugaku_shoyonomi
   , COALESCE(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN ceil((c.shikyugaku_kyu_teate * 14) * (b.kyuyo_ambun_ritu / 100))
      WHEN c.gekkyu_nempo = '1' THEN ceil(((c.shikyugaku_a * 12) + (c.nempo_shoyo_1 + c.nempo_shoyo_2)) * (b.kyuyo_ambun_ritu / 100))
      ELSE '0'
     END
     ,0) as year_total_all
  FROM
   mst_jugyoin a
   INNER JOIN mst_kyuyo_ambun b
     ON a.nendo = b.nendo
     AND a.staff_code = b.staff_code
   LEFT JOIN (
    SELECT
     nendo,
     staff_code,
     gekkyu_nempo,
     (
      COALESCE(shokuseki_kyu,0) +
      COALESCE(kihon_kyu,0) +
      COALESCE(kotei_kihon_kyu,0) +
      COALESCE(kaikin_teate,0) +
      COALESCE(tosi_teate,0) +
      COALESCE(shorei_teate,0) +
      COALESCE(chosei_teate,0) +
      COALESCE(tenkin_jutaku_teate,0) +
      COALESCE(tansinfunin_teate,0) +
      COALESCE(kazoku_teate,0) +
      COALESCE(korituzangyo_teate_gaitogaku,0)
      ) as shikyugaku_kyu_teate,
      COALESCE(shikyugaku_a,0) as shikyugaku_a,
      COALESCE(nempo_shoyo_1,0) as nempo_shoyo_1,
      COALESCE(nempo_shoyo_2,0) as nempo_shoyo_2
    FROM
     hon_kyuyo_tbl
    ) c
     ON a.staff_code = c.staff_code
     AND a.nendo = c.nendo
  WHERE
   a.nendo = '{$nendo}'
   AND a.status = '0';
EOM;

  $result = pg_query($sql);

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    foreach($array as &$rd){
      if($rd['busho_cd'] == $rows['ambun_busho_cd']){

        $rd['month_total_kyuyo'   ] = $rd['month_total_kyuyo']    + $rows['month_shikyugaku'];
        $rd['year_total_kyuyonomi'] = $rd['year_total_kyuyonomi'] + $rows['year_shikyugaku_kyuyonomi'];
        $rd['year_total_shoyonomi'] = $rd['year_total_shoyonomi'] + $rows['year_shikyugaku_shoyonomi'];
        $rd['year_total']           = $rd['year_total']           + $rows['year_total_all'];

      }
    }
  }

  return $array;
}

////////////////////////////////////////////////////////////////////////////////
//＜＜従業員一覧＞＞
// 全従業員の情報に申請用給与テーブルの情報を付けて取得する
//$nendo:年度
//返り値：
//レコードが存在しない場合は空のarrayを返す。
//全従業員の情報が配列(配列の中は連想配列)で返す。
////////////////////////////////////////////////////////////////////////////////
function getUserKanriJugyoinAllSinsei($nendo){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   mj.staff_code,
   mj.staff_name,
   mj.busho_cd,
   mb.busho_name,
   skt.kimmuchi_cd,
   skt.nenkan_kyujitu,
   skt.rodo_jikan_per_day,
   skt.gekkyu_nempo,
   skt.shokuseki_kyu,
   skt.kihon_kyu,
   skt.kotei_kihon_kyu,
   skt.kaikin_teate,
   skt.tosi_teate,
   skt.shorei_teate,
   skt.chosei_teate,
   skt.tenkin_jutaku_teate,
   skt.tansinfunin_teate,
   skt.nempo_nengaku,
   skt.nempo_12_14,
   skt.nempo_uchi_koteizang_dai,
   skt.nempo_uchi_eigyo_teate,
   skt.kazoku_teate,

   mk.todofuken_cd,
   

   (skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) shikyu_getugaku,
   ((skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) * 12) shikyu_nengaku,



   skt.shikyugaku_a,
   skt.sagaku_teate,
   (skt.shikyugaku_a * 12) as soshikyugaku,
   skt.kakutei_jotai,
   CASE skt.kakutei_jotai
     WHEN '0' THEN '未確定'
     WHEN '1' THEN '仮確定'
     WHEN '2' THEN '確定'
     ELSE ''
   END as kakutei,
   CASE
    WHEN mj2.staff_name IS NOT NULL THEN mj2.staff_name
    ELSE ''
   END as update_user_name
  FROM
   mst_jugyoin mj
   LEFT JOIN sinsei_kyuyo_tbl skt
   ON mj.nendo = skt.nendo
   AND mj.staff_code = skt.staff_code
   LEFT JOIN mst_busho mb
   ON mj.nendo = mb.nendo
   AND mj.busho_cd = mb.busho_cd

   LEFT JOIN mst_kimmuchi mk
   ON mj.nendo = mk.nendo
   AND mj.kimmuchi_cd = mk.siten_cd

   LEFT JOIN mst_jugyoin mj2
   ON mj.nendo = mj2.nendo
   AND skt.update_user = mj2.staff_code
   AND mj2.status = '0'
  WHERE
   mj.status = '0'
   AND mj.nendo = '{$nendo}'

   AND mj.duties_cd <= 8000

  ORDER BY mj.busho_cd, mj.staff_code;
EOM;

  $result = pg_query($sql);

  if (!$result) {
		die('クエリーが失敗しました（全従業員を取得）'.pg_last_error());
		print('クエリーが失敗しました。'.$sql);
	}

  $array = array();

  //レコード取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}

////////////////////////////////////////////////////////////////////////////////
//＜＜給与シミュレーションデータ一覧＞＞
// 全従業員の情報にシミュレーション給与テーブルの情報を付けて取得する
//$nendo:年度
//$pattern_id:パターンｉｄ
//返り値：
//レコードが存在しない場合は空のarrayを返す。
//全従業員の情報が配列(配列の中は連想配列)で返す。
////////////////////////////////////////////////////////////////////////////////
function getUserKanriJugyoinAllSimu($nendo, $pattern_id, $busho_cd = false){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }


  $sql  = "SELECT";
  $sql .= " mj.staff_code,";
  $sql .= " mj.staff_name,";
  $sql .= " mj.busho_cd,";
  $sql .= " mb.busho_name,";

  $sql .= " skt.kimmuchi_cd,";
  $sql .= " skt.nenkan_kyujitu,";
  $sql .= " skt.rodo_jikan_per_day,";
  $sql .= " skt.gekkyu_nempo,";
  $sql .= " skt.shokuseki_kyu,";
  $sql .= " skt.kihon_kyu,";
  $sql .= " skt.kotei_kihon_kyu,";
  $sql .= " skt.kaikin_teate,";
  $sql .= " skt.tosi_teate,";
  $sql .= " skt.shorei_teate,";
  $sql .= " skt.chosei_teate,";
  $sql .= " skt.tenkin_jutaku_teate,";
  $sql .= " skt.tansinfunin_teate,";
  $sql .= " skt.nempo_nengaku,";
  $sql .= " skt.nempo_12_14,";
  $sql .= " skt.nempo_uchi_koteizang_dai,";
  $sql .= " skt.nempo_uchi_eigyo_teate,";
  $sql .= " skt.kazoku_teate,";

  $sql .= " mk.todofuken_cd,";

  $sql .= " (skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) shikyu_getugaku,";
  $sql .= " ((skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) * 12) shikyu_nengaku,";
  $sql .= " skt.shikyugaku_a,";
  $sql .= " skt.sagaku_teate,";
  $sql .= " (skt.shikyugaku_a * 12) as soshikyugaku,";
  $sql .= " CASE skt.kakutei_jotai";
  $sql .= " WHEN '0' THEN '未確定'";
  $sql .= " WHEN '1' THEN '仮確定'";
  $sql .= " WHEN '2' THEN '確定'";
  $sql .= " ELSE ''";
  $sql .= " END as kakutei,";
  $sql .= " CASE";
  $sql .= " WHEN mj2.staff_name IS NOT NULL THEN mj2.staff_name";
  $sql .= " ELSE ''";
  $sql .= " END as update_user_name";
  $sql .= " FROM";
  $sql .= " mst_jugyoin mj";
  $sql .= " LEFT JOIN (";
  $sql .= " SELECT *";
  $sql .= " FROM simu_kyuyo_tbl";
  $sql .= " WHERE pattern_id = " . $pattern_id;
  $sql .= " ) skt";
  $sql .= " ON mj.nendo = skt.nendo";
  $sql .= " AND mj.staff_code = skt.staff_code";
  $sql .= " LEFT JOIN mst_busho mb";
  $sql .= " ON mj.nendo = mb.nendo";
  $sql .= " AND mj.busho_cd = mb.busho_cd";
  $sql .= " LEFT JOIN mst_kimmuchi mk";
  $sql .= " ON mj.nendo = mk.nendo";
  $sql .= " AND mj.kimmuchi_cd = mk.siten_cd";
  $sql .= " LEFT JOIN mst_jugyoin mj2";
  $sql .= " ON mj.nendo = mj2.nendo";
  $sql .= " AND skt.update_user = mj2.staff_code";
  $sql .= " AND mj2.status = '0'";
  $sql .= " WHERE mj.status = '0'";
  $sql .= " AND mj.nendo = '" . $nendo . "'";
  $sql .= " AND mj.duties_cd <= 8000";
  
  if($busho_cd){
	  $sql .= " AND mj.busho_cd = " . $busho_cd;
  }
  
  $sql .= " ORDER BY mj.busho_cd, mj.staff_code;";




//   $sql = <<< EOM
//   SELECT
//    mj.staff_code,
//    mj.staff_name,
//    mj.busho_cd,
//    mb.busho_name,

//    (skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) shikyu_getugaku,
//    ((skt.shokuseki_kyu + skt.kihon_kyu + skt.kotei_kihon_kyu + skt.kaikin_teate + skt.tosi_teate + skt.shorei_teate + skt.chosei_teate + skt.tenkin_jutaku_teate + skt.tansinfunin_teate + skt.kazoku_teate + skt.korituzangyo_teate_gaitogaku) * 12) shikyu_nengaku,

//    skt.shikyugaku_a,
//    skt.sagaku_teate,
//    (skt.shikyugaku_a * 12) as soshikyugaku,

//    CASE skt.kakutei_jotai
//      WHEN '0' THEN '未確定'
//      WHEN '1' THEN '仮確定'
//      WHEN '2' THEN '確定'
//      ELSE ''
//    END as kakutei,
//    CASE
//     WHEN mj2.staff_name IS NOT NULL THEN mj2.staff_name
//     ELSE ''
//    END as update_user_name
//   FROM
//    mst_jugyoin mj
//    LEFT JOIN (
//     SELECT *
//     FROM simu_kyuyo_tbl
//     WHERE pattern_id = {$pattern_id}
//    ) skt
//    ON mj.nendo = skt.nendo
//    AND mj.staff_code = skt.staff_code
//    LEFT JOIN mst_busho mb
//    ON mj.nendo = mb.nendo
//    AND mj.busho_cd = mb.busho_cd
//    LEFT JOIN mst_jugyoin mj2
//    ON mj.nendo = mj2.nendo
//    AND skt.update_user = mj2.staff_code
//    AND mj2.status = '0'
//   WHERE
//    mj.status = '0'
//    AND mj.nendo = '{$nendo}'

//    AND mj.duties_cd <= 8000

//   ORDER BY mj.busho_cd, mj.staff_code;
// EOM;

  $result = pg_query($sql);

  if (!$result) {
		die('クエリーが失敗しました（全従業員を取得）'.pg_last_error());
		print('クエリーが失敗しました。'.$sql);
	}

  $array = array();

  //レコード取得
  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーション用従業員一覧画面用＞＞
//部署ごとの月間、年間人件費総額を取得する。
//$nendo:年度
//$pattern_id：パターンid
//引数が空の場合は-1を返す。
//
//返り値:
//配列の中にbusho_cd,busho_name,shikyugaku_month_total,shikyugaku_year_totalの連想配列
//が入っている
//////////////////////////////////////////////////////////////////////////////////////////////
function getSimuTotalSalaryDepartmentalForMonthYear($nendo,$pattern_id){
  //引数が正しくない場合はエラー
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   c.busho_name
   , c.busho_cd
   , COALESCE(d.shikyugaku_year_total, 0) as shikyugaku_year_total
   , COALESCE(d.shikyugaku_month_total, 0) as shikyugaku_month_total
  FROM
   mst_busho c
   LEFT JOIN (
    SELECT
      a.nendo
      , a.busho_cd
      , SUM(c.shikyugaku_a * 12) as shikyugaku_year_total
      , SUM(c.shikyugaku_a) as shikyugaku_month_total
    FROM
      mst_jugyoin a
      LEFT JOIN mst_kyuyo_ambun b
        ON a.nendo = b.nendo
        AND a.staff_code = b.staff_code
      LEFT JOIN simu_kyuyo_tbl c
        ON a.nendo = c.nendo
        AND a.staff_code = c.staff_code
    WHERE
      b.nendo IS NULL
      AND a.status = '0'
      AND c.pattern_id = '{$pattern_id}'
    GROUP BY
      a.nendo
      , a.busho_cd
   ) d
    ON c.busho_cd = d.busho_cd
    AND c.nendo = d.nendo
  WHERE
   c.nendo = '{$nendo}'
  ORDER BY
   busho_cd ASC;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  $sql = <<< EOM
  SELECT
   b.ambun_busho_cd
   , COALESCE(ceil((c.shikyugaku_a * 12) * (b.kyuyo_ambun_ritu / 100)),0) as nen_shukyugaku
   , COALESCE(ceil(c.shikyugaku_a * (b.kyuyo_ambun_ritu / 100)),0) as tsuki_shukyugaku
  FROM
   mst_jugyoin a
   INNER JOIN mst_kyuyo_ambun b
     ON a.nendo = b.nendo
     AND a.staff_code = b.staff_code
   LEFT JOIN simu_kyuyo_tbl c
     ON a.staff_code = c.staff_code
     AND a.nendo = c.nendo
  WHERE
   a.nendo = '{$nendo}'
   AND c.pattern_id = '{$pattern_id}'
   AND a.status = '0';
EOM;

  $result = pg_query($sql);

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    foreach($array as &$rd){
      if($rd['busho_cd'] == $rows['ambun_busho_cd']){

        $rd['shikyugaku_year_total'] = $rd['shikyugaku_year_total'] + $rows['nen_shukyugaku'];
        $rd['shikyugaku_month_total'] = $rd['shikyugaku_month_total'] + $rows['tsuki_shukyugaku'];

      }
    }
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜昇給訂正／昇給嘆願　要望一覧用＞＞
//ログインしているユーザが管理する従業員の昇給訂正/嘆願情報を取得する。
//申請用系テーブルから取得する。
//$nendo:年度
//$staff_code:従業員番号
//引数が空の場合は-1を返す。
//////////////////////////////////////////////////////////////////////////////////////////////
function getShukyuTanganListSinsei($nendo, $staff_code, $user_shubetu){
  if(empty($nendo) || empty($staff_code) || empty($user_shubetu)){
    return -1;
  }

  if(($user_shubetu == 3)||($user_shubetu == 4)||($user_shubetu == 5)){//一般参照ユーザー、運用管理ユーザー、システム管理ユーザーの場合は全て
    $where = <<< EOM
a.nendo = '{$nendo}'
AND a.status = '0'
EOM;
  }else{

    $sql = <<< EOM
SELECT
 *
 FROM
  mst_jugyoin
 WHERE
  nendo = '{$nendo}'
  AND status = '0'
  AND staff_code = '{$staff_code}'
 ORDER BY
  busho_cd,
  kanrisha_staff_code,
  staff_code
EOM;
    
    $result = pg_query($sql);
    
    if(pg_num_rows($result) < 1){//もし指定の年にユーザーの情報がない場合は空の配列を返す
      return array();
    }
    
    $rogin_user_data = pg_fetch_array($result, NULL, PGSQL_ASSOC);
    
    if($rogin_user_data["duties_cd"] >= 8000){//部長代理以上の場合
      $temp = substr($rogin_user_data["busho_cd"], 0, 4);
    
      if(substr($temp, 0, 2) == "15"){
        $sanshokubun = "15";
      } else {
        $sanshokubun = $temp;
      }
    
      $where = <<< EOM
 a.nendo = '{$nendo}'
 AND a.status = '0'
 AND (
 (
 cast(a.busho_cd as varchar) LIKE '{$sanshokubun}%'
 AND a.duties_cd < '{$rogin_user_data["duties_cd"]}'
 )
 OR a.staff_code = '{$staff_code}'
 )
EOM;
    } else {//それ以外の場合
      $where = <<< EOM
a.nendo = '{$nendo}'
 AND a.status = '0'
 AND (
 a.kanrisha_staff_code = '{$staff_code}'
 OR a.staff_code = '{$staff_code}'
 )
EOM;
    }
  }


  $sql = <<< EOM
SELECT
  a.nendo
  , a.staff_code
  , a.duties_cd
  , b.busho_name
  , d.staff_name as kanrisha
  , a.staff_name as taishosha
  , c.shokyu_gaku as choseimae
  , c.shokyu_teisei_gaku as choseigo
  , c.shokyu_tangan_gaku as shokyugaku
  , c.shokyu_tangan_riyu as shokyuriyu
FROM
  mst_jugyoin a
  LEFT JOIN mst_busho b
    ON a.nendo = b.nendo
    AND a.busho_cd = b.busho_cd
  LEFT JOIN sinsei_kyuyo_tbl c
    ON a.nendo = c.nendo
    AND a.staff_code = c.staff_code
  LEFT JOIN mst_jugyoin d
    ON a.nendo = d.nendo
    AND a.kanrisha_staff_code = d.staff_code
    AND d.status = '0'
WHERE
  {$where}
ORDER BY
 a.staff_code;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜確定制御用＞＞
//年度マスタ状態から指定した年月の情報を取得する。
//$nendo:年度
//引数が空の場合は-1を返す。
//////////////////////////////////////////////////////////////////////////////////////////////
function getDataForKakutei($nendo){
  return getKakuteiJotaiKyuyoHyoka($nendo);
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜確定制御用＞＞
//年度マスタ状態から指定した年月の給与確定状態を変更する。
//$nendo:年度

//引数が空の場合は-1を返す。
//////////////////////////////////////////////////////////////////////////////////////////////
function setDataForKyuyoKakutei($nendo,$jotai){
  if(empty($nendo)){
    return -1;
  }
  $sql = <<< EOM
  UPDATE
   mst_nendo
  SET
   kakutei_jotai = '{$jotai}'
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜確定制御用＞＞
//年度マスタ状態から指定した年月の夏季評価確定状態を変更する。
//$nendo:年度

//引数が空の場合は-1を返す。
//////////////////////////////////////////////////////////////////////////////////////////////
function setDataForKakiHyokaKakutei($nendo,$jotai){
  if(empty($nendo)){
    return -1;
  }
  $sql = <<< EOM
  UPDATE
   mst_nendo
  SET
   kaki_hyoka_kakutei_jotai = '{$jotai}'
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜確定制御用＞＞
//年度マスタ状態から指定した年月の冬季評価確定状態を変更する。
//$nendo:年度

//引数が空の場合は-1を返す。
//////////////////////////////////////////////////////////////////////////////////////////////
function setDataForTokiHyokaKakutei($nendo,$jotai){
  if(empty($nendo)){
    return -1;
  }
  $sql = <<< EOM
  UPDATE
   mst_nendo
  SET
   toki_hyoka_kakutei_jotai = '{$jotai}'
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜評価点入力用＞＞
//評価点情報を取得する。
//(管理する従業員を指定しないばあいは管理する従業員すべてを返す)
//$nendo:年度
//$staff_code:従業員番号
//$kanri_staff_code:管理する従業員を指定
//引数が空の場合は-1を返す。
//////////////////////////////////////////////////////////////////////////////////////////////
function getHyoukatenInfo($nendo,$staff_code,$user_shubetsu,$kanri_staff_code){
  if(empty($nendo) || empty($staff_code) || $user_shubetsu == ""){
    return -1;
  }

  if($user_shubetsu == "04" || $user_shubetsu == "05"){
    $where = "";
  } elseif($user_shubetsu == "01"){
    $where = " AND a.kanrisha_staff_code = '{$staff_code}' ";
  } else {
    //それ以外は空の配列を返す。
    return array();
  }

  if($kanri_staff_code != ""){
    $where .= " AND a.staff_code = '{$kanri_staff_code}' ";
  }

  $sql = <<< EOM
SELECT
  b.busho_name,
  b.busho_cd,
  a.staff_name,
  a.staff_code,
  cast(c.kaki_konnando_1 as integer),
  cast(c.kaki_konnando_2 as integer),
  cast(c.kaki_konnando_3 as integer),
  cast(c.kaki_konnando_4 as integer),
  cast(c.kaki_konnando_5 as integer),
  cast(c.kaki_tasseido_1 as integer),
  cast(c.kaki_tasseido_2 as integer),
  cast(c.kaki_tasseido_3 as integer),
  cast(c.kaki_tasseido_4 as integer),
  cast(c.kaki_tasseido_5 as integer),
  cast(c.toki_konnando_1 as integer),
  cast(c.toki_konnando_2 as integer),
  cast(c.toki_konnando_3 as integer),
  cast(c.toki_konnando_4 as integer),
  cast(c.toki_konnando_5 as integer),
  cast(c.toki_tasseido_1 as integer),
  cast(c.toki_tasseido_2 as integer),
  cast(c.toki_tasseido_3 as integer),
  cast(c.toki_tasseido_4 as integer),
  cast(c.toki_tasseido_5 as integer),
  'false' as focus_flg
FROM
  mst_jugyoin a
  LEFT JOIN mst_busho b
  ON a.nendo = b.nendo
  AND a.busho_cd = b.busho_cd
  LEFT JOIN hyoka_tbl c
  ON a.nendo = c.nendo
  AND a.staff_code = c.staff_code
WHERE
  a.nendo = '{$nendo}'
  AND a.status = '0'
  {$where}
ORDER BY
 a.busho_cd,
 a.staff_code;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜評価点入力用＞＞
//評価点情報を更新する。
//(管理する従業員を指定しないばあいは管理する従業員すべてを返す)
//$nendo:年度
//$satei:評価点情報
//////////////////////////////////////////////////////////////////////////////////////////////
function updateHyoukatenInfo($nendo,$satei){
  if(empty($nendo) || empty($satei)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   hyoka_tbl
  WHERE
   nendo = '{$nendo}'
   AND staff_code = '{$satei["staff_code"]}'
EOM;

  $result = pg_query($sql);

  for($i = 1 ; $i <= 5 ; $i++){
    $satei["kaki_konnando_" . $i] = $satei["kaki_konnando_" . $i] == null || $satei["kaki_konnando_" . $i] == "" ? "0" : $satei["kaki_konnando_" . $i];
    $satei["toki_konnando_" . $i] = $satei["toki_konnando_" . $i] == null || $satei["toki_konnando_" . $i] == "" ? "0" : $satei["toki_konnando_" . $i];

    $satei["kaki_tasseido_" . $i] = $satei["kaki_tasseido_" . $i] == null || $satei["kaki_tasseido_" . $i] == "" ? "0" : $satei["kaki_tasseido_" . $i];
    $satei["toki_tasseido_" . $i] = $satei["toki_tasseido_" . $i] == null || $satei["toki_tasseido_" . $i] == "" ? "0" : $satei["toki_tasseido_" . $i];
  }

  if(pg_num_rows($result) == 0){

    $sql = <<< EOM
    INSERT INTO
     hyoka_tbl
     (
      nendo,
      staff_code,
      kaki_konnando_1,
      kaki_konnando_2,
      kaki_konnando_3,
      kaki_konnando_4,
      kaki_konnando_5,
      kaki_tasseido_1,
      kaki_tasseido_2,
      kaki_tasseido_3,
      kaki_tasseido_4,
      kaki_tasseido_5,
      toki_konnando_1,
      toki_konnando_2,
      toki_konnando_3,
      toki_konnando_4,
      toki_konnando_5,
      toki_tasseido_1,
      toki_tasseido_2,
      toki_tasseido_3,
      toki_tasseido_4,
      toki_tasseido_5
    )
    VALUES(
      '{$nendo}',
      '{$satei["staff_code"]}',
      '{$satei["kaki_konnando_1"]}',
      '{$satei["kaki_konnando_2"]}',
      '{$satei["kaki_konnando_3"]}',
      '{$satei["kaki_konnando_4"]}',
      '{$satei["kaki_konnando_5"]}',
      '{$satei["kaki_tasseido_1"]}',
      '{$satei["kaki_tasseido_2"]}',
      '{$satei["kaki_tasseido_3"]}',
      '{$satei["kaki_tasseido_4"]}',
      '{$satei["kaki_tasseido_5"]}',
      '{$satei["toki_konnando_1"]}',
      '{$satei["toki_konnando_2"]}',
      '{$satei["toki_konnando_3"]}',
      '{$satei["toki_konnando_4"]}',
      '{$satei["toki_konnando_5"]}',
      '{$satei["toki_tasseido_1"]}',
      '{$satei["toki_tasseido_2"]}',
      '{$satei["toki_tasseido_3"]}',
      '{$satei["toki_tasseido_4"]}',
      '{$satei["toki_tasseido_5"]}'
    );
EOM;
  } else {
    $sql = <<< EOM
    UPDATE
     hyoka_tbl
    SET
     kaki_konnando_1 = '{$satei["kaki_konnando_1"]}',
     kaki_konnando_2 = '{$satei["kaki_konnando_2"]}',
     kaki_konnando_3 = '{$satei["kaki_konnando_3"]}',
     kaki_konnando_4 = '{$satei["kaki_konnando_4"]}',
     kaki_konnando_5 = '{$satei["kaki_konnando_5"]}',
     kaki_tasseido_1 = '{$satei["kaki_tasseido_1"]}',
     kaki_tasseido_2 = '{$satei["kaki_tasseido_2"]}',
     kaki_tasseido_3 = '{$satei["kaki_tasseido_3"]}',
     kaki_tasseido_4 = '{$satei["kaki_tasseido_4"]}',
     kaki_tasseido_5 = '{$satei["kaki_tasseido_5"]}',
     toki_konnando_1 = '{$satei["toki_konnando_1"]}',
     toki_konnando_2 = '{$satei["toki_konnando_2"]}',
     toki_konnando_3 = '{$satei["toki_konnando_3"]}',
     toki_konnando_4 = '{$satei["toki_konnando_4"]}',
     toki_konnando_5 = '{$satei["toki_konnando_5"]}',
     toki_tasseido_1 = '{$satei["toki_tasseido_1"]}',
     toki_tasseido_2 = '{$satei["toki_tasseido_2"]}',
     toki_tasseido_3 = '{$satei["toki_tasseido_3"]}',
     toki_tasseido_4 = '{$satei["toki_tasseido_4"]}',
     toki_tasseido_5 = '{$satei["toki_tasseido_5"]}'
    WHERE
     nendo = '{$nendo}'
     AND staff_code = '{$satei["staff_code"]}'
EOM;
  }

  $result = pg_query($sql);

  if (!$result) {
    pg_query("ROLLBACK");
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜評価点入力用＞＞
//プルダウン用部署情報を取得する。
//$nendo:年度
//$staff_code:ログインユーザー
//////////////////////////////////////////////////////////////////////////////////////////////
function getHyoukatenBusho($nendo,$staff_code,$user_shubetsu){
  if(empty($nendo) || empty($staff_code) || $user_shubetsu == ""){
    return -1;
  }

  if($user_shubetsu == "4" || $user_shubetsu == "5"){
    $where = "";
  } elseif($user_shubetsu == "1"){
    $where = " AND a.kanrisha_staff_code = '{$staff_code}' ";
  } else {
    //それ以外は空の配列を返す。
    return array();
  }

  $sql = <<< EOM
SELECT
  b.busho_name,
  b.busho_cd
FROM
  mst_jugyoin a
  LEFT JOIN mst_busho b
  ON a.nendo = b.nendo
  AND a.busho_cd = b.busho_cd
WHERE
  a.nendo = '{$nendo}'
  AND a.status = '0'
  {$where}
GROUP BY
 b.busho_cd,
 b.busho_name
ORDER BY
 b.busho_cd;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)年間休日数設定用＞＞
//年間休日マスタ情報を取得する。(申請用)
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getDataForNenkanKyujituSinsei($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   sinsei_mst_nenkankyujitu
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  return pg_fetch_array($result, NULL, PGSQL_ASSOC);

}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)年間休日数設定用＞＞
//年間休日マスタ情報を更新する。
//$nendo:年度
//$holiday_1year:年間休日日数
//$workingtime_1day:1日あたりの労働時間
//$breaktime_1day:1日当たりの休憩時間
//////////////////////////////////////////////////////////////////////////////////////////////
function updateDataForNenkanKyujituSinsei($nendo,$yusen,$holiday_1year,$workingtime_1day,$breaktime_1day){
  if(empty($nendo)
  || ($yusen != "0" && $yusen != "1")
  || $holiday_1year != "0" && empty($holiday_1year)
  || $workingtime_1day != "0" && empty($workingtime_1day)
  || ($breaktime_1day != "0" && empty($breaktime_1day))){
    return -1;
  }

  $sql = <<< EOM
  UPDATE
   sinsei_mst_nenkankyujitu
  SET
   yusen                = '{$yusen}',
   nenkan_kyujitu       = '{$holiday_1year}',
   rodo_jikan_per_day   = '{$workingtime_1day}',
   kyukei_jikan_per_day = '{$breaktime_1day}'
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    pg_query("ROLLBACK");
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)年間休日数設定用＞＞
//シミュ―レーションパターンマスタ情報を取得する。
//////////////////////////////////////////////////////////////////////////////////////////////
function getPattenMastaForNenkanKyujitusimu(){
  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_pattern;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)年間休日数設定用＞＞
//年間休日マスタ情報を取得する。(シミュレーション用)
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getDataForNenkanKyujitusimu($nendo,$pattern){
  if(empty($nendo) || empty($pattern)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_nenkankyujitu
  WHERE
   nendo = '{$nendo}'
   AND pattern_id = '{$pattern}';
EOM;

  $result = pg_query($sql);

  return pg_fetch_array($result, NULL, PGSQL_ASSOC);
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)年間休日数設定用＞＞
//年間休日マスタ情報を更新する。
//$nendo:年度
//$pattern:パターンID
//$holiday_1year:年間休日日数
//$workingtime_1day:1日あたりの労働時間
//$breaktime_1day:1日当たりの休憩時間
//////////////////////////////////////////////////////////////////////////////////////////////
function updateDataForNenkanKyujituSimu($nendo,$pattern,$yusen,$holiday_1year,$workingtime_1day,$breaktime_1day){
  if(empty($nendo)
  || empty($pattern)
  || ($yusen != "0" && $yusen != "1")
  || $holiday_1year != "0" && empty($holiday_1year)
  || $workingtime_1day != "0" && empty($workingtime_1day)
  || ($breaktime_1day != "0" && empty($breaktime_1day))){
    return -1;
  }

  $sql = <<< EOM
  UPDATE
   simu_mst_nenkankyujitu
  SET
   yusen                = '{$yusen}',
   nenkan_kyujitu       = '{$holiday_1year}',
   rodo_jikan_per_day   = '{$workingtime_1day}',
   kyukei_jikan_per_day = '{$breaktime_1day}'
  WHERE
   nendo = '{$nendo}'
   AND pattern_id = '{$pattern}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜支店別残業時間入力用＞＞
//シミュ―レーションパターンマスタ情報を取得する。
//////////////////////////////////////////////////////////////////////////////////////////////
function getPattenMastaForZangyoJikanInputSimu(){
  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_pattern;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜支店別残業時間入力用＞＞
//指定の年度の部署マスタ情報を取得する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getAllBushoForZangyoJikanInput($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   busho_cd,
   busho_name,
   'false' as focus_flg
  FROM
   mst_busho
  WHERE
   nendo = '{$nendo}'
  ORDER BY
   busho_cd;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜支店別残業時間入力用＞＞
//シミュレーション支店別残業時間テーブルからデータを１つ取得する。
//$nendo:年度
//$pattern:パターン
//$busho_cd:部署コード
//////////////////////////////////////////////////////////////////////////////////////////////
function getShitenZangyoJikanDataSimu($nendo,$pattern,$busho_cd){
  if(empty($nendo) || empty($pattern) ||  empty($busho_cd)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_siten_zangyo_jikan_tbl
  WHERE
   nendo = '{$nendo}'
   AND pattern_id = '{$pattern}'
   AND busho_cd = '{$busho_cd}';
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜支店別残業時間入力用＞＞
//シミュレーション支店別残業時間を新規登録、または更新する。
//$nendo:年度
//$pattern:パターン
//$busho_cd:部署コード
//$t_z_jikan:通常残業時間
//$s_z_jikan:深夜残業時間
//$k_r_jikan:休日労働時間
//$k_s_r_jikan:休日深夜労働時間
//////////////////////////////////////////////////////////////////////////////////////////////
function updateShitenZangyoJikanDataSimu($nendo,$pattern,$busho_cd,$t_z_jikan,$s_z_jikan,$k_r_jikan,$k_s_r_jikan){
  if(empty($nendo) || empty($pattern) || empty($busho_cd)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_siten_zangyo_jikan_tbl
  WHERE
   nendo = '{$nendo}'
   AND pattern_id = '{$pattern}'
   AND busho_cd = '{$busho_cd}';
EOM;

  $result = pg_query($sql);

  if(pg_num_rows($result) == 0){

    $sql = <<< EOM
    INSERT INTO
     simu_siten_zangyo_jikan_tbl
     (
      nendo,
      pattern_id,
      busho_cd,
      tujo_zan_jikan,
      sinya_zan_jikan,
      kyujitu_rodo_jikan,
      kyujitusinya_rodo_jikan
    )
    VALUES(
      '{$nendo}',
      '{$pattern}',
      '{$busho_cd}',
      '{$t_z_jikan}',
      '{$s_z_jikan}',
      '{$k_r_jikan}',
      '{$k_s_r_jikan}'
    );
EOM;
  } else {
    $sql = <<< EOM
    UPDATE
     simu_siten_zangyo_jikan_tbl
    SET
     tujo_zan_jikan         = '{$t_z_jikan}',
     sinya_zan_jikan         = '{$s_z_jikan}',
     kyujitu_rodo_jikan      = '{$k_r_jikan}',
     kyujitusinya_rodo_jikan = '{$k_s_r_jikan}'
    WHERE
     nendo = '{$nendo}'
     AND pattern_id = '{$pattern}'
     AND busho_cd = '{$busho_cd}';
EOM;
  }

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)職責・職責給マスタ管理入力用＞＞
//指定の年度の職責・職責給マスタ情報を取得する。
//職責コードの指定がある場合は1件のみ取得する。
//$nendo:年度
//$duties_cd:職責コード
//////////////////////////////////////////////////////////////////////////////////////////////
function getAllShokusekiForShokusekiKanriSinsei($nendo,$duties_cd){
  if(empty($nendo)){
    return -1;
  }

  if($duties_cd == ""){
    $where = "";
  } else {
    $where = "AND duties_cd = '{$duties_cd}'";
  }

  $sql = <<< EOM
  SELECT
   nendo,
   duties_cd,
   duties_name,
   shokusekikyu_jogen,
   shokusekikyu_kagen,
   'false' as focus_flg
  FROM
   sinsei_mst_shokuseki
  WHERE
   nendo = '{$nendo}'
   {$where}
  ORDER BY
   duties_cd;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)職責・職責給マスタ管理入力用＞＞
//指定の年度の職責・職責給マスタ情報を更新する。
//$shokuseki:職責マスタに登録するデータがはいったオブジェクト
//////////////////////////////////////////////////////////////////////////////////////////////
function updateDataForShokusekiSinsei($shokuseki){
  if($shokuseki == []){
    return -1;
  }

  $duties_name = pg_escape_literal($shokuseki["duties_name"]);

  $sql = <<< EOM
    UPDATE
     sinsei_mst_shokuseki
    SET
     duties_name        = {$duties_name},
     shokusekikyu_jogen = '{$shokuseki["shokusekikyu_jogen"]}',
     shokusekikyu_kagen = '{$shokuseki["shokusekikyu_kagen"]}'
    WHERE
     nendo = '{$shokuseki["nendo"]}'
     AND duties_cd = '{$shokuseki["duties_cd"]}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)職責・職責給マスタ管理入力用＞＞
//指定の年度、職責コードの職責・職責給マスタ情報を削除する。
//$nendo:年度
//$duties_cds:削除する職責コードの配列
//////////////////////////////////////////////////////////////////////////////////////////////
function deleteDataForShokusekiSinsei($nendo,$duties_cds){
  if(empty($duties_cds) || empty($nendo)){
    return -1;
  }

  $duties_cd_str = "'" . implode("','",$duties_cds) . "'";

  $sql = <<< EOM
  DELETE FROM
   sinsei_mst_shokuseki
  WHERE
   nendo = '{$nendo}'
   AND duties_cd IN ({$duties_cd_str});
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }

  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)職責・職責給マスタ管理入力用＞＞
//新規登録を行う。
//すでに指定の年度、職責コードでデータが存在する場合はエラーコード”２”を返す。
//$nendo:年度
//$duties_cds:削除する職責コードの配列
//////////////////////////////////////////////////////////////////////////////////////////////
function insertDataForShokusekiSinsei($shokuseki){
  if($shokuseki == []){
    return -1;
  }

  //すでに指定年度の職責コードが存在する場合は登録しない
  $sql = <<< EOM
  SELECT
   *
  FROM
   sinsei_mst_shokuseki
  WHERE
   nendo = '{$shokuseki["nendo"]}'
   AND duties_cd = '{$shokuseki["duties_cd"]}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }

  if(pg_num_rows($result) != 0){
    return "2";
  }

  $duties_name = pg_escape_literal($shokuseki["duties_name"]);

  $sql = <<< EOM
  INSERT INTO
   sinsei_mst_shokuseki
  (
   nendo,
   duties_cd,
   duties_name,
   shokusekikyu_jogen,
   shokusekikyu_kagen
   )
   VALUES(
     '{$shokuseki["nendo"]}',
     '{$shokuseki["duties_cd"]}',
     {$duties_name},
     '{$shokuseki["shokusekikyu_jogen"]}',
     '{$shokuseki["shokusekikyu_kagen"]}'
   );
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)職責・職責給マスタ管理入力用＞＞
//シミュ―レーションパターンマスタ情報を取得する。
//////////////////////////////////////////////////////////////////////////////////////////////
function getPattenMastaForShokusekiSimu(){
  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_pattern;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)職責・職責給マスタ管理入力用＞＞
//指定の年度、指定パターンの職責・職責給マスタ情報を取得する。
//職責コードの指定がある場合は1件のみ取得する。
//$nendo:年度
//$pattern:パターンID
//$duties_cd:職責コード
//////////////////////////////////////////////////////////////////////////////////////////////
function getAllShokusekiForShokusekiKanriSimu($nendo,$pattern,$duties_cd){
  if(empty($nendo) || empty($pattern)){
    return -1;
  }

  if($duties_cd == ""){
    $where = "";
  } else {
    $where = "AND duties_cd = '{$duties_cd}'";
  }

  $sql = <<< EOM
  SELECT
   nendo,
   pattern_id,
   duties_cd,
   duties_name,
   shokusekikyu_jogen,
   shokusekikyu_kagen,
   'false' as focus_flg
  FROM
   simu_mst_shokuseki
  WHERE
   nendo = '{$nendo}'
   AND pattern_id = '{$pattern}'
   {$where}
  ORDER BY
   duties_cd;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)職責・職責給マスタ管理入力用＞＞
//指定の年度、指定パターンのの職責・職責給マスタ情報を更新する。
//$shokuseki:職責マスタに登録するデータがはいったオブジェクト
//////////////////////////////////////////////////////////////////////////////////////////////
function updateDataForShokusekiSimu($shokuseki){
  if($shokuseki == []){
    return -1;
  }

  $duties_name = pg_escape_literal($shokuseki["duties_name"]);

  $sql = <<< EOM
    UPDATE
     simu_mst_shokuseki
    SET
     duties_name        = {$duties_name},
     shokusekikyu_jogen = '{$shokuseki["shokusekikyu_jogen"]}',
     shokusekikyu_kagen = '{$shokuseki["shokusekikyu_kagen"]}'
    WHERE
     nendo = '{$shokuseki["nendo"]}'
     AND pattern_id = '{$shokuseki["pattern_id"]}'
     AND duties_cd = '{$shokuseki["duties_cd"]}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)職責・職責給マスタ管理入力用＞＞
//新規登録を行う。
//すでに指定の年度、指定パターン、職責コードでデータが存在する場合はエラーコード”２”を返す。
//$nendo:年度
//$duties_cds:削除する職責コードの配列
//////////////////////////////////////////////////////////////////////////////////////////////
function insertDataForShokusekiSimu($shokuseki){
  if($shokuseki == []){
    return -1;
  }

  //すでに指定年度の職責コードが存在する場合は登録しない
  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_shokuseki
  WHERE
   nendo = '{$shokuseki["nendo"]}'
   AND pattern_id = '{$shokuseki["pattern_id"]}'
   AND duties_cd = '{$shokuseki["duties_cd"]}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }

  if(pg_num_rows($result) != 0){
    return "2";
  }

  $duties_name = pg_escape_literal($shokuseki["duties_name"]);

  $sql = <<< EOM
  INSERT INTO
   simu_mst_shokuseki
  (
   nendo,
   pattern_id,
   duties_cd,
   duties_name,
   shokusekikyu_jogen,
   shokusekikyu_kagen
   )
   VALUES(
     '{$shokuseki["nendo"]}',
     '{$shokuseki["pattern_id"]}',
     '{$shokuseki["duties_cd"]}',
     {$duties_name},
     '{$shokuseki["shokusekikyu_jogen"]}',
     '{$shokuseki["shokusekikyu_kagen"]}'
   );
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)職責・職責給マスタ管理入力用＞＞
//指定の年度、指定パターン、職責コードの職責・職責給マスタ情報を削除する。
//$nendo:年度
//$duties_cds:削除する職責コードの配列
//////////////////////////////////////////////////////////////////////////////////////////////
function deleteDataForShokusekiSimu($nendo,$pattern,$duties_cds){
  if(empty($duties_cds) || empty($nendo) || empty($pattern)){
    return -1;
  }

  $duties_cd_str = "'" . implode("','",$duties_cds) . "'";

  $sql = <<< EOM
  DELETE FROM
   simu_mst_shokuseki
  WHERE
   nendo = '{$nendo}'
   AND pattern_ID = '{$pattern}'
   AND duties_cd IN ({$duties_cd_str});
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }

  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜都道府県別最低賃金用＞＞
//都道府県マスタから全レコードを取得する。
//引数なし
//////////////////////////////////////////////////////////////////////////////////////////////
function getTodofukenForSaiteiChingin(){
  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_todofuken
  ORDER BY
   todofuken_cd;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜都道府県別最低賃金用＞＞
//勤務地マスタから都道府県コードを取得する(distinctで重複まとめる)
//$nendo:最新年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getShitenAriTodofukenForSaiteiChingin($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   DISTINCT todofuken_cd
  FROM
   mst_kimmuchi
  WHERE
   nendo = '{$nendo}'
  ORDER BY
   todofuken_cd;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜都道府県別最低賃金用＞＞
//最低賃金マスタから指定の年度の情報を取得する
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getChingenForSaiteiChingin($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   mst_saitei_chingin
  WHERE
   nendo = '{$nendo}'
  ORDER BY
   todofuken_cd;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜都道府県別最低賃金用＞＞
//最低賃金マスタを更新する。
//$data:更新するデータの配列
//////////////////////////////////////////////////////////////////////////////////////////////
function updateChingenForSaiteiChingin($data1, $data2){

  pg_query("BEGIN");

  foreach($data1 as $record){
    $sql = <<< EOM
      UPDATE
       mst_saitei_chingin
      SET
       saitei_chingin = {$record["saitei_chingin"]}
      WHERE
       nendo = '{$record["nendo"]}'
       AND todofuken_cd = '{$record["todofuken_cd"]}';
EOM;

    $result = pg_query($sql);

    if (!$result) {
      return 'クエリーが失敗しました。'.pg_last_error();
      pg_query("ROLLBACK");
    }
  }

  foreach($data2 as $record){
    $sql = <<< EOM
      UPDATE
       mst_saitei_chingin
      SET
       saitei_chingin = {$record["saitei_chingin"]}
      WHERE
       nendo = '{$record["nendo"]}'
       AND todofuken_cd = '{$record["todofuken_cd"]}';
EOM;

    $result = pg_query($sql);

    if (!$result) {
      return 'クエリーが失敗しました。'.pg_last_error();
      pg_query("ROLLBACK");
    }
  }

  pg_query("COMMIT");
  return "1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)基本給管理用＞＞
//基本給を取得する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getKihonkyuSinsei($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   nendo
   ,age
   ,( CASE WHEN age=20 THEN '歳以下' WHEN age=30 THEN '歳以上' ELSE '歳' END ) ageplus
   ,kihon_kyu
  FROM
   sinsei_mst_kihonkyu
  WHERE
   nendo = '{$nendo}'
  ORDER BY
   age;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)基本給管理用＞＞
//基本給を基本給を更新する。
//$kihonkyu_records:更新する基本給データ配列
//////////////////////////////////////////////////////////////////////////////////////////////
function updateKihonkyuSinsei($kihonkyu_records){

  pg_query("BEGIN");

  function uksSql($record){
    $sql = <<< EOM
      UPDATE
       sinsei_mst_kihonkyu
      SET
       kihon_kyu  = '{$record["kihon_kyu"]}'
      WHERE
       nendo = '{$record["nendo"]}'
       AND age = '{$record["age"]}';
EOM;

    return $sql;
  }

  foreach ($kihonkyu_records as $record) {

    $result = pg_query(uksSql($record));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(基本給を基本給を更新する)'.pg_last_error();
    }
  }

  pg_query("COMMIT");
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)基本給管理用＞＞
//シミュ―レーションパターンマスタ情報を取得する。
//////////////////////////////////////////////////////////////////////////////////////////////
function getPattenMastaForkihonkyuSimu(){
  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_pattern;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)基本給管理用＞＞
//基本給を取得する。
//$nendo:年度
//$pattern:パターンID
//////////////////////////////////////////////////////////////////////////////////////////////
function getKihonkyuSimu($nendo,$pattern){
  if(empty($nendo) || empty($pattern)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   nendo
   ,pattern_id
   ,age
   ,( CASE WHEN age=20 THEN '歳以下' WHEN age=30 THEN '歳以上' ELSE '歳' END ) ageplus
   ,kihon_kyu
 FROM
   simu_mst_kihonkyu
  WHERE
   nendo = '{$nendo}'
   AND pattern_id = '{$pattern}'
  ORDER BY
   age;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)基本給管理用＞＞
//基本給を基本給を更新する。
//$kihonkyu_records:更新する基本給データ配列
//////////////////////////////////////////////////////////////////////////////////////////////
function updateKihonkyuSimu($kihonkyu_records){

  pg_query("BEGIN");

  function uksiSql($record){
    $sql = <<< EOM
      UPDATE
       simu_mst_kihonkyu
      SET
       kihon_kyu  = '{$record["kihon_kyu"]}'
      WHERE
       nendo = '{$record["nendo"]}'
       AND age = '{$record["age"]}'
       AND pattern_id = '{$record["pattern_id"]}';
EOM;

    return $sql;
  }

  foreach ($kihonkyu_records as $record) {

    $result = pg_query(uksiSql($record));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(基本給を基本給を更新する)'.pg_last_error();
    }
  }

  pg_query("COMMIT");
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)固定基本給管理用＞＞
//固定基本給データを取得する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getKoteiKihonkyuSinsei($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   sinsei_mst_kotei_kihonkyu
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)固定基本給管理用＞＞
//基本給を更新する。
//$nendo:年度
//$kihonkyu:基本給
//////////////////////////////////////////////////////////////////////////////////////////////
function updateKoteiKihonkyuSinsei($nendo,$kihonkyu){
  if(empty($nendo) || $kihonkyu < 0){
    return -1;
  }

  $sql = <<< EOM
    UPDATE
     sinsei_mst_kotei_kihonkyu
    SET
     kotei_kihon_kyu  = '{$kihonkyu}'
    WHERE
     nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(申請用)固定基本給管理用＞＞
//ブライダル基本給を更新する。
//$nendo:年度
//$bridalkihonkyu:ブライダル基本給
//////////////////////////////////////////////////////////////////////////////////////////////
function updateKoteiBridalKihonkyuSinsei($nendo,$bridalkihonkyu){
  if(empty($nendo) || $bridalkihonkyu < 0){
    return -1;
  }

  $sql = <<< EOM
    UPDATE
     sinsei_mst_kotei_kihonkyu
    SET
     bridal_kotei_kihon_kyu  = '{$bridalkihonkyu}'
    WHERE
     nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)固定基本給管理用＞＞
//シミュ―レーションパターンマスタ情報を取得する。
//////////////////////////////////////////////////////////////////////////////////////////////
function getKoteiPattenMastaForkihonkyuSimu(){
  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_pattern;
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)固定基本給管理用＞＞
//固定基本給データを取得する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getKoteiKihonkyuSimu($nendo,$pattern){
  if(empty($nendo) || empty($pattern)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_kotei_kihonkyu
  WHERE
   nendo = '{$nendo}'
   AND pattern_id = '{$pattern}';
EOM;

  $result = pg_query($sql);

  //返却する配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}


//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)固定基本給管理用＞＞
//基本給を更新する。
//$nendo:年度
//$pattern:パターン
//$kihonkyu:基本給
//////////////////////////////////////////////////////////////////////////////////////////////
function updateKoteiKihonkyuSimu($nendo,$pattern,$kihonkyu){
  if(empty($nendo) || empty($pattern) ||  $kihonkyu < 0){
    return -1;
  }

  $sql = <<< EOM
    UPDATE
     simu_mst_kotei_kihonkyu
    SET
     kotei_kihon_kyu  = '{$kihonkyu}'
    WHERE
     nendo = '{$nendo}'
     AND pattern_id = '{$pattern}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜(シミュレーション用)固定基本給管理用＞＞
//ブライダル基本給を更新する。
//$nendo:年度
//$pattern:パターン
//$bridalkihonkyu:ブライダル基本給
//////////////////////////////////////////////////////////////////////////////////////////////
function updateKoteiBridalKihonkyuSimu($nendo,$pattern,$bridalkihonkyu){
  if(empty($nendo) || empty($pattern) ||  $bridalkihonkyu < 0){
    return -1;
  }

  $sql = <<< EOM
    UPDATE
     simu_mst_kotei_kihonkyu
    SET
     bridal_kotei_kihon_kyu  = '{$bridalkihonkyu}'
    WHERE
     nendo = '{$nendo}'
     AND pattern_id = '{$pattern}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    return 'クエリーが失敗しました。'.pg_last_error();
  }
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜職責別人件費総合計＞＞
//指定した年度、職責の人件費を取得する
//本データ系テーブルから取得
//$nendo:年度
//$duties_cd:職責コード
//////////////////////////////////////////////////////////////////////////////////////////////
function getshokusekibetsuJinkenhiTotalHon($nendo,$duties_cd){
  if(empty($nendo) || $duties_cd < 0){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   a.nendo
   , c.duties_cd
   , COALESCE(SUM(
      CASE
       WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 12)
       WHEN c.gekkyu_nempo = '1' THEN (c.shikyugaku_a * 12)
       ELSE '0'
      END
     ),0) as year_total_kyuyonomi
   , COALESCE(SUM(
      CASE
       WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 2)
       WHEN c.gekkyu_nempo = '1' THEN (c.nempo_shoyo_1 + c.nempo_shoyo_2)
       ELSE '0'
      END
    ),0) as year_total_shoyonomi
   , COALESCE(SUM(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 14)
      WHEN c.gekkyu_nempo = '1' THEN (c.shikyugaku_a * 12 + (c.nempo_shoyo_1 + c.nempo_shoyo_2))
      ELSE '0'
     END
   ),0) as  year_total
  FROM
   mst_jugyoin a
   LEFT JOIN (
     SELECT
      nendo,
      staff_code,
      duties_cd,
      gekkyu_nempo,
      (
       COALESCE(shokuseki_kyu,0) +
       COALESCE(kihon_kyu,0) +
       COALESCE(kotei_kihon_kyu,0) +
       COALESCE(kaikin_teate,0) +
       COALESCE(tosi_teate,0) +
       COALESCE(shorei_teate,0) +
       COALESCE(chosei_teate,0) +
       COALESCE(tenkin_jutaku_teate,0) +
       COALESCE(tansinfunin_teate,0) +
       COALESCE(kazoku_teate,0) +
       COALESCE(korituzangyo_teate_gaitogaku,0)
       ) as shikyugaku_kyu_teate,
       COALESCE(shikyugaku_a,0) as shikyugaku_a,
       COALESCE(nempo_shoyo_1,0) as nempo_shoyo_1,
       COALESCE(nempo_shoyo_2,0) as nempo_shoyo_2
     FROM
      hon_kyuyo_tbl
   ) c
     ON a.nendo = c.nendo
     AND a.staff_code = c.staff_code
  WHERE
   a.status = '0'
   AND a.nendo = '{$nendo}'
   AND c.duties_cd = '{$duties_cd}'
  GROUP BY
   a.nendo,
   c.duties_cd;
EOM;

  $result = pg_query($sql);

  if(pg_num_rows($result) != '0'){
    $shokuseki = pg_fetch_array($result, NULL, PGSQL_ASSOC);
  } else {
    $shokuseki = array(
      'nendo'=>$nendo,
      'duties_cd'=>$duties_cd,
      'year_total_kyuyonomi'=>'-',
      'year_total_shoyonomi'=>'-',
      'year_total'=>'-'
    );
  }

  return $shokuseki;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜年間人件費総額表示（休日ベース）＞＞
//年間休日マスタから年間休日を取得する。
//本データ系から取得
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getNenkankyujituForJinkenhiKyujituHon($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   nenkan_kyujitu
  FROM
   hon_mst_nenkankyujitu
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  $dt_arr = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  if($dt_arr){
	return $dt_arr["nenkan_kyujitu"];
  }else{
	return "-";
  }

}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜年間人件費総額表示（休日ベース）＞＞
//人件費総額を取得する。
//本データ系から取得。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getJinkenhiTotalForJinkenhiKyujituHon($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   a.nendo
   , COALESCE(SUM(
      CASE
       WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 12)
       WHEN c.gekkyu_nempo = '1' THEN (c.shikyugaku_a * 12)
       ELSE '0'
      END
     ),0) as year_total_kyuyonomi
   , COALESCE(SUM(
      CASE
       WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 2)
       WHEN c.gekkyu_nempo = '1' THEN (c.nempo_shoyo_1 + c.nempo_shoyo_2)
       ELSE '0'
      END
    ),0) as year_total_shoyonomi
   , COALESCE(SUM(
     CASE
      WHEN c.gekkyu_nempo = '0' THEN (c.shikyugaku_kyu_teate * 14)
      WHEN c.gekkyu_nempo = '1' THEN (c.shikyugaku_a * 12 + (c.nempo_shoyo_1 + c.nempo_shoyo_2))
      ELSE '0'
     END
   ),0) as  year_total
  FROM
   mst_jugyoin a
   LEFT JOIN (
     SELECT
      nendo,
      staff_code,
      gekkyu_nempo,
      (
       COALESCE(shokuseki_kyu,0) +
       COALESCE(kihon_kyu,0) +
       COALESCE(kotei_kihon_kyu,0) +
       COALESCE(kaikin_teate,0) +
       COALESCE(tosi_teate,0) +
       COALESCE(shorei_teate,0) +
       COALESCE(chosei_teate,0) +
       COALESCE(tenkin_jutaku_teate,0) +
       COALESCE(tansinfunin_teate,0) +
       COALESCE(kazoku_teate,0) +
       COALESCE(korituzangyo_teate_gaitogaku,0)
       ) as shikyugaku_kyu_teate,
       COALESCE(shikyugaku_a,0) as shikyugaku_a,
       COALESCE(nempo_shoyo_1,0) as nempo_shoyo_1,
       COALESCE(nempo_shoyo_2,0) as nempo_shoyo_2
     FROM
      hon_kyuyo_tbl
   ) c
     ON a.nendo = c.nendo
     AND a.staff_code = c.staff_code
  WHERE
   a.status = '0'
   AND a.nendo = '{$nendo}'
  GROUP BY
   a.nendo
EOM;

  $result = pg_query($sql);

  if(pg_num_rows($result) != '0'){
    $jinkenhi = pg_fetch_array($result, NULL, PGSQL_ASSOC);
  } else {
    $jinkenhi = array(
      'nendo'=>$nendo,
      'year_total_kyuyonomi'=>'-',
      'year_total_shoyonomi'=>'-',
      'year_total'=>'-'
    );
  }

  return $jinkenhi;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜年間人件費総額表示（休日ベース）＞＞
//人件費総額を計算する。
//本データ系から取得
//$nendo:年度
//$nenkankyujitu:年間休日
//////////////////////////////////////////////////////////////////////////////////////////////
function calcJinkenhiTotalForJinkenhiKyujituHon($nendo,$nenkankyujitu){
  if(empty($nendo) || $nenkankyujitu < 0){
    return -1;
  }

  //福岡の最低賃金を取得
  $fukuoka_saiteichingin = getFukuokaSaiteiChingin($nendo);

  if($fukuoka_saiteichingin == "-1"){
    return 2;//福岡の最低賃金が取得できなかった
  }

  $sql = <<< EOM
  SELECT
   a.staff_code,

   c.saitei_chingin,

   d.gekkyu_nempo,

   d.shokuseki_kyu,
   d.kihon_kyu,
   d.kotei_kihon_kyu,
   d.kaikin_teate,
   d.tosi_teate,
   d.shorei_teate,
   d.chosei_teate,
   d.tenkin_jutaku_teate,
   d.tansinfunin_teate,
   d.kazoku_teate,
   d.korituzangyo_teate_gaitogaku,

   d.nempo_shoyo_1,
   d.nempo_shoyo_2,

   d.shikyugaku_a
  FROM
   mst_jugyoin a
   LEFT JOIN mst_kimmuchi b
   ON a.nendo = b.nendo
   AND a.kimmuchi_cd = b.siten_cd
   LEFT JOIN mst_saitei_chingin c
   ON a.nendo = c.nendo
   AND b.todofuken_cd = cast(c.todofuken_cd as integer)
   LEFT JOIN hon_kyuyo_tbl d
   ON a.nendo = d.nendo
   AND a.staff_code = d.staff_code
  WHERE
   a.status = '0'
   AND a.nendo = '{$nendo}'
  ORDER BY
   a.staff_code;
EOM;

  $result = pg_query($sql);

  $year_total_kyuyonomi = 0;
  $year_total_shoyonomi = 0;
  $year_total           = 0;

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    if($rows["gekkyu_nempo"] == "1"){//年俸の場合

      $year_kyuyonomi = $rows["shikyugaku_a"] * 12;
      $year_shoyonomi = $rows["nempo_shoyo_1"] + $rows["nempo_shoyo_2"];

      $year_total_kyuyonomi += $year_kyuyonomi;
      $year_total_shoyonomi += $year_shoyonomi;
      $year_total           += $year_kyuyonomi + $year_shoyonomi;

    } else if($rows["gekkyu_nempo"] == "0") {//月給の場合

      $saitei_chingin = $rows["saitei_chingin"];

      if($saitei_chingin == null){
        return 3;//最低賃金が取得できなかった
      }

      $toshi_teate = calcTosiTeate($saitei_chingin,$fukuoka_saiteichingin,$nenkankyujitu);

      $shikyugaku_a = $rows["shokuseki_kyu"]
                    + $rows["kihon_kyu"]
                    + $rows["kotei_kihon_kyu"]
                    + $rows["kaikin_teate"]
                    + $toshi_teate
                    + $rows["shorei_teate"]
                    + $rows["chosei_teate"]
                    + $rows["tenkin_jutaku_teate"]
                    + $rows["tansinfunin_teate"]
                    + $rows["kazoku_teate"]
                    + $rows["korituzangyo_teate_gaitogaku"];

      $year_kyuyonomi = $shikyugaku_a * 12;
      $year_shoyonomi = $shikyugaku_a * 2;

      $year_total_kyuyonomi += $year_kyuyonomi;
      $year_total_shoyonomi += $year_shoyonomi;
      $year_total           += $year_kyuyonomi + $year_shoyonomi;

    }
  }

  return array(
    'year_total_kyuyonomi' => $year_total_kyuyonomi,
    'year_total_shoyonomi' => $year_total_shoyonomi,
    'year_total'           => $year_total
  );
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜年間人件費総額表示（休日ベース）＞＞
//福岡の最低賃金を取得する（内部ファンクション）
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getFukuokaSaiteiChingin($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   saitei_chingin
  FROM
   mst_saitei_chingin
  WHERE
   nendo = '{$nendo}'
   AND todofuken_cd = '40';
EOM;

  $result = pg_query($sql);

  $saitei_chingin = pg_fetch_array($result, NULL, PGSQL_ASSOC)["saitei_chingin"];

  return $saitei_chingin != null && $saitei_chingin >= 0 ? $saitei_chingin : "-1";
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜年間人件費総額表示（休日ベース）＞＞
//都市手当を計算する（内部ファンクション）
//$k_sc:勤務地の最低賃金
//$f_sc:福岡の最低賃金
//$nk:年間休日日数
//////////////////////////////////////////////////////////////////////////////////////////////
function calcTosiTeate($k_sc,$f_sc,$nk){
  //勤務地の最低賃金が福岡の最低賃金以下の場合は「都市手当」は０
  $sagaku = $k_sc - $f_sc;
  if($sagaku <= 0){
    return 0;
  }

  //一日の所定労働時間を算出する
  $day_A = 365 - $nk;
  $day_B = 40 * 52.14;
  $day_worktime = floor( $day_B / $day_A * 100 ) / 100;

  //1か月の平均所定労働時間を算出する
  $month_A = 365 - $nk;
  $month_B = $month_A * $day_worktime;
  $month_worktime = floor( $month_B / 12 * 100 ) / 100;

  //都市手当
  return ceil($sagaku * $month_worktime);//小数点切り上げ
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//指定年度のシミュレーションパターンマスタ情報を取得する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getPatternDateForSmlPatternKanriSimu($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   *
  FROM
   simu_mst_pattern
  WHERE
   nendo = '{$nendo}'
  ORDER BY
   pattern_id;
EOM;

  $result = pg_query($sql);

  if(pg_num_rows($result) == 0){
    return array();
  } else {
    return pg_fetch_all($result);
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//指定シミュレーションパターンを本データに上書きする。
//$nendo:年度
//$pattern_id:指定したシミュレーションパターンのid
//////////////////////////////////////////////////////////////////////////////////////////////
function uwagakiDataForSmlPatternKanriHon($nendo,$pattern_id){
  if(empty($nendo) || empty($pattern_id)){
    return -1;
  }

  pg_query("BEGIN");

  //本データ削除処理/////////////////////////////////////////////////////////////
  deleteHondataHon($nendo,"hon_mst_nenkankyujitu");
  deleteHondataHon($nendo,"hon_mst_kihonkyu");
  deleteHondataHon($nendo,"hon_mst_kotei_kihonkyu");
  deleteHondataHon($nendo,"hon_mst_shokuseki");
  deleteHondataHon($nendo,"hon_mst_teate_shosai");
  deleteHondataHon($nendo,"hon_mst_teate");
  deleteHondataHon($nendo,"hon_kyuyo_tbl");
  deleteHondataHon($nendo,"hon_siten_zangyo_jikan_tbl");
  //////////////////////////////////////////////////////////////////////////////


  //本データへシミュレーションデータを追加//////////////////////////////////////////

  //シミュレーション用年間休日マスタ
  $nenkan_kyujitu_data = selectSimuData('simu_mst_nenkankyujitu',$nendo,$pattern_id);

  if($nenkan_kyujitu_data != false){
    setCopyDataMstNenkanKyujituHon($nendo,$nenkan_kyujitu_data);
  }

  //シミュレーション用基本給マスタ
  $kihonkyu_data = selectSimuData('simu_mst_kihonkyu',$nendo,$pattern_id);

  if($kihonkyu_data != false){
    setCopyDataMstKihonkyuHon($nendo,$kihonkyu_data);
  }

  //シミュレーション用固定基本給マスタ
  $kotei_kihonkyu_data = selectSimuData('simu_mst_kotei_kihonkyu',$nendo,$pattern_id);

  if($kotei_kihonkyu_data != false){
    setCopyDataMstKoteiKihonkyuHon($nendo,$kotei_kihonkyu_data);
  }

  //シミュレーション用職責・職責給マスタ
  $shokuseki_data = selectSimuData('simu_mst_shokuseki',$nendo,$pattern_id);

  if($shokuseki_data != false){
    setCopyDataMstShokusekiHon($nendo,$shokuseki_data);
  }

  //シミュレーション用手当マスタ
  $teate_data = selectSimuData('simu_mst_teate',$nendo,$pattern_id);

  if($teate_data != false){
    setCopyDataMstTeateHon($nendo,$teate_data);
  }

  //シミュレーション用手当詳細マスタ
  $teate_shosai_data = selectSimuData('simu_mst_teate_shosai',$nendo,$pattern_id);

  if($teate_shosai_data != false){
    setCopyDataMstTeateShosaiHon($nendo,$teate_shosai_data);
  }

  //シミュレーション用給与テーブル
  $kyuyo_data = selectSimuData('simu_kyuyo_tbl',$nendo,$pattern_id);

  if($kyuyo_data != false){
    setCopyDataJugyoinKyuyoTblHon($nendo,$kyuyo_data);
  }

  //シミュレーション用支店別残業時間テーブル
  $zangyo_data = selectSimuData('simu_siten_zangyo_jikan_tbl',$nendo,$pattern_id);

  if($zangyo_data != false){
    setCopyDataSitenZangyoJikanTblHon($nendo,$zangyo_data);
  }

  //////////////////////////////////////////////////////////////////////////////


  pg_query("COMMIT");
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//指定のテーブルの指定年度を削除する。
//$nendo:年度
//$table_name:テーブル名
//////////////////////////////////////////////////////////////////////////////////////////////
function deleteHondataHon($nendo,$table_name){
  $sql = <<< EOM
  DELETE
  FROM
   {$table_name}
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  if (!$result) {
    pg_query("ROLLBACK");
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//本データ用年間休日マスタにシミュレーション年間休日マスタをコピーする。
//$nendo:年度
//$setData:テーブルに登録するシミュレーションデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataMstNenkanKyujituHon($nendo,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     hon_mst_nenkankyujitu
     (
      nendo,
      nenkan_kyujitu,
      rodo_jikan_per_day,
      kyukei_jikan_per_day,
      yusen
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($record["nenkan_kyujitu"])},
      {$scsq($record["rodo_jikan_per_day"])},
      {$scsq($record["kyukei_jikan_per_day"])},
      {$scsq($record["yusen"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//本データ用基本給マスタにシミュレーション基本給マスタをコピーする。
//$nendo:年度
//$setData:テーブルに登録するシミュレーションデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataMstKihonkyuHon($nendo,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     hon_mst_kihonkyu
     (
      nendo,
      age,
      kihon_kyu
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($record["age"])},
      {$scsq($record["kihon_kyu"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//本データ用固定基本給マスタにシミュレーション固定基本給マスタをコピーする。
//$nendo:年度
//$setData:テーブルに登録するシミュレーションデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataMstKoteiKihonkyuHon($nendo,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     hon_mst_kotei_kihonkyu
     (
      nendo,
      kotei_kihon_kyu,
      bridal_kotei_kihon_kyu
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($record["kotei_kihon_kyu"])},
      {$scsq($record["bridal_kotei_kihon_kyu"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//本データ用職責・職責給マスタにシミュレーション職責・職責給マスタをコピーする。
//$nendo:年度
//$setData:テーブルに登録するシミュレーションデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataMstShokusekiHon($nendo,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     hon_mst_shokuseki
     (
      nendo,
      duties_cd,
      duties_name,
      shokusekikyu_jogen,
      shokusekikyu_kagen
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($record["duties_cd"])},
      {$scsq($record["duties_name"])},
      {$scsq($record["shokusekikyu_jogen"])},
      {$scsq($record["shokusekikyu_kagen"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//本データ用手当マスタにシミュレーション手当マスタをコピーする。
//$nendo:年度
//$setData:テーブルに登録するシミュレーションデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataMstTeateHon($nendo,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     hon_mst_teate
     (
      nendo,
      teate_id,
      teate_name,
      muko_flg
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($record["teate_id"])},
      {$scsq($record["teate_name"])},
      {$scsq($record["muko_flg"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//本データ用手当詳細マスタにシミュレーション手当詳細マスタをコピーする。
//$nendo:年度
//$setData:テーブルに登録するシミュレーションデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataMstTeateShosaiHon($nendo,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     hon_mst_teate_shosai
     (
      nendo,
      teate_id,
      teate_shosai_id,
      teate_shosai_name,
      sikyu_gaku,
      kijungaku_jogen,
      kijungaku_kagen,
      duties_cd
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($record["teate_id"])},
      {$scsq($record["teate_shosai_id"])},
      {$scsq($record["teate_shosai_name"])},
      {$scsq($record["sikyu_gaku"])},
      {$scsq($record["kijungaku_jogen"])},
      {$scsq($record["kijungaku_kagen"])},
      {$scsq($record["duties_cd"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//本データ用給与テーブルにシミュレーション給与テーブルをコピーする。
//$nendo:年度
//$setData:テーブルに登録するシミュレーションデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataJugyoinKyuyoTblHon($nendo,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     hon_kyuyo_tbl
     (
      nendo,
      staff_code,
      duties_cd,
      kimmuchi_cd,
      tansinfunin_flg,
      shaho_fuyo_flg,
      nenkan_kyujitu,
      rodo_jikan_per_day,
      kyukei_jikan_per_day,
      gekkyu_nempo,
      shokyu_gaku,
      shokyu_teisei_gaku,
      shokyu_tangan_riyu,
      shokyu_tangan_gaku,
      zennen_shokuseki_kyu,
      shokuseki_kyu,
      kihon_kyu,
      kotei_kihon_kyu,
      kaikin_teate,
      tosi_teate,
      shorei_teate,
      chosei_teate,
      tenkin_jutaku_teate,
      tansinfunin_teate,
      kazoku_teate,
      korituzangyo_teate,
      korituzangyo_teate_gaitogaku,
      teate_yobi_01,
      teate_yobi_02,
      teate_yobi_03,
      teate_yobi_04,
      teate_yobi_05,
      zennen_nempo_nengaku,
      nempo_nengaku,
      nempo_12_14,
      nempo_uchi_koteizang_dai,
      nempo_uchi_eigyo_teate,
      nempo_shoyo_1,
      nempo_shoyo_2,
      shikyugaku_a,
      sagaku_teate,
      zangyo_tanka,
      tujo_zan_jikan_tuki,
      tujo_zan_teate_tuki,
      tujo_zan_jikan_nenkei,
      tujo_zan_teate_nenkei,
      sinya_zan_jikan_tuki,
      sinya_zan_teate_tuki,
      sinya_zan_jikan_nenkei,
      sinya_zan_teate_nenkei,
      kyujitu_rodo_jikan_tuki,
      kyujitu_rodo_teate_tuki,
      kyujitu_rodo_jikan_nenkei,
      kyujitu_rodo_teate_nenkei,
      kyujitusinya_rodo_jikan_tuki,
      kyujitusinya_rodo_teate_tuki,
      kyujitusinya_rodo_jikan_nenkei,
      kyujitusinya_rodo_teate_nenkei,
      zangyo_teate_gokei,
      kekkin_kojo,
      chikoku_sotai_kojo,
      biko,
      keiri_upd_flg_shokuseki,
      keiri_upd_flg_shokyu_teisei,
      keiri_upd_flg_shokyu_tangan,
      keiri_upd_flg_tsujo_zan,
      keiri_upd_flg_sinya_zan,
      keiri_upd_flg_kyujitu_rodo,
      keiri_upd_flg_kyujitu_sinya,
      kakutei_jotai,
      delete_flg,
      insert_user,
      insert_date,
      update_user,
      update_date
       )
    VALUES(
      {$scsq($nendo)},

      {$scsq($record["staff_code"])},
      {$scsq($record["duties_cd"])},
      {$scsq($record["kimmuchi_cd"])},
      {$scsq($record["tansinfunin_flg"])},
      {$scsq($record["shaho_fuyo_flg"])},
      {$scsq($record["nenkan_kyujitu"])},
      {$scsq($record["rodo_jikan_per_day"])},
      {$scsq($record["kyukei_jikan_per_day"])},
      {$scsq($record["gekkyu_nempo"])},
      {$scsq($record["shokyu_gaku"])},
      {$scsq($record["shokyu_teisei_gaku"])},
      {$scsq($record["shokyu_tangan_riyu"])},
      {$scsq($record["shokyu_tangan_gaku"])},
      {$scsq($record["zennen_shokuseki_kyu"])},
      {$scsq($record["shokuseki_kyu"])},
      {$scsq($record["kihon_kyu"])},
      {$scsq($record["kotei_kihon_kyu"])},
      {$scsq($record["kaikin_teate"])},
      {$scsq($record["tosi_teate"])},
      {$scsq($record["shorei_teate"])},
      {$scsq($record["chosei_teate"])},
      {$scsq($record["tenkin_jutaku_teate"])},
      {$scsq($record["tansinfunin_teate"])},
      {$scsq($record["kazoku_teate"])},
      {$scsq($record["korituzangyo_teate"])},
      {$scsq($record["korituzangyo_teate_gaitogaku"])},
      {$scsq($record["teate_yobi_01"])},
      {$scsq($record["teate_yobi_02"])},
      {$scsq($record["teate_yobi_03"])},
      {$scsq($record["teate_yobi_04"])},
      {$scsq($record["teate_yobi_05"])},
      {$scsq($record["zennen_nempo_nengaku"])},
      {$scsq($record["nempo_nengaku"])},
      {$scsq($record["nempo_12_14"])},
      {$scsq($record["nempo_uchi_koteizang_dai"])},
      {$scsq($record["nempo_uchi_eigyo_teate"])},
      {$scsq($record["nempo_shoyo_1"])},
      {$scsq($record["nempo_shoyo_2"])},
      {$scsq($record["shikyugaku_a"])},
      {$scsq($record["sagaku_teate"])},
      {$scsq($record["zangyo_tanka"])},
      {$scsq($record["tujo_zan_jikan_tuki"])},
      {$scsq($record["tujo_zan_teate_tuki"])},
      {$scsq($record["tujo_zan_jikan_nenkei"])},
      {$scsq($record["tujo_zan_teate_nenkei"])},
      {$scsq($record["sinya_zan_jikan_tuki"])},
      {$scsq($record["sinya_zan_teate_tuki"])},
      {$scsq($record["sinya_zan_jikan_nenkei"])},
      {$scsq($record["sinya_zan_teate_nenkei"])},
      {$scsq($record["kyujitu_rodo_jikan_tuki"])},
      {$scsq($record["kyujitu_rodo_teate_tuki"])},
      {$scsq($record["kyujitu_rodo_jikan_nenkei"])},
      {$scsq($record["kyujitu_rodo_teate_nenkei"])},
      {$scsq($record["kyujitusinya_rodo_jikan_tuki"])},
      {$scsq($record["kyujitusinya_rodo_teate_tuki"])},
      {$scsq($record["kyujitusinya_rodo_jikan_nenkei"])},
      {$scsq($record["kyujitusinya_rodo_teate_nenkei"])},
      {$scsq($record["zangyo_teate_gokei"])},
      {$scsq($record["kekkin_kojo"])},
      {$scsq($record["chikoku_sotai_kojo"])},
      {$scsq($record["biko"])},
      {$scsq($record["keiri_upd_flg_shokuseki"])},
      {$scsq($record["keiri_upd_flg_shokyu_teisei"])},
      {$scsq($record["keiri_upd_flg_shokyu_tangan"])},
      {$scsq($record["keiri_upd_flg_tsujo_zan"])},
      {$scsq($record["keiri_upd_flg_sinya_zan"])},
      {$scsq($record["keiri_upd_flg_kyujitu_rodo"])},
      {$scsq($record["keiri_upd_flg_kyujitu_sinya"])},
      {$scsq($record["kakutei_jotai"])},
      {$scsq($record["delete_flg"])},
      {$scsq($record["insert_user"])},
      {$scsq($record["insert_date"])},
      {$scsq($record["update_user"])},
      {$scsq($record["update_date"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//本データ用支店別残業時間テーブルにシミュレーション支店別残業時間テーブルをコピーする。
//$nendo:年度
//$setData:テーブルに登録するシミュレーションデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSitenZangyoJikanTblHon($nendo,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
    hon_siten_zangyo_jikan_tbl
     (
      nendo,
      busho_cd,
      tujo_zan_jikan,
      sinya_zan_jikan,
      kyujitu_rodo_jikan,
      kyujitusinya_rodo_jikan
       )
    VALUES(
      {$scsq($nendo)},
      {$record["busho_cd"]},
      {$record["tujo_zan_jikan"]},
      {$record["sinya_zan_jikan"]},
      {$record["kyujitu_rodo_jikan"]},
      {$record["kyujitusinya_rodo_jikan"]}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//選択したシミュレーションパターン情報を削除する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function deleteSimuDataForSmlPatternKanriSimu($nendo,$checked_pattern_id){
  if(empty($nendo) || empty($checked_pattern_id)){
    return -1;
  }

  function deleteSimDataSimu($nendo,$checked_str,$tbl){
    $sql = <<< EOM
    DELETE FROM
     {$tbl}
    WHERE
     nendo = '{$nendo}'
     AND pattern_id IN ({$checked_str});
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }

  $checked_pattern_str = "'" . implode("','",$checked_pattern_id) . "'";

  pg_query("BEGIN");

  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_mst_pattern");
  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_mst_nenkankyujitu");
  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_mst_kotei_kihonkyu");
  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_mst_kihonkyu");
  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_mst_shokuseki");
  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_mst_teate_shosai");
  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_mst_teate");
  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_kyuyo_tbl");
  deleteSimDataSimu($nendo,$checked_pattern_str,"simu_siten_zangyo_jikan_tbl");

  pg_query("COMMIT");
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//指定年度の申請データ情報をシミュレーションデータにコピーする。
//$nendo:年度
//$pattern_name:シミュレーションパターン名称
//////////////////////////////////////////////////////////////////////////////////////////////
function copySinseiDataForSmlPatternKanri($nendo,$pattern_name){
  if(empty($nendo) || empty($pattern_name)){
    return -1;
  }

  pg_query("BEGIN");


  ///まずはパターンマスタを作成する:start/////////////////////
  $pattern_id = insertSimuMstPatternSimu($nendo,$pattern_name)[0];
  ///まずはパターンマスタを作成する:end///////////////////////

  //申請用年間休日マスタ
  $nenkan_kyujitu_data = selectData('sinsei_mst_nenkankyujitu',$nendo);

  if($nenkan_kyujitu_data != false){
    setCopyDataSimuMstNenkanKyujituSimu($nendo,$pattern_id,$nenkan_kyujitu_data);
  }

  //申請用基本給マスタ
  $kihonkyu_data = selectData('sinsei_mst_kihonkyu',$nendo);

  if($kihonkyu_data != false){
    setCopyDataSimuMstKihonkyuSimu($nendo,$pattern_id,$kihonkyu_data);
  }

  //申請用固定基本給マスタ
  $kotei_kihonkyu_data = selectData('sinsei_mst_kotei_kihonkyu',$nendo);

  if($kotei_kihonkyu_data != false){
    setCopyDataSimuMstKoteiKihonkyuSimu($nendo,$pattern_id,$kotei_kihonkyu_data);
  }

  //申請用職責・職責給マスタ
  $shokuseki_data = selectData('sinsei_mst_shokuseki',$nendo);

  if($shokuseki_data != false){
    setCopyDataSimuMstShokusekiSimu($nendo,$pattern_id,$shokuseki_data);
  }

  //申請用手当マスタ
  $teate_data = selectData('sinsei_mst_teate',$nendo);

  if($teate_data != false){
    setCopyDataSimuMstTeateSimu($nendo,$pattern_id,$teate_data);
  }

  //申請用手当詳細マスタ
  $teate_shosai_data = selectData('sinsei_mst_teate_shosai',$nendo);

  if($teate_shosai_data != false){
    setCopyDataSimuMstTeateShosaiSimu($nendo,$pattern_id,$teate_shosai_data);
  }

  //申請用給与テーブル
  $kyuyo_data = selectData('sinsei_kyuyo_tbl',$nendo);

  if($kyuyo_data != false){
    setCopyDataSimuJugyoinKyuyoTblSimu($nendo,$pattern_id,$kyuyo_data);
  }


  pg_query("COMMIT");
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//指定年度の本データ情報をシミュレーションデータにコピーする。
//$nendo:年度
//$pattern_name:シミュレーションパターン名称
//////////////////////////////////////////////////////////////////////////////////////////////
function copyHonDataForSmlPatternKanri($nendo,$pattern_name){
  if(empty($nendo) || empty($pattern_name)){
    return -1;
  }

  pg_query("BEGIN");


  ///まずはパターンマスタを作成する:start/////////////////////
  $pattern_id = insertSimuMstPatternSimu($nendo,$pattern_name)[0];
  ///まずはパターンマスタを作成する:end///////////////////////

  //本データ用年間休日マスタ
  $nenkan_kyujitu_data = selectData('hon_mst_nenkankyujitu',$nendo);

  if($nenkan_kyujitu_data != false){
    setCopyDataSimuMstNenkanKyujituSimu($nendo,$pattern_id,$nenkan_kyujitu_data);
  }

  //本データ用基本給マスタ
  $kihonkyu_data = selectData('hon_mst_kihonkyu',$nendo);

  if($kihonkyu_data != false){
    setCopyDataSimuMstKihonkyuSimu($nendo,$pattern_id,$kihonkyu_data);
  }

  //本データ用固定基本給マスタ
  $kotei_kihonkyu_data = selectData('hon_mst_kotei_kihonkyu',$nendo);

  if($kotei_kihonkyu_data != false){
    setCopyDataSimuMstKoteiKihonkyuSimu($nendo,$pattern_id,$kotei_kihonkyu_data);
  }

  //本データ用職責・職責給マスタ
  $shokuseki_data = selectData('hon_mst_shokuseki',$nendo);

  if($shokuseki_data != false){
    setCopyDataSimuMstShokusekiSimu($nendo,$pattern_id,$shokuseki_data);
  }

  //本データ用手当マスタ
  $teate_data = selectData('hon_mst_teate',$nendo);

  if($teate_data != false){
    setCopyDataSimuMstTeateSimu($nendo,$pattern_id,$teate_data);
  }

  //本データ用手当詳細マスタ
  $teate_shosai_data = selectData('hon_mst_teate_shosai',$nendo);

  if($teate_shosai_data != false){
    setCopyDataSimuMstTeateShosaiSimu($nendo,$pattern_id,$teate_shosai_data);
  }

  //本データ用給与テーブル
  $kyuyo_data = selectData('hon_kyuyo_tbl',$nendo);

  if($kyuyo_data != false){
    setCopyDataSimuJugyoinKyuyoTblSimu($nendo,$pattern_id,$kyuyo_data);
  }

  //本データ用支店別残業時間テーブル
  $zangyo_data = selectData('hon_siten_zangyo_jikan_tbl',$nendo);

  if($zangyo_data != false){
    setCopyDataSimuSitenZangyoJikanTblSimu($nendo, $pattern_id, $zangyo_data);
  }

  pg_query("COMMIT");
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//指定した年度、パターンIDのシミュレーションデータ情報を新規シミュレーションデータにコピーする。
//$nendo:年度
//$pattern_id:指定パターンid
//$pattern_name:シミュレーションパターン名称
//////////////////////////////////////////////////////////////////////////////////////////////
function copySimuDataForSmlPatternKanri($nendo,$pattern_id,$pattern_name){
  if(empty($nendo) || empty($pattern_id) || empty($pattern_name)){
    return -1;
  }

  pg_query("BEGIN");


  ///まずはパターンマスタを作成する:start/////////////////////
  $insert_pattern_id = insertSimuMstPatternSimu($nendo,$pattern_name)[0];
  ///まずはパターンマスタを作成する:end///////////////////////

  //シミュレーション用年間休日マスタを取得
  $nenkan_kyujitu_data = selectSimuData('simu_mst_nenkankyujitu',$nendo,$pattern_id);

  if($nenkan_kyujitu_data != false){
    setCopyDataSimuMstNenkanKyujituSimu($nendo,$insert_pattern_id,$nenkan_kyujitu_data);
  }

  //シミュレーション用基本給マスタ
  $kihonkyu_data = selectSimuData('simu_mst_kihonkyu',$nendo,$pattern_id);

  if($kihonkyu_data != false){
    setCopyDataSimuMstKihonkyuSimu($nendo,$insert_pattern_id,$kihonkyu_data);
  }

  //シミュレーション用固定基本給マスタ
  $kotei_kihonkyu_data = selectSimuData('simu_mst_kotei_kihonkyu',$nendo,$pattern_id);

  if($kotei_kihonkyu_data != false){
    setCopyDataSimuMstKoteiKihonkyuSimu($nendo,$insert_pattern_id,$kotei_kihonkyu_data);
  }

  //シミュレーション用職責・職責給マスタ
  $shokuseki_data = selectSimuData('simu_mst_shokuseki',$nendo,$pattern_id);

  if($shokuseki_data != false){
    setCopyDataSimuMstShokusekiSimu($nendo,$insert_pattern_id,$shokuseki_data);
  }

  //シミュレーション用手当マスタ
  $teate_data = selectSimuData('simu_mst_teate',$nendo,$pattern_id);

  if($teate_data != false){
    setCopyDataSimuMstTeateSimu($nendo,$insert_pattern_id,$teate_data);
  }

  //シミュレーション用手当詳細マスタ
  $teate_shosai_data = selectSimuData('simu_mst_teate_shosai',$nendo,$pattern_id);

  if($teate_shosai_data != false){
    setCopyDataSimuMstTeateShosaiSimu($nendo,$insert_pattern_id,$teate_shosai_data);
  }

  //シミュレーション用給与テーブル
  $kyuyo_data = selectSimuData('simu_kyuyo_tbl',$nendo,$pattern_id);

  if($kyuyo_data != false){
    setCopyDataSimuJugyoinKyuyoTblSimu($nendo,$insert_pattern_id,$kyuyo_data);
  }

  //シミュレーション支店別残業時間テーブル
  $simu_siten_zangyo_jikan_data = selectSimuData('simu_siten_zangyo_jikan_tbl',$nendo,$pattern_id);

  if($simu_siten_zangyo_jikan_data != false){
    setCopyDataSimuSitenZangyoJikanTblSimu($nendo,$insert_pattern_id,$simu_siten_zangyo_jikan_data);
  }


  pg_query("COMMIT");
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーションパターンマスタを追加する。
//$nendo:年度
//$pattern_name:シミュレーションパターン名称
//////////////////////////////////////////////////////////////////////////////////////////////
function insertSimuMstPatternSimu($nendo,$pattern_name){
  $sql = <<< EOM
  INSERT INTO
   simu_mst_pattern
   (
    nendo,
    pattern_name,
    insert_date,
    update_date
     )
  VALUES(
    '{$nendo}',
    '{$pattern_name}',
    current_timestamp,
    current_timestamp

  )
  returning pattern_id;
EOM;

  $result = pg_query($sql);

  if (!$result) {
    pg_query("ROLLBACK");
  } else {
    return pg_fetch_row($result);
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//指定のテーブルの指定年度のデータを取得する。
//$table_name:テーブル名称
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function selectData($table_name,$nendo){
  $sql = <<< EOM
  SELECT
   *
  FROM
   {$table_name}
  WHERE
   nendo = '{$nendo}';
EOM;

  $result = pg_query($sql);

  return pg_fetch_all($result);
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//指定のテーブルの指定年度、パターンIDのデータを取得する。
//シミュレーションテーブル用
//$table_name:テーブル名称
//$nendo:年度
//$pattern_id:パターンID
//////////////////////////////////////////////////////////////////////////////////////////////
function selectSimuData($table_name,$nendo,$pattern_id){
  $sql = <<< EOM
  SELECT
   *
  FROM
   {$table_name}
  WHERE
   nendo = '{$nendo}'
   AND pattern_id = '{$pattern_id}';
EOM;

  $result = pg_query($sql);

  return pg_fetch_all($result);
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーション年間休日マスタにデータをコピーする
//シミュレーションテーブル用
//$nendo:年度
//$pattern_id:パターンID
//$setData:コピー元のデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSimuMstNenkanKyujituSimu($nendo,$pattern_id,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     simu_mst_nenkankyujitu
     (
      nendo,
      pattern_id,
      nenkan_kyujitu,
      rodo_jikan_per_day,
      kyukei_jikan_per_day,
      yusen
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($pattern_id)},
      {$scsq($record["nenkan_kyujitu"])},
      {$scsq($record["rodo_jikan_per_day"])},
      {$scsq($record["kyukei_jikan_per_day"])},
      {$scsq($record["yusen"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーション基本給マスタにデータをコピーする
//シミュレーションテーブル用
//$nendo:年度
//$pattern_id:パターンID
//$setData:コピー元のデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSimuMstKihonkyuSimu($nendo,$pattern_id,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     simu_mst_kihonkyu
     (
      nendo,
      pattern_id,
      age,
      kihon_kyu
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($pattern_id)},
      {$scsq($record["age"])},
      {$scsq($record["kihon_kyu"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーション固定基本給マスタにデータをコピーする
//シミュレーションテーブル用
//$nendo:年度
//$pattern_id:パターンID
//$setData:コピー元のデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSimuMstKoteiKihonkyuSimu($nendo,$pattern_id,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     simu_mst_kotei_kihonkyu
     (
      nendo,
      pattern_id,
      kotei_kihon_kyu,
      bridal_kotei_kihon_kyu
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($pattern_id)},
      {$scsq($record["kotei_kihon_kyu"])},
      {$scsq($record["bridal_kotei_kihon_kyu"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーション職責・職責給マスタにデータをコピーする
//シミュレーションテーブル用
//$nendo:年度
//$pattern_id:パターンID
//$setData:コピー元のデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSimuMstShokusekiSimu($nendo,$pattern_id,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     simu_mst_shokuseki
     (
      nendo,
      pattern_id,
      duties_cd,
      duties_name,
      shokusekikyu_jogen,
      shokusekikyu_kagen
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($pattern_id)},
      {$scsq($record["duties_cd"])},
      {$scsq($record["duties_name"])},
      {$scsq($record["shokusekikyu_jogen"])},
      {$scsq($record["shokusekikyu_kagen"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーション手当マスタにデータをコピーする
//シミュレーションテーブル用
//$nendo:年度
//$pattern_id:パターンID
//$setData:コピー元のデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSimuMstTeateSimu($nendo,$pattern_id,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     simu_mst_teate
     (
      nendo,
      pattern_id,
      teate_id,
      teate_name,
      muko_flg
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($pattern_id)},
      {$scsq($record["teate_id"])},
      {$scsq($record["teate_name"])},
      {$scsq($record["muko_flg"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーション手当詳細マスタにデータをコピーする
//シミュレーションテーブル用
//$nendo:年度
//$pattern_id:パターンID
//$setData:コピー元のデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSimuMstTeateShosaiSimu($nendo,$pattern_id,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     simu_mst_teate_shosai
     (
      nendo,
      pattern_id,
      teate_id,
      teate_shosai_id,
      teate_shosai_name,
      sikyu_gaku,
      kijungaku_jogen,
      kijungaku_kagen,
      duties_cd
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($pattern_id)},
      {$scsq($record["teate_id"])},
      {$scsq($record["teate_shosai_id"])},
      {$scsq($record["teate_shosai_name"])},
      {$scsq($record["sikyu_gaku"])},
      {$scsq($record["kijungaku_jogen"])},
      {$scsq($record["kijungaku_kagen"])},
      {$scsq($record["duties_cd"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーション給与にデータをコピーする
//シミュレーションテーブル用
//$nendo:年度
//$pattern_id:パターンID
//$setData:コピー元のデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSimuJugyoinKyuyoTblSimu($nendo,$pattern_id,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     simu_kyuyo_tbl
     (
      nendo,
      pattern_id,
      staff_code,
      duties_cd,
      kimmuchi_cd,
      tansinfunin_flg,
      shaho_fuyo_flg,
      nenkan_kyujitu,
      rodo_jikan_per_day,
      kyukei_jikan_per_day,
      gekkyu_nempo,
      shokyu_gaku,
      shokyu_teisei_gaku,
      shokyu_tangan_riyu,
      shokyu_tangan_gaku,
      zennen_shokuseki_kyu,
      shokuseki_kyu,
      kihon_kyu,
      kotei_kihon_kyu,
      kaikin_teate,
      tosi_teate,
      shorei_teate,
      chosei_teate,
      tenkin_jutaku_teate,
      tansinfunin_teate,
      kazoku_teate,
      korituzangyo_teate,
      korituzangyo_teate_gaitogaku,
      teate_yobi_01,
      teate_yobi_02,
      teate_yobi_03,
      teate_yobi_04,
      teate_yobi_05,
      zennen_nempo_nengaku,
      nempo_nengaku,
      nempo_12_14,
      nempo_uchi_koteizang_dai,
      nempo_uchi_eigyo_teate,
      nempo_shoyo_1,
      nempo_shoyo_2,
      shikyugaku_a,
      sagaku_teate,
      zangyo_tanka,
      tujo_zan_jikan_tuki,
      tujo_zan_teate_tuki,
      tujo_zan_jikan_nenkei,
      tujo_zan_teate_nenkei,
      sinya_zan_jikan_tuki,
      sinya_zan_teate_tuki,
      sinya_zan_jikan_nenkei,
      sinya_zan_teate_nenkei,
      kyujitu_rodo_jikan_tuki,
      kyujitu_rodo_teate_tuki,
      kyujitu_rodo_jikan_nenkei,
      kyujitu_rodo_teate_nenkei,
      kyujitusinya_rodo_jikan_tuki,
      kyujitusinya_rodo_teate_tuki,
      kyujitusinya_rodo_jikan_nenkei,
      kyujitusinya_rodo_teate_nenkei,
      zangyo_teate_gokei,
      kekkin_kojo,
      chikoku_sotai_kojo,
      biko,
      keiri_upd_flg_shokuseki,
      keiri_upd_flg_shokyu_teisei,
      keiri_upd_flg_shokyu_tangan,
      keiri_upd_flg_tsujo_zan,
      keiri_upd_flg_sinya_zan,
      keiri_upd_flg_kyujitu_rodo,
      keiri_upd_flg_kyujitu_sinya,
      kakutei_jotai,
      delete_flg,
      insert_user,
      insert_date,
      update_user,
      update_date
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($pattern_id)},

      {$scsq($record["staff_code"])},
      {$scsq($record["duties_cd"])},
      {$scsq($record["kimmuchi_cd"])},
      {$scsq($record["tansinfunin_flg"])},
      {$scsq($record["shaho_fuyo_flg"])},
      {$scsq($record["nenkan_kyujitu"])},
      {$scsq($record["rodo_jikan_per_day"])},
      {$scsq($record["kyukei_jikan_per_day"])},
      {$scsq($record["gekkyu_nempo"])},
      {$scsq($record["shokyu_gaku"])},
      {$scsq($record["shokyu_teisei_gaku"])},
      {$scsq($record["shokyu_tangan_riyu"])},
      {$scsq($record["shokyu_tangan_gaku"])},
      {$scsq($record["zennen_shokuseki_kyu"])},
      {$scsq($record["shokuseki_kyu"])},
      {$scsq($record["kihon_kyu"])},
      {$scsq($record["kotei_kihon_kyu"])},
      {$scsq($record["kaikin_teate"])},
      {$scsq($record["tosi_teate"])},
      {$scsq($record["shorei_teate"])},
      {$scsq($record["chosei_teate"])},
      {$scsq($record["tenkin_jutaku_teate"])},
      {$scsq($record["tansinfunin_teate"])},
      {$scsq($record["kazoku_teate"])},
      {$scsq($record["korituzangyo_teate"])},
      {$scsq($record["korituzangyo_teate_gaitogaku"])},
      {$scsq($record["teate_yobi_01"])},
      {$scsq($record["teate_yobi_02"])},
      {$scsq($record["teate_yobi_03"])},
      {$scsq($record["teate_yobi_04"])},
      {$scsq($record["teate_yobi_05"])},
      {$scsq($record["zennen_nempo_nengaku"])},
      {$scsq($record["nempo_nengaku"])},
      {$scsq($record["nempo_12_14"])},
      {$scsq($record["nempo_uchi_koteizang_dai"])},
      {$scsq($record["nempo_uchi_eigyo_teate"])},
      {$scsq($record["nempo_shoyo_1"])},
      {$scsq($record["nempo_shoyo_2"])},
      {$scsq($record["shikyugaku_a"])},
      {$scsq($record["sagaku_teate"])},
      {$scsq($record["zangyo_tanka"])},
      {$scsq($record["tujo_zan_jikan_tuki"])},
      {$scsq($record["tujo_zan_teate_tuki"])},
      {$scsq($record["tujo_zan_jikan_nenkei"])},
      {$scsq($record["tujo_zan_teate_nenkei"])},
      {$scsq($record["sinya_zan_jikan_tuki"])},
      {$scsq($record["sinya_zan_teate_tuki"])},
      {$scsq($record["sinya_zan_jikan_nenkei"])},
      {$scsq($record["sinya_zan_teate_nenkei"])},
      {$scsq($record["kyujitu_rodo_jikan_tuki"])},
      {$scsq($record["kyujitu_rodo_teate_tuki"])},
      {$scsq($record["kyujitu_rodo_jikan_nenkei"])},
      {$scsq($record["kyujitu_rodo_teate_nenkei"])},
      {$scsq($record["kyujitusinya_rodo_jikan_tuki"])},
      {$scsq($record["kyujitusinya_rodo_teate_tuki"])},
      {$scsq($record["kyujitusinya_rodo_jikan_nenkei"])},
      {$scsq($record["kyujitusinya_rodo_teate_nenkei"])},
      {$scsq($record["zangyo_teate_gokei"])},
      {$scsq($record["kekkin_kojo"])},
      {$scsq($record["chikoku_sotai_kojo"])},
      {$scsq($record["biko"])},
      {$scsq($record["keiri_upd_flg_shokuseki"])},
      {$scsq($record["keiri_upd_flg_shokyu_teisei"])},
      {$scsq($record["keiri_upd_flg_shokyu_tangan"])},
      {$scsq($record["keiri_upd_flg_tsujo_zan"])},
      {$scsq($record["keiri_upd_flg_sinya_zan"])},
      {$scsq($record["keiri_upd_flg_kyujitu_rodo"])},
      {$scsq($record["keiri_upd_flg_kyujitu_sinya"])},
      {$scsq($record["kakutei_jotai"])},
      {$scsq($record["delete_flg"])},
      {$scsq($record["insert_user"])},
      {$scsq($record["insert_date"])},
      {$scsq($record["update_user"])},
      {$scsq($record["update_date"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーションパターンデータ管理＞＞
//内部ファンクション
//シミュレーション支店別残業時間テーブルにデータをコピーする
//シミュレーションテーブル用
//$nendo:年度
//$pattern_id:パターンID
//$setData:コピー元のデータ
//////////////////////////////////////////////////////////////////////////////////////////////
function setCopyDataSimuSitenZangyoJikanTblSimu($nendo,$pattern_id,$setData){

  $scsq = "setCulumnSingleQuotation";

  foreach ($setData as $record) {
    $sql = <<< EOM
    INSERT INTO
     simu_siten_zangyo_jikan_tbl
     (
      nendo,
      pattern_id,
      busho_cd,
      tujo_zan_jikan,
      sinya_zan_jikan,
      kyujitu_rodo_jikan,
      kyujitusinya_rodo_jikan
       )
    VALUES(
      {$scsq($nendo)},
      {$scsq($pattern_id)},
      {$scsq($record["busho_cd"])},
      {$scsq($record["tujo_zan_jikan"])},
      {$scsq($record["sinya_zan_jikan"])},
      {$scsq($record["kyujitu_rodo_jikan"])},
      {$scsq($record["kyujitusinya_rodo_jikan"])}
    );
EOM;

    $result = pg_query($sql);

    if (!$result) {
      pg_query("ROLLBACK");
    }
  }
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜評価点昇給額マスタ管理用＞＞
//評価点昇給額マスタデータを取得する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getMstHyokatenShokyugaku($nendo){
  if(empty($nendo)){
    return -1;
  }

  $sql = <<< EOM
  SELECT
   nendo
   ,heikin_ten_025
   ,shikyu_gaku
  FROM
   mst_hyoka_shokyugaku
  WHERE
   nendo = '{$nendo}'
  ORDER BY
   heikin_ten_025;
EOM;

  $result = pg_query($sql);

  //返す配列
  $array = array();

  while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
    array_push($array,$rows);
  }

  return $array;
}
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜評価点昇給額マスタ管理用＞＞
//評価点昇給額マスタを更新する。
//$hyoka_shokyugaku_records:更新する評価点昇給額マスタデータ配列
//////////////////////////////////////////////////////////////////////////////////////////////
function updateMstHyokaShokyugaku($hyoka_shokyugaku_records){

  pg_query("BEGIN");

  function updSql($record){
    $sql = <<< EOM
      UPDATE
      mst_hyoka_shokyugaku
      SET
      shikyu_gaku  = '{$record["shikyu_gaku"]}'
      WHERE
       nendo = '{$record["nendo"]}'
       AND heikin_ten_025 = '{$record["heikin_ten_025"]}';
EOM;

    return $sql;
  }

  foreach ($hyoka_shokyugaku_records as $record) {

    $result = pg_query(updSql($record));

    if (!$result) {
      pg_query("ROLLBACK");
      return 'クエリーが失敗しました。(評価点昇給額マスタを更新する)'.pg_last_error();
    }
  }

  pg_query("COMMIT");
  return "1";
}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーション用手当マスタメンテ画面用＞＞
//指定の年度のシミュレーション用手当マスタ情報を取得する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getSimuMstTeateForSimuMstMainte($nendo, $pattern_id){
	if(empty($nendo)){
	  return -1;
	}
  
	$sql  = "SELECT";
	$sql .= " pattern_id";
	$sql .= ", teate_id";
	$sql .= ", teate_name";
	$sql .= ", yobi_flg";
	$sql .= ", muko_flg";
	$sql .= ", (CASE WHEN yobi_flg = 0 THEN '' ELSE '予備' END) yobi_text";
	$sql .= ", (CASE WHEN muko_flg = 0 THEN '' ELSE '無効' END) muko_text";
	$sql .= ", (CASE WHEN yobi_flg = 0 THEN false ELSE true END) yobi_data";
	$sql .= ", (CASE WHEN muko_flg = 0 THEN false ELSE true END) muko_data";
	$sql .= ", 'false' as focus_flg";
	$sql .= " FROM simu_mst_teate";
	$sql .= " WHERE";
	$sql .= " nendo = '$nendo'";
	$sql .= " AND pattern_id = $pattern_id";
	$sql .= " ORDER BY";
	$sql .= " muko_flg";
	$sql .= ", yobi_flg";
	$sql .= ", teate_id";
	$sql .= ";";

	$result = pg_query($sql);
  

	// if(pg_num_rows($result) == 0){
	// 	return array();
	// } else {
	// 	return pg_fetch_all($result);
	// }
	
	//返却する配列
	$array = array();
  
	while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
	  array_push($array,$rows);
	}
  
	return $array;
  }

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーション用手当マスタメンテ画面用＞＞
//＜＜申請用手当マスタメンテ画面用＞＞
//職責マスタ情報を取得する。
//////////////////////////////////////////////////////////////////////////////////////////////
function getDutiesMstForSimuMstMainte($nendo){

	if(empty($nendo)){
		return -1;
	}
  
	$sql  = "SELECT *";
	$sql .= " FROM mst_shokuseki";
	$sql .= " WHERE";
	$sql .= " nendo = '$nendo'";
	$sql .= " ORDER BY";
	$sql .= " duties_cd";
	$sql .= ";";
 
	$result = pg_query($sql);

	// if(pg_num_rows($result) == 0){
	// 	return array();
	// } else {
	// 	return pg_fetch_all($result);
	// }
	
	//返却する配列
	$array = array();
  
	while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
	  array_push($array,$rows);
	}
  
	return $array;
  }
//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーション用手当マスタメンテ画面用＞＞
//シミュレーション手当詳細マスタからデータを１つ取得する。
//$nendo:年度
//$pattern:パターン
//$teate_id:手当ＩＤ
//////////////////////////////////////////////////////////////////////////////////////////////
function getTeateShosaiForSimuMstMainte($nendo, $pattern, $teate_id){
	if(empty($nendo) || empty($pattern) ||  empty($teate_id)){
	  return -1;
	}

$sql = <<< EOM
SELECT
 smts.nendo,
 smts.pattern_id,
 smts.teate_id,
 smts.teate_shosai_id,
 smts.teate_shosai_name,
 smts.sikyu_gaku,
 smts.kijungaku_jogen,
 smts.kijungaku_kagen,
 smts.duties_cd,
 ms.duties_name,
 'false' as focus_flg
 FROM
 simu_mst_teate_shosai as smts
LEFT JOIN mst_shokuseki as ms
ON smts.duties_cd = ms.duties_cd
AND smts.nendo = ms.nendo
WHERE
 smts.nendo = '{$nendo}'
 AND smts.pattern_id = {$pattern}
 AND smts.teate_id = {$teate_id}
ORDER BY smts.teate_shosai_id;
EOM;
  
	$result = pg_query($sql);

	// return pg_fetch_array($result);

	//返す配列
	$array = array();
  
	while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
	  array_push($array,$rows);
	}
  
	return $array;

}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜シミュレーション用手当マスタメンテ画面用＞＞
//シミュレーション用手当マスタを更新、手当詳細マスタデータを新規登録、または更新する。
//$nendo:年度
//$pattern:パターン
//$teate_id:手当ID
//$teate_name:手当名称
//$muko_flg:無効フラグ
//$shosai_records:手当詳細データ
//////////////////////////////////////////////////////////////////////////////////////////////
function updateTeateDataSimu($nendo, $pattern_id, $teate_id, $teate_name, $muko_flg, $shosai_records){
	if(empty($nendo) || empty($pattern_id) || empty($teate_id)){
	  return -1;
	}

	//トランザクション開始
	pg_query("BEGIN");

	//手当マスタの該当データを更新
$sql = <<< EOM
UPDATE
 simu_mst_teate
 SET
 teate_name = '{$teate_name}',
 muko_flg = '{$muko_flg}'
 WHERE
 nendo = '{$nendo}'
 AND pattern_id = '{$pattern_id}'
 AND teate_id = '{$teate_id}';
EOM;
	  
	$result = pg_query($sql);
	  
	if (!$result) {
		return 'クエリーが失敗しました。'.pg_last_error();
	}
	
	//手当詳細マスタの該当データを削除
$sql = <<< EOM
DELETE FROM
 simu_mst_teate_shosai
 WHERE
 nendo = '{$nendo}'
 AND pattern_id = {$pattern_id}
 AND teate_id = '{$teate_id}';
EOM;

	$result = pg_query($sql);

	if (!$result) {
		pg_query("ROLLBACK");
		return 'クエリーが失敗しました。(削除処理失敗)'.pg_last_error();
	}
	
	//手当詳細マスタへinputする関数
	function updSql($record){
		$sql  = "INSERT INTO";
		$sql .= " simu_mst_teate_shosai(";
		$sql .= " nendo";
		$sql .= ", pattern_id";
		$sql .= ", teate_id";
		$sql .= ", teate_shosai_id";
		$sql .= ", teate_shosai_name";
		$sql .= ", sikyu_gaku";
		$sql .= ", kijungaku_jogen";
		$sql .= ", kijungaku_kagen";
		if($record['duties_cd'] != ""){
			$sql .= ", duties_cd";
		}
		$sql .= " )VALUES(";
		$sql .= " '" . $record['nendo'] . "'";
		$sql .= ", " . $record['pattern_id'];
		$sql .= ", " . $record['teate_id'];
		$sql .= ", " . $record['teate_shosai_id'];
		$sql .= ", '" . $record['teate_shosai_name'] . "'";
		$sql .= ", " . $record['sikyu_gaku'];
		$sql .= ", " . $record['kijungaku_jogen'];
		$sql .= ", " . $record['kijungaku_kagen'];
		if($record['duties_cd'] != ""){
		// if(0 <= $record['duties_cd']){
			$sql .= ", " . $record['duties_cd'];
		}
		$sql .= ");";
		
		// $sql = <<< EOM
// INSERT INTO
//  simu_mst_teate_shosai
//  (
//  nendo,
//  pattern_id,
//  teate_id,
//  teate_shosai_id,
//  teate_shosai_name,
//  sikyu_gaku,
//  kijungaku_jogen,
//  kijungaku_kagen,
//  duties_cd
//  )VALUES(
//  '{$record["nendo"]}',
//  {$record["pattern_id"]},
//  {$record["teate_id"]},
//  {$record["teate_shosai_id"]},
//  '{$record["teate_shosai_name"]}',
//  {$record["sikyu_gaku"]},
//  {$record["kijungaku_jogen"]},
//  {$record["kijungaku_kagen"]},
//  {$record["duties_cd"]}
// );
// EOM;

		return $sql;
	}

	//手当詳細マスタに該当データを追加する処理
	foreach ($shosai_records as $record) {
		$result = pg_query(updSql($record));
		if (!$result) {
			pg_query("ROLLBACK");
			return "クエリーが失敗しました。(手当詳細マスタを更新する)".pg_last_error();
		}
	}

	pg_query("COMMIT");
	return "1";

}

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜申請用手当マスタメンテ画面用＞＞
//指定の年度の申請用手当マスタ情報を取得する。
//$nendo:年度
//////////////////////////////////////////////////////////////////////////////////////////////
function getSinseiMstTeateForSinseiMstMainte($nendo){
	if(empty($nendo)){
	  return -1;
	}
  
	$sql  = "SELECT";
	$sql .= " teate_id";
	$sql .= ", teate_name";
	$sql .= ", yobi_flg";
	$sql .= ", muko_flg";
	$sql .= ", (CASE WHEN yobi_flg = 0 THEN '' ELSE '予備' END) yobi_text";
	$sql .= ", (CASE WHEN muko_flg = 0 THEN '' ELSE '無効' END) muko_text";
	$sql .= ", (CASE WHEN yobi_flg = 0 THEN false ELSE true END) yobi_data";
	$sql .= ", (CASE WHEN muko_flg = 0 THEN false ELSE true END) muko_data";
	$sql .= ", 'false' as focus_flg";
	$sql .= " FROM sinsei_mst_teate";
	$sql .= " WHERE";
	$sql .= " nendo = '$nendo'";
	$sql .= " ORDER BY";
	$sql .= " muko_flg";
	$sql .= ", yobi_flg";
	$sql .= ", teate_id";
	$sql .= ";";

	$result = pg_query($sql);
  

	// if(pg_num_rows($result) == 0){
	// 	return array();
	// } else {
	// 	return pg_fetch_all($result);
	// }
	
	//返却する配列
	$array = array();
  
	while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
	  array_push($array,$rows);
	}
  
	return $array;
  }

//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜申請用手当マスタメンテ画面用＞＞
//申請手当詳細マスタからデータを１つ取得する。
//$nendo:年度
//$teate_id:手当ＩＤ
//////////////////////////////////////////////////////////////////////////////////////////////
function getTeateShosaiForSinseiMstMainte($nendo, $teate_id){
	if(empty($nendo) || empty($teate_id)){
	  return -1;
	}

$sql = <<< EOM
SELECT
 smts.nendo,
 smts.teate_id,
 smts.teate_shosai_id,
 smts.teate_shosai_name,
 smts.sikyu_gaku,
 smts.kijungaku_jogen,
 smts.kijungaku_kagen,
 smts.duties_cd,
 ms.duties_name,
 'false' as focus_flg
 FROM
 sinsei_mst_teate_shosai as smts
 LEFT JOIN mst_shokuseki as ms
 ON smts.duties_cd = ms.duties_cd
 AND smts.nendo = ms.nendo
 WHERE
 smts.nendo = '{$nendo}'
 AND smts.teate_id = {$teate_id}
 ORDER BY smts.teate_shosai_id;
EOM;
  
	$result = pg_query($sql);

	// return pg_fetch_array($result);

	//返す配列
	$array = array();
  
	while($rows = pg_fetch_array($result, NULL, PGSQL_ASSOC)){
	  array_push($array,$rows);
	}
  
	return $array;

}


//////////////////////////////////////////////////////////////////////////////////////////////
//＜＜申請用手当マスタメンテ画面用＞＞
//申請用手当マスタを更新、手当詳細マスタデータを新規登録、または更新する。
//$nendo:年度
//$teate_id:手当ID
//$teate_name:手当名称
//$muko_flg:無効フラグ
//$shosai_records:手当詳細データ
//////////////////////////////////////////////////////////////////////////////////////////////
function updateTeateDataSinsei($nendo, $teate_id, $teate_name, $muko_flg, $shosai_records){
	if(empty($nendo) || empty($teate_id)){
	  return -1;
	}

	//トランザクション開始
	pg_query("BEGIN");

	//手当マスタの該当データを更新
$sql = <<< EOM
UPDATE
 sinsei_mst_teate
 SET
 teate_name = '{$teate_name}',
 muko_flg = '{$muko_flg}'
 WHERE
 nendo = '{$nendo}'
 AND teate_id = '{$teate_id}';
EOM;
	  
	$result = pg_query($sql);
	  
	if (!$result) {
		return 'クエリーが失敗しました。'.pg_last_error();
	}
	
	//手当詳細マスタの該当データを削除
$sql = <<< EOM
DELETE FROM
 sinsei_mst_teate_shosai
 WHERE
 nendo = '{$nendo}'
 AND teate_id = '{$teate_id}';
EOM;

	$result = pg_query($sql);

	if (!$result) {
		pg_query("ROLLBACK");
		return 'クエリーが失敗しました。(削除処理失敗)'.pg_last_error();
	}
	
	//手当詳細マスタへinputする関数
	function updSql($record){
		$sql  = "INSERT INTO";
		$sql .= " sinsei_mst_teate_shosai(";
		$sql .= " nendo";
		$sql .= ", teate_id";
		$sql .= ", teate_shosai_id";
		$sql .= ", teate_shosai_name";
		$sql .= ", sikyu_gaku";
		$sql .= ", kijungaku_jogen";
		$sql .= ", kijungaku_kagen";
		if($record['duties_cd'] != ""){
			$sql .= ", duties_cd";
		}
		$sql .= " )VALUES(";
		$sql .= " '" . $record['nendo'] . "'";
		$sql .= ", " . $record['teate_id'];
		$sql .= ", " . $record['teate_shosai_id'];
		$sql .= ", '" . $record['teate_shosai_name'] . "'";
		$sql .= ", " . $record['sikyu_gaku'];
		$sql .= ", " . $record['kijungaku_jogen'];
		$sql .= ", " . $record['kijungaku_kagen'];
		if($record['duties_cd'] != ""){
		// if(0 <= $record['duties_cd']){
			$sql .= ", " . $record['duties_cd'];
		}
		$sql .= ");";
		
		return $sql;
	}

	//手当詳細マスタに該当データを追加する処理
	foreach ($shosai_records as $record) {
		$result = pg_query(updSql($record));
		if (!$result) {
			pg_query("ROLLBACK");
			return "クエリーが失敗しました。(手当詳細マスタを更新する)".pg_last_error();
		}
	}

	pg_query("COMMIT");
	return "1";

}

?>