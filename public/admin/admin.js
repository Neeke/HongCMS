function $$(id) {
    return typeof id == "string" ? document.getElementById(id) : id;
}

//显示缩略图
var userAgent = navigator.userAgent.toLowerCase();
var is_ie = window.ActiveXObject && userAgent.indexOf('msie') != -1 && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
function ShowBigImage(src, delay, target) {
	if(typeof tt != 'undefined') clearTimeout(tt);
	var e = window.event || arguments.callee.caller.arguments[0];
	var me = is_ie ? e.srcElement : e.target;
	var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);         
	var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);

	if(target) {
		var ei = $$(target);
	}else{
		var ei = $$("big_image_div");
	}

	if(ei && (ei.style.display != "none")){
		if(ei.offsetHeight > (e.clientY-16)){
			ei.style.top  = scrollTop + e.clientY + 16 + "px";
		}else{
			ei.style.top  = scrollTop + e.clientY - ei.offsetHeight - 16 + "px";
		}
		
		if(ei.offsetWidth > (e.clientX-16)){
			ei.style.left  = scrollLeft + e.clientX + 16 + "px";
		}else{
			ei.style.left = scrollLeft + e.clientX - ei.offsetWidth - 16 + "px";
		}
	}else if(target) {
		var xx = e.clientX;
		var yy = e.clientY;
		tt = setTimeout(function() {
			ei.style.display = "";
			if(ei.offsetHeight > (yy - 16)){
				ei.style.top  = scrollTop + yy + 16 + "px";
			}else{
				ei.style.top  = scrollTop + yy - ei.offsetHeight - 16 + "px";
			}
			if(ei.offsetWidth > (xx - 16)){
				ei.style.left  = scrollLeft + xx + 16 + "px";
			}else{
				ei.style.left = scrollLeft + xx - ei.offsetWidth - 16 + "px";
			}
		}, (delay || delay == 0)? delay : 600);
	}else{
		var xx = e.clientX;
		var yy = e.clientY;

		tt = setTimeout(function() {
			var ei = document.createElement("DIV");
			ei.id = "big_image_div";
			ei.style.cssText = "padding:6px;text-align:center;border:1px solid #B2B2B2;position:absolute;background:#FFF;z-index:80000;width:200px;height:200px;";
			ei = document.body.appendChild(ei);
			ei.innerHTML = "<img src=\"" + (src ? src : me.src.replace(/_s/ig, "_m")) + "\" width=200 border=0>";
			ei.style.display = "";

			if(ei.offsetHeight > (yy - 16)){
				ei.style.top  = scrollTop + yy + 16 + "px";
			}else{
				ei.style.top  = scrollTop + yy - ei.offsetHeight - 16 + "px";
			}
			if(ei.offsetWidth > (xx - 16)){
				ei.style.left  = scrollLeft + xx + 16 + "px";
			}else{
				ei.style.left = scrollLeft + xx - ei.offsetWidth - 16 + "px";
			}
		}, (delay || delay == 0)? delay : 600);
	}

	me.onmouseout = function() {
		if(typeof tt != 'undefined') clearTimeout(tt);
		if(target) {
			var ei = $$(target);
			if(ei)	ei.style.display = "none";
		}else{
			var ei = $$("big_image_div");
			if(ei)	document.body.removeChild(ei);
		}
	};
}


//TAB
function tab(o, s, cb, ev){
	var $ = function(o){return document.getElementById(o)};
	var css = o.split((s||'_'));
	if(css.length!=4)return;
	this.event = ev || 'onclick';
	o = $(o);
	if(o){
		this.ITEM = [];
		o.id = css[0];
		var item = o.getElementsByTagName(css[1]);
		var j=1;
		for(var i=0;i<item.length;i++){
			if(item[i].className.indexOf(css[2])>=0 || item[i].className.indexOf(css[3])>=0){
				if(item[i].className == css[2])o['cur'] = item[i];
				item[i].callBack = cb||function(){};
				item[i]['css'] = css;
				item[i]['link'] = o;
				this.ITEM[j] = item[i];
				item[i]['Index'] = j++;
				item[i][this.event] = this.ACTIVE;
			}
		}
		return o;
	}
}
tab.prototype = {
	ACTIVE:function(){
		var $ = function(o){return document.getElementById(o)};
		this['link']['cur'].className = this['css'][3];
		this.className = this['css'][2];
		try{
			$(this['link']['id']+'_'+this['link']['cur']['Index']).style.display = 'none';
			$(this['link']['id']+'_'+this['Index']).style.display = 'block';
		}catch(e){}
		this.callBack.call(this);
		this['link']['cur'] = this;
	}
}


