<?php
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Course Converter</title>
    <link rel="stylesheet" type="text/css" href="static/style.css">
    <script src="vendors/jquery/jquery-3.6.0.js"></script>
    <script type="text/javascript" src="static/index.js"></script>
</head>
<body>
    <div id="container">
        <h1>edConverter</h1>
        <button id="btn-edcrumble" style="margin-right: 10px;">Convert edCrumble to edX</button>
        <button id="btn-edx">Convert edX to edCrumble</button>
    </div>

    <div id="modal" class="modal">
        <div id="inner-modal">
            <input id="current" type="hidden" value="">
            <h2></h2>
            <div id="parser" class="box">
                <div class="box__input">
                    <input type="file" name="file" id="input-file" class="inputfile" />
                    <label for="input-file"><strong>Choose a file</strong></label>
                    <span class="box__dragndrop"> or drag it here.</span>
                </div>
            </div>
            <div id="message-display"></div>
        </div>
    </div>
</body>
</html>
