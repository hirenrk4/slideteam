		require(['jquery'], function ($) {
		$(document).ready(function($){
			CustomFormPadding();       
		});
		$(window).on('resize',function(){
			CustomFormPadding();
		})
		$(window).on('load',function(){
			CustomFormPadding();
		})
		function CustomFormPadding(){
			$("form#respond :input").each(function(){
			 var input = $(this); // This is the jquery object of the input, do what you will
			 input.css("padding-left","","important");
			}); 
		}
		})