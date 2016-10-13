jQuery(document).ready(function() {
    // export CSV
    if($('#export_csv')){

        var $modal = $('#ajax-modal');

        $('#export_csv').click(function(event){
            event.preventDefault();
            $('body').modalmanager('loading');

            setTimeout(function(){
                $modal.load($('#export-member-url').val(), function(){
                    $modal.modal();
                    });
                }, 1000);
        });
    }

    App.init();
    //EcommerceProducts.init();
});