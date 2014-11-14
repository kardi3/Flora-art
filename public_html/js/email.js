// document ready function
$(document).ready(function() { 	

	//--------------- Data tables ------------------//

	if($('table').hasClass('emailTable')){
		$('.emailTable').dataTable({
			"bJQueryUI": false,
			"bAutoWidth": false,
			"bLengthChange": false,
			"oLanguage": {
				"sSearch": "",
		        "sInfo": "Got a total of _TOTAL_ emails to show (_START_ to _END_)"
		    },
		    "fnDrawCallback": function(){
    			$("input[type=checkbox]").uniform();
            },
		    "aoColumns": [
			    { 
			    	"sWidth": "10px",
			    	"bSortable": false
			    },
			    { 
			    	"sWidth": "10px", 
			    	"bSortable": false
			    },
				{ 
			    	"bSortable": false
			    },
				{ 
			    	"bSortable": false
			    },
			    { 
			    	"sWidth": "20px",
			    	"bSortable": false
			    },
				{ 
			    	"sWidth": "80px", 
			    	"bSortable": false
		    	}
		    ]
		});
	}		

	//------------- Email page  -------------//
	
	var emailTable = $('.emailTable');
	var emailStar = emailTable.find('td.star>span.icon16');

	//setup the star in click
	emailStar.click(function() {
		if($(this).hasClass('icomoon-icon-star-3')) {
			$(this).removeClass('icomoon-icon-star-3').addClass('icomoon-icon-star');
			//make callback here

		} else {
			$(this).removeClass('icomoon-icon-star').addClass('icomoon-icon-star-3');
			//make callback here
		}
	});

	//auto complete for compose To form field
	$('#to').typeahead({
		source: ['jonh@yahoo.com','silva@yahoo.com','carlos@gmail.com','sugge@gmail.com']
	})

	//check all checkboxes in email table
	$(".checkAll").click(function() {
		var $this = $(this);
		var checkedStatus = $this.find('span').hasClass('checked');
		$(".emailTable tr .check input:checkbox").each(function() {
			this.checked = checkedStatus;
				if (checkedStatus == this.checked) {
					$(this).closest('.checker > span').removeClass('checked');
				}
				if (this.checked) {
					$(this).closest('.checker > span').addClass('checked');
				}
		});
	});

	//show compose form
	$('.composeBtn>.btn').click(function() {
		$('.email-content>.box.plain').fadeOut(200);
		$('.compose').fadeIn();
	});

	//close compose form on discard click
	$('#discard').click(function() {
		$('.compose').fadeOut(200);
		$('.email-content>.box.plain').fadeIn(300);
	});

	//save click event
	$('#save').click(function() {
		$('.compose').fadeOut(200);
		$('.email-content>.box.plain').fadeIn(300);
		$.pnotify({
			type: 'success',
		    title: 'Done',
    		text: 'Email is saved',
		    icon: 'picon icon16 iconic-icon-check-alt white',
		    opacity: 0.95,
		    history: false,
		    sticker: false
		});
		//save callback here
	});

	//on send msg click
	$('#send').click(function() {
		$('.compose').fadeOut(200);
		$('.email-content>.box.plain').fadeIn(300);
		//add some notification
		$.pnotify({
			type: 'success',
		    title: 'Done',
    		text: 'Email send successfull',
		    icon: 'picon icon16 iconic-icon-check-alt white',
		    opacity: 0.95,
		    history: false,
		    sticker: false
		});
		//calback function here
	});

	//on send msg click
	$('#backToInbox').click(function() {
		$('.read-email').fadeOut(200);
		$('.email-content>.box.plain').fadeIn(300);
		//calback function here
	});

	emailTable.find('td a.link').click(function() {
		$('.email-content>.box.plain').fadeOut(200);
		$('.read-email').fadeIn(300);
		//calback function here
	});


	//Boostrap modal
	$('#myModal').modal({ show: false});
	
	//add event to modal after closed
	$('#myModal').on('hidden', function () {
	  	console.log('modal is closed');
	})

});//End document ready functions
