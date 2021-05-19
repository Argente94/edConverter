<?php
include('../lib/EdxParser.php');
include('../lib/utils.php');

//get JSON data
global $json;
$json = $_POST['json'];

// validate edcrumble parameters
\EdxParser\validate_edcrumble();

// create temp directory for course storage
global $course_path;
$token = uniqid();
$course_path = '../tmp/courses/' . $token;
mkdir($course_path);

//create temp directory for compressed files
$tar_path = '../tmp/tar/' . $token;
mkdir($tar_path);

try{
    \EdxParser\parse_edx_from_edcrumble_json();

    $filename = 'edcrumble_course';

    // compress course directory
    $tar_file = new PharData($tar_path . '/' . $filename . '.tar');
    $tar_file->buildFromDirectory($course_path);
    $tar_file->compress(Phar::GZ);

    // force file download
    header('Content-Type: application/octetstream');
    header("Content-Disposition: attachment; filename=" . $filename . ".tar.gz");
    @readfile($tar_path . '/' . $filename . '.tar.gz');

    //clean files
    \Utils\remove_directory($course_path);
    \Utils\remove_directory($tar_path);

    exit;
}
catch (Exception $e){
    header('HTTP/1.1 500 Internal server error!');
    exit;
}

