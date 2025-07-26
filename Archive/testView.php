<?php
include_once("dbconn.php");
if (empty($_GET['code'])) { echo "Key not found! Please contact IT Deptt."; exit(); }
echo genView("SELECT station_name, station_code FROM ams_db.stations WHERE station_code = ? ", $_GET['code'], ["station_name"=>"Name", "station_code"=>"Code"], '', '');
