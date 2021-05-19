
//check browser has drag and drop and FileReader features
var isDragAndDrop = () => {
    const div = document.createElement('div');
    return "ondrag" in div && 'FormData' in window && 'FileReader' in window;
}

document.addEventListener('DOMContentLoaded', () => {
    var modal = document.getElementById("modal");

    //main button events
    $('#btn-edcrumble').on('click', () => {
        modal.style.display = "block";
        $('#inner-modal h2').html('edCrumble to edX');
        $('#current').val('edcrumble');
        $('#message-display').css('display', 'none');
    });

    $('#btn-edx').on('click', () => {
        modal.style.display = "block";
        $('#inner-modal h2').html('edX to edCrumble');
        $('#current').val('edx');
        $('#message-display').css('display', 'none');
    });

    // close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // file selector
    $('input[type="file"]').on('change', e => {
        console.log("file input");
        parseFile(e.target.files[0]);
    });

    // drag & drop control
    if(isDragAndDrop){
        const box = $('#parser');
        box.addClass('has-advanced-upload');

        var counter = 0;
        box.on('dragenter dragleave dragover drop', function(e){
            e.preventDefault();
        })
        .on('dragenter', function() {
            counter++;
            box.addClass('is-dragover');
        })
        .on('dragleave', function() {
            counter--;
            if(counter === 0){
                box.removeClass('is-dragover');
            }
        })
        .on('drop', function(e) {
            counter = 0;
            box.removeClass('is-dragover');
            var files = e.originalEvent.dataTransfer.files;
            parseFile(files[0]);
        });
    }
});

function parseFile(file){
    var current_method = $('#current').val();

    if(current_method == 'edcrumble'){
        // get text from file
        file.text().then(text => {
            try {
                var json_data = JSON.parse(text);
                downloadEdxCourse(json_data);
            }
            catch (error) {
                showMessage('The input file is not JSON valid', 'red');
            }
        });
    }
    else if(current_method == 'edx'){
        if(file.type == "application/x-gzip"){
            //transfer file
            downloadEdcrumbleDesign(file);
        }
        else{
            showMessage('The input file must be a GZip file', 'red');
        }
    }
}

function downloadEdxCourse(json){

    $.ajax({
        url: 'actions/parse_edcrumble.php',
        method: 'post',
        xhrFields: {
            responseType: 'blob' // to avoid binary data being mangled on charset conversion
        },
        data: { json: json },
        success: function(blob, status, xhr){
            showMessage('Conversion was successful!', 'green');
            downloadFromBlob(blob, xhr);
        },
        error: function(xhr, status, error) {
            showMessage(error, 'red');
        }
    });
}

function downloadEdcrumbleDesign(file){
    var form_data = new FormData();
    form_data.append('file', file);

    $.ajax({
        url: 'actions/parse_edx.php',
        method: 'post',
        xhrFields: {
            responseType: 'blob' // to avoid binary data being mangled on charset conversion
        },
        contentType: false,
        processData: false,
        data: form_data,
        success: function(blob, status, xhr){
            showMessage('Conversion was successful!', 'green');
            downloadFromBlob(blob, xhr);
        },
        error: function(xhr, status, error) {
            showMessage(error, 'red');
        }
    });
}

function downloadFromBlob(blob, xhr){
    var filename = "";
    var disposition = xhr.getResponseHeader('Content-Disposition');
    if (disposition && disposition.indexOf('attachment') !== -1) {
        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
        var matches = filenameRegex.exec(disposition);
        if (matches != null && matches[1])
            filename = matches[1].replace(/['"]/g, '');
    }

    // generate download URL
    var URL = window.URL || window.webkitURL;
    var downloadUrl = URL.createObjectURL(blob);

    // create link in the DOM and start download
    var a = document.createElement("a");
    a.href = downloadUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
}

function showMessage(text, color){
    console.log(text);
    var error = $('#message-display');
    error.html(text);
    error.css('color', color);
    error.css('display', 'block');
}


