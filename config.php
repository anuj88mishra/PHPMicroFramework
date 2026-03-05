<?php
const DB_DRIVER = 'mysql';
const DB_HOST = 'localhost';
const DB_NAME = 'test_crud';
const DB_USER = 'root';
const DB_PASS = '';
const BASE_URL = 'http://localhost:8000/';
const BASE_DIR = __DIR__ . '/';
const SITE_TITLE = "PHPMicroFramework - Test";
const SITE_DESC = "Low-code Framework with CRUD Builder and Menu Editor";
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