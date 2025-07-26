<?php
final class Util {
    // Utility Fucntions
    public static function C_CEIL($number, $significance = 1){
        return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
    }
    public static function C_FLOOR($number, $significance = 1){
        return ( is_numeric($number) && is_numeric($significance) ) ? (floor($number/$significance)*$significance) : false;
    }
    public static function C_CLEAN_SELECT($sql) {
        if (preg_match("/;|update|insert|drop|delete|truncate/", strtolower($sql))) { return false; }
        return true;
    }
    public static function C_CLEAN_UPDATE($sql) {
        if (preg_match("/;|drop|delete|truncate/", strtolower($sql))) { return false; }
        return true;
    }
    public static function C_EMPTY($str) {
        return empty($str);
    }
    public static function C_ARRAY_INDEX($arr, $idx = 0) {
        if (is_array($arr) && empty($idx)) {
            return $arr[array_key_first($arr)];
        } elseif (is_array($arr) && $idx) {
            return $arr[array_keys($arr)[$idx]];
        } else {
            return $arr;
        }
    }
}
