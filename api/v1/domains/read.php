<?php
require('../validate_api_key.php');

if($_SERVER['REQUEST_METHOD'] !== "GET"){
    header("HTTP/1.1 405 Method Not Allowed");
    $return_arr['success'] = "False";
    $return_arr['message'] = "Can only send GET requests to this endpoint.";
    echo json_encode($return_arr);
    exit();
}

// Specific domain via ID (single)
if(isset($_GET['domain_id'])){
    $id = intval($_GET['domain_id']);
    $sql = mysqli_query($mysqli, "SELECT * FROM domains WHERE domain_id = '$id' AND company_id = '$company_id'");
}

// Domain by name
elseif(isset($_GET['domain_name'])){
    $name = mysqli_real_escape_string($mysqli,$_GET['domain_name']);
    $sql = mysqli_query($mysqli, "SELECT * FROM domains WHERE domain_name = '$name' AND company_id = '$company_id' ORDER BY asset_id LIMIT $limit OFFSET $offset");
}

// Domain via client ID
elseif(isset($_GET['domain_client_id'])){
    $client = intval($_GET['domain_client_id']);
    $sql = mysqli_query($mysqli, "SELECT * FROM domains WHERE domain_client_id = '$client' AND company_id = '$company_id' ORDER BY domain_id LIMIT $limit OFFSET $offset");
}

// All domains
else{
    $sql = mysqli_query($mysqli, "SELECT * FROM domains WHERE company_id = '$company_id' ORDER BY domain_id LIMIT $limit OFFSET $offset");
}

// Output
include("../read_output.php");