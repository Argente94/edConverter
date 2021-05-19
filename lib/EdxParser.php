<?php

namespace EdxParser;

use DOMDocument;

function validate_edcrumble(){
    global $json;

    $error_missing = array();
    $error_format = array();

    // design title
    if(!isset($json['designTitle'])){
        $error_missing[] = "designTitle";
    }
    else{
        if(!is_string($json['designTitle'])){
            $error_format[] = "designTitle";
        }
    }

    // description
    if(!isset($json['description'])){
        $error_missing[] = "description";
    }
    else{
        if(!is_string($json['description'])){
            $error_format[] = "description";
        }
    }

    // objectives list
    if(!isset($json['objectivesList'])){
        //array
        $json['objectivesList'] = array();
    }
    else{
        if(!is_array($json['objectivesList'])){
            $error_format[] = "objectivesList";
        }
        else{
            foreach($json['objectivesList'] as $objective){
                if(!is_string($objective)){
                    $error_format[] = "objectivesList element";
                    break;
                }
            }
        }
    }

    // start date
    if(!isset($json['startDate'])){
        $error_missing[] = "startDate";
    }
    else{
        if(!strtotime($json['startDate'])){
            $error_format[] = "startDate";
        }
    }

    // end date
    if(!isset($json['endDate'])){
        $error_missing[] = "endDate";
    }
    else{
        if(!strtotime($json['endDate'])){
            $error_format[] = "endDate";
        }
    }

    // resources list
    if(isset($json['resourcesList'])){

        if(!is_array($json['resourcesList'])){
            $error_format[] = "resourcesList";
        }
        else{
            foreach($json['resourcesList'] as $key => $resource){
                if(!is_array($resource)){
                    $error_format[] = "resource";
                    break;
                }
                else{
                    // resource id
                    if(!isset($resource['id'])){
                        $error_missing[] = "resource id";
                        break;
                    }
                    else{
                        if(!ctype_digit($resource['id'])){
                            $error_format[] = "resource id";
                            break;
                        }
                    }

                    // resource type
                    if(!isset($resource['type'])){
                        $error_missing[] = "resource type";
                        break;
                    }
                    else{
                        if(!is_string($resource['type'])){
                            $error_format[] = "resource type";
                            break;
                        }
                    }

                    // resource medium
                    if(!isset($resource['medium_type'])){
                        $error_missing[] = "resource medium";
                        break;
                    }
                    else{
                        if(!ctype_digit($resource['medium_type'])){
                            $error_format[] = "resource medium";
                            break;
                        }
                    }
                }
            }
        }
    }

    // items list
    if(isset($json['itemsList'])){

        if(!is_array($json['itemsList'])){
            $error_format[] = "itemsList";
        }
        else{
            foreach($json['itemsList'] as $key => $item){
                if(!is_array($item)){
                    $error_format[] = "item";
                    break;
                }
                else{
                    // item id
                    if(!isset($item['id'])){
                        $error_missing[] = "item id";
                        break;
                    }
                    else{
                        if(!ctype_digit($item['id'])){
                            $error_format[] = "item id";
                            break;
                        }
                    }

                    // item title
                    if(!isset($item['title'])){
                        $error_missing[] = "item title";
                        break;
                    }
                    else{
                        if(!is_string($item['title'])){
                            $error_format[] = "item title";
                            break;
                        }
                    }

                    // item start
                    if(!isset($item['start'])){
                        $error_missing[] = "item start";
                        break;
                    }
                    else{
                        if(!strtotime($item['start'])){
                            $error_format[] = "item start";
                            break;
                        }
                    }

                    // item end
                    if(!isset($item['end'])){
                        $error_missing[] = "item end";
                        break;
                    }
                    else{
                        if(!strtotime($item['end'])){
                            $error_format[] = "item end";
                            break;
                        }
                    }

                    // item tasks
                    if(isset($item['tasks'])){
                        if(!is_array($item['tasks'])){
                            $error_format[] = "item tasks";
                        }
                        else{
                            foreach($item['tasks'] as $key2 => $task){

                                //task description
                                if(!isset($task['description'])){
                                    $error_missing[] = "task description";
                                    break;
                                }
                                else{
                                    if(!is_string($task['description'])){
                                        $error_format[] = "task description";
                                        break;
                                    }
                                }

                                //task resources
                                if(isset($task['resources'])){
                                    if(!is_array($task['resources'])){
                                        $error_format[] = "task resources";
                                        break;
                                    }
                                    else{
                                        foreach($task['resources'] as $resource){
                                            if(!ctype_digit($resource)){
                                                $error_format[] = "task resources element";
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if(!empty($error_missing)){
        header('HTTP/1.1 500 Missing parameter: ' . $error_missing[0]);
        exit;
    }

    if(!empty($error_format)){
        header('HTTP/1.1 500 Parameter with wrong format: ' . $error_format[0]);
        exit;
    }
}

function parse_edx_from_edcrumble_json(){
    global $course_path, $json;

    $path = $course_path . '/edcrumble_course';

    //create course directory
    mkdir($path);

    create_course_file($path);
    create_course_directory($path);
    create_about_directory($path);

    // if design has activities
    if(isset($json['itemsList'])){
        create_chapter_directory($path);
        create_sequential_directory($path);
    }
}

function create_course_file($path){

    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput = true;

    $course = $domtree->createElement("course");

    $url_name = $domtree->createAttribute('url_name');
    $url_name->value = 'edcrumble';
    $course->appendChild($url_name);

    $org = $domtree->createAttribute('org');
    $org->value = 'default';
    $course->appendChild($org);

    $course_att = $domtree->createAttribute('course');
    $course_att->value = 'default';
    $course->appendChild($course_att);

    $domtree->appendChild($course);

    //save XML file in course directory
    $domtree->save($path . '/course.xml');
}

function create_course_directory($path){
    global $json;

    mkdir($path . '/course');

    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput = true;

    $course = $domtree->createElement("course");

    // design title
    $display_name = $domtree->createAttribute('display_name');
    $display_name->value = $json['designTitle'];
    $course->appendChild($display_name);

    // start date
    $start = $domtree->createAttribute('start');
    $start->value = $json['startDate'];
    $course->appendChild($start);

    // end date
    $end = $domtree->createAttribute('end');
    $end->value = $json['endDate'];
    $course->appendChild($end);

    //self paced
    $self_paced = $domtree->createAttribute('self_paced');
    $self_paced->value = "true";
    $course->appendChild($self_paced);

    // chapter
    $chapter = $domtree->createElement('chapter');

    // chapter url
    $chapter_url = $domtree->createAttribute('url_name');
    $chapter_url->value = 'defaultChapter';
    $chapter->appendChild($chapter_url);

    $course->appendChild($chapter);
    $domtree->appendChild($course);

    $domtree->save($path . '/course/edcrumble.xml');
}

function create_about_directory($path){
    global $json;

    mkdir($path . '/about');

    //create short_description.html
    $desc_file = fopen($path . '/about/short_description.html', 'w');
    fwrite($desc_file, $json['description']);
    fclose($desc_file);
}

function create_chapter_directory($path){
    global $json;

    mkdir($path . '/chapter');

    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput = true;

    $chapter = $domtree->createElement("chapter");

    // chapter name
    $chapter_name = $domtree->createAttribute('display_name');
    $chapter_name->value = $json['designTitle'];
    $chapter->appendChild($chapter_name);

    foreach($json['itemsList'] as $key => $item){
        //sequential
        $sequential = $domtree->createElement('sequential');

        //sequential url
        $sequential_url = $domtree->createAttribute('url_name');
        $sequential_url->value = 'activity' . ($key + 1);
        $sequential->appendChild($sequential_url);

        $chapter->appendChild($sequential);
    }

    $domtree->appendChild($chapter);
    $domtree->save($path . '/chapter/defaultChapter.xml');
}

function create_sequential_directory($path){
    global $json, $task_counter, $resource_counter;

    mkdir($path . '/sequential');

    $exist_task = false;

    foreach($json['itemsList'] as $key => $item){
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $domtree->formatOutput = true;

        $sequential = $domtree->createElement("sequential");

        // sequential name
        $sequential_name = $domtree->createAttribute('display_name');
        $sequential_name->value = $item['title'];
        $sequential->appendChild($sequential_name);

        // handle verticals
        if(isset($item['tasks'])){
            //create vertical and html directory
            if(!$exist_task){
                mkdir($path . '/vertical');
                $exist_task = true;

                //init keys
                $task_counter = 1;
                $resource_counter['html'] = 1;
                $resource_counter['problem'] = 1;
                $resource_counter['video'] = 1;
                $resource_counter['discussion'] = 1;
            }

            // add verticals to sequential
            foreach($item['tasks'] as $key2 => $task){
                $vertical = $domtree->createElement('vertical');

                $vertical_url = $domtree->createAttribute('url_name');
                $vertical_url->value = 'task' . $task_counter;
                $vertical->appendChild($vertical_url);

                $sequential->appendChild($vertical);

                // add vertical to vertical directory
                add_vertical($task, $path);

                $task_counter++;
            }
        }

        $domtree->appendChild($sequential);
        $domtree->save($path . '/sequential/activity' . ($key + 1) . '.xml');
    }
}

function add_vertical($task, $path){
    global $task_counter, $resource_counter, $json;

    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput = true;

    $vertical = $domtree->createElement('vertical');

    $vertical_name = $domtree->createAttribute('display_name');
    $vertical_name->value = 'Task ' . $task_counter;
    $vertical->appendChild($vertical_name);

    if(isset($json['resourcesList']) && isset($task['resources'])){
        foreach($task['resources'] as $resource_id){
            if(isset($json['resourcesList'][$resource_id])){
                $resource = $json['resourcesList'][$resource_id];

                // if medium type is edX
                if($resource['medium_type'] == "8"){
                    if($resource['type'] == "edxhtml"){
                        $html = $domtree->createElement('html');
                        $html_url = $domtree->createAttribute('url_name');
                        $html_url->value = 'html' . $resource_counter['html'];
                        $html->appendChild($html_url);
                        $vertical->appendChild($html);
                        add_html($path);
                    }
                    else if($resource['type'] == "edxproblem"){
                        $problem = $domtree->createElement('problem');
                        $problem_url = $domtree->createAttribute('url_name');
                        $problem_url->value = 'problem' . $resource_counter['problem'];
                        $problem->appendChild($problem_url);
                        $vertical->appendChild($problem);
                        add_problem($path);
                    }
                    else if($resource['type'] == "edxvideo"){
                        $video = $domtree->createElement('video');
                        $video_url = $domtree->createAttribute('url_name');
                        $video_url->value = 'video' . $resource_counter['video'];
                        $video->appendChild($video_url);
                        $vertical->appendChild($video);
                        add_video($path);
                    }
                    else if($resource['type'] == "edxdiscussion"){
                        $discussion = $domtree->createElement('discussion');
                        $discussion_url = $domtree->createAttribute('url_name');
                        $discussion_url->value = 'discussion' . $resource_counter['discussion'];
                        $discussion->appendChild($discussion_url);
                        $vertical->appendChild($discussion);
                        add_discussion($path);
                    }
                }
            }
        }
    }

    $domtree->appendChild($vertical);
    $domtree->save($path . '/vertical/task' . $task_counter . '.xml');
}

function add_html($path){
    global $resource_counter;

    if($resource_counter['html'] == 1){
        mkdir($path . '/html');
    }

    //xml file
    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput = true;

    $html = $domtree->createElement('html');

    $html_filename = $domtree->createAttribute('filename');
    $html_filename->value = 'html' . $resource_counter['html'];
    $html->appendChild($html_filename);
    $html_displayname = $domtree->createAttribute('display_name');
    $html_displayname->value = 'Raw HTML';
    $html->appendChild($html_displayname);
    $html_editor = $domtree->createAttribute('editor');
    $html_editor->value = 'raw';
    $html->appendChild($html_editor);

    $domtree->appendChild($html);
    $domtree->save($path . '/html/html' . $resource_counter['html'] . '.xml');

    //html file
    $html_file = fopen($path . '/html/html' . $resource_counter['html'] . '.html', 'w');
    fwrite($html_file, '<p>The Raw HTML editor saves your HTML exactly as you enter it.
You can switch to the Visual editor by clicking the Settings tab and 
changing the Editor setting to Visual. Note, however, that some of your 
HTML may be modified when you save the component if you switch to the 
Visual editor.</p>');
    fclose($html_file);

    $resource_counter['html']++;
}

function add_problem($path){
    global $resource_counter;

    if($resource_counter['problem'] == 1){
        mkdir($path . '/problem');
    }

    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput = true;

    $problem = $domtree->createElement('problem');

    $problem_name = $domtree->createAttribute('display_name');
    $problem_name->value = 'Blank problem';
    $problem->appendChild($problem_name);
    $problem_markdown = $domtree->createAttribute('markdown');
    $problem_markdown->value = '';
    $problem->appendChild($problem_markdown);

    $domtree->appendChild($problem);
    $domtree->save($path . '/problem/problem' . $resource_counter['problem'] . '.xml');

    $resource_counter['problem']++;
}

function add_video($path){
    global $resource_counter;

    if($resource_counter['video'] == 1){
        mkdir($path . '/video');
    }

    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput = true;

    $video = $domtree->createElement('video');

    $video_name = $domtree->createAttribute('display_name');
    $video_name->value = 'Video';
    $video->appendChild($video_name);

    $domtree->appendChild($video);
    $domtree->save($path . '/video/video' . $resource_counter['video'] . '.xml');

    $resource_counter['video']++;
}

function add_discussion($path){
    global $resource_counter;

    if($resource_counter['discussion'] == 1){
        mkdir($path . '/discussion');
    }

    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput = true;

    $discussion = $domtree->createElement('discussion');

    $discussion_name = $domtree->createAttribute('display_name');
    $discussion_name->value = 'Discussion';
    $discussion->appendChild($discussion_name);
    $discussion_category = $domtree->createAttribute('discussion_category');
    $discussion_category->value = 'Discussion category';
    $discussion->appendChild($discussion_category);
    $discussion_target = $domtree->createAttribute('discussion_target');
    $discussion_target->value = 'Discussion target';
    $discussion->appendChild($discussion_target);

    $domtree->appendChild($discussion);
    $domtree->save($path . '/discussion/discussion' . $resource_counter['discussion'] . '.xml');

    $resource_counter['discussion']++;
}