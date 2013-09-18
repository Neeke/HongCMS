//图片延迟加载
(function(a) {
	a.fn.lazyload = function(j, n) {
		if (!this.length) {
			return;
		}
		var f = a.extend({
			type: "image",
			offsetParent: null,
			source: "original",
			placeholderImage: siteConfig.siteurl + "public/e.gif",
			placeholderClass: "loading1",
			threshold: 200
		},
		j || {}),
		k = this,
		g,
		m,
		l = function(r) {
			var u = r.scrollLeft,
			t = r.scrollTop,
			s = r.offsetWidth,
			q = r.offsetHeight;
			while (r.offsetParent) {
				u += r.offsetLeft;
				t += r.offsetTop;
				r = r.offsetParent;
			}
			return {
				left: u,
				top: t,
				width: s,
				height: q
			};
		},
		e = function() {
			var v = document.documentElement,
			r = document.body,
			u = window.pageXOffset ? window.pageXOffset: (v.scrollLeft || r.scrollLeft),
			t = window.pageYOffset ? window.pageYOffset: (v.scrollTop || r.scrollTop),
			s = v.clientWidth,
			q = v.clientHeight;
			return {
				left: u,
				top: t,
				width: s,
				height: q
			};
		},
		d = function(w, v) {
			var y,
			x,
			s,
			r,
			q,
			u,
			z = f.threshold ? parseInt(f.threshold) : 0;
			y = w.left + w.width / 2;
			x = v.left + v.width / 2;
			s = w.top + w.height / 2;
			r = v.top + v.height / 2;
			q = (w.width + v.width) / 2;
			u = (w.height + v.height) / 2;
			return Math.abs(y - x) < (q + z) && Math.abs(s - r) < (u + z);
		},
		b = function(q, s, r) {
			if (f.placeholderImage && f.placeholderClass) {
				r.attr("src", f.placeholderImage).addClass(f.placeholderClass);
			}
			if (q) {
				r.attr("src", s).removeAttr(f.source);
				if (n) {
					n(s, r);
				}
			}
		},
		c = function(q, t, r) {
			if (q) {
				var s = a("#" + t);
				s.html(r.val()).removeAttr(f.source);
				r.remove();
				if (n) {
					n(t, r);
				}
			}
		},
		p = function(q, s, r) {
			if (q) {
				r.removeAttr(f.source);
				if (n) {
					n(s, r);
				}
			}
		},
		o = function() {
			m = e(),
			k = k.filter(function() {
				return a(this).attr(f.source);
			});
			a.each(k,
			function() {
				var t = a(this).attr(f.source);
				if (!t) {
					return;
				}
				var s = (!f.offsetParent) ? m: l(a(f.offsetParent).get(0)),
				r = l(this),
				q = d(s, r);
				switch (f.type) {
				case "image":
					b(q, t, a(this));
					break;
				case "textarea":
					c(q, t, a(this));
					break;
				case "module":
					p(q, t, a(this));
					break;
				default:
					break;
				}
			});
		},
		h = function() {
			if (k.length > 0) {
				clearTimeout(g);
				g = setTimeout(function() {
					o();
				},
				10);
			}
		};
		o();
		if (!f.offsetParent) {
			a(window).bind("scroll",
			function() {
				h();
			}).bind("reset",
			function() {
				h();
			});
		} else {
			a(f.offsetParent).bind("scroll",
			function() {
				h();
			});
		}
	};
})(jQuery);

$(function() {
  $("img[original]").lazyload();
});

