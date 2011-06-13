$(document).ready(init);

function init()
{
	$(".show").click( showContributor );
	$(".hide").click( hideContributor );
}

function showContributor()
{
	$(this).next().show(); 
	$(this).hide();
	$(this.parentNode).next().show();
}

function hideContributor()
{
	$(this).prev().show(); 
	$(this).hide();
	$(this.parentNode).next().hide();
}