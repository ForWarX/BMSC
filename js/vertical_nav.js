jQuery(document).ready(function($){
	var contentSections = $('.cd-section'),
		navigationItems = $('#cd-vertical-nav a');

	updateNavigation();
	$(window).on('scroll', function(){
		updateNavigation();
	});

	//smooth scroll to the section
	navigationItems.on('click', function(event){
        event.preventDefault();
        runScroll($(this.hash));
		var id = $(this).attr('id');
		
		$(this).find('.navigator').find('#img_above_text_' + id).find('div').css('color','#fff');
    });
    //smooth scroll to second section
    $('.cd-scroll-down').on('click', function(event){
        event.preventDefault();
        runScroll($(this.hash));
    }); 

	navigationItems.on('mouseover', function(event){
		if ($(this).hasClass('is-selected') === false){
			var id = $(this).attr('id');
			var ori_content = $(this).html();
			$('#ori_img_above_text_' + id).html(ori_content);
			var content = $('#text_under_img_' + id).find('div').html();
			$(this).find('.navigator').find('#img_above_text_' + id).find('div').html(content);
			$(this).find('.navigator').find('#img_above_text_' + id).removeClass('img_above_text');
			$(this).find('.navigator').find('#img_above_text_' + id).addClass('text_under_img');
			$(this).find('.navigator').find('#img_above_text_' + id).find('div').css('color','#FF5400');
		}
    });
	navigationItems.on('mouseout', function(event){
		if ($(this).hasClass('is-selected') === false){
			var id = $(this).attr('id');
			$(this).find('.navigator').find('#img_above_text_' + id).removeClass('text_under_img');
			$(this).find('.navigator').find('#img_above_text_' + id).addClass('img_above_text');
			$(this).find('.navigator').find('#img_above_text_' + id).find('div').html(id + 'F');
			$(this).find('.navigator').find('#img_above_text_' + id).find('div').css('color','#666');			
		}
    });

    //open-close navigation on touch devices
    $('.touch .cd-nav-trigger').on('click', function(){
    	$('.touch #cd-vertical-nav').toggleClass('open');
  
    });
    //close navigation on touch devices when selectin an elemnt from the list
    $('.touch #cd-vertical-nav a').on('click', function(){
    	$('.touch #cd-vertical-nav').removeClass('open');
    });

    var constant = 0;
	function updateNavigation() {
		contentSections.each(function(){
			$this = $(this);
			var activeSection = $('#cd-vertical-nav a[href="#'+$this.attr('id')+'"]').data('number') - 1;
			var num = $(this).attr('id').replace("section", "");
			if ( ( $this.offset().top - $(window).height()/3 < $(window).scrollTop() ) && ( $this.offset().top + $this.height() - $(window).height()/3 > $(window).scrollTop() ) ) {
				navigationItems.eq(activeSection).addClass('is-selected');
				//if (num != constant){
					constant = num;
					focus(num);
				//}

			}else {
				navigationItems.eq(activeSection).removeClass('is-selected');
				
				unfocus(num);
			}
		});
	}

	
	function focus(num){
		 			var content = $('#text_under_img_' + num).find('div').html();
					$('#img_above_text_' + num).find('div').html(content);
					$('#img_above_text_' + num).removeClass('img_above_text');
					$('#img_above_text_' + num).addClass('text_under_img');
					$('#img_above_text_' + num).find('div').css('color','#fff');
	}
	
	function unfocus(num){
				$('#img_above_text_' + num).removeClass('text_under_img');
				$('#img_above_text_' + num).addClass('img_above_text');
				$('#img_above_text_' + num).find('div').css('color','#666');
				$('#img_above_text_' + num).find('div').html(num + 'F');
	}
	
	
	
	/*function smoothScroll(target) {
        $('body,html').animate(
        	{'scrollTop':target.offset().top},
        	600
        );
	}*/
	 function scrollTop() {
        if (window.pageYOffset) {
            return window.pageYOffset;
        } else if (document.body.scrollTop) {
            return document.body.scrollTop;
        } else {
            return document.documentElement.scrollTop;
        };
    }
	
		
	function runScroll(target) {
	  scrollTo(document.body, target.offset().top, 300);
	}
	/*var scrollme;
	scrollme = document.querySelector("#scrollme");
	scrollme.addEventListener("click",runScroll,false)*/

	function scrollTo(element, to, duration) {
	  if (duration <= 0) return;
	  var difference = to - $(window).scrollTop();
	  var perTick = difference / duration * 10;

	  setTimeout(function() {
		window.scroll(0, $(window).scrollTop() + perTick);
		if (element.scrollTop == to) return;
		scrollTo(element, to, duration - 10);
	  }, 10);
	}
	
	
});