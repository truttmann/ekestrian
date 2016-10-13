jQuery(document).ready(function() {
    //App.init();
    EcommerceProductsEdit.init();

    $('.save').click(function() {

         $( "form:first" ).trigger( "submit" );
    });
});