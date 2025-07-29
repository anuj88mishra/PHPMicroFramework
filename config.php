<?php
const DB_HOST = 'localhost';
const DB_NAME = 'admdb';
const DB_USER = 'postgres';
const DB_PASS = 'pgsql';
const BASE_URL = 'http://localhost/PHPMicroFramework/';
const BASE_DIR = 'E:/xampp/htdocs/PHPMicroFramework/'; 
const SITE_TITLE = "MySite";
const SITE_DESC = "Glorious site description";
const DEBUG = true;
const SECURE = true;


//Error Key Values
const ERR = [
    "BADCON" => "Error Establishing Connection with DB",
    "UNCLEANSQL" => "Please check the query, it seems malicious",
    "PARAMERR" => "Please check the parameter string provided",
    "SQLERR" => "Please check the query. Error: ",
    "BADSQL" => "Malformed query, please check the query provided",
    "REQVAL" => "A required value is missing! "
];