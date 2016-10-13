/**
 * Created by rco on 06/08/14.
 */
function BrcCamping(){



    this.construct = function(){
        $('.selectpicker').selectpicker();
        this.initializeButtonBrc();
    },

    this.initializeButtonBrc = function(){
        var i = 0;
        $('#add-partner').click(function(event){
            event.preventDefault();

            var container = $('#partner_select_list');


            container.find('select').addClass('selectpicker'+i);

            var el = container.html();

            $('#partner-list').append(el);
            container.find('select').removeClass('selectpicker'+i);
            $('.selectpicker'+i).selectpicker();




            i++;
        }.bind(this));
    }

}