//返回顶部
var scrolltotop = {
	setting: {
		startline: 100, //滚动条向下移动多少px后出现
		scrollto: 0,
		scrollduration: 50, //点击后上向滚动开始到完成所需要的时间
		fadeduration: [500, 100]
	},
	controlHTML: '<a href="javascript:void(0);" class="scrollTopImg" hidefocus="true"></a>',
	controlattrs: {
		offsetx: 140,
		offsety: 0 //按钮离底部的距离
	},
	anchorkeyword: "#top",
	state: {
		isvisible: false,
		shouldvisible: false
	},
	scrollup: function() {
		if (!this.cssfixedsupport) {
			this.$control.hide()
		}
		var a = isNaN(this.setting.scrollto) ? this.setting.scrollto: parseInt(this.setting.scrollto);
		if (typeof a == "string" && jQuery("#" + a).length == 1) {
			a = jQuery("#" + a).offset().top
		} else {
			a = 0
		}
		this.$body.animate({
			scrollTop: a
		},
		this.setting.scrollduration)
	},
	keepfixed: function() {
		var c = jQuery(window);
		var b = c.scrollLeft() + c.width() - this.$control.width() - this.controlattrs.offsetx;
		var a = c.scrollTop() + c.height() - this.$control.height() - 0 - this.controlattrs.offsety;
		if (a > ($(document).height() - 20)) {
			a = $(document).height() - 20
		}
		this.$control.css({
			left: "50%",
			top: a + "px"
		})
	},
	togglecontrol: function() {
		var a = jQuery(window).scrollTop();
		if (!this.cssfixedsupport) {
			this.keepfixed()
		}
		this.state.shouldvisible = (a >= this.setting.startline) ? true: false;
		if (this.state.shouldvisible && !this.state.isvisible) {
			this.$control.stop().fadeIn(200);
			this.state.isvisible = true;
			$("#shortcut, #shortcut .w").addClass("shortcut_add");
		} else {
			if (this.state.shouldvisible == false && this.state.isvisible) {
				this.$control.stop().fadeOut(200);
				this.state.isvisible = false
				$("#shortcut, #shortcut .w").removeClass("shortcut_add");
			}
		}
	},
	init: function() {
			jQuery(document).ready(function(c) {
			var a = scrolltotop;
			var b = document.all;
			a.cssfixedsupport = !b || b && document.compatMode == "CSS1Compat" && window.XMLHttpRequest;
			a.$body = (window.opera) ? (document.compatMode == "CSS1Compat" ? c("html") : c("body")) : c("html,body");
			a.$control = c('<div id="topcontrol">' + a.controlHTML + "</div>").css({
				position: a.cssfixedsupport ? "fixed": "absolute",
				bottom: 1,
				left: "50%",
				marginLeft: "476px",
				display: "none",
				cursor: "pointer"
			}).attr({
				title: siteConfig.scrolltop
			}).click(function() {
				a.scrollup();
				return false
			}).appendTo("body");

			if (document.all && !window.XMLHttpRequest && a.$control.text() != "") {
				a.$control.css({
					width: a.$control.width()
				})
			}
			a.togglecontrol();
			c('a[href="' + a.anchorkeyword + '"]').click(function() {
				a.scrollup();
				return false
			});
			c(window).bind("scroll resize",
			function(g) {
				a.togglecontrol();
			})
		})
	}
};


// Superfish v1.4.8 - jQuery menu 菜单
;(function($){
	$.fn.superfish = function(op){

		var sf = $.fn.superfish,
			c = sf.c,
			$arrow = $(['<span class="',c.arrowClass,'"> &#187;</span>'].join('')),
			over = function(){
				var $$ = $(this), menu = getMenu($$);
				clearTimeout(menu.sfTimer);
				$$.showSuperfishUl().siblings().hideSuperfishUl();
			},
			out = function(){
				var $$ = $(this), menu = getMenu($$), o = sf.op;
				clearTimeout(menu.sfTimer);
				menu.sfTimer=setTimeout(function(){
					o.retainPath=($.inArray($$[0],o.$path)>-1);
					$$.hideSuperfishUl();
					if (o.$path.length && $$.parents(['li.',o.hoverClass].join('')).length<1){over.call(o.$path);}
				},o.delay);	
			},
			getMenu = function($menu){
				var menu = $menu.parents(['ul.',c.menuClass,':first'].join(''))[0];
				sf.op = sf.o[menu.serial];
				return menu;
			},
			addArrow = function($a){ $a.addClass(c.anchorClass).append($arrow.clone()); };
			
		return this.each(function() {
			var s = this.serial = sf.o.length;
			var o = $.extend({},sf.defaults,op);
			o.$path = $('li.'+o.pathClass,this).slice(0,o.pathLevels).each(function(){
				$(this).addClass([o.hoverClass,c.bcClass].join(' '))
					.filter('li:has(ul)').removeClass(o.pathClass);
			});
			sf.o[s] = sf.op = o;
			
			$('li:has(ul)',this)[($.fn.hoverIntent && !o.disableHI) ? 'hoverIntent' : 'hover'](over,out).each(function() {
				if (o.autoArrows) addArrow( $('>a:first-child',this) );
			})
			.not('.'+c.bcClass)
				.hideSuperfishUl();
			
			var $a = $('a',this);
			$a.each(function(i){
				var $li = $a.eq(i).parents('li');
				$a.eq(i).focus(function(){over.call($li);}).blur(function(){out.call($li);});
			});
			o.onInit.call(this);
			
		}).each(function() {
			var menuClasses = [c.menuClass];
			if (sf.op.dropShadows  && !($.browser.msie && $.browser.version < 7)) menuClasses.push(c.shadowClass);
			$(this).addClass(menuClasses.join(' '));
		});
	};

	var sf = $.fn.superfish;
	sf.o = [];
	sf.op = {};
	sf.IE7fix = function(){
		var o = sf.op;
		if ($.browser.msie && $.browser.version > 6 && o.dropShadows && o.animation.opacity!=undefined)
			this.toggleClass(sf.c.shadowClass+'-off');
		};
	sf.c = {
		bcClass     : 'sf-breadcrumb',
		menuClass   : 'sf-js-enabled',
		anchorClass : 'sf-with-ul',
		arrowClass  : 'sf-sub-indicator',
		shadowClass : 'sf-shadow'
	};
	sf.defaults = {
		hoverClass	: 'sfHover',
		pathClass	: 'overideThisToUse',
		pathLevels	: 1,
		delay		: 600,
		animation	: {opacity:'show'},
		speed		: 'normal',
		autoArrows	: true,
		dropShadows : true,
		disableHI	: true,		// true disables hoverIntent detection
		onInit		: function(){}, // callback functions
		onBeforeShow: function(){},
		onShow		: function(){},
		onHide		: function(){}
	};
	$.fn.extend({
		hideSuperfishUl : function(){
			var o = sf.op,
				not = (o.retainPath===true) ? o.$path : '';
			o.retainPath = false;
			var $ul = $(['li.',o.hoverClass].join(''),this).add(this).not(not).removeClass(o.hoverClass)
					.find('>ul').hide().css('visibility','hidden');
			o.onHide.call($ul);
			return this;
		},
		showSuperfishUl : function(){
			var o = sf.op,
				sh = sf.c.shadowClass+'-off',
				$ul = this.addClass(o.hoverClass)
					.find('>ul:hidden').css('visibility','visible');
			sf.IE7fix.call($ul);
			o.onBeforeShow.call($ul);
			$ul.animate(o.animation,o.speed,function(){ sf.IE7fix.call($ul); o.onShow.call($ul); });
			return this;
		}
	});

})(jQuery);

