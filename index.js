
$(document).ready(function(){

	$('#main_tab').parent('body').css({
		'background-image' : 'url("./img/BG.png")',
		'background-repeat' : 'repeat',
		'background-size' : 'cover',
		'background-position' : '50%'
	});
	
	$('.header,.footer').css({
		'color' :'#FAFAFA'
	});
	
	$('.main_links').css({
		'color' : '#FAFAFA',
		'text-decoration' : 'none'
	});
	
	
	$('.main_links').hover(
		function(){
			$(this).css({
				'color' : 'red',
				'text-decoration':'underline'
			});
		},
		function(){
			$(this).css({
				'color' : '#FAFAFA',
				'text-decoration':'none'
			});		
		}
	);

	$('.mode_item')
	.bind('mouseenter', function(e){
		m_over.m_in(e);
	})
	.bind('mouseleave', function(e){
		m_over.m_out(e);
	});
	

	
});

//пререход на онлайн
var online =  function(url){
	//переадресуем на онлайн просмотр
    //alert(url);
	user_layouts.redirect(url, true);
};

var m_over = {
		factor : 1.15,
		m_in: function(e){
			var h, w;
			var him, wim;
			h= $(e.currentTarget).height();
			w= $(e.currentTarget).width();
			him= $('img', e.currentTarget).height();
			wim= $('img', e.currentTarget).width();
			$(e.currentTarget)
			.css({
				'z-index': 10,
				'width': w*m_over.factor+'px',
				'height': h*m_over.factor+'px',
				'left': (w-w*m_over.factor)/2,
				'top': (h-h*m_over.factor)/2
			})
			.attr({'h':h, 'w':w , 'him':him, 'wim':wim })
			.find('img')
			.css({
				'width': wim*m_over.factor,
				'height': him*m_over.factor
			});
		},
		
		m_out: function(e){
			var h, w;
			var him, wim;
			h= $(e.currentTarget).attr('h');
			w= $(e.currentTarget).attr('w');
			him= $(e.currentTarget).attr('him');
			wim= $(e.currentTarget).attr('wim');
			$(e.currentTarget)
			.css({
				'z-index': 0,
				'width': w+'px',
				'height': h+'px',
				'left': 0,
				'top': 0
			})
			.find('img')
			.css({
				'width': wim,
				'height': him
			});
		}
		
};

