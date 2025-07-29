<?php
include BASE_DIR."class/Util.php";
final class Conn {
    private $dbh;
    public function __construct() {
        try {
            $this->dbh = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);;
        } catch(PDOException $e) {
            $_SESSION['FERROR'] = "Connection failed: " . (DEBUG?$e->getMessage():"");
            die();
        }
    }
    public function __destruct() {
        $this->dbh = null;
    }
    // USE QUESTION MARK ? AS PARAM PLACEHOLDER IN QUERIES FOR MySQL and pgSQL both
    public function SQLFetch($qry, $param=NULL) {
        if (!$this->dbh) { $_SESSION['FERROR'] = ERR['BADCON']; return 0; }
        if (!Util::C_CLEAN_SELECT($qry)) { $_SESSION['FERROR'] = ERR['UNCLEANSQL']; return 0; }
        if(!empty($param)) {
            if(!is_array($param)) {
                if(strpos($param, '~') >= 0) $param = explode('~', $param);
            }
            if(!is_array($param)) { $_SESSION['FERROR'] = ERR['PARAMERR']; return 0; }
            try {
                $stmt = $this->dbh->prepare($qry);
                $stmt->execute($param);
                // print_r($param); print_r($qry);
            } catch(PDOException $e) {
                $_SESSION['FERROR'] = ERR['SQLERR'] . (DEBUG?$e->getMessage():"");
                return 0;
            }
        } else {
            try {
                $stmt = $this->dbh->prepare($qry);
                $stmt->execute();
            } catch(PDOException $e) {
                $_SESSION['FERROR'] = ERR['SQLERR'] . (DEBUG?$e->getMessage():"");
                return 0;
            }
        }
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row || !isset($row[0])) return "";
        return $row[0];
    }
    public function SQLFetchRow($qry, $param=NULL) {
        if (!$this->dbh) { $_SESSION['FERROR'] = ERR['BADCON']; return 0; }
        if (!Util::C_CLEAN_SELECT($qry)) { $_SESSION['FERROR'] = ERR['UNCLEANSQL']; return 0; }
        if(!empty($param)) {
            if(!is_array($param)) {
                if(strpos($param, '~') >= 0) $param = explode('~', $param);
            }
            if(!is_array($param)) { $_SESSION['FERROR'] = ERR['PARAMERR']; return 0; }
            try {
                $stmt = $this->dbh->prepare($qry);
                $stmt->execute($param);
            } catch(PDOException $e) {
                $_SESSION['FERROR'] = ERR['SQLERR'] . (DEBUG?$e->getMessage():"");
                return 0;
            }
        } else {
            try {
                $stmt = $this->dbh->prepare($qry);
                $stmt->execute();
            } catch(PDOException $e) {
                $_SESSION['FERROR'] = ERR['SQLERR'] . (DEBUG?$e->getMessage():"");
                return 0;
            }
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $row;
    }
    public function SQLCursor($qry, $param=NULL) {
        if (!$this->dbh) { $_SESSION['FERROR'] = ERR['BADCON']; return 0; }
        if (!Util::C_CLEAN_SELECT($qry)) { $_SESSION['FERROR'] = ERR['UNCLEANSQL']; return 0; }
        if(!empty($param)) {
            if(!is_array($param)) {
                if(strpos($param, '~') >= 0) $param = explode('~', $param);
            }
            if(!is_array($param)) { $_SESSION['FERROR'] = ERR['PARAMERR']; return 0; }
            try {
                $stmt = $this->dbh->prepare($qry);
                $stmt->execute($param);
            } catch(PDOException $e) {
                $_SESSION['FERROR'] = ERR['SQLERR'] . (DEBUG?$e->getMessage():"");
                return 0;
            }
        } else {
            try {
                $stmt = $this->dbh->prepare($qry);
                $stmt->execute();
            } catch(PDOException $e) {
                $_SESSION['FERROR'] = ERR['SQLERR'] . (DEBUG?$e->getMessage():"");
                return 0;
            }
        }
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $row;
    }
    public function ExecSQL($qry, $param=NULL) {
        if (!$this->dbh) { $_SESSION['FERROR'] = ERR['BADCON']; return 0; }
        if (!Util::C_CLEAN_UPDATE($qry)) { $_SESSION['FERROR'] = ERR['UNCLEANSQL']; return 0; }
        if(!empty($param)) {
            if(!is_array($param)) {
                if(strpos($param, '~') >= 0) $param = explode('~', $param);
            }
            if(!is_array($param)) { $_SESSION['FERROR'] = ERR['PARAMERR']; return 0; }
            try {
                $stmt = $this->dbh->prepare($qry);
                $stmt->execute($param);
            } catch(PDOException $e) {
                $_SESSION['FERROR'] = ERR['SQLERR'] . (DEBUG?$e->getMessage():"");
                return 0;
            }
        } else {
            try {
                $stmt = $this->dbh->prepare($qry);
                $stmt->execute();
            } catch(PDOException $e) {
                $_SESSION['FERROR'] = ERR['SQLERR'] . (DEBUG?$e->getMessage():"");
                return 0;
            }
        }
        $stmt->closeCursor();
        return 1;
    }
}
