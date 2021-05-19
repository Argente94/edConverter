<?php
include('../lib/EdcrumbleParser.php');
include('../lib/utils.php');

//check uploaded file
if(empty($_FILES['file'])){
    header('HTTP/1.1 500 Error uploading file!');
    exit;
}

//create directory and move file
$token = uniqid();
$tar_path = '../tmp/tar/' . $token;
mkdir($tar_path);
$file_path = $tar_path . '/' . $_FILES['file']['name'];
move_uploaded_file($_FILES['file']['tmp_name'], $file_path);

try {
    //extract compressed folder
    $p = new PharData($file_path);
    $p->extractTo($tar_path);
    unlink($file_path);
}
catch(Exception $e){
    header('HTTP/1.1 500 Error decompressing file!');
    exit;
}

$json = \EdcrumbleParser\parse_edcrumble_from_edx_olx($tar_path);

// force file download
header('Content-Type: application/octetstream');
header("Content-Disposition: attachment; filename=edcrumbleDesign.json");
echo json_encode($json);

\Utils\remove_directory($tar_path);

exit;