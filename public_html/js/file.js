// document ready function
$(document).ready(function() { 	

	//------------- Elfinder file manager  -------------//
    var elf = $('#elfinder').elfinder({
		// lang: 'ru',             // language (OPTIONAL)
		url : 'php/connector.php'  // connector URL (REQUIRED)
	}).elfinder('instance');

    //------------- Plupload php upload  -------------//
    // Setup html4 version
	$("#html4_uploader").pluploadQueue({
		// General settings
		runtimes : 'html4', 
		url : 'php/upload.php',
		max_file_size : '10mb',
		max_file_count: 15, // user can add no more then 15 files at a time
		chunk_size : '1mb',
		unique_names : true,
		multiple_queues : true,

		// Resize images on clientside if we can
		resize : {width : 320, height : 240, quality : 80},
		
		// Rename files by clicking on their titles
		rename: true,
		
		// Sort files
		sortable: true,

		// Specify what files to browse for
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Zip files", extensions : "zip,avi"}
		]
	});

	//Boostrap modal
	$('#myModal').modal({ show: false});
	
	//add event to modal after closed
	$('#myModal').on('hidden', function () {
	  	console.log('modal is closed');
	})

});//End document ready functions
