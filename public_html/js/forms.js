// document ready function
$(document).ready(function() { 	

	//------------- Tags plugin  -------------//
	
	$("#tags").select2({
		tags:["red", "green", "blue", "orange"]
	});

	//------------- Elastic textarea -------------//
	if ($('textarea').hasClass('elastic')) {
		$('.elastic').elastic();
	}

	//------------- Input limiter -------------//
	if ($('textarea').hasClass('limit')) {
		$('.limit').inputlimiter({
			limit: 100
		});
	}

	//------------- Masked input fields -------------//
	$("#mask-phone").mask("(999) 999-9999", {completed:function(){alert("Callback action after complete");}});
	$("#mask-phoneExt").mask("(999) 999-9999? x99999");
	$("#mask-phoneInt").mask("+40 999 999 999");
	$(".mask-date").mask("hh:mm");
	$("#mask-ssn").mask("999-99-9999");
	$("#mask-productKey").mask("a*-999-a999", { placeholder: "*" });
	$("#mask-eyeScript").mask("~9.99 ~9.99 999");
	$("#mask-percent").mask("99%");

	//------------- I button  -------------//
	$(".ibutton").iButton({
		 labelOn: "ON",
		 labelOff: "OFF",
		 enableDrag: false
	});
	$(".ibutton1").iButton({
		 labelOn: "ONLINE",
		 labelOff: "OFFLINE",
		 enableDrag: false
	});
	$(".ibuttonCheck").iButton({
		 labelOn: "<span class='icon16 icomoon-icon-checkmark-2 white'></span>",
		 labelOff: "<span class='icon16 icomoon-icon-cancel-3 white'></span>",
		 enableDrag: false
	});

	//------------- Spinners with steps  -------------//
	if($('#ns_0').length) {
		$('#ns_0').stepper();
	}
	if($('#ns_1').length) {
		$('#ns_1').stepper({
			min:-100, 
			max:100, 
			step:10,
			start:-100
		});
	}
	if($('#ns_2').length) {
		$('#ns_2').stepper({
			step:0.1, 
			decimals:1
		});
	}
	if($('#ns_3').length) {
		$('#ns_3').stepper({
			step:0.5, 
			format:'currency'
		});
	}

	//------------- Colorpicker -------------//
	if($('div').hasClass('picker')){
		$('.picker').farbtastic('#color');
	}	
	//------------- Datepicker -------------//
	if($('#datepicker').length) {
		$("#datepicker").datepicker({
			showOtherMonths:true
		});
	}
	if($('#datepicker-inline').length) {
		$('#datepicker-inline').datepicker({
	        inline: true,
			showOtherMonths:true
	    });
	}

	//------------- Combined picker -------------//
	if($('#combined-picker, .combiner-picker').length) {
		$('#combined-picker, .combiner-picker').datetimepicker({
                    dateFormat: 'dd/mm/yy', 
                    timeFormat: 'hh:mm' 
                });
	}
	
    //------------- Time entry (picker) -------------//
	$('#timepicker').timeEntry({
		show24Hours: true,
		spinnerImage: ''
	});
	$('#timepicker').timeEntry('setTime', '22:15')

	//------------- Select plugin -------------//
	$("#select1").select2();
	$("#select2").select2();

	//--------------- Dual multi select ------------------//
	$.configureBoxes();

	//--------------- Tinymce ------------------//
	tinymce.init({
    selector: ".tinymce",
    theme: "modern",
    plugins: [
        "responsivefilemanager, fullscreen, advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor colorpicker textpattern"
    ],
    toolbar1: " filemanager, fullscreen,template,insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
    toolbar2: "| responsivefilemanager | print preview media | forecolor backcolor emoticons",
    mage_advtab: true ,
    external_filemanager_path:"/filemanager/",
   filemanager_title:"Responsive Filemanager" ,
   
    relative_urls: false,
    convert_urls: false,
    pagebreak_separator: "<div class='clearfix'></div>",
    style_formats: [
    {title: "Images", items: [
        {
         title: 'Align Left',
        selector: 'img',
        styles: {
            'float': 'left', 
            'margin': '0 10px 10px 0'
        }
        
                        },{
         title: 'Align Right',
        selector: 'img',
        styles: {
            'float': 'right', 
            'margin': '0 0 10px 10px'
        }
        
                        },
    ]},
     {title: "Headers", items: [
        {title: "Header 1", format: "h1"},
        {title: "Header 2", format: "h2"},
        {title: "Header 3", format: "h3"},
        {title: "Header 4", format: "h4"},
        {title: "Header 5", format: "h5"},
        {title: "Header 6", format: "h6"}
    ]},
    {title: "Inline", items: [
        {title: "Bold", icon: "bold", format: "bold"},
        {title: "Italic", icon: "italic", format: "italic"},
        {title: "Underline", icon: "underline", format: "underline"},
        {title: "Strikethrough", icon: "strikethrough", format: "strikethrough"},
        {title: "Superscript", icon: "superscript", format: "superscript"},
        {title: "Subscript", icon: "subscript", format: "subscript"},
        {title: "Code", icon: "code", format: "code"}
    ]},
    {title: "Blocks", items: [
        {title: "Paragraph", format: "p"},
        {title: "Blockquote", format: "blockquote"},
        {title: "Div", format: "div"},
        {title: "Pre", format: "pre"}
    ]},
    {title: "Alignment", items: [
        {title: "Left", icon: "alignleft", format: "alignleft"},
        {title: "Center", icon: "aligncenter", format: "aligncenter"},
        {title: "Right", icon: "alignright", format: "alignright"},
        {title: "Justify", icon: "alignjustify", format: "alignjustify"}
    ]}],
    valid_elements: "*[*]",
   
   external_plugins: { "filemanager" : "/filemanager/plugin.min.js"},
    
});

	//Boostrap modal
	$('#myModal').modal({ show: false});
	
	//add event to modal after closed
	$('#myModal').on('hidden', function () {
	  	console.log('modal is closed');
	})

});//End document ready functions
