jQuery.fn.edituploader = function(root_id,photo_dimension_name) {
        var element = $(this);
      
         $(element).delegate(".actionBtn .del", "click", function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                dataType: "json",
                url: '/admin/media/remove-photo/id/'+$(this).attr('rel'),
                data: data,
                success: function(resp) {
                    if(resp.status == "success") {
                        $("#uploader-gallery").html(resp.body);
                        $(element).trigger('update');
                    }
                }
            });
     }); 
      
      $(element).delegate(".actionBtn .move", "click", function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                dataType: "json",
                url: '/admin/media/move-photo/move/'+$(this).attr('move')+'/id/'+$(this).attr('rel'),
                data: data,
                success: function(resp) {
                    if(resp.status == "success") {
                        $("#uploader-gallery").html(resp.body);
                        $(element).trigger('update');
                    }
                }
            });
     }); 
      
        $('#uploader').on('change',function(){
            var files = $(this).prop('files');
            var counter = $('.preview-box').length;
            for(var i = 0;i<files.length;i++){
                 var urls;
                var reader = new FileReader();
                reader.onload = function (e) {
                    urls = e.target.result.split("<wpt ");
                    
                   
                }
                 
               
                
              reader.readAsDataURL(files[i]);
            }
            
            var formData =  new FormData($('form')[0]);
            $.ajax({
                url: '/admin/media/save-root-photos/root-id/'+root_id+'/photo-dimension/'+photo_dimension_name,
                type: 'POST',
                cache: false,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $("#output").html("Uploading, please wait....");
                },
                success: function (resp) { 
                   if(resp.status == "success") {
                        $("#uploader-gallery").html(resp.body);
                        $(element).trigger('update');
                    }
                },
                error: function () {
                    alert("ERROR in upload");
                }
            });
        
   });
   
   
   }