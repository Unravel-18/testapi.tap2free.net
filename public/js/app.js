$(document).ready(function(){ 
    $('.datepicker').datepicker({dateFormat: "yy-mm-dd", changeMonth: true, changeYear: true});
    
    $("#androids_form_search .param_search").change(function(){
        $("#androids_form_search").submit();
    });
    
    $('#files2').on('change', function() {
        $("#names_files2").empty();
        
        var files = this.files;
        
        for(var a=0;a<files.length;a++){
            $("#names_files2").append('<div>'+files[a].name+'</div>');
        }
    });
    
    $("#select_servers_all").click(function(){
        if($("#select_servers_all").prop('checked')){
            $(".select_servers").prop('checked', true);
        } else {
            $(".select_servers").prop('checked', false);
        }
    });
    
    $("#select_androids_all").click(function(){
        if($("#select_androids_all").prop('checked')){
            $(".select_androids").prop('checked', true);
        } else {
            $(".select_androids").prop('checked', false);
        }
    });    
    
    $("#del_androids_sel").click(function(){
        var androids_id = [];
        
        $(".select_androids").each(function() {
            if($(this).prop('checked')){
                androids_id.push($(this).attr('data-id'));
            }
        });
        
        if(androids_id.length && confirm('Подтвердите действие')){
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/apis/androids/del-select",
		    	data: {
		    	    androids_id: androids_id,              
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	    $("#status_before_del_androids_sel").show();
		    	    $("#status_success_del_androids_sel").hide();
		    	},
			    error: function (result) {
			    },
                success: function(result) {
		    	    $("#status_before_del_androids_sel").hide();
		    	    $("#status_success_del_androids_sel").show();
                    
                    for(var i in androids_id){
                        $("#tr_android_"+androids_id[i]).remove();
                    }
		    	},
	    	});
         }
    });
    
    $("#update_crt_sel").click(function(){
        var servers_id = [];
        
        $(".select_servers").each(function() {
            if($(this).prop('checked')){
                servers_id.push($(this).attr('data-id'));
            }
        });
        
        if(servers_id.length && confirm('Подтвердите действие')){
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/servers/copy-select",
		    	data: {
		    	    servers_id: servers_id,
                    new_api_id: $("#new_api_id_sel").val(),              
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	    $("#status_before_sel").show();
		    	    $("#status_success_sel").hide();
		    	},
			    error: function (result) {
			     //alert(JSON.stringify(result));
                 $('body').html(result['responseText']);
			    },
                success: function(result) {
		    	    $("#status_before_sel").hide();
		    	    $("#status_success_sel").show();
                    window.location.reload();
		    	},
	    	});
         }
    });
    
    $(".btn-click-confirm").click(function () {
        var $this = $(this);
        
        if(confirm('Подтвердите действие')){
            location = $this.attr('data-href');
        }
    });
    
    $(".message-delete").click(function(){
        var $this = $(this);
        
        if(confirm('Подтвердите действие')){
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/messages/message_delete",
		    	data: {
		    	    id: $this.attr('data-id'),                  
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {			     
			    },
                success: function(result) {                   
		    	    if(result.status) {
		    	        $this.parent().parent().remove();
		    	    }
		    	},
	    	});
         }
    });
    
    $(".openMessageModal").click(function(){
        var $this = $(this);
        
        $("#messageModalBody").html('...');
        
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/messages/message",
		    	data: {
		    	    id: $this.attr('data-id'),                  
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {			     
			    },
                success: function(result) {                   
		    	    if(result.message) {
		    	        $("#messageModalBody").html(result.message.text);
                        $("#messageModal").modal('show');
                        $this.parent().parent().find(".inpt-status-message").prop('checked',true);
		    	    }
		    	},
	    	});
    });
    $(".inpt-status-message").change(function(){
        var $this = $(this);
                
        $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/messages/set_status",
		    	data: {
		    	    id: $this.attr('data-id'),  
                    status: $this.prop('checked')?1:0                
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {			     
			    },
                success: function(result) {                 
		    	},
	    	});
    });
    
    $('.inpt-id').change(function(){
        var $this = $(this);
        
        if($(this).val() != $(this).attr('data-id') && confirm('Подтвердите действие')){
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/servers/update-id",
		    	data: {
		    	    id: $this.attr('data-id'),
                    new_id: $this.val()                    
                },
                type: 'POST',
                dataType: 'json',
		    	beforeSend: function () {
		    	},
			    error: function (result) {
			        $this.val($this.attr('data-id'));
			    },
                success: function(result) {
		    	    if(result.status) {
		    	        $this.attr('data-id', $this.val());
		    	    } else {
		    	        $this.val($this.attr('data-id'));
		    	    }
		    	},
	    	});
        }
    });
    
    $("#update_crt").click(function(){
        if(confirm('Действительно ли хотите обновить?')){
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/servers/update_crt",
		    	data: {
                },
                type: 'GET',
                dataType: 'json',
		    	beforeSend: function () {
		    	    $("#status_before").show();
		    	    $("#status_success").hide();
		    	},
			    error: function (result) {
			    },
                success: function(result) {
		    	    $("#status_before").hide();
		    	    $("#status_success").show();
                    
                    $("#message").empty();
                    
                    
                    
                    if (result.response['ca.crt']) {
                        $("#message").append('<div>ca.crt: '+result.response['ca.crt']+'<div>');
                    }
                    if (result.response['client1.crt']) {
                        $("#message").append('<div>client1.crt: '+result.response['client1.crt']+'<div>');
                    }
                    if (result.response['client1.key']) {
                        $("#message").append('<div>client1.key: '+result.response['client1.key']+'<div>');
                    }
                    if (result.response['server-conf.txt']) {
                        $("#message").append('<div>server-conf.txt: '+result.response['server-conf.txt']+'<div>');
                    }
                    if (result.response['ovpn']) {
                        $("#message").append('<div>ovpn: '+result.response['ovpn']+'<div>');
                    }
                    
                    if (result.response['ips']) {
                        var ips = [];
                        
                        for (var i in result.response['ips']) {
                            ips.push(result.response['ips'][i]);
                        }
                        
                        $("#message").append('<div>ips: '+ips.join(', ')+'<div>');
                    }
		    	},
	    	});
        }
    })
    
    $(".btn-delete").click(function(){
        if(confirm('Подтвердите действие')){
            $(this).parent().submit();
        }
    });
    
    $('#files').on('change', function() {
        $("#names_files").empty();
        
        var files = this.files;
        
        for(var a=0;a<files.length;a++){
            $("#names_files").append('<div>'+files[a].name+'</div>');
        }
    });
    
    $('#inpt_img_flag').on('change', function(){
		var data;

		data = new FormData();
		data.append('img_flag', $('#inpt_img_flag')[0].files[0]);
		data.append('_token', $('[name="_token"]').val());
        data.append('id', $("#id").val());
		
		$.ajax({
			url: '/upload_flag',
			data: data,
			processData: false,
			contentType: false,
			type: 'POST',
			dataType: 'json',
			success: function(result) {
				if (result.filelink) {
				    $("#upload_img_flag").html('<div class="block-img-upload" data-id="'+result.id+'"><div><span class="glyphicon glyphicon-remove" onclick="deleteImgFlag(this)"></span></div><a href="'+result.filelink+'"><img src="'+result.filelink+'" style="max-width:200px;" /></a></div>');
					$('#error_img_flag').hide();
                    $('#img_flag_f').val(result.name);
				} else {
					$('#error_img_flag').text(result.message);
					$('#error_img_flag').show();
				}
			},
			error: function (result) {
				$('#error_img_flag').text("Upload impossible");
				$('#error_img_flag').show();
			}
		});
	});
    
    $('#inpt_img_map').on('change', function(){
		var data;

		data = new FormData();
		data.append('img_map', $('#inpt_img_map')[0].files[0]);
		data.append('_token', $('[name="_token"]').val());
        data.append('id', $("#id").val());
		
		$.ajax({
			url: '/upload_map',
			data: data,
			processData: false,
			contentType: false,
			type: 'POST',
			dataType: 'json',
			success: function(result) {
				if (result.filelink) {
				    $("#upload_img_map").html('<div class="block-img-upload" data-id="'+result.id+'"><div><span class="glyphicon glyphicon-remove" onclick="deleteImgMap(this)"></span></div><a href="'+result.filelink+'"><img src="'+result.filelink+'" style="max-width:200px;" /></a></div>');
					$('#error_img_map').hide();
                    $('#img_map_f').val(result.name);
				} else {
					$('#error_img_map').text(result.message);
					$('#error_img_map').show();
				}
			},
			error: function (result) {
				$('#error_img_map').text("Upload impossible");
				$('#error_img_map').show();
			}
		});
	});
});

function deleteImgFlag(obj){
    var obj_block = $(obj).parent().parent();
    
    if(obj_block && obj_block.hasClass('block-img-upload')) {
        if(obj_block.attr('data-id')) {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/upload_flag/ajax-delete",
		    	data: {
		    	   id: obj_block.attr('data-id')
                },
                type: 'POST',
                dataType: 'json',
		    	success: function(result) {
			        if(result.status == 1){
			            obj_block.remove();
                        $("#inpt_img_flag").val('');
			        } 
		    	},
			    error: function (result) {
			    }
	    	});
      } else {
          obj_block.remove();
      }
    }
}

function deleteImgMap(obj){
    var obj_block = $(obj).parent().parent();
    
    if(obj_block && obj_block.hasClass('block-img-upload')) {
        if(obj_block.attr('data-id')) {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken },
		    	url: "/upload_map/ajax-delete",
		    	data: {
		    	   id: obj_block.attr('data-id')
                },
                type: 'POST',
                dataType: 'json',
		    	success: function(result) {
			        if(result.status == 1){
			            obj_block.remove();
                        $("#inpt_img_map").val('');
			        } 
		    	},
			    error: function (result) {
			    }
	    	});
      } else {
          obj_block.remove();
      }
    }
}