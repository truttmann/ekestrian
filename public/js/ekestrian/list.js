jQuery(document).ready(function() {
    // export CSV
    if($('#export_csv')){
        $('#export_csv').click(function(event){
            event.preventDefault();
            $('#export-csv').val('1');
            $('#datatable_ajax_wrapper').submit();
            $('#export-csv').val('');
        });
    }

    App.init();
    //EcommerceProducts.init();
});