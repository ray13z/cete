$(function(){
    var ul = $('#upload ul');

    $('#drop a').click(function(){
        // Simulate a click on the file input button
        
        // to show the file browser dialog
        $(this).parent().find('input').click();
    });

    // Initialize the jQuery File Upload plugin
    $('#upload').fileupload({

        // This element will accept file drag/drop uploading
        dropZone: $('#drop'),

        // This function is called when a file is added to the queue;
        // either via the browse button, or via drag/drop:

        add: function(e, data){
            
//            alert("inside add function");
            
            var tpl = $('<li class="working"><input type="text" value="0" data-width="48" data-height="48"' +
                ' data-fgColor="#0788a5" data-readOnly="1" data-bgColor="#3e4043" /><p></p><div class="check-error-list-button-placeholder"></div><span title="Click to close"></span></li>');


            // Append the file name and file size
            tpl.find('p').text(data.files[0].name)
                         .append('<i>' + formatFileSize(data.files[0].size) + '</i>');

            // Add the HTML to the UL element
            data.context = tpl.appendTo(ul);
            
            // Initialize the knob plugin
            tpl.find('input').knob();

            // Listen for clicks on the cancel icon
            tpl.find('span').click(function(){

                if (tpl.hasClass('working')) {
                    jqXHR.abort();
                }

                tpl.fadeOut(function () {
                    tpl.remove();
                });

            });
            
            // Automatically upload the file once it is added to the queue
            // var jqXHR = data.submit();
            
            //custom
            // Automatically upload the file once it is added to the queue
            
            var fileName = data.files[0].name;

            var jqXHR = data.submit().success(function(result, textStatus, jqXHR){

                // debug
                console.log("AJAX response = " + result);  
//                console.log("AJAX submit text status = " + textStatus);
//                console.log("AJAX jQuery XHR response text = " + jqXHR.responseText);
//                
                try {
                    var json = JSON.parse(result);
                } catch (e) {
                    console.error("Parsing error:", e);   
                }
                var status = json['status'];
                
//                console.log("JSON status = " + status);
//                console.log("AJAX submit text status = " + textStatus);
//                console.log("AJAX jQuery XHR response text = " + jqXHR.responseText);
                

                if (status == 'error') {
                    successful_upload_status = false;
                    
                    data.context.addClass('error');
                    
                    var error_msg = json['description'];
                    
                    data.context.find('p').text(data.files[0].name)
                         .append('<i title="' + error_msg + '">' + error_msg + '</i>');
                    
                    // Check for Checker errors
                    if(json.hasOwnProperty('errors')) {
                        var errors      = json['errors'];
                        var correctable = "0";
                        // debug
                        console.log("errors = " + errors);
                        
//                        console.dir(data.context.find('div.check-error-list-button-placeholder'));
                        
                        $('<div class="check-error-list-button"></div>').appendTo($('.check-error-list-button-placeholder:last'));
                        $('<a href="#listErrorsModal" data-toggle="modal">View Errors</a>').appendTo('.check-error-list-button:last');
                        
                        if(json.hasOwnProperty('correctable')) {
                            correctable = json['correctable'];
                        }
                        
                        $('.check-error-list-button a').click(function(event){
                            $('#errorListModalLabel').text(fileName); 
                            if(correctable !== "1") {
                                $('#attempt-to-fix').hide();
                            }
                            var stuff = getErrorListTableHTML(errors);
//                            $(stuff).appendTo($('.modal-body'));
                            $('.modal-body').html(stuff);
                        });
                        
                        $('#attempt-to-fix').click(function(event){
                            // debug
                            console.log("attempt-to-fix begin!");
                            
                            var request = $.ajax({
                                                url     : 'process_.php',
                                                type    : "POST",
                                                data    : {action : "attemptToFix", filename : fileName}
                                            });
                            
                            request.done(function(response, textStatus, jqXHR){
                                // debug
                                console.log("response = " + response);
                                console.log("textStatus = " + textStatus);
                                
                                // @todo magic
                                $('#listErrorsModal').modal('hide');
                                data.context.removeClass('error');
                                data.context.find('.check-error-list-button a').hide();
                                data.context.find('i').text(formatFileSize(data.files[0].size));
                                if ($.trim($('#check-result-button').html()).length == 0) {
                                        $('<a href="results.php">Check Results</a>').appendTo('#check-result-button');
                                }
                                console.log("yay");
                            });
                            
                            request.fail(function(jqXHR, textStatus, errorThrown){
                                console.error(
                                    "The following error occurred: " +
                                    textStatus, errorThrown
                                );    
                            });
                            
                            event.preventDefault();
                        });
                    }
                }
                
                if(status == 'success') {  
                    if ($.trim($('#check-result-button').html()).length == 0) {
                            $('<a href="results.php">Check Results</a>').appendTo('#check-result-button');
                    }
                }
                
                
//                alert("end of add function");

                // setTimeout(function(){
                //    data.context.fadeOut('slow');
                // },3000);
            });

        },

        progress: function(e, data){

            // Calculate the completion percentage of the upload
            var progress = parseInt(data.loaded / data.total * 100, 10);

            // Update the hidden input field and trigger a change
            // so that the jQuery knob plugin knows to update the dial
            data.context.find('input').val(progress).change();

            if (progress == 100) {
                data.context.removeClass('working');
            }
        },

        fail: function(e, data){
            // Something has gone wrong!
            data.context.addClass('error');
        }
        
    });


    // Prevent the default action when a file is dropped on the window
    $(document).on('drop dragover', function (e) {
        e.preventDefault();
    });

    // Helper function that formats the file sizes
    function formatFileSize(bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }

        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }

        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }

        return (bytes / 1000).toFixed(2) + ' KB';
    }
    
    /**
      * Return a table of formatted errors
      * Table contains line number and error
      */
    function getErrorListTableHTML(errors) {
        // define list of errors
        var errorDef = {
            // "errorNo" : "errorName"
            "901" : "Multiple statements per line",
            "902" : "Single line loop statement",
            "903" : "Recursive function call"
        }
        
        var tableStart  = '<div class="table-responsive"><table class="table table-striped">';
        var tableBody   =   '<tr> '                         +
                            '   <th>#</th>'                 +
                            '   <th>Line number</th>'       +
                            '   <th>Error description</th>' + 
                            '</tr>';
        var tableEnd    = '</table></div>'; 
        
        var errorTerms = errors.split(',');
        
        $.each(errorTerms, function(key, value) {

            var errorTerm = value.split(":");
            tableBody = tableBody                                       +
                            '<tr>'                                      +
                            '<td>' + (key+1) + '</td>'                  +
                            '<td>' + errorTerm[0] + '</td>'             +
                            '<td>' + errorDef[errorTerm[1]] + '</td>'   +
                            '</tr>';
        });
        
        return tableStart + tableBody + tableEnd;
    }

});