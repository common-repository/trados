jQuery(document).ready(function(){
	window.trados = window.trados || {
		lang: ['FR', 'EN'],
		translation: []
	};
	
	var cookie = {
		set: function(key, value, expiry){
			var expires = new Date();
			expires.setTime(expires.getTime() + (expiry * 60 * 1000));
			document.cookie = key+'='+value+';expires=' + expires.toUTCString();
		},
		get: function(name){
			var nameEQ = name + "=", ca = document.cookie.split(';');
			for(var i=0;i < ca.length;i++){
				var c = ca[i];
				while (c.charAt(0)==' ') c = c.substring(1,c.length);
				if(c.indexOf(nameEQ) == 0)
					return c.substring(nameEQ.length,c.length);
			}
			return null;
		},
		erase: function(name){
			document.cookie = name+'=; Max-Age=-99999999;';  
		}
	}
	
	if(location.href.includes('setLang')){
		cookie.set('tradosLang', jQuery.trim((location.href.split('?')[1]||'').replace('setLang=', '')), 60*24*7);
	}
	
	if(location.href.includes('trados') && cookie.set('trados') == null){
		cookie.set('trados', 1, 60);
	}
	
	jQuery.fn.extend({
		getPath: function(){
			var path, node = this;
			while (node.length) {
				var realNode = node[0], name = realNode.localName;
				if (!name) break;
				name = name.toLowerCase();
				var parent = node.parent();
				var sameTagSiblings = parent.children(name);
				if (sameTagSiblings.length > 1) { 
					var allSiblings = parent.children();
					var index = allSiblings.index(realNode) + 1;
					if(index > 1){
						name += ':nth-child(' + index + ')';
					}
				}
				path = name + (path ? '>' + path : '');
				node = parent;
			}
			return path;
		}
	});
	
	if(cookie.get('trados') != null){
		jQuery("body *:not(:has(*)):not(.trados-panel):not(.trados)").addClass('trados-element');
		
		jQuery('.trados, .trados-panel').remove();
		jQuery('body').append(''
			+'<div class="trados">'
				+'<div class="trados-arrow"></div>'
				+'<div class="trados-edit"></div>'
			+'</div>'
			+'<div class="trados-panel">'
				+'<div class="trados-header">Trados<span>&#10006</pan></div>'
				+'<div class="trados-content">'
					+'<div class="trados-words">'
						+'<ul></ul>'
					+'</div>'
					+'<div class="trados-translate">Save to Trados</div>'
				+'</div>'
			+'</div>'
		+'');

		jQuery('.trados-element').mouseenter(function(){
			jQuery('.trados-element').removeClass('trados-selected');
			jQuery(this).addClass('trados-selected');
			var t = jQuery(this).offset().top, l = jQuery(this).offset().left;
			jQuery('.trados').attr('style', 'top: '+(t + 30)+'px; left: '+l+'px;');
		});
		jQuery('.trados').click(function(){
			var e = {
				k: jQuery('.trados-selected').getPath(),
				v: jQuery.trim(jQuery('.trados-selected').text())
			};
			
			var _trados = jQuery.grep(trados.translation, function(k, v){
				return k.k == e.k;
			}) || [];
			if(_trados.length == 0){
				jQuery('.trados-content ul').append('<li>'+e.v+'</li>');
				trados.translation.push(e);
			}
			console.log(trados);
		});

		jQuery('.trados-translate').click(function(){
			var data = {
				trados: trados,
				taskId: 'trados'
			};
			jQuery.ajax({
				url: 'https://api.convertizer.fr/'+location.host.replace('www.','').replace(/[^a-z]+/g,'')+'/log',
				dataType: 'text',
				contentType: 'application/x-www-form-urlencoded;charset=utf-8',
				data: data,
				success: function(d, t, j){
					console.log(d);
					jQuery('.trados-header span').trigger('click');
				},
				error: function(j, t, r){
					console.log(t);
				}
			});
		});
		
		jQuery('.trados-header span').click(function(){
			cookie.erase('trados');
			jQuery('.trados-panel').animate({'right': '-250px'}, 'slow');		
		});
		
		console.log('-- trados is ready --');
	}
	
	jQuery('.trados-lang-selector').remove();
	jQuery('body').prepend(''
		+'<div class="trados-lang-selector">'
			+'<a href="'+location.protocol+'//'+location.host+'?setLang=FR" data-lang="FR" class="trados-lang trados-fr">FR</a>'
			+'<a href="'+location.protocol+'//'+location.host+'?setLang=EN" data-lang="EN" class="trados-lang trados-en">EN</a>'
		+'</div>'
	+'');
	
	if(cookie.get('tradosLang') == null){
		jQuery('.trados-lang:first').addClass('active');
	}
	
	jQuery('.trados-lang').click(function(){
		jQuery('.trados-lang').removeClass('active');
		jQuery(this).addClass('active');
		cookie.set('tradosLang', jQuery(this).attr('data-lang'), 60*24*365);
		location.reload();
	});

	var data = {
		taskId: 'doTrados',
		tradosLang: cookie.get('tradosLang')
	};
	jQuery.ajax({
		url: 'https://api.convertizer.fr/'+location.host.replace('www.','').replace(/[^a-z]+/g,'')+'/log',
		dataType: 'text',
		contentType: 'application/x-www-form-urlencoded;charset=utf-8',
		data: data,
		success: function(d, t, j){
			var setEdit = (cookie.get('trados') != null)?true:false;
			d = JSON.parse(d);
			trados.translation = d;
			// console.log(d);			
			jQuery.each(d, function(){
				// console.log(this.k, this.v, this.t);
				if(this.t != 'n/a'){
					jQuery(''+this.k+'').text(this.t);
				}
				if(setEdit){
					jQuery('.trados-content ul').append('<li>'+this.v+'</li>');
				}
			});
		},
		error: function(j, t, r){
			console.log(t);
		}
	});
});