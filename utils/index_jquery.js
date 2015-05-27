$(document).ready( function() {

	$( "#login_button span").click( function()
	{
		$(this).parent().parent().submit();
	});
	
	$('#komentarz_logowania>span').delay(2000).fadeOut(500);
});