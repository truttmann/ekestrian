/**
 * Created by rco on 19/06/14.
 */
function BrcContract(search_base_url_contract, url_autocompletion){

    this.search_base_url = search_base_url_contract;
    this.url_autocompletion = url_autocompletion;

    this.construct = function(){

        this.initializeButton();
        this.initializeAutocompletion();
        this.reloadContractTypeSelect();
    };

    this.initializeButton = function(){

        if(!$('#contract_type').hasClass('disabled')){
            $('#contract_type').attr('disabled', false);
        }


        $('#contract-partner').click(function(event){
            this.reloadContractTypeSelect();
            this.reloadFormHead();
        }.bind(this));

        $('#contract-camping').click(function(event){
            this.reloadContractTypeSelect();
            this.reloadFormHead();
        }.bind(this));

        // on recharge le form head si différent

        $('#contract_type').change(function(event){
            event.preventDefault();
            this.reloadFormHead();
        }.bind(this));

        var i = 0;
        $('#add-camping').click(function(event){
            event.preventDefault();
            i++;

            var el = $('<div/>',{
                'class': 'form-group',
                style:'position:relative;'
            });
            var label = $('<label/>',{
                'class': 'col-md-2 control-label',
                text:''
            }).appendTo(el);

            var div = $('<div/>',{
                'class': 'col-md-10 input-group',
                text:''
            });

            var input_1 = $('<input/>',{
                'class': 'camping-autocomplete form-control required',
                id:"camping-autocomplete-label-" + i,
                type:"text",
                text:''
            }).attr('autocomplete','off').appendTo(div);

            var input_2 = $('<input/>',{
                'class': 'camping-autocomplete-id required',
                id:"camping-autocomplete-id-" + i,
                type:"hidden",
                name:'camping_ids[]'
            }).appendTo(div);


            var span_2 = $('<span/>',{
                'class':"input-group-btn"
            });

            var button_2 = $('<button/>',{
                'class':'btn btn-danger date-reset',
                type:"button",
                onclick:"$(this).parent().parent().parent().remove()"
            });

            var icon = $('<i/>',{
                'class':"fa fa-times"
            }).appendTo(button_2);

            button_2.appendTo(span_2);

            span_2.appendTo(div);

            div.appendTo(el);

            var div_2 = $('<div/>',{
                'id': 'camping-container-' + i,
                'class':'camping-container'

            }).appendTo(el);

            el.insertAfter('#camping-list');

            var brc_auto = new BrcAutoCompletion('camping-autocomplete-label-'+i, 'camping-container-'+i, this.url_autocompletion, 'camping-autocomplete-id-'+i);
            brc_auto.construct();

        }.bind(this));
    };

    this.getCurrentRecipient = function(){

        if($('#contract-camping').prop('checked')) return $('#contract-camping').val();

        if($('#contract-partner').prop('checked')) return $('#contract-partner').val();

        return null;
    };

    this.getCurrentTypeId = function(){
        return $('#contract_type').val();
    };
    /**
     * le client provient d'une fiche camping, on regarde alors l id courant
     * on vérifie ensuite si le camping est bien dans la liste auto complete
     * si tel est le cas on renvoit $('#current_camping_id').val() sinon null
     * => le client a changé de camping au cours de l'édition
     */
    this.getCurrentCampingId = function(){

        var camping_id = null;

        if($('#current_camping_id').val()){
            $('.camping-autocomplete-id').each(function(index, item){

                if($(item).val() == $('#current_camping_id').val()){
                    camping_id = $('#current_camping_id').val()
                }
            })
        }

        return camping_id;
    };

    this.reloadContractTypeSelect = function(){

        var recipient = this.getCurrentRecipient();
        var current_package_product_id = $('#pakage_product_id').val();

        if(recipient){
            this.loader($('#contract_type').parent().find('div.btn-group'));
            var request = $.ajax({
                type: "POST",
                url:  this.search_base_url + '/contract_per_recipient',
                data: {'recipient':recipient, 'current_package_product_id':current_package_product_id},
                success: function(data){
                    var result = $.parseJSON(data);

                    $('#contract_type').find('option').remove();

                    // creation des options pour les types de contrats
                    var option = $('<option/>', {
                        value: '',
                        text:'Choisissez un type de contrat'
                    });

                    $('#contract_type').append(option);

                    $(result).each(function(index, item){

                        var option = $('<option/>', {
                            value: item.id,
                            text: item.label,
                            selected:item.is_selected
                        });

                        option.click(function(event){
                            event.preventDefault();
                            this.reloadFormHead();
                        }.bind(this));

                        $('#contract_type').append(option);

                        $('.date-picker').datepicker({
                            rtl: App.isRTL(),
                            autoclose: true
                        });

                        /**
                         * a chaque changement de date start on met a jour date end
                         */
                        $('.date_start').change(function() {
                            var date_start = $(this).val();
                            var data = date_start.split("/");

                            var day = data[0];
                            var month = data[1];
                            var year = data[2];
                            year++;
                            if(day > 1){
                                day--;
                            }else{
                                day = 30;
                            }

                            $(this).next().next().val(day + '/' + month +  '/' + year);
                        });



                    }.bind(this));
                    $('.selectpicker').selectpicker('refresh');
                    $('#contract_type').selectpicker('val', current_package_product_id);
                    this.unLoader($('#contract_type').parent().find('div.btn-group'));
                }.bind(this)
            });
        }else{
            // alert('Une erreur est survenue');
        }
    };

    /**
     * recupere les champs de formulaires en plus si besoins selon le type de contrat sélectionné
     */
    this.reloadFormHead = function(){

        var type_id = this.getCurrentTypeId();
        var recipient = this.getCurrentRecipient();
        var camping_id = this.getCurrentCampingId();

        if(type_id && recipient){
            this.loader($('#custom-html'));
            var request = $.ajax({
                type: "POST",
                url:  this.search_base_url + '/contract_form_head_per_type_per_recipient',
                data: {'recipient':recipient,'type_id':type_id,'camping_id':camping_id},
                success: function(data){
                    $('#custom-html').empty();

                    var result = $.parseJSON(data);
                    $('#custom-html').html(result);
                    this.initializeButton();
                    this.initializeAutocompletion();

                    $('.date-picker').datepicker({
                        rtl: App.isRTL(),
                        autoclose: true
                    });


                    $('.date_start').change(function() {
                        var date_start = $(this).val();
                        var data = date_start.split("/");

                        var day = data[0];
                        var month = data[1];
                        var year = data[2];
                        year++;
                        if(day > 1){
                            day--;
                        }else{
                            day = 30;
                        }

                        $(this).next().next().val(day + '/' + month +  '/' + year);
                    });

                    $('.selectpicker').selectpicker();
                    this.unLoader($('#custom-html'));
                }.bind(this)
            });
        }else{
            // alert('Une erreur est survenue');
        }
    };

    /**
     * recupere un camping en autocompletion
     */
    this.initializeAutocompletion = function(){
        var brc_auto = new BrcAutoCompletion('camping-autocomplete-label', 'camping-container', this.url_autocompletion, 'camping-autocomplete-id');
        brc_auto.construct();
    };

    this.loader = function(element) {
        var current_url = $('#base-url').val();

        element.block({

            message: '<img style="" src="' + current_url + '/img/loading-spinner-grey.gif" align="">',
            css: {
                'background-color': 'transparent',
                border: 'none'
            },
            overlayCSS: {
                opacity: 0.1
            }
        });
    };

    this.unLoader = function(element) {
        element.unblock();
    };

}

