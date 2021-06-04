<?php
include 'connect.php';

$token = $_GET['token'];
$database_name = $_GET['database_name'];



// authenticate user here by checking token
$sql = "SELECT * FROM tokenlist WHERE token = '" . $token . "' ";
$result = $conn->query($sql);
// echo '<pre>';
// print_r($result);
// exit;

if ($result->num_rows > 0) {
    // user authentication successful
    // check database name work now 
    $sql2 = "SELECT * FROM master_table WHERE db_name = '" . $database_name . "' ";
    $result_db = $conn->query($sql2);
    if ($result_db->num_rows > 0) {
        // database exists, return success message
        $arr = array(
            "error" => 0,
            "message" => "Database " . $database_name . " exists "
        );
        echo json_encode($arr);
        exit;
    } else {
        // database not found,
        // create new DB
        $get_schema_query = " SHOW CREATE TABLE clone_db.clone_tb ";
        $get_schema_result = $conn->query($get_schema_query);
        if ($get_schema_result->num_rows > 0) {
            while ($row = $get_schema_result->fetch_assoc()) {
                $create_table_query = $row["Create Table"].';';
                $sub = substr($create_table_query,13);
                $sub = "CREATE TABLE ".$database_name.".".$sub;
                // print_r($sub);
                // exit;
                $create_db_query = "CREATE DATABASE " . $database_name . "; ";
                if ($conn->query($create_db_query) === TRUE) {
                    // database created successfully, now create table
                    if ($conn->query($sub) === TRUE) {
                        $arr = array(
                            "error" => 0,
                            "message" => "New Database " . $database_name . " created successfully "
                        );
                        echo json_encode($arr);
                        exit;
                    } else {
                        $arr = array(
                            "error" => 1,
                            "message" => "Error creating table in Database",
                            "database_error" => $conn->error
                        );

                        echo json_encode($arr);
                        exit;
                    }
                } else {
                    $arr = array(
                        "error" => 1,
                        "message" => "Error creating Database " . $database_name,
                        "database_error" => $conn->error
                    );
                    echo json_encode($arr);
                    exit;
                }
            }
        }
    }
} else {
    // no token found in DB, send authentication error
    $arr = array(
        "error" => 1,
        "message" => "Invalid Token! Try with valid token string"
    );

    echo json_encode($arr);
    exit;
}
