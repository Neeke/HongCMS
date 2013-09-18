//JS基础库
var userAgent = navigator.userAgent.toLowerCase();
var is_ie = window.ActiveXObject && userAgent.indexOf('msie') != -1 && userAgent.substr(userAgent.indexOf('msie') + 5, 3);

function $$(id) {
    return typeof id == "string" ? document.getElementById(id) : id;
}

//添加到收藏夹
function addToFavorite() {
	var d = siteConfig.siteurl;
	var c = siteConfig.sitename;
	if (document.all) {
		window.external.AddFavorite(d, c);
	} else {
		if (window.sidebar) {
			window.sidebar.addPanel(c, d, "");
		} else {
			alert("对不起，您的浏览器不支持此操作!\n请您使用菜单栏或Ctrl+D收藏本站。");
		}
	}
}

//下拉dt
(function(a) {
	a.fn.Jdropdown = function(d, e) {
		if (!this.length) {
			return;
		}
		if (typeof d == "function") {
			e = d;
			d = {};
		}
		var c = a.extend({
			event: "mouseover",
			current: "hover",
			delay: 0
		},
		d || {});
		var b = (c.event == "mouseover") ? "mouseout": "mouseleave";
		a.each(this,
		function() {
			var h = null,
			g = null,
			f = false;
			a(this).bind(c.event,
			function() {
				if (f) {
					clearTimeout(g);
				} else {
					var j = a(this);
					h = setTimeout(function() {
						j.addClass(c.current);
						f = true;
						if (e) {
							e(j);
						}
					},
					c.delay);
				}
			}).bind(b,
			function() {
				if (f) {
					var j = a(this);
					g = setTimeout(function() {
						j.removeClass(c.current);
						f = false;
					},
					c.delay);
				} else {
					clearTimeout(h);
				}
			});
		});
	};
})(jQuery);

//检查输入框是否为空白
function CheckSpace(inputid, msg) {
	var inputObj = $$(inputid);
	if(!inputObj) return false;

	String.prototype.trim = function() {
		return this.replace(/(^\s+)|\s+$/g,"");
	}
	 
	if(inputObj.value.trim() == "") { 
		alert(msg);
		inputObj.value = '';
		inputObj.focus();
		return false;
	}
}

//设置cookie
function setCookie (cookieKey,cookieValue,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}else{
		var expires = "";
	}

	document.cookie = cookieKey+"="+cookieValue+expires+"; path=/";
}

//滚动图片
function slide_im(arr, time){
	var _self=this;
	this.is_mouseover=false;
	this.arr=arr;
	this.init=function(){
		$(arr+" .slide-controls span").mouseover(function(){_self.show(this);});
		$(arr+" .slide-controls span").click(function(){
			if(!$(this).hasClass("curr")){
				_self.show(this);
			}
		});
		$(arr+" .slide-controls span:first").click();
	}

	this.show=function(slide_a){
		$(arr+ " .slide-controls span").removeClass('curr');
		$(slide_a).addClass('curr');
		var src=$(slide_a).attr('pic');
		var e=$(arr+ " img");
		$(arr + " .slide_img a").attr('href',$(slide_a).attr('href'));
		e.fadeOut('fast',function(){e.attr('src','');e.attr('src',src);e.fadeIn('fast');});
		$(arr+ " .slide-controls span").attr('href',this.href);
	}

	this.autonext=function(){
		if(_self.is_mouseover==true){
			return;
		}
		var e=$(_self.arr).find(".slide-controls .curr").next();
		if(e.length==0){
			e=$(_self.arr).find(".slide-controls span:first");
		}
		e.click();
	}

	$(arr).hover(function(){_self.is_mouseover=true;},function(){_self.is_mouseover=false;});
	setInterval(_self.autonext,time);
}

//JS固定DIV
function js_scrolly(p){
	var d = document, dd = d.documentElement, db = d.body, w = window, o = d.getElementById(p.id), ie6 = /msie 6/i.test(navigator.userAgent), style, timer;

	if(o){
		var cssPub = ";position:"+(p.f&&!ie6?'fixed':'absolute')+";"+(p.t!=undefined?'top:'+p.t+'px;':'bottom:0;');

		if (p.r != undefined && p.l == undefined) {
			o.style.cssText += cssPub + ('right:'+p.r+'px;');
		} else {
			o.style.cssText += cssPub + ('margin-left:'+p.l+'px;');
		}

		if(p.f&&ie6){
			var cssTop = ';top:expression(documentElement.scrollTop +'+(p.t==undefined?dd.clientHeight-o.offsetHeight:p.t)+'+ "px" );';
			var cssRight = ';right:expression(documentElement.scrollright + '+(p.r==undefined?dd.clientWidth-o.offsetWidth:p.r)+' + "px")';

			if (p.r != undefined && p.l == undefined) {
				o.style.cssText += cssRight + cssTop;
			} else {
				o.style.cssText += cssTop;
			}

			dd.style.cssText +=';background-image: url(about:blank);background-attachment:fixed;';
		}else{
			if(!p.f){
				w.onresize = w.onscroll = function(){
					clearInterval(timer);

					timer = setInterval(function(){
						//双选择为了修复chrome 下xhtml解析时dd.scrollTop为 0
						var st = (dd.scrollTop||db.scrollTop),c;
						c = st - o.offsetTop + (p.t!=undefined?p.t:(w.innerHeight||dd.clientHeight)-o.offsetHeight);
						if(c!=0){
							o.style.top = o.offsetTop + Math.ceil(Math.abs(c)/10)*(c<0?-1:1) + 'px';
						}else{
							clearInterval(timer);
						}
					},10);
				};
			}
		}
	}
}

//显示缩略图
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