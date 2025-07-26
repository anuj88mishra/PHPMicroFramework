<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: -1");
header("X-XSS-Protection: 1; mode=block");
header('Access-Control-Allow-Headers: *');
header('X-Content-Type-Options: nosniff');
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allowed headers

header('Access-Control-Allow-Headers: Content-Type');

header("Access-Control-Expose-Headers: Content-Disposition");

header_remove("X-Powered-By");
date_default_timezone_set("Asia/Kolkata");
// Initialize variables
//require('fpdf.php'); // Include the FPDF class
//require('libs/fpdf.php');

$dbconn = FALSE;
$ERRMSG = '';
// Connect to MySQL database using mysqli
// Database connection
try {
    $dbh = new PDO("pgsql:host=localhost;dbname=admdb", "postgres", "pgsql");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    return 0;
}

/* -------------------------------------------- */
// USE QUESTION MARK ? AS PARAM PLACEHOLDER IN QUERIES FOR MySQL and pgSQL both
function SQLFetch($qry, $param=NULL) {
    global $dbh;
    if (!$dbh) return 0;
    if (!C_CLEAN_SELECT($qry)) { return 0; }
    $result = null;
    if(!empty($param)) {
        if(!is_array($param)) {
            if(strpos($param, '~') >= 0) $param = explode('~', $param);
        }
        if(!is_array($param)) return 0;
        try {
            $stmt = $dbh->prepare($qry);
            $stmt->execute($param);
            // print_r($param); print_r($qry);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    } else {
        try {
            $stmt = $dbh->prepare($qry);
            $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    }
    $row = $stmt->fetch(PDO::FETCH_NUM);
    $stmt->closeCursor();
    if (!$row || !isset($row[0])) return "";
    return $row[0];
}
/* -------------------------------------------- */
function SQLFetchRow($qry, $param=NULL) {
    global $dbh;
    if (!$dbh) return 0;
    if (!C_CLEAN_SELECT($qry)) { return 0; }
    $result = null;
    if(!empty($param)) {
        if(!is_array($param)) {
            if(strpos($param, '~') >= 0) $param = explode('~', $param);
        }
        if(!is_array($param)) return 0;
        try {
            $stmt = $dbh->prepare($qry);
            $stmt->execute($param);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    } else {
        try {
            $stmt = $dbh->prepare($qry);
            $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $row;
}
/* -------------------------------------------- */
function SQLCursor($qry, $param=NULL) {
    global $dbh;
    if (!$dbh) return 0;
    if (!C_CLEAN_SELECT($qry)) { return 0; }
    $result = null;
    if(!empty($param)) {
        if(!is_array($param)) {
            if(strpos($param, '~') >= 0) $param = explode('~', $param);
        }
        if(!is_array($param)) return 1;
        try {
            $stmt = $dbh->prepare($qry);
            $stmt->execute($param);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 2;
        }
    } else {
        try {
            $stmt = $dbh->prepare($qry);
            $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 3;
        }
    }
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $row;
}
/* -------------------------------------------- */
function ExecSQL($qry, $param=NULL) {
    global $dbh;
    if (!$dbh) return 0;
    if (!C_CLEAN_UPDATE($qry)) { return 0; }
    $result = null;
    if(!empty($param)) {
        if(!is_array($param)) {
            if(strpos($param, '~') >= 0) $param = explode('~', $param);
        }
        if(!is_array($param)) return 0;
        try {
            $stmt = $dbh->prepare($qry);
            $stmt->execute($param);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    } else {
        try {
            $stmt = $dbh->prepare($qry);
            $stmt->execute();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return 0;
        }
    }
    $stmt->closeCursor();
    return 1;
}
// End Database Connection and helper functions

// Table Generation Function
function genTable($sql, $param, $fields, $withSrch, $tblId, $tblClass, $limit = '20', $offset = '0', $srchTerm = '') {
    $srch = ""; $srch2 = ""; $pg = 1; $html = ""; $sqlWhere = ""; $i = 0;
    if (isset($_GET['pg'])) {
        $pg = $_GET['pg'];
        if ((int)$pg == 1) {
            $offset = 0;
        } else {
            $offset = ((int)$pg - 1) * (int)$limit;
        }
    }
    if (empty($sql) || empty($fields)) { return "Mandatory parameters missing!"; }
    if (!is_numeric($limit) || !is_numeric($offset)) { return "Invalid limit or offset provided"; }
    // Prepare search strings
    if (isset($_GET['srch'])) {
        $srch = $_GET['srch'];
    }
    if ($withSrch) {
        // Make Search Box
        $html .= '<form action="" method="get">';
        $html .= "<div class='field has-addons'>";
        $html .= '<div class="control is-expanded"><input name="srch" id="srch" class="search input is-primary" placeholder="Search" value="'.$srch.'"/></div>';
        $html .= '<div class="control"><button type="submit" id="searchButton" aria-label="Search Button" class="button is-info">Search</button></div>';
        $html .= "</div>";
        $html .= '</form>';
    }
    if (!empty($_GET['srch'])) { 
        foreach ($fields as $col => $caption) {
            if (preg_match("/c_/", strtolower($col))) continue;
            $srch2 .= "~".$srch;
            if ($i++ == 0 )
            $sqlWhere .= " AND ( $col::varchar ILIKE '%' || ? || '%'";
            else
            $sqlWhere .= " OR $col::varchar ILIKE '%' || ? || '%'";
        }
        $sqlWhere .= ")";
        if (empty($param)) $srch2 = ltrim($srch2, '~');
    }
    //Get array from SQL
    // echo $sql.$sqlWhere;
    // echo "<br />".$param.$srch2;
    $tblData = SQLCursor($sql.$sqlWhere." LIMIT $limit OFFSET $offset", $param.$srch2);
    if($tblData === 0) { return $html."<br />Problem while fetching data! Please inform IT Deptt."; }
    // Get total Count without limit & offset by removing columns in select with count(1)
    // print_r(preg_replace('/SELECT [\w, ()"\'<>=.\/?]* FROM /', 'SELECT count(1) FROM ', $sql.$sqlWhere));
    $fullCnt = SQLFetch(preg_replace('/select [\w, ()"\'<>=.\/?|]* from /', 'SELECT count(1) FROM ', strtolower($sql.$sqlWhere)), $param.$srch2);
    if (empty($tblData)) { return $html."<br />No data to display!"; }
    $html .= "Total records: $fullCnt<br />";
    $html .= "<div class='tableDiv'>";
    $html .= "<table class='table is-fullwidth $tblClass' id='$tblId'><thead><tr>";
    foreach ($fields as $col => $caption) {
        $html .= "<th>$caption</th>";
    }
    $html .= "</tr></thead>";
    $html .= "<tbody>";
    foreach ($tblData as $oKey => $row) {
        $html .= "<tr>";
        foreach ($fields as $col=>$caption) {
            $html .= "<td>$row[$col]</td>";
        }
        $html .= "</tr>";
    }
    $html .= "</tbody></table>";
    $html .= "</div>"; //End tableDiv
    // Pagination
    // echo "limit=$limit, offset=$offset";
    $base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
    $url = $base_url . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $max_page = C_CEIL((int)$fullCnt,$limit)/(int)$limit;
    if ($fullCnt > $limit) {
        $html .= '<nav class="pagination" role="navigation" aria-label="pagination"><ul class="pagination-list">';
        // First Page
        if ((int)$offset > 0) {
            $html .= '<li><a class="pagination-link" href="'.$url."?pg=1&srch=$srch".'">First</a></li>';
        }
        // Prev Pages
        $html .= (int)$pg-3>0?"<li><a class='pagination-ellipsis' href='#'>&hellip;</a></li>":"";
        $html .= (int)$pg-2>0?"<li><a class='pagination-link' href='".$url."?pg=".($pg-2)."&srch=$srch'>".($pg-2)."</a></li>":"";
        $html .= (int)$pg-1>0?"<li><a class='pagination-link' href='".$url."?pg=".($pg-1)."&srch=$srch'>".($pg-1)."</a></li>":"";
        // Current Page
        $html .= "<li><a class='pagination-link is-current' href='#'>$pg</a></li>";
        // Next Pages
        $html .= (int)$pg+1<=$max_page?"<li><a class='pagination-link' href='".$url."?pg=".($pg+1)."&srch=$srch'>".($pg+1)."</a></li>":"";
        $html .= (int)$pg+2<=$max_page?"<li><a class='pagination-link' href='".$url."?pg=".($pg+2)."&srch=$srch'>".($pg+2)."</a></li>":"";
        $html .= (int)$pg+3<=$max_page?"<li><a class='pagination-ellipsis' href='#'>&hellip;</a></li>":"";
        // Last Page
        if ((int)$offset <= ((int)$fullCnt - (int)$limit)) {
            $html .= "<li><a class='pagination-link' href='".$url.'?pg='.$max_page."&srch=$srch"."'>Last</a></li>";
        }
        $html .= '</ul></nav>';
    }
    return $html;
    // print_r($tblData);
}
// View record function
function genView($sql, $param, $fields, $tblId, $tblClass) {
    $html = "";
    if (empty($sql) || empty($param) || empty($fields)) { return "Mandatory parameters missing!"; }
    $tblData = SQLFetchRow($sql, $param);
    if($tblData === 0) { return $html."<br />Problem while fetching data! Please inform IT Deptt."; }
    if (empty($tblData)) { return $html."<br />No data to display!"; }
    $html .= "<div class='viewDiv'>";
    $html .= "<table class='$tblClass' id='$tblId' border='1' cellspacing='0'><tbody>";
    foreach ($fields as $col => $caption) {
        $html .= "<tr><th>$caption</th><td>$tblData[$col]</td></tr>";
    }
    $html .= "</tbody></table>";
    $html .= "</div>";
    return $html;
}

// Utility Functions
function C_CEIL($number, $significance = 1){
    return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
}
function C_FLOOR($number, $significance = 1){
    return ( is_numeric($number) && is_numeric($significance) ) ? (floor($number/$significance)*$significance) : false;
}
function C_CLEAN_SELECT($sql) {
    if (preg_match("/;|update|insert|drop|delete|truncate/", strtolower($sql))) { return false; }
    return true;
}
function C_CLEAN_UPDATE($sql) {
    if (preg_match("/;|drop|delete|truncate/", strtolower($sql))) { return false; }
    return true;
}
