<?php

namespace EdcrumbleParser;

function validate_top_level_dir($path){
    $files = array_values(array_diff(scandir($path), array('.','..')));

    // check that compressed file contains only one file
    if(count($files) != 1){
        header('HTTP/1.1 500 The compressed file must contain a single folder!');
        exit;
    }

    //check that decompressed file is a directory
    $top_dir = $path . '/' . $files[0];
    if(!is_dir($top_dir)){
        header('HTTP/1.1 500 The compressed file must contain a single folder!');
        exit;
    }

    return $top_dir;
}

function validate_course_file(){
    global $top_dir, $top_dir_files;

    //check course.xml exists
    if(in_array('course.xml', $top_dir_files)){
        if(is_dir("$top_dir/course.xml")){
            header('HTTP/1.1 500 course.xml file is missing!');
            exit;
        }
    }
    else{
        header('HTTP/1.1 500 course.xml file is missing!');
        exit;
    }

    try{
        // try loading the XML file
        if(($xml = simplexml_load_file("$top_dir/course.xml")) === false){
            throw new Exception('');
        }

        // if root element is not course
        if($xml->getName() != 'course'){
            throw new Exception('');
        }

        // if root element does not contain attribute url_name
        if(!isset($xml['url_name'])){
            throw new Exception('');
        }

        return $xml['url_name'][0];
    }
    catch(Exception $e){
        header('HTTP/1.1 500 Error loading course.xml!');
        exit;
    }
}

function validate_course_dir($course_url){
    global $top_dir, $top_dir_files;

    //check course directory exists
    if(in_array('course', $top_dir_files)){
        if(!is_dir("$top_dir/course")){
            header('HTTP/1.1 500 course subdirectory is missing!');
            exit;
        }
    }
    else{
        header('HTTP/1.1 500 course subdirectory is missing!');
        exit;
    }

    $path = "$top_dir/course";
    $files = array_values(array_diff(scandir($path), array('.','..')));

    //check course file exists
    if(in_array("$course_url.xml", $files)){
        if(is_dir("$path/$course_url.xml")){
            header('HTTP/1.1 500 course file is missing!');
            exit;
        }
    }
    else{
        header('HTTP/1.1 500 course file is missing!');
        exit;
    }

    try{
        // try loading the XML file
        if(($xml = simplexml_load_file("$path/$course_url.xml")) === false){
            throw new Exception('');
        }

        // if root element is not course
        if($xml->getName() != 'course'){
            throw new Exception('');
        }
    }
    catch(Exception $e){
        header('HTTP/1.1 500 Error loading course.xml!');
        exit;
    }
}

function default_values(){
    return [
        "version" => 7,
        "designTitle" => "",
        "description" => "",
        "evaluation" => "",
        "experience" => "",
        "objectivesList" => [],
        "students" => "0",
        "startDate" => "",
        "endDate" => "",
        "topic" => "",
        "educationalLevel" => "",
        "last_resource_id" => 0,
        "last_item_id" => 0,
        "last_mooc_id" => 0,
        "DPList" => [],
        "resourcesList" => new \stdClass(),
        "moocsList" => [],
        "itemsList" => []
    ];
}

function parse_edcrumble_from_edx_olx($path){
    global $json, $top_dir, $top_dir_files;

    // build json structure
    $json = default_values();

    // validate top level directory
    $top_dir = validate_top_level_dir($path);
    $top_dir_files = array_values(array_diff(scandir($top_dir), array('.','..')));

    parse_course();

    return $json;
}

function parse_course(){
    global $json, $top_dir, $top_dir_files;

    //validate course
    $course_url = validate_course_file();
    validate_course_dir($course_url);

    //parse course
    $xml = simplexml_load_file("$top_dir/course/$course_url.xml");

    $json['designTitle'] = empty($xml['display_name']) ? "Untitled Design" : $xml['display_name'] . "";
    $json['startDate'] = empty($xml['start']) ? "2021-05-06T08:00:00.000Z" : $xml['start'] . "";
    $json['endDate'] = empty($xml['display_name']) ? "2021-06-06T23:00:00.000Z" : $xml['end'] . "";
}