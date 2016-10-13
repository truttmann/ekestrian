var list_bp_select = [];

$(document).ready(function() {    
    initEvent();
    initBpEvent();

    $.fn.datepicker.dates['en'] = {
        days: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        daysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
        daysMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
        months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        monthsShort: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Jui', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        today: "Aujourd\'hui",
        clear: "Vider"
    };

    $('.date_newsletter').datepicker({
        rtl: App.isRTL(),
        format: 'dd/mm/yyyy',
        language: "fr",
        autoclose: true
    });
});
function initEvent(){
    $('.tools .config').unbind('click').on('click', function(event) {
        event.preventDefault();
        var id_bloc = $(this).parent().parent().parent().attr("id");
        var title_bloc = $(this).parent().parent().find(".caption").text().trim();
        $('#portlet-config #bloc_title2').val(title_bloc);
        $('#portlet-config input[name="bloc_id"]').val(id_bloc);
        initEvent();
    });

    $('#portlet-config button[name="save"]').unbind('click').on('click', function(){
        var title = $('#portlet-config #bloc_title2').val();
        var id = $('#portlet-config input[name="bloc_id"]').val();
        if(title != "") {
            if(id != "") {
                var input_name = $('#'+id+' .caption input').attr('name');
                $('#'+id+' .caption').html('<i class="fa fa-cogs"></i>'+title+'<input type="hidden" name="'+input_name+'" value="'+title.replace('"', '&quot;')+'" />');
                $('#portlet-config button[name="close"]').trigger('click');
                initEvent();
            }
        }
    });

    $('a[href="#basic"]').unbind('click').on('click', function(){
        $('#basic #bloc_title').val('');
        $('#basic #bloc_copie_bloc').empty().append('<option value=""></option>');
        for (var i = 0; i < listblocexistant.length; i++) {
            $('#basic #bloc_copie_bloc').append('<option value="'+listblocexistant[i].id+'">'+listblocexistant[i].label+'</option>');
        };
        initEvent();
    })

    $('#basic button[name="save"]').unbind('click').on('click', function(){
        var title = $('#basic #bloc_title').val();
        var id_bloc = $('#basic #bloc_copie_bloc').val();
        var idnewbloc = Math.floor((Math.random() * 1000) + 1);
        if(title != "") {
            $('#div_general_bloc').append('<div class="portlet box grey"  id="port_new_'+idnewbloc+'">'+
                    '<div class="portlet-title">'+
                        '<div class="caption">'+
                            '<i class="fa fa-cogs"></i>'+title+'<input type="hidden" name="title_bloc_new_'+idnewbloc+'" value="'+title+'" />'+
                        '</div>'+
                        '<div class="tools">'+
                            '<a href="javascript:;" class="collapse"></a>'+
                            '<a href="#portlet-config" data-toggle="modal" class="config"></a>'+
                            '<a href="javascript:" class="add"></a>'+
                            '<a href="javascript:" class="remove"></a>'+
                        '</div>'+
                    '</div>'+
                    '<div class="portlet-body"><div class="row bloc_general"></div></div>'+
                '</div>');
            $('#basic button[name="close"]').click();
            initEvent();
        } else if(id_bloc != "") {
            // appel ajax pour récuperer les sous bloc de ce bloc
            $.ajax({ 
                url: urlAjaxBloc+'/'+id_bloc,
                data: {},
                dataType: 'json',
                type: "GET",
                success : function(data) {
                    var idnewbloc = Math.floor((Math.random() * 1000) + 1);
                    var chaine = '<div class="portlet box grey"  id="port_new_'+idnewbloc+'">'+
                        '<div class="portlet-title">'+
                            '<div class="caption">'+
                                '<i class="fa fa-cogs"></i>'+data.title+'<input type="hidden" name="title_bloc_new_'+idnewbloc+'" value="'+data.title+'" />'+
                            '</div>'+
                            '<div class="tools">'+
                                '<a href="javascript:;" class="collapse"></a>'+
                                '<a href="#portlet-config" data-toggle="modal" class="config"></a>'+
                                '<a href="javascript:" class="add"></a>'+
                                '<a href="javascript:" class="remove"></a>'+
                            '</div>'+
                        '</div>'+
                        '<div class="portlet-body"><div class="row bloc_general">';
                    var list_id = new Array();
                    for (var i = 0; i < data.listBloc.length; i++) {
                        var id = Math.floor((Math.random() * 1000) + 1);
                        chaine += '<div class="row" id="bloc_ex_new_'+idnewbloc+'_new_'+id+'">'+
                            '<div class="col-md-2 bloc-img">'+
                                '<div class="fileinput fileinput-new" data-provides="fileinput">'+
                                    '<input type="hidden" value="'+(( data.listBloc[i].photo_path != null)?data.listBloc[i].photo_path:null)+'" name="img_bloc_'+idnewbloc+'_'+id+'">'+
                                    '<div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">'+
                                        '<img src="'+(( data.listBloc[i].photo_path != null)?('/'+data.listBloc[i].photo_path):"http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image")+'" alt=""/>'+
                                    '</div>'+
                                    '<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;">'+
                                    '</div>'+
                                    '<div>'+
                                        '<span class="btn default btn-file">'+
                                            '<span class="fileinput-new">'+
                                                 'Select image'+
                                            '</span>'+
                                            '<span class="fileinput-exists">'+
                                                 'Change'+
                                            '</span>'+
                                            '<input type="file" name="file_bloc_'+idnewbloc+'_'+id+'" value="'+(( data.listBloc[i].photo_path != null)?data.listBloc[i].photo_path:null)+'" >'+
                                        '</span>'+
                                        '<a href="#" class="btn default fileinput-exists" data-dismiss="fileinput">'+
                                             'Remove'+
                                        '</a>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-10 blog-article">'+
                                '<div class="form-group">'+
                                    '<label class="col-md-2">Titre</label>'+
                                    '<div class="col-md-10">'+
                                        '<input type="text" class="form-control" placeholder="Enter text" name="title_bloc_'+idnewbloc+'_new_'+id+'" value="'+data.listBloc[i].title+'">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="form-group">'+
                                    '<label class="col-md-2">Sous-Titre</label>'+
                                    '<div class="col-md-10">'+
                                        '<input type="text" class="form-control" placeholder="Enter text" name="subtitle_bloc_'+idnewbloc+'_new_'+id+'" value="'+data.listBloc[i].subtitle+'">'+
                                    '</div>'+
                                '</div>'+
                                '<div class="form-group">'+
                                    '<label class="col-md-2">Description</label>'+
                                    '<div class="col-md-10">'+
                                        '<textarea class="ckeditor form-control" rows="3" id="ckeditor_'+id+'" name="desc_bloc_'+idnewbloc+'_new_'+id+'" >'+data.listBloc[i].description+'</textarea>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="right">'+
                                '<button type="button" class="btn default delete">Supprimer</button>'+
                            '</div>'+
                        '</div>'+
                        '<hr>';

                        list_id.push('ckeditor_'+id);
                    };
                    chaine += '</div></div></div>';
                    $('#div_general_bloc').append(chaine);

                    for (var i = 0; i < list_id.length; i++) {
                        CKEDITOR.replace( list_id[i] );
                    };

                    $('#basic button[name="close"]').click();
                    initEvent();
                },
                error: function(data) {
                    alert("Impossible de charger la liste des d&eacute;partements");   
                }
            });
        }
    });

    $('.tools .remove').unbind('click').on('click', function(event){
        event.preventDefault();
        $(this).parent().parent().parent().remove();
    });

    $('.delete').unbind('click').on('click', function(event) {
        $(this).parent().parent().next().remove();
        $(this).parent().parent().remove();
    });

    $('.tools .add').unbind('click').on('click', function(event){
        event.preventDefault();
        var idParent = $(this).parent().parent().parent().attr("id");
            idParent = idParent.substring(idParent.lastIndexOf('_')+1);
        var id = Math.floor((Math.random() * 1000) + 1);
        
        $(this).parent().parent().parent().find('div.bloc_general').append(''+
            '<div class="row" id="bloc_ex_new_'+idParent+'_new_'+id+'">'+
                '<div class="col-md-2 bloc-img">'+
                    '<div class="fileinput fileinput-new" data-provides="fileinput">'+
                        '<div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">'+
                            '<img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>'+
                        '</div>'+
                        '<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;">'+
                        '</div>'+
                        '<div>'+
                            '<span class="btn default btn-file">'+
                                '<span class="fileinput-new">'+
                                     'Select image'+
                                '</span>'+
                                '<span class="fileinput-exists">'+
                                     'Change'+
                                '</span>'+
                                '<input type="file" name="file_bloc_'+idParent+'_'+id+'">'+
                            '</span>'+
                            '<a href="#" class="btn default fileinput-exists" data-dismiss="fileinput">'+
                                 'Remove'+
                            '</a>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="col-md-10 blog-article">'+
                    '<div class="form-group">'+
                        '<label class="col-md-2">Titre</label>'+
                        '<div class="col-md-10">'+
                            '<input type="text" class="form-control" placeholder="Enter text" name="title_bloc_'+idParent+'_new_'+id+'" >'+
                        '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                        '<label class="col-md-2">Sous-Titre</label>'+
                        '<div class="col-md-10">'+
                            '<input type="text" class="form-control" placeholder="Enter text" name="subtitle_bloc_'+idParent+'_new_'+id+'" >'+
                        '</div>'+
                    '</div>'+
                    '<div class="form-group">'+
                        '<label class="col-md-2">Description</label>'+
                        '<div class="col-md-10">'+
                            '<textarea class="ckeditor form-control" rows="3" id="ckeditor_'+id+'" name="desc_bloc_'+idParent+'_new_'+id+'" ></textarea>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="right">'+
                    '<button type="button" class="btn default delete">Supprimer</button>'+
                '</div>'+
            '</div>'+
        '<hr>');
        CKEDITOR.replace( 'ckeditor_'+id );
        initEvent();
    });

    $('.page-title .save').unbind('click').on('click', function(event){
        event.preventDefault();
        $('form').submit();
    });
}

