// document ready function
$(document).ready(function() { 	

	//--------------- Tabs ------------------//

    $('#myTab a').click(function (e) {
	  	e.preventDefault();
	  	$(this).tab('show');
	})
    //activate loaders tabs
	$('#myTabLoaders a').click(function (e) {
	  	e.preventDefault();
	  	$(this).tab('show');
	})

    //make 2 tab active ( remove if not want )
	$('.tabs-right li:eq(1) a').tab('show'); // Select third tab (0-indexed)
	$('.tabs-left li:eq(1) a').tab('show'); // Select third tab (0-indexed)

	$('#loadersTab a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	})

	//--------------- Accordion ------------------//
    var acc = $('.accordion'); //get all accordions
    var accHeading = acc.find('.accordion-heading');
	var accBody = acc.find('.accordion-body');

	//function to put icons
	accPutIcon = function () {
		acc.each(function(index) {
		   accExp = $(this).find('.accordion-body.in');
		   accExp.prev().find('a.accordion-toggle').append($('<span class="icon12 entypo-icon-minus-2 gray"></span>'));

		   accNor = $(this).find('.accordion-body').not('.accordion-body.in');
		   accNor.prev().find('a.accordion-toggle').append($('<span class="icon12 entypo-icon-plus-2 gray"></span>'));


		});
	}

	//function to update icons
	accUpdIcon = function() {
		acc.each(function(index) {
		   accExp = $(this).find('.accordion-body.in');
		   accExp.prev().find('span').remove();
		   accExp.prev().find('a.accordion-toggle').append($('<span class="icon12 entypo-icon-minus-2 gray"></span>'));

		   accNor = $(this).find('.accordion-body').not('.accordion-body.in');
		   accNor.prev().find('span').remove();
		   accNor.prev().find('a.accordion-toggle').append($('<span class="icon12 entypo-icon-plus-2 gray"></span>'));


		});
	}

	accPutIcon();

	$('.accordion').on('shown', function () {
		accUpdIcon();
	}).on('hidden', function () {
		accUpdIcon();
	})

	//--------------- Sliders ------------------//
	//simple slider
	$( "#slider" ).slider(); 
	//with 50 range
	$( "#slider1" ).slider({
		range: "min",
		value:100,
		min: 1,
		max: 500,
		step: 50,
		slide: function( event, ui ) {
			$( "#amount" ).val( "$" + ui.value );
		}
	});
	$( "#amount" ).val( "$" + $( "#slider" ).slider( "value" ) );
	//range slider
	$( "#slider-range" ).slider({
		range: true,
		min: 0,
		max: 500,
		values: [ 75, 300 ],
		slide: function( event, ui ) {
			$( "#amount1" ).val( "Price range: $" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
		}
	});
	$( "#amount1" ).val( "Price range: $" + $( "#slider-range" ).slider( "values", 0 ) +
		" - $" + $( "#slider-range" ).slider( "values", 1 ) );

	//with minimum
	$( "#slider-range-min" ).slider({
		range: "min",
		value: 37,
		min: 1,
		max: 700,
		slide: function( event, ui ) {
			$( "#amount2" ).val( "Maximum price: $" + ui.value );
		}
	});
	$( "#amount2" ).val( "Maximum price: $" + $( "#slider-range-min" ).slider( "value" ) );
	//with maximum
	$( "#slider-range-max" ).slider({
		range: "max",
		min: 1,
		max: 10,
		value: 2,
		slide: function( event, ui ) {
			$( "#amount3" ).val("Minimum number of bedrooms:" + ui.value );
		}
	});
	$( "#amount3" ).val( "Minimum number of bedrooms:" + $( "#slider-range-max" ).slider( "value" ) );

	//vertical sliders
	$( "#eq > span" ).each(function() {
		// read initial values from markup and remove that
		var value = parseInt( $( this ).text(), 10 );
		$( this ).empty().slider({
			value: value,
			range: "min",
			animate: true,
			orientation: "vertical"
		});
	});

	//--------------- Progress bars ------------------//
	$( "#progressbar" ).progressbar({
		value: 37
	});

	//animated progress bar
	$('#progress1').anim_progressbar();

	// from second #5 till 15
    var iNow = new Date().setTime(new Date().getTime() + 5 * 1000); // now plus 5 secs
    var iEnd = new Date().setTime(new Date().getTime() + 15 * 1000); // now plus 15 secs
    $('#progress2').anim_progressbar({start: iNow, finish: iEnd, interval: 100});

    // we will just set interval of updating to 2 sec
    $('#progress3').anim_progressbar({interval: 2000});

	//circular progrress bar
	$(function () {

		$(".greenCircle").knob({
            'min':0,
            'max':100,
            'readOnly': true,
            'width': 80,
            'height': 80,
            'fgColor': '#9FC569',
            'dynamicDraw': true,
            'thickness': 0.2,
            'tickColorizeValues': true
        })
        $(".redCircle").knob({
            'min':0,
            'max':100,
            'readOnly': true,
            'width': 80,
            'height': 80,
            'fgColor': '#ED7A53',
            'dynamicDraw': true,
            'thickness': 0.2,
            'tickColorizeValues': true
        })
        $(".blueCircle").knob({
            'min':0,
            'max':100,
            'readOnly': true,
            'width': 80,
            'height': 80,
            'fgColor': '#88BBC8',
            'dynamicDraw': true,
            'thickness': 0.2,
            'tickColorizeValues': true
        })

	});

    //--------------- Dialogs ------------------//
	$('#openDialog').click(function(){
		$('#dialog').dialog('open');
		return false;
	});

	$('#openModalDialog').click(function(){
		$('#modal').dialog('open');
		return false;
	});

	// JQuery Dialog			
	$('#dialog').dialog({
		autoOpen: false,
		dialogClass: 'dialog',
		buttons: {
			"Close": function() { 
				$(this).dialog("close"); 
			}
		}
	});

	// JQuery UI Modal Dialog			
	$('#modal').dialog({
		autoOpen: false,
		modal: true,
		dialogClass: 'dialog',
		buttons: {
			"Close": function() { 
				$(this).dialog("close"); 
			}
		}
	});

	$("div.dialog button").addClass("btn");

	//Boostrap modal
	$('#myModal').modal({ show: false});
	//add event to modal after closed
	$('#myModal').on('hidden', function () {
	  	$.pnotify({
		    title: 'Modal',
		    text: 'Modal window is closed',
		    icon: 'picon icon16 entypo-icon-warning white',
		    opacity: 0.95,
		    sticker: false,
		    history: false
		});
	})

	//--------------- Popovers ------------------//
	//using data-placement trigger
	$("a[rel=popover]")
      .popover()
      .click(function(e) {
        e.preventDefault()
     })

    //using js trigger
    $("a[rel=popoverTop]")
      .popover({placement: 'top'})
      .click(function(e) {
        e.preventDefault()
     })


    //--------------- Pines notify  ------------------//

    //regular notice
    $('#noticeR').click(function(){
		$.pnotify({
		    title: 'Regular Notice',
		    text: 'Check me out! I\'m a notice.',
		    icon: 'picon icon16 entypo-icon-warning white',
		    opacity: 0.95,
		    sticker: false,
		    history: false
		});
	});

	//Sticky notice
    $('#noticeS').click(function(){
		$.pnotify({
		    title: 'Sticky Notice',
		    text: 'Check me out! I\'m a sticky notice. You\'ll have to close me yourself.',
		    hide: false,
		    icon: 'picon icon16 entypo-icon-warning white',
		    opacity: 0.95,
		    history: false,
		    sticker: false
		});
	});

	//Regular info
    $('#infoR').click(function(){
		$.pnotify({
			type: 'info',
		    title: 'New Thing',
    		text: 'Just to let you know, something happened.',
		    icon: 'picon icon16 brocco-icon-info white',
		    opacity: 0.95,
		    history: false,
		    sticker: false
		});
	});

	//Sticky info
    $('#infoS').click(function(){
		$.pnotify({
			type: 'info',
		    title: 'Sticky Info',
   			text: 'Sticky info, you know, like a newspaper covered in honey.',
		    icon: 'picon icon16 brocco-icon-info white',
		    hide: false,
		    opacity: 0.95,
		    history: false,
		    sticker: false
		});
	});

	//Regular success
    $('#successR').click(function(){
		$.pnotify({
			type: 'success',
		    title: 'Regular Success',
    		text: 'That thing that you were trying to do worked!',
		    icon: 'picon icon16 iconic-icon-check-alt white',
		    opacity: 0.95,
		    history: false,
		    sticker: false
		});
	});

	//Sticky success
    $('#successS').click(function(){
		$.pnotify({
			type: 'success',
		    title: 'Sticky Success',
    		text: 'Sticky success... I\'m not even gonna make a joke.',
		    icon: 'picon icon16 iconic-icon-check-alt white',
		    opacity: 0.95,
		    hide:false,
		    history: false,
		    sticker: false
		});
	});

	//Regular success
    $('#errorR').click(function(){
		$.pnotify({
			type: 'error',
		    title: 'Oh No!',
    		text: 'Something terrible happened.',
		    icon: 'picon icon24 typ-icon-cancel white',
		    opacity: 0.95,
		    history: false,
		    sticker: false
		});
	});

	//Sticky success
    $('#errorS').click(function(){
		$.pnotify({
			type: 'error',
		    title: 'Oh No!',
    		text: 'Something terrible happened.',
		    icon: 'picon icon24 typ-icon-cancel white',
		    opacity: 0.95,
		    hide:false,
		    history: false,
		    sticker: false
		});
	});
	
	//--------------- Boostrap tooltips ------------------//
    $('.btip').tooltip();

    	
	//Boostrap modal
	$('#myModal').modal({ show: false});
	
	//add event to modal after closed
	$('#myModal').on('hidden', function () {
	  	console.log('modal is closed');
	})

});//End document ready functions
