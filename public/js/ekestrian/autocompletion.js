function BrcAutoCompletion(id, container, url, recipient){

    this.limit_length_to_begin = 2;
    this.current_request = null;
    this.id = id;
    this.url = url;
    this.container = container;
    this.recipient = recipient;

    this.construct = function(){

        $( "#" + this.id ).keyup(function() {

            // on lance la requete que si on a assez de char
            if($("#" + this.id ).val().length >= this.limit_length_to_begin){

                // check si requete deja en cours si oui on la tue et on relance
                if(this.request_complete == false){
                    this.current_request.abort();
                    this.request_complete = true;
                }

                this.executeRequest($("#" + this.id ).val());
            }
        }.bind(this));
    }

    this.executeRequest = function(text){

        var container = this.container;
        var recipient = this.recipient;
        var id = this.id;
        this.request_complete = false;

        this.current_request = $.ajax({
            type: "POST",
            url:  this.url,
            data: {'item_name':text},
            success: function(data){

                $('#' + container).empty();

                if(data){

                    var result = jQuery.parseJSON(data);
                    var ul_element = $('<ul/>',{
                        'class': 'tt-dropdown-menu',
                        style:'position: absolute; top: 100%; left: 16px; z-index: 100;  right: auto;'
                    })

                    $(result).each(function(index, item){

                        var li_element = jQuery('<li/>',{
                            text:item.name,
                            'class':'tt-suggestions'
                        });

                        li_element.click(function(event){
                            $('#' +id).val(item.name);
                            $('#' + recipient).val(item.id);
                            $('#' + container).empty();
                        });

                        li_element.appendTo(ul_element);
                    });

                    ul_element.appendTo('#' + container);
                    this.request_complete = true;
                }
            }
        });
    }
};