function initBpEvent() {
    $('.tools .add_gp').unbind('click').on('click', function(event){
        event.preventDefault();
        $('#select_bp_departement').empty();
        $('#select_bp').empty();

        $.ajax({ 
            url: urlAjaxDept,
            data: {
                date : $('input[name="date"]').val()
            },
            dataType: 'json',
            type: "POST",
            success : function(data) {
                if(data.status == 1) {
                    $('#select_bp_departement').append('<option value=""></option>');
                    for (var i = 0;i < data.data.length; i++) {
                        $('#select_bp_departement').append('<option value="'+data.data[i].id+'">'+data.data[i].label_origin+'</option>')
                    };
                    initBpEvent();
                } else {
                    alert(data.message);
                }
            },
            error: function(data) {
                alert("Impossible de charger la liste des d&eacute;partements");   
            }
        });
    });

    $('#select_bp_departement').unbind('change').on('change', function() {
        $('#select_bp').empty();
        if($('#select_bp_departement').val() != "") {
            $.ajax({ 
                url: urlAjaxGp,
                data: {
                    date : $('input[name="date"]').val(),
                    dept: $('#select_bp_departement').val(),
                },
                dataType: 'json',
                type: "POST",
                success : function(data) {
                    if(data.status == 1) {
                        $('#select_bp').append('<option value=""></option>');
                        list_bp_select = data.data;
                        for (var i = 0;i < data.data.length; i++) {
                            $('#select_bp').append('<option value="'+data.data[i].id+'">'+data.data[i].title_origin+'</option>')
                        };
                        
                    } else {
                        alert(data.message);
                    }
                },
                error: function(data) {
                    alert("Impossible de charger la liste des d&eacute;partements");   
                }
            });
        }
    });

    $('#addBonPLan button[name="save"]').unbind('click').on('click', function(event) {
        event.preventDefault();
        var deptid = $('#select_bp_departement').val();
        var bpid = $('#select_bp').val();
        var description = "";
        var titre = $('#select_bp option:selected').text();

        for (var i = 0; i < list_bp_select.length ; i++) {
            if(list_bp_select[i].id == bpid) {
                description = list_bp_select[i].content_origin.substring(0,40)+'...';
                titre = list_bp_select[i].title_origin;
                break;
            }
        };

        $('#tab_bp').append('<tr><td>'+$('#select_bp_departement option:selected').text()+'</td><td><input type="hidden" name="bp[]" value="'+bpid+'" />'+titre+'</td><td>'+description+'</td></td><td><a class="delete" href="javascript:;">Suppression</a></td></tr>');
        $('#addBonPLan button[name="close"]').trigger('click'); 
        initBpEvent();      
    });
}