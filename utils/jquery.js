$(document).ready( function() {
	$( "#zakrycie" ).click( function() {
		$(this).slideUp( 200 );
	});
	
	$( "#login_button span").click( function()
	{
		$(this).parent().parent().submit();
	});
	
	$( "#lista_testow li.aktywny span").click( function()
	{
		$(this).parent().submit();
	});
	
});