//JQuery滚动图片
(function(a) {
	a.fn.ImageMove = function(b) {
		var c = {
			prevId: "prevBtn",
			nextId: "nextBtn",
			offbtnleft: "",
			offbtnright: "",
			pageId: "", //当前页数的span样式
			totalId: "", //全部页数的span样式
			controlsFade: true, //无法滚动时按键切换样式
			vertical: false, //滚动方向
			speed: 800,
			auto: false,
			pause: 2000,
			continuous: false,
			offline: 1 //表示一次走几个UL
		};
		var b = a.extend(c, b);
		this.each(function() {
			var m = b.offline;
			var e = a(this);
			var l = a("ul", e).length;
			var j = a("ul", e).width();
			var f = a("li", e).height();
			var g = l % m;

			if(b.pageId) a(b.pageId).html(1); //改变当前页数字为1
			if(b.totalId) a(b.totalId).html(l); //改变全部页数字

			if (g != 0) {
				g = parseInt((l / m))
			} else {
				g = parseInt((l / m)) - 1
			}
			var k = 0;
			if (!b.vertical) {
				a("ul", e).css("width", j)
			}
			a(b.nextId).click(function() {
				d("next", true)
			});
			a(b.prevId).click(function() {
				d("prev", true)
			});
			function d(h, n) {
				var o = k;
				switch (h) {
				case "next":
					k = (o >= g) ? (b.continuous ? 0: g) : k + 1;
					break;
				case "prev":
					k = (k <= 0) ? (b.continuous ? g: 0) : k - 1;
					break;
				case "first":
					k = 0;
					break;
				case "last":
					k = g;
					break;
				default:
					break
				}

				if(b.pageId) a(b.pageId).html(k+1); //改变当前页数字

				var r = Math.abs(o - k);
				var q = r * b.speed;
				if (!b.vertical) {
					p = (k * j * -1);
					e.animate({
						marginLeft: m * p
					},
					q)
				} else {
					p = (k * f * -1);
					e.animate({
						marginTop: m * p
					},
					q)
				}

				if (!b.continuous && b.controlsFade) {
					if (k == g) {
						a(b.nextId).addClass(b.offbtnright)
					} else {
						a(b.nextId).removeClass(b.offbtnright)
					}
					if (k == 0) {
						a(b.prevId).addClass(b.offbtnleft)
					} else {
						a(b.prevId).removeClass(b.offbtnleft)
					}
				}
				if (n) {
					clearTimeout(i)
				}
				if (b.auto && !n) {
					if(h == "next"){
						i = setTimeout(function() {
							d(((k >= g) ? "prev" : "next"), false)
						},	r * b.speed + b.pause);
					}else if(h == "prev"){
						i = setTimeout(function() {
							d(((k <= 0) ? "next" : "prev"), false)
						},	r * b.speed + b.pause);
					}
				}


				if (h == "next") {
					//点下一页时显示延迟加载的图片
					var hidesrc = "";

					$("#" + e.attr("id") + " img[step='"+k+"']").each(function(){
						hidesrc = $(this).attr("hide");
						if(hidesrc){
							$(this).attr("src", siteConfig.siteurl + "public/e.gif").addClass("loading1").attr("src", hidesrc).removeAttr("hide");
						}
					});
				}
			}

			var i;
			if (b.auto) {
				i = setTimeout(function() {
					d("next", false)
				},
				b.pause)
			}
			if (!b.continuous && b.controlsFade) {
				a(b.prevId).addClass(b.offbtnleft)
			}
			if (k == g) {
				a(b.nextId).addClass(b.offbtnright)
			}
		})
	}
})(jQuery);
