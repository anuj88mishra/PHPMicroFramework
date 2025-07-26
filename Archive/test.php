<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.1/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.0.7/js/all.js"></script>
  </head>
  <body>
<?php
include_once("dbconn.php");
// ini_set('display_errors', '1');
// print_r(SQLFetchRow("SELECT * FROM adm.user_1 WHERE usr_cd = ?","SREXEIT1"));
$qry = <<<EOD
SELECT usr_cd, usr_name , ROW_NUMBER() OVER (ORDER BY usr_cd) as c_sno, '<a href="./testView.php?code=' || usr_cd || '">View</a>' as c_vw FROM adm.user_1 WHERE usr_cd ilike 'SR%' 
EOD;
// print_r($qry);
echo genTable(trim($qry), '', ["c_sno"=>"S.No.","usr_cd"=>"User ID", "usr_name"=>"Name","c_vw"=>"View"], 1, '', '', 5);
?>
</body>
</html>