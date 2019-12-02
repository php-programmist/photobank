$(document).ready(function () {
	$('.toggle-hidden').on('click',(e)=>{
		//e.preventDefault();
		let selector = $(e.target).data('selector');
		let hidden = $(selector);
		hidden.slideToggle();
	})
});