//设置container, sidebar兼容浏览器
function do_fix(open){
	var o = $("#container");

	if(open){
		o.width(180);
		$("#sidebar").css("cssText", "");
	}else{
		o.width(40);
		js_scrolly({id:"sidebar", l:0, t:40});
	}
}

//左侧JS固定DIV
function js_scrolly(p){
	var o = document.getElementById(p.id);

	if(o){
		var dd = document.documentElement, ie6 = /msie 6/i.test(navigator.userAgent);
		var cssPub = ";position:"+(!ie6?'fixed':'absolute')+";"+(p.t!=undefined?'top:'+p.t+'px;':'bottom:0;');

		if (p.r != undefined && p.l == undefined) {
			o.style.cssText += cssPub + ('right:'+p.r+'px;');
		} else {
			o.style.cssText += cssPub + ('margin-left:'+p.l+'px;');
		}

		if(ie6){
			var cssTop = ';top:expression(documentElement.scrollTop +'+(p.t==undefined?dd.clientHeight-o.offsetHeight:p.t)+'+ "px" );';
			var cssRight = ';right:expression(documentElement.scrollright + '+(p.r==undefined?dd.clientWidth-o.offsetWidth:p.r)+' + "px")';

			if (p.r != undefined && p.l == undefined) {
				o.style.cssText += cssRight + cssTop;
			} else {
				o.style.cssText += cssTop;
			}

			dd.style.cssText +=';background-image: url(about:blank);background-attachment:fixed;';
		}
	}
}


//Ajax封装
function ajax(url, send_data, callback) {
	$.ajax({
		url: url,
		data: send_data,
		type: "post",
		cache: false,
		dataType: "json",
		beforeSend: function(){$("#ajax-loader").show();},
		complete: function(){$("#ajax-loader").hide();},
		success: function(data){
			if(data.s == 0){
				$.dialog({lock:true, title:"Ajax错误", content:"<span class=red>" + data.i + "</span>", okValue:'  确定  ', ok:true});
			}else{
				callback(data);
			}
		},
		error: function(XHR, Status, Error) {
			$.dialog({lock:true, title:"Ajax错误", content:"数据: " + XHR.responseText + "<br>状态: " + Status + "<br>错误: " + Error, okValue:'  确定  ', ok:true});
		}
	});
}

//顶部下拉菜单 b为参数对象, c为下拉菜单显示后的事件函数
(function(a) {
	a.fn.Jdropdown = function(b, c) {
		if (this.length) {
			"function" == typeof b && (c = b, b = {});
			var d = a.extend({
					event: "mouseover",
					current: "hover",
					delay: 0
				}, b || {}),
				e = "mouseover" == d.event ? "mouseout" : "mouseleave";
			a.each(this, function() {
				var b = null,f = null,g = !1;
				a(this).bind(d.event, function() {
					if (g) clearTimeout(f);
					else {
						var e = a(this);
						b = setTimeout(function() {
							e.addClass(d.current), g = !0, c && c(e)
						}, d.delay);
					}
				}).bind(e, function() {
					if (g) {
						var c = a(this);
						f = setTimeout(function() {
							c.removeClass(d.current), g = !1
						}, d.delay)
					} else clearTimeout(b);
				});
			});
		}
	}
})(jQuery);

//左侧菜单等系统功能
var App = function () {
    var handleSidebarMenu = function () {
		var objs = $("#sidebar .has-sub > a");
		var linkfound = false, linkfoundtop = false;

		objs.mouseover(function(){
			var sub = $(this).next();

			var pixtobottom = $(window).height() + $(document).scrollTop() - $(this).offset().top - $(this).height() - 26;

			if(pixtobottom < sub.height()){
				sub.addClass("top");
			}
		});

        objs.click(function (e) {
            if($("#container").hasClass("sidebar-closed") === false) {
				var last = jQuery('.has-sub.open', $('#sidebar'));
				last.removeClass("open");
				jQuery('.arrow', last).removeClass("open");
				jQuery('.sub', last).slideUp(200);

				var sub = jQuery(this).next();
				if (sub.is(":visible")) {
					jQuery('.arrow', jQuery(this)).removeClass("open");
					jQuery(this).parent().removeClass("open");
					sub.slideUp(200);
				} else {
					jQuery('.arrow', jQuery(this)).addClass("open");
					jQuery(this).parent().addClass("open");
					sub.slideDown(200);
				}
			}
			e.preventDefault();
        });


		//查找左侧菜单当前链接并设置样式
		var href, leftmenuli, leftmenulinks, topmenulinks;
		leftmenulinks = $("#sidebar .sub > li > a");

		leftmenulinks.each(function(){
			href = $(this).attr('href');
			if(this_uri == href || this_uri == (href + '/') || href == (this_uri + '/')){
				$(this).parent().addClass("active").parent().parent().addClass("active open").find("a span.arrow").addClass("open");
				linkfound = true;
				return false;
			}
		});

		if(!linkfound){
			leftmenuli = $("#sidebar > ul > li");
			leftmenuli.not(".has-sub").each(function(){
				href = $(this).children("a").attr('href');
				if(this_uri == href || this_uri == (href + '/') || href == (this_uri + '/')){
					$(this).addClass("active");
					linkfound = true;
					return false;
				}
			});
		}

		if(!linkfound){
			leftmenulinks.each(function(){
				if(this_uri.indexOf($(this).attr('href')) >= 0){
					$(this).parent().addClass("active").parent().parent().addClass("active open").find("a span.arrow").addClass("open");
					linkfound = true;
					return false;
				}
			});

			if(!linkfound){
				leftmenuli.not(".has-sub").each(function(){
					href = $(this).children("a").attr('href');
					if(this_uri.indexOf(href) >= 0){
						$(this).addClass("active");
						return false;
					}
				});
			}
		}

		//查找顶部菜单当前链接并设置样式
		topmenulinks = $("#topmenu > dl > dd > div > li > a");
		topmenulinks.each(function(){
			href = $(this).attr('href');
			if(this_uri == href || this_uri == (href + '/') || href == (this_uri + '/')){
				$(this).addClass("active");
				linkfoundtop = true;
				return false;
			}
		});

		if(!linkfoundtop){
			topmenulinks.each(function(){
				if(this_uri.indexOf($(this).attr('href')) >= 0){
					$(this).addClass("active");
					return false;
				}
			});
		}
	}

    var handleSidebarToggler = function () {
        if ($.cookie('sidebar-closed')) {
			$("#container").addClass("sidebar-closed");
			$(".sidebar-toggler").attr("title","展开菜单(Ctrl >)");
			
			do_fix(false);
        }

		$(".sidebar-toggler").hover(function(){
			$(this).children("i").addClass("hover");
		}, function(){
			$(this).children("i").removeClass("hover");
		});
        
		$('.sidebar-toggler').click(function () {
            if ($("#container").hasClass("sidebar-closed") === false) {
                $("#container").addClass("sidebar-closed");
				$.cookie('sidebar-closed', 1, {expires: 365, path: '/'});
				$(this).attr("title","展开菜单(Ctrl >)");
				
				do_fix(false);
            } else {
                $("#container").removeClass("sidebar-closed");
                $.removeCookie('sidebar-closed', {path: '/'});
				$(this).attr("title","收拢菜单(Ctrl <)");
				
				do_fix(true);
            }
        });
    }

    return {
        init: function () {
            handleSidebarMenu();
            handleSidebarToggler();

			$(document).keydown(function(e){
				if(e.ctrlKey && (e.which == 37 || e.which == 39)) {
					$('.sidebar-toggler').click();
				}
			});

        }
    };
}();