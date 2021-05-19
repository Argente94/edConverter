<?php

namespace Utils;

function remove_directory($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? remove_directory("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}