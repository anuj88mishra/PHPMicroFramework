<?php
include BASE_DIR."class/Conn.php";
include BASE_DIR."class/HtmlClass.php";
class Gen extends HtmlClass{
    protected $conn;
    public $sql;
    public $param;
    public $fields;
    public $tblId;
    public $html;
    // List specific attributes
    public $limit;
    public $offset;
    public $withSrch;
    
    public function __construct() {
        parent::__construct();
        $this->conn = new Conn();
        $this->sql = "";
        $this->param = "";
        $this->fields = [];
        $this->tblId = "";
        $this->html = "";
        
        $this->limit = 20;
        $this->offset = 0;
        $this->withSrch = 1;
    }
    public function __destruct() {
        parent::__destruct();
        $this->conn = null;
        $this->sql = null;
        $this->param = null;
        $this->fields = null;
        $this->tblId = null;
        $this->html = null;
        
        $this->limit = null;
        $this->offset = null;
        $this->withSrch = null;
    }
    public function preTran() { 
        /* provision for overriding */ 
    }
    public function postTran() { 
        /* provision for overriding */ 
    }
    public function preNotify() { 
        /* provision for overriding */ 
    }
    public function view() {
        if (empty($this->sql) || empty($this->param) || empty($this->fields)) {
            return $this->html."Mandatory parameters missing!"; 
        }
        $tblData = $this->conn->SQLFetchRow($this->sql, $this->param);
        if($tblData === 0) { 
            return $this->html."<br />Problem while fetching data! Please inform IT Deptt.";
        }
        if (empty($tblData)) {
            return $this->html."<br />No data to display!";
        }
        $this->html .= "<div class='".$this->divClass."'>";
        $this->html .= "<table class='$this->tblClass' id='$this->tblId'><tbody>";
        foreach ($this->fields as $col => $caption) {
            $this->html .= "<tr><th>$caption</th><td>$tblData[$col]</td></tr>";
        }
        $this->html .= "</tbody></table>";
        $this->html .= "</div>";
        return $this->html;
    }
    public function table() {
        if (empty($this->sql) || empty($this->fields)) { return $this->html."Mandatory parameters missing!"; }
        $srch = ""; $srch2 = ""; $pg = 1; $sqlWhere = ""; $i = 0;
        if (isset($_GET['pg'])) {
            $pg = $_GET['pg'];
            if ((int)$pg == 1) {
                $this->offset = 0;
            } else {
                $this->offset = ((int)$pg - 1) * (int)$this->limit;
            }
        }
        if (!is_numeric($this->limit) || !is_numeric($this->offset)) { return $this->html."Invalid limit or offset provided"; }
        // Prepare search strings
        if (isset($_GET['srch'])) {
            $srch = $_GET['srch'];
        }
        if ($this->withSrch) {
            // Make Search Box
            $this->html .= "<form class='$this->searchFormClass' action='' method='get'>";
            $this->html .= "<div class='$this->searchControlClass'><input name='srch' id='srch' class='$this->searchInputClass' placeholder='Search' value='$srch'/></div>";
            $this->html .= "<div class='$this->searchButtonControlClass'><button type='submit' id='searchButton' aria-label='Search Button' class='$this->searchButtonClass'>Search</button></div>";
            $this->html .= '</form>';
        }
        if (!empty($_GET['srch'])) { 
            foreach ($this->fields as $col => $caption) {
                if (preg_match("/c_/", strtolower($col))) continue;
                $srch2 .= "~".$srch;
                if ($i++ == 0 )
                $sqlWhere .= " AND ( $col::varchar ILIKE '%' || ? || '%'";
                else
                $sqlWhere .= " OR $col::varchar ILIKE '%' || ? || '%'";
            }
            $sqlWhere .= ")";
            if (empty($this->param)) $srch2 = ltrim($srch2, '~');
        }
        //Get array from SQL
        // echo $sql.$sqlWhere;
        // echo "<br />".$this->param.$srch2;
        $tblData = $this->conn->SQLCursor($this->sql.$sqlWhere." LIMIT $this->limit OFFSET $this->offset", $this->param.$srch2);
        if($tblData === 0) { return $this->html."<br />Problem while fetching data! Please inform IT Deptt."; }
        // Get total Count without limit & offset by removing columns in select with count(1)
        // print_r(preg_replace('/SELECT [\w, ()"\'<>=.\/?*|]* FROM /', 'SELECT count(1) FROM ', $this->sql.$sqlWhere));
        $fullCnt = $this->conn->SQLFetch(preg_replace('/select [\w, ()"\'<>=.\/?*|]* from /', 'SELECT count(1) FROM ', strtolower($this->sql.$sqlWhere)), $this->param.$srch2);
        if (empty($tblData)) { return $this->html."<br />No data to display!"; }
        $this->html .= "Total records: $fullCnt<br />";
        $this->html .= "<div class='$this->divClass'>";
        $this->html .= "<table class='$this->tblClass' id='$this->tblId'><thead><tr>";
        foreach ($this->fields as $col => $caption) {
            $this->html .= "<th>$caption</th>";
        }
        $this->html .= "</tr></thead>";
        $this->html .= "<tbody>";
        foreach ($tblData as $oKey => $row) {
            $this->html .= "<tr>";
            foreach ($this->fields as $col=>$caption) {
                $this->html .= "<td>$row[$col]</td>";
            }
            $this->html .= "</tr>";
        }
        $this->html .= "</tbody></table>";
        $this->html .= "</div>"; //End tableDiv
        // Pagination
        // echo "limit=$this->limit, offset=$this->offset";
        $BASE_DIR = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];
        $url = $BASE_DIR . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $max_page = Util::C_CEIL((int)$fullCnt,$this->limit)/(int)$this->limit;
        
        if ($fullCnt > $this->limit) {
            $this->html .= "<nav class='$this->pageControlClass' role='navigation' aria-label='pagination'><ul class='$this->pageListClass'>";
            // First Page
            if ((int)$this->offset > 0) {
                $this->html .= "<li><a class='$this->pageLinkClass' href='".$url."?pg=1&srch=$srch"."'>First</a></li>";
            }
            // Prev Pages
            $this->html .= (int)$pg-3>0?"<li><a class='$this->pageEllipsesClass' href='#'>&hellip;</a></li>":"";
            $this->html .= (int)$pg-2>0?"<li><a class='$this->pageLinkClass' href='".$url."?pg=".($pg-2)."&srch=$srch'>".($pg-2)."</a></li>":"";
            $this->html .= (int)$pg-1>0?"<li><a class='$this->pageLinkClass' href='".$url."?pg=".($pg-1)."&srch=$srch'>".($pg-1)."</a></li>":"";
            // Current Page
            $this->html .= "<li><a class='$this->pageCurrentClass' href='#'>$pg</a></li>";
            // Next Pages
            $this->html .= (int)$pg+1<=$max_page?"<li><a class='$this->pageLinkClass' href='".$url."?pg=".($pg+1)."&srch=$srch'>".($pg+1)."</a></li>":"";
            $this->html .= (int)$pg+2<=$max_page?"<li><a class='$this->pageLinkClass' href='".$url."?pg=".($pg+2)."&srch=$srch'>".($pg+2)."</a></li>":"";
            $this->html .= (int)$pg+3<=$max_page?"<li><a class='$this->pageEllipsesClass' href='#'>&hellip;</a></li>":"";
            // Last Page
            if ((int)$this->offset <= ((int)$fullCnt - (int)$this->limit)) {
                $this->html .= "<li><a class='$this->pageLinkClass' href='".$url.'?pg='.$max_page."&srch=$srch"."'>Last</a></li>";
            }
            $this->html .= '</ul></nav>';
        }
        return $this->html;
    }
    public function form() {
        // if (empty($this->sql) || empty($this->param) || empty($this->fields)) {
        //     return $this->html."Mandatory parameters missing!"; 
        // }
        if (empty($this->fields)) {
            return $this->html."Mandatory parameters missing!"; 
        }

        // Process submission
        if (isset($_POST['submit'])) {
            $this->preTran();
            $this->param = explode('~', $this->param);
            foreach ($this->param as $key => $value) {
                $this->param[$key] = $_POST[$value];
            }
            // exit(implode('~',$this->param));
            $ret = $this->conn->ExecSQL($this->sql, implode('~',$this->param));
            if ($ret) { 
                $this->notifyClass = "is-success is-light";
                $this->notifyMessage = "Add/ Update successful";
            } else {
                $this->notifyClass = "is-danger is-light";
                $this->notifyMessage = "Add/ Update failed";
            }
            $this->preNotify();
            $this->html .= "<div class='notification $this->notifyClass'><button class='delete'></button>$this->notifyMessage</div>";
            $this->postTran();
        }
        // Generate Form
        $this->html .= "<form class='".$this->tblClass."' id='".$this->tblId."' method='POST' action='".(isset($this->fields['actions']['submit']['link'])?$this->fields['actions']['submit']['link']:"#")."'>";
        foreach ($this->fields as $key => $value) {
            if ($key == "actions") continue;
            if (empty($value['type'])) { $this->html .= "<div class='$this->fieldGroupClass'>Blank type for control!</div>"; continue; }
            if (!in_array($value['type'],["text", "email", "date", "datetime-local", "password", "month", "week", "number", "textarea", "select"])) { 
                $this->html .= "<div class='$this->fieldGroupClass'>Type of control $value[type] not yet implemented!</div>"; 
                continue; 
            }
            if ($value['type'] == "datetime") $value["type"] = "datetime-local";
            if (empty($value['label_class'])) $value['label_class'] = "";
            if (empty($value['control_class'])) $value['control_class'] = "";
            $this->html .= "<div class='$this->fieldGroupClass'><label class='label $value[label_class]' for='$key'>".(isset($value['label'])?$value['label']:$key).(isset($value['req'])?'*':'')."</label><div class='$this->fieldControlClass'>";
            if (in_array($value['type'],["text", "email", "date", "datetime-local", "password", "month", "week", "number"])) {
                $this->html .= "<input class='$this->fieldInputClass $value[control_class]' type='".$value['type']."' id='$key' name='$key' ";
                if (!empty($value['attributes'])) {
                    foreach ($value['attributes'] as $attKey => $attValue) {
                        $this->html .= "$attKey= '$attValue' ";
                    }
                }
                $this->html .= " />";
            } elseif ($value['type'] == "textarea") {
                $this->html .= "<textarea class='$this->fieldInputClass $value[control_class]' type='".$value['type']."' id='$key' name='$key' ";
                if (!empty($value['attributes'])) {
                    foreach ($value['attributes'] as $attKey => $attValue) {
                        $this->html .= "$attKey= '$attValue' ";
                    }
                }
                $this->html .= "/>";
            } elseif ($value['type'] == "select" ) {
                $this->html .= "<select class='$this->fieldInputClass $value[control_class]' id='$key' name='$key' ";
                if (!empty($value['attributes'])) {
                    foreach ($value['attributes'] as $attKey => $attValue) {
                        $this->html .= "$attKey= '$attValue' ";
                    }
                }
                $this->html .= ">";
                if (empty($value['options'])) continue;
                foreach ($value['options'] as $sKey => $sVal) {
                    $this->html .= "<option value='$sKey' ";
                    if (is_array($sVal)) { 
                        $this->html .= "selected='selected' >".$sVal[0]; 
                    } else $this->html .= ">".$sVal; 
                    "</option>";
                }
                $this->html .= "</select>";
            }
            $this->html .= "</div></div>";
        }
        if (isset($this->fields['actions'])) {
            $this->html .= "<div class='field is-grouped'>";
            foreach ($this->fields['actions'] as $key => $value) {
                if (empty($value['type'])) { $this->html .= "<div class='$this->fieldGroupClass'>Blank type for control!</div>"; continue; }
                if (!in_array($value['type'],["submit", "link", "button"])) { $this->html .= "<div class='$this->fieldGroupClass'>Type of control $value[type] not yet implemented!</div>"; continue; }
                $this->html .= "<div class='$this->fieldGroupClass'>";
                if ($value['type'] == "submit") {
                    $this->html .= "<input class='$this->buttonClass ".(isset($value['class'])?$value['class']:"")."' type='".$value['type']."' id='$key' name='$key' value='$value[label]'/>";
                } elseif ($value['type'] == "link") {
                    $this->html .= "<a class='$this->buttonClass ".(isset($value['class'])?$value['class']:"")."' href='$value[link]'>$value[label]</a>";
                } elseif ($value['type'] == "button") {
                    $this->html .= "<button class='$this->buttonClass ".(isset($value['class'])?$value['class']:"")."' onclick='".(isset($value['proc'])?$value['proc']:"")."'>$value[label]</button>";
                }
                $this->html .= "</div>";
            }
            $this->html .= "</div>";
        }
        $this->html .= "</form>";
        return $this->html;
    }
}
