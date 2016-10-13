/**
 * Created by rco on 19/06/14.
 */
$(function() {

    /** gestion du menu prncipale **/
    if($('.page-sidebar-menu')){
        $('ul.page-sidebar-menu li').each(function(index, item){
            if($(item).hasClass('active')) {
                if($(item).parent().parent().prop("tagName") == 'LI'){
                    $(item).parent().parent().addClass('active')
                }
            }
        });
    }

    // suppression d'une entreé dans une liste
    if($('.delete_prompt')){
        $('.delete_prompt').each(function(index, item){
            $(item).click(function(event){

                bootbox.dialog({
                    message: "Etes-vous sûr de vouloir supprimer cette entrée ?",
                    buttons: {
                        yes: {
                            label: 'Oui',
                            className: 'btn-success',
                            callback: function() {
                                window.location = $(item).attr("href");
                            }
                        },
                        no: {
                            label: 'Non',
                            className: 'btn-danger'
                        }
                    }
                });

                event.preventDefault();
                return false;
            });
        });
    }

    // suppression d'une entreé dans une liste
    if($('.delete_prompt_contract_logo_partner')){
        $('.delete_prompt_contract_logo_partner').each(function(index, item){
            $(item).click(function(event){

                bootbox.dialog({
                    message: "Etes-vous sûr de vouloir supprimer cette entrée ? (Attention, la suppression de la liaison camping / logo sera effectuée pour toutes les langues)",
                    buttons: {
                        yes: {
                            label: 'Oui',
                            className: 'btn-success',
                            callback: function() {
                                window.location = $(item).attr("href");
                            }
                        },
                        no: {
                            label: 'Non',
                            className: 'btn-danger'
                        }
                    }
                });

                event.preventDefault();
                return false;
            });
        });
    }

    /***  LISTE ***/
    if($('.filter-cancel')){
        $('.filter-cancel').each(function(index, item){
            $(item).click(function(event){
                event.preventDefault();
                $('#reset-filter').val('1');
                $('#datatable_ajax_wrapper').submit();
            });
        });
    }



    if($('.select-item-per-page')){
        $('.select-item-per-page').each(function(index, item){
            $(item).live("change keyup", function (event) {
                event.preventDefault();
                var selected = $(item).val();
                // toutes les paginations doivent avoir la meme valeur
                $('.select-item-per-page').each(function(index, item2){
                    $(item2).val(selected);
                });

                this.form.submit();
            });
        });
    }



});

