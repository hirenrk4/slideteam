!function(a,b,c){var d=a[c.k]={w:a,d:b,a:c,s:{},f:function(){return{callback:[],get:function(a,b){var c=null;return c="string"===typeof a[b]?a[b]:a.getAttribute(b)},getData:function(a,b){return b=d.a.dataAttributePrefix+b,d.f.get(a,b)},set:function(a,b,c){"string"===typeof a[b]?a[b]=c:a.setAttribute(b,c)},make:function(a){var b,c,e=!1;for(b in a)
if(a[b].hasOwnProperty){e=d.d.createElement(b);for(c in a[b])a[b][c].hasOwnProperty&&"string"===typeof a[b][c]&&d.f.set(e,c,a[b][c]);break}
return e},kill:function(a){"string"===typeof a&&(a=d.d.getElementById(a)),a&&a.parentNode&&a.parentNode.removeChild(a)},replace:function(a,b){a.parentNode.insertBefore(b,a),d.f.kill(a)},getEl:function(a){var b=null;return b=a.target?3===a.target.nodeType?a.target.parentNode:a.target:a.srcElement},listen:function(a,b,c){"undefined"!==typeof d.w.addEventListener?a.addEventListener(b,c,!1):"undefined"!==typeof d.w.attachEvent&&a.attachEvent("on"+b,c)},call:function(a,b){var c,e,f="?";c=d.f.callback.length,e=d.a.k+".f.callback["+c+"]",d.f.callback[c]=function(a){b(a,c),d.f.kill(e)},a.match(/\?/)&&(f="&"),d.d.b.appendChild(d.f.make({SCRIPT:{id:e,type:"text/javascript",charset:"utf-8",src:a+f+"callback="+e}}))},debug:function(a){d.v.config.debug&&d.w.console&&d.w.console.log&&d.w.console.log(a)},presentation:function(){var a,b,e;a=d.f.make({STYLE:{type:"text/css"}}),b=d.a.cdn[d.w.location.protocol]||d.a.cdn["http:"],e=d.a.rules.join("\n"),e=e.replace(/\._/g,"."+c.k+"_"),e=e.replace(/;/g,"!important;"),e=e.replace(/_cdn/g,b),e=e.replace(/_rez/g,d.v.resolution),a.styleSheet?a.styleSheet.cssText=e:a.appendChild(d.d.createTextNode(e)),d.d.h?d.d.h.appendChild(a):d.d.b.appendChild(a)},getPos:function(a){var b=0,c=0;if(a.offsetParent){do b+=a.offsetLeft,c+=a.offsetTop;while(a=a.offsetParent);return{left:b,top:c}}},hideFloatingButton:function(){d.s.floatingButton&&(d.s.floatingButton.style.display="none")},getThis:function(a,b){var c=d.a.endpoint.builder+a+"&"+b;d.f.log("&type=getThis&href="+encodeURIComponent(c)),d.w.open(c,"pin"+(new Date).getTime())},showFloatingButton:function(a){if(a.height>d.a.minImgSize&&a.width>d.a.minImgSize&&!a.src.match(/^data/)){d.s.floatingButton||(d.s.floatingButton=d.f.make({A:{className:d.a.k+"_pin_it_button "+d.a.k+"_pin_it_button_floating responsive_pin",title:"Pin it!",target:"_blank"}}),d.f.set(d.s.floatingButton,d.a.dataAttributePrefix+"log","button_pinit_floating"),d.d.b.appendChild(d.s.floatingButton));var b=d.f.getPos(a),c=d.a.endpoint.create;c=c+"url="+encodeURIComponent(d.d.URL)+"&media="+encodeURIComponent(a.src)+"&description="+encodeURIComponent(a.getAttribute("data-pin-description")||a.title||a.alt||d.d.title),d.s.floatingButton.href=c,d.s.floatingButton.onclick=function(){return d.w.open(this.href,"pin"+(new Date).getTime(),d.a.pop),d.f.hideFloatingButton(),d.v.hazFloatingButton=!1,!1},d.s.floatingButton.style.top=b.top+d.a.floatingButtonOffsetTop+"px",d.s.floatingButton.style.left=b.left+d.a.floatingButtonOffsetLeft+"px",d.s.floatingButton.style.display="block"}},over:function(a){var b,c;b=a||d.w.event,c=d.f.getEl(b),c&&("IMG"===c.tagName&&c.src&&!d.f.getData(c,"no-hover")&&!d.f.get(c,"nopin")&&d.v.config.hover?d.v.hazFloatingButton===!1?(d.v.hazFloatingButton=!0,d.f.showFloatingButton(c)):(d.f.hideFloatingButton(),d.f.showFloatingButton(c)):d.v.hazFloatingButton===!0&&c!==d.s.floatingButton&&(d.v.hazFloatingButton=!1,d.f.hideFloatingButton()))},click:function(a){a=a||d.w.event;var b,c;b=d.f.getEl(a),b&&(c=d.f.getData(b,"log"),c&&(d.f.log("&type="+c+"&href="+encodeURIComponent(b.href||d.f.getData(b,"href"))),b.className.match(/hazClick/)||(b.className=b.className+" "+d.a.k+"_hazClick")))},filter:function(a){var b,c;b="",c="";try{b=decodeURIComponent(a)}catch(d){}
return c=b.replace(/</g,"&lt;"),c=c.replace(/>/g,"&gt;")},behavior:function(){d.f.listen(d.d.b,"click",d.f.click),d.v.config.hover&&d.f.listen(d.d.b,"mouseover",d.f.over)},getPinCount:function(a){var b="?url="+a+"&ref="+encodeURIComponent(d.v.here)+"&source="+d.a.countSource;d.f.call(d.a.endpoint.count+b,d.f.ping.count)},prettyPinCount:function(a){return a>999&&(a=a<1e6?parseInt(a/1e3,10)+"K+":a<1e9?parseInt(a/1e6,10)+"M+":"++"),a},tile:function(a,b){a.style.display="block";var c={height:d.a.tile.scale.height,width:d.a.tile.scale.width},e=d.f.getData(a,"scale-height");e&&e>=d.a.tile.scale.minHeight&&(c.height=parseInt(e,10));var f=d.f.getData(a,"scale-width");f&&f>=d.a.tile.scale.minWidth&&(c.width=parseInt(f,10));var g=d.f.getData(a,"board-width")||a.offsetWidth;g>a.offsetWidth&&(g=a.offsetWidth);var h=Math.floor(g/(c.width+d.a.tile.style.margin));if(h>d.a.tile.maxColumns&&(h=d.a.tile.maxColumns),h<d.a.tile.minColumns)return!1;var i=d.f.make({SPAN:{className:d.a.k+"_embed_board_bd"}});i.style.height=c.height+"px",d.v.renderedWidth=h*(c.width+d.a.tile.style.margin)-d.a.tile.style.margin,i.style.width=d.v.renderedWidth+"px";for(var j=0,k=[],l=0,m=b.length;l<m;l+=1){var n=d.f.make({A:{className:d.a.k+"_embed_board_th",target:"_blank",href:a.href,title:b[l].description}});d.f.set(n,d.a.dataAttributePrefix+"log","embed_board");var o={height:b[l].images["237x"].height*(c.width/b[l].images["237x"].width),width:c.width},p=d.f.make({IMG:{src:b[l].images["237x"].url,nopin:"true",height:o.height,width:o.width,className:d.a.k+"_embed_board_img",alt:b[l].description}});p.style.height=o.height+"px",p.style.width=o.width+"px",p.style.marginTop=0-o.height/d.a.tile.style.margin+"px",o.height>c.height&&(o.height=c.height),n.appendChild(p),n.style.height=o.height+"px",n.style.width=o.width+"px",k[j]||(k[j]=0),n.style.top=k[j]+"px",n.style.left=j*(c.width+d.a.tile.style.margin)+"px",k[j]=k[j]+o.height+d.a.tile.style.margin,n.appendChild(p),i.appendChild(n),j=(j+1)%h}
return i},makeFooter:function(a,b){var c=d.f.make({A:{className:d.a.k+"_embed_board_ft",href:a.href,target:"_blank"}});d.v.renderedWidth>d.a.tile.minWidthToShowAuxText&&(c.innerHTML=d.v.strings.seeOn),d.f.set(c,d.a.dataAttributePrefix+"log",b);var e=d.f.make({SPAN:{className:d.a.k+"_embed_board_ft_logo"}});return c.appendChild(e),c},cssHook:function(a,b){var c=d.f.getData(a,"css-hook");c&&(b.className=b.className+" "+c)},fireBookmark:function(){d.d.b.appendChild(d.f.make({SCRIPT:{type:"text/javascript",charset:"utf-8",src:d.a.endpoint.bookmark+"?r="+99999999*Math.random()}}))},ping:{log:function(){},count:function(a,b){var c=d.d.getElementById(d.a.k+"_pin_count_"+b);if(c){d.f.debug("API replied with count: "+a.count);var e=c.parentNode,f=d.f.getData(e,"config");0===a.count&&("above"===f?(d.f.debug("Rendering zero count above."),c.className=d.a.k+"_pin_it_button_count",c.appendChild(d.d.createTextNode("0"))):d.f.debug("Zero pin count not rendered to the side.")),a.count>0&&(d.f.debug("Got "+a.count+" pins for the requested URL."),"above"===f||"beside"===f?(d.f.debug("Rendering pin count "+f),c.className=d.a.k+"_pin_it_button_count",c.appendChild(d.d.createTextNode(d.f.prettyPinCount(a.count)))):d.f.debug("No valid pin count position specified; not rendering.")),d.f.cssHook(e,c)}else d.f.debug("Pin It button container not found.")},pin:function(a,b){var c=d.d.getElementById(d.a.k+"_"+b);if(c&&a.data&&a.data[0]){d.f.debug("API replied with pin data");var e=a.data[0],f={};if(e.images&&(f=e.images["237x"]),e&&e.id&&e.description&&f.url&&f.width&&f.height){d.f.debug("Found enough data to embed a pin");var g=d.f.make({SPAN:{className:d.a.k+"_embed_pin","data-pin-id":e.id}}),h=d.f.getData(c,"style");"plain"!==h&&(g.className=g.className+" "+d.a.k+"_fancy");var i=d.f.make({A:{className:d.a.k+"_embed_pin_link",title:e.description,href:"http://www.pinterest.com/pin/"+e.id+"/",target:"_blank"}}),j=d.f.make({IMG:{className:d.a.k+"_embed_pin_link_img",alt:e.description,nopin:"true",src:f.url,width:f.width,height:f.height}});d.f.set(j,d.a.dataAttributePrefix+"log","image_from_embedded_pin"),d.f.set(j,d.a.dataAttributePrefix+"href","http://www.pinterest.com/pin/"+e.id+"/"),j.style.width=f.width+"px",j.style.height=f.height+"px",i.appendChild(j);var k=d.f.make({I:{className:d.a.k+"_repin","data-pin-id":e.id}});d.f.set(k,d.a.dataAttributePrefix+"log","repin"),d.f.set(k,d.a.dataAttributePrefix+"href",d.a.endpoint.repin.replace(/%s/,e.id)),i.appendChild(k),k.onclick=function(){this.className.match(/hazClick/)||(this.className=this.className+" "+d.a.k+"_hazClick");var a=d.a.endpoint.repin.replace(/%s/,d.f.get(this,"data-pin-id"));return d.w.open(a,"pin"+(new Date).getTime(),d.a.popLarge),!1};var l=d.f.make({I:{className:d.a.k+"_getThis",innerHTML:d.v.strings.getThis+"<i></i>","data-pin-id":e.id}});i.appendChild(l),l.onclick=function(){var a=d.f.get(this,"data-pin-id");return d.f.getThis("do_embed_pin",a),!1},d.f.set(i,d.a.dataAttributePrefix+"log","embed_pin"),g.appendChild(i);var m=d.f.make({SPAN:{className:d.a.k+"_embed_pin_desc",innerHTML:d.f.filter(e.description)}});if(e.attribution&&e.attribution.url&&e.attribution.author_name&&e.attribution.provider_icon_url){d.f.debug("Building attribution line");var n=d.f.make({SPAN:{className:d.a.k+"_embed_pin_attrib"}});n.appendChild(d.f.make({IMG:{className:d.a.k+"_embed_pin_attrib_icon",src:e.attribution.provider_icon_url}})),n.appendChild(d.f.make({SPAN:{className:d.a.k+"_embed_pin_attrib",innerHTML:d.v.strings.attribTo+' <a href="'+e.attribution.url+'" target="_blank">'+d.f.filter(e.attribution.author_name)+"</a>"}})),m.appendChild(n)}
if(g.appendChild(m),e.pinner&&e.pinner.profile_url&&e.pinner.image_small_url&&e.pinner.full_name){d.f.debug("Building pinner line");var o=d.f.make({A:{className:d.a.k+"_embed_pin_text",href:e.pinner.profile_url,target:"_blank"}});o.appendChild(d.f.make({IMG:{className:d.a.k+"_embed_pin_text_avatar",src:e.pinner.image_small_url}})),o.appendChild(d.f.make({SPAN:{className:d.a.k+"_embed_pin_text_container",innerHTML:d.v.strings.pinnedBy+' <em class="'+d.a.k+'_embed_pin_text_container_em">'+d.f.filter(e.pinner.full_name)+"</em>"}}));var p=d.f.make({B:{className:d.a.k+"_embed_pin_link_shield"}});d.f.set(p,d.a.dataAttributePrefix+"log","pinner_from_embedded_pin"),d.f.set(p,d.a.dataAttributePrefix+"href",e.pinner.profile_url),o.appendChild(p),g.appendChild(o)}
if(e.board&&e.board.url&&e.board.image_thumbnail_url&&e.board.name){d.f.debug("Building board line"),e.board.url.match(/^(\/\/pinterest\.com|http:\/\/pinterest\.com|https:\/\/pinterest\.com)/)||(e.board.url="//www.pinterest.com"+e.board.url,d.f.debug("appending Pinterest prefix to board URL"));var q=d.f.make({A:{className:d.a.k+"_embed_pin_text",href:e.board.url,target:"_blank"}});q.appendChild(d.f.make({IMG:{className:d.a.k+"_embed_pin_text_avatar",src:e.board.image_thumbnail_url}})),q.appendChild(d.f.make({SPAN:{className:d.a.k+"_embed_pin_text_container",innerHTML:d.v.strings.onto+' <em class="'+d.a.k+'_embed_pin_text_container_em">'+d.f.filter(e.board.name)+"</em>"}}));var r=d.f.make({B:{className:d.a.k+"_embed_pin_link_shield"}});d.f.set(r,d.a.dataAttributePrefix+"log","board_from_embedded_pin"),d.f.set(r,d.a.dataAttributePrefix+"href",e.board.url),q.appendChild(r),g.appendChild(q)}
d.f.cssHook(c,g),d.f.replace(c,g)}else d.f.debug("Not enough data to embed a pin; aborting")}},user:function(a,b){var c=d.d.getElementById(d.a.k+"_"+b);if(c&&a.data&&a.data.pins&&a.data.pins.length){d.f.debug("API replied with a user");var e=d.f.make({SPAN:{className:d.a.k+"_embed_board"}}),f=d.f.getData(c,"style");"plain"!==f&&(e.className=e.className+" "+d.a.k+"_fancy");var g=d.f.make({SPAN:{className:d.a.k+"_embed_board_hd"}}),h=d.f.make({A:{className:d.a.k+"_embed_board_title",innerHTML:d.f.filter(a.data.user.full_name),target:"_blank",href:c.href}});d.f.set(h,d.a.dataAttributePrefix+"log","embed_user"),g.appendChild(h),e.appendChild(g);var i=d.f.tile(c,a.data.pins);i&&(e.appendChild(i),c.href=c.href+"pins/",e.appendChild(d.f.makeFooter(c,"embed_user")),d.f.cssHook(c,e),d.f.replace(c,e))}},board:function(a,b){var c=d.d.getElementById(d.a.k+"_"+b);if(c&&a.data&&a.data.pins&&a.data.pins.length){d.f.debug("API replied with a group of pins");var e=d.f.make({SPAN:{className:d.a.k+"_embed_board"}}),f=d.f.getData(c,"style");"plain"!==f&&(e.className=e.className+" "+d.a.k+"_fancy");var g=d.f.tile(c,a.data.pins),h=d.f.make({SPAN:{className:d.a.k+"_embed_board_hd"}}),i=d.f.make({A:{className:d.a.k+"_embed_board_name",innerHTML:d.f.filter(a.data.board.name),target:"_blank",href:c.href}});if(d.f.set(i,d.a.dataAttributePrefix+"log","embed_board"),h.appendChild(i),d.v.renderedWidth>d.a.tile.minWidthToShowAuxText){var j=d.f.make({A:{log:"embed_board",className:d.a.k+"_embed_board_author",innerHTML:"<span>"+d.v.strings.attribTo+"</span> "+d.f.filter(a.data.user.full_name),target:"_blank",href:c.href}});d.f.set(j,d.a.dataAttributePrefix+"log","embed_board"),h.appendChild(j)}else i.className=d.a.k+"_embed_board_title";e.appendChild(h),g&&(e.appendChild(g),e.appendChild(d.f.makeFooter(c,"embed_board")),d.f.cssHook(c,e),d.f.replace(c,e))}}},parse:function(a,b){var c,d,e,f,g,h;if(h={},c=a.split("#")[0].split("?"),c[1])
for(d=c[1].split("&"),f=0,g=d.length;f<g;f+=1)e=d[f].split("="),2===e.length&&b[e[0]]&&(h[e[0]]=e[1]);return h},fixUrl:function(a){var b="";try{b=decodeURIComponent(a)}catch(c){}
return b===a&&(a=encodeURIComponent(a)),a.match(/^http/i)||(a.match(/^%2F%2F/i)||(a="%2F%2F"+a),a="http%3A"+a,d.f.debug("fixed URL: "+a)),a},deepLink:{ios_safari:function(a){var b,c,e,f,g,h,i;b=a.href,c=b.split("?")[1],c=c.replace(/url=/,"source_url="),c=c.replace(/media=/,"image_url="),c="pinit://pinit/?"+c,e=(new Date).getTime(),f=0,g=10,h=80,i=function(){d.w.setTimeout(function(){if(f<g)i();else{var a=e+f*h,c=(new Date).getTime(),j=(c-a)/g;j<h&&(d.w.location=b)}
f+=1},h)},d.w.location=c,i()}},render:{buttonBookmark:function(a){d.f.debug("build bookmarklet button");var b=d.f.make({A:{href:a.href,className:d.a.k+"_pin_it_button "+d.a.k+"_pin_it_button_inline"}});d.f.set(b,d.a.dataAttributePrefix+"log","button_pinit_bookmarklet");var c=d.f.getData(a,"config");d.a.config.pinItCountPosition[c]===!0?(d.f.set(b,d.a.dataAttributePrefix+"config",c),b.className=b.className+" "+d.a.k+"_pin_it_"+c):b.className=b.className+" "+d.a.k+"_pin_it_none",d.f.getPinCount(encodeURIComponent(d.v.here)),b.onclick=function(){return d.f.fireBookmark(),!1};var e=d.f.make({SPAN:{className:d.a.k+"_hidden",id:d.a.k+"_pin_count_"+d.f.callback.length,innerHTML:"<i></i>"}});b.appendChild(e),d.f.replace(a,b)},buttonPin:function(a){d.f.debug("build Pin It button");var b,c;c=d.f.parse(a.href,{url:!0,media:!0,description:!0}),c.media?c.media=d.f.fixUrl(c.media):(c.media="",d.f.debug("no media found; click will pop bookmark")),c.url?c.url=d.f.fixUrl(c.url):(c.url=encodeURIComponent(d.d.URL),d.f.debug("no url found; click will pin this page")),c.description||(c.description=encodeURIComponent(d.d.title||"")),b=d.a.endpoint.create+"url="+c.url+"&media="+c.media+"&guid="+d.v.guid+"-"+d.v.buttonId+"&description="+c.description,d.v.buttonId=d.v.buttonId+1;var e=d.f.make({A:{href:b,className:d.a.k+"_pin_it_button "+d.a.k+"_pin_it_button_inline",target:"_blank"}});d.f.set(e,d.a.dataAttributePrefix+"log","button_pinit");var f=d.f.getData(a,"config");if(d.a.config.pinItCountPosition[f]===!0?(d.f.set(e,d.a.dataAttributePrefix+"config",f),e.className=e.className+" "+d.a.k+"_pin_it_"+f):e.className=e.className+" "+d.a.k+"_pin_it_none",e.onclick=function(){var a=d.f.parse(this.href,{url:!0,media:!0,description:!0});return a.description||d.f.log("&type=config_warning&warning_msg=no_description&href="+encodeURIComponent(d.d.URL)),a.url&&a.url.match(/^http/i)&&a.media&&a.media.match(/^http/i)?"function"===typeof d.f.deepLink[d.v.deepBrowser]?d.f.deepLink[d.v.deepBrowser](this):d.w.open(this.href,"pin"+(new Date).getTime(),d.a.pop):(d.f.log("&type=config_error&error_msg=invalid_url&href="+encodeURIComponent(d.d.URL)),d.f.fireBookmark()),!1},c.url){var g=d.f.make({SPAN:{className:d.a.k+"_hidden",id:d.a.k+"_pin_count_"+d.f.callback.length,innerHTML:"<i></i>"}});e.appendChild(g),d.f.getPinCount(c.url),d.f.replace(a,e)}},buttonFollow:function(a){d.f.debug("build follow button");var b="_follow_me_button",c=d.f.getData(a,"render");c&&(b=b+"_"+c);var e=d.f.make({A:{target:"_blank",href:a.href,innerHTML:a.innerHTML,className:d.a.k+b}});e.appendChild(d.f.make({B:{}})),e.appendChild(d.f.make({I:{}})),d.f.set(e,d.a.dataAttributePrefix+"log","button_follow"),d.f.replace(a,e)},embedPin:function(a){d.f.debug("build embedded pin");var b=a.href.split("/")[4];b&&parseInt(b,10)>0&&d.f.getPinsIn("pin","",{pin_ids:b})},embedUser:function(a){d.f.debug("build embedded profile");var b=a.href.split("/")[3];b&&d.f.getPinsIn("user",b+"/pins/")},embedBoard:function(a){d.f.debug("build embedded board");var b=a.href.split("/")[3],c=a.href.split("/")[4];b&&c&&d.f.getPinsIn("board",b+"/"+c+"/pins/")}},getPinsIn:function(a,b,c){var e,f="",g="?";for(e in c)c[e].hasOwnProperty&&(f=f+g+e+"="+c[e],g="&");d.f.call(d.a.endpoint[a]+b+f,d.f.ping[a])},build:function(a){"object"===typeof a&&null!==a&&a.parentNode||(a=d.d);var b,c,e,f,g,h=a.getElementsByTagName("A"),i={vertical:"above",horizontal:"beside"},j=[];for(c=0,b=h.length;c<b;c+=1)j.push(h[c]);for(c=0,b=j.length;c<b;c+=1)j[c].href&&j[c].href.match(d.a.myDomain)&&(e=d.f.getData(j[c],"do"),!e&&j[c].href.match(/pin\/create\/button/)&&(e="buttonPin",g="none",f=d.f.get(j[c],"count-layout"),f&&i[f]&&(g=i[f]),d.f.set(j[c],"data-pin-config",g)),"function"===typeof d.f.render[e]&&(j[c].id=d.a.k+"_"+d.f.callback.length,d.f.render[e](j[c])))},config:function(){var a,b,c=d.d.getElementsByTagName("SCRIPT"),e=c.length,f=!1;for(a=0;a<e;a+=1)
if(d.a.me&&c[a]&&c[a].src&&c[a].src.match(d.a.me)){if(f===!1){for(b=0;b<d.a.configParam.length;b+=1)d.v.config[d.a.configParam[b]]=d.f.get(c[a],d.a.dataAttributePrefix+d.a.configParam[b]);f=!0}
d.f.kill(c[a])}
"string"===typeof d.v.config.build&&(d.w[d.v.config.build]=function(a){d.f.build(a)}),d.w.setTimeout(function(){"string"===typeof d.v.config.logc?d.f.log("&type=pidget&logc="+d.v.config.logc,d.a.endpoint.logc):d.f.log("&type=pidget")},1e3)},log:function(a,b){b||(b=d.a.endpoint.log);var c="?via="+encodeURIComponent(d.v.here)+"&guid="+d.v.guid;a&&(c+=a),d.f.call(b+c,d.f.ping.log)},init:function(){d.d.b=d.d.getElementsByTagName("BODY")[0],d.d.h=d.d.getElementsByTagName("HEAD")[0],d.v={resolution:1,here:d.d.URL.split("#")[0],hazFloatingButton:!1,config:{},strings:d.a.strings.en,guid:"",buttonId:0,deepBrowser:null},null!==d.w.navigator.userAgent.match(/iP/)&&null===d.w.navigator.userAgent.match(/Pinterest/)&&null===d.w.navigator.userAgent.match(/CriOS/)&&(d.v.deepBrowser="ios_safari");for(var a=0;a<12;a+=1)d.v.guid=d.v.guid+"0123456789ABCDEFGHJKLMNPQRSTUVWXYZ_abcdefghijkmnopqrstuvwxyz".substr(Math.floor(60*Math.random()),1);var b=d.d.getElementsByTagName("HTML")[0].getAttribute("lang");b&&(b=b.toLowerCase(),"object"===typeof d.a.strings[b]?d.v.strings=d.a.strings[b]:(b=b.split("-")[0],"object"===typeof d.a.strings[b]&&(d.v.strings=d.a.strings[b]))),d.w.devicePixelRatio&&d.w.devicePixelRatio>=2&&(d.v.resolution=2),d.f.config(),d.f.build(),d.f.presentation(),d.f.behavior()}}}()};d.f.init()}(window,document,{k:"PIN_"+(new Date).getTime(),myDomain:/^https?:\/\/(www\.|)pinterest\.com\//,me:/pinit.*?\.js$/,floatingButtonOffsetTop:120,floatingButtonOffsetLeft:130,endpoint:{bookmark:"//assets.pinterest.com/js/pinmarklet.js",builder:"http://business.pinterest.com/widget-builder/#",count:"//widgets.pinterest.com/v1/urls/count.json",pin:"//widgets.pinterest.com/v3/pidgets/pins/info/",repin:"//pinterest.com/pin/%s/repin/x/",board:"//widgets.pinterest.com/v3/pidgets/boards/",user:"//widgets.pinterest.com/v3/pidgets/users/",log:"//log.pinterest.com/",logc:"//logc.pinterest.com/",create:"//www.pinterest.com/pin/create/button/?"},config:{pinItCountPosition:{none:!0,above:!0,beside:!0}},minImgSize:300,countSource:6,dataAttributePrefix:"data-pin-",configParam:["build","debug","style","hover","logc"],pop:"status=no,resizable=yes,scrollbars=yes,personalbar=no,directories=no,location=no,toolbar=no,menubar=no,width=632,height=270,left=0,top=0",popLarge:"status=no,resizable=yes,scrollbars=yes,personalbar=no,directories=no,location=no,toolbar=no,menubar=no,width=900,height=500,left=0,top=0",cdn:{"https:":"https://s-passets.pinimg.com","http:":"http://s-passets.pinimg.com"},tile:{scale:{minWidth:200,minHeight:200,width:92,height:175,},minWidthToShowAuxText:150,minContentWidth:120,minColumns:1,maxColumns:6,style:{margin:2,padding:10}},strings:{en:{seeOn:"See On",getThis:"get this",attribTo:"by",pinnedBy:"Pinned by",onto:"Onto"},de:{seeOn:"Ansehen auf",getThis:"bekomme",attribTo:"von",pinnedBy:"Gepinnt von",onto:"Auf"},es:{seeOn:"Ver En",getThis:"obtener",attribTo:"por",pinnedBy:"Pineado por",onto:"En"},fr:{seeOn:"Voir sur",getThis:"obtenir",attribTo:"par",pinnedBy:"&#201;pingl&#233; par",onto:"Sur"},nl:{seeOn:"Bekijken op",getThis:"krijg",attribTo:"door",pinnedBy:"Gepind door",onto:"Op"},pt:{seeOn:"Ver em",getThis:"obter",attribTo:"por",pinnedBy:"Pin afixado por",onto:"Em"},"pt-br":{seeOn:"Ver em",getThis:"obter",attribTo:"por",pinnedBy:"Pinado por",onto:"Em"}},rules:["a._pin_it_button {  background-image: url(_cdn/images/pidgets/bps_rez.png); background-repeat: none; background-size: 40px 60px; height: 20px; margin: 235px 0 0 310px; padding: 0; vertical-align: baseline; text-decoration: none; width: 40px; background-position: 0 -20px }","a._pin_it_button:hover { background-position: 0 0px }","a._pin_it_button:active, a._pin_it_button._hazClick { background-position: 0 -40px }","a._pin_it_button_inline { position: relative; display: inline-block; }","a._pin_it_button_floating { position: absolute; }","a._pin_it_button span._pin_it_button_count { position: absolute; color: #777; text-align: center; text-indent: 0; }","a._pin_it_above span._pin_it_button_count { background: transparent url(_cdn/images/pidgets/fpa_rez.png) 0 0 no-repeat; background-size: 40px 29px; position: absolute; bottom: 21px; left: 0px; height: 29px; width: 40px; font: 12px Arial, Helvetica, sans-serif; line-height: 24px; text-indent: 0;}","a._pin_it_beside span._pin_it_button_count, a._pin_it_beside span._pin_it_button_count i { background-color: transparent; background-repeat: no-repeat; background-image: url(_cdn/images/pidgets/fpb_rez.png); }","a._pin_it_beside span._pin_it_button_count { padding: 0 3px 0 10px; background-size: 45px 20px; background-position: 0 0; position: absolute; top: 0; left: 41px; height: 20px; font: 10px Arial, Helvetica, sans-serif; line-height: 20px; }","a._pin_it_beside span._pin_it_button_count i { background-position: 100% 0; position: absolute; top: 0; right: -2px; height: 20px; width: 2px; }","a._pin_it_button._pin_it_above { margin-top: 20px; }","a._follow_me_button, a._follow_me_button i { background-size: 200px 60px; background: transparent url(_cdn/images/pidgets/bfs_rez.png) 0 0 no-repeat }",'a._follow_me_button { color: #444; display: inline-block; font: bold normal normal 11px/20px "Helvetica Neue",helvetica,arial,san-serif; height: 20px; margin: 0; padding: 0; position: relative; text-decoration: none; text-indent: 19px; vertical-align: baseline;}',"a._follow_me_button:hover { background-position: 0 -20px}","a._follow_me_button:active  { background-position: 0 -40px}","a._follow_me_button b { position: absolute; top: 3px; left: 3px; height: 14px; width: 14px; background-size: 14px 14px; background-image: url(_cdn/images/pidgets/log_rez.png); }","a._follow_me_button i { position: absolute; top: 0; right: -4px; height: 20px; width: 4px; background-position: 100% 0px; }","a._follow_me_button:hover i { background-position: 100% -20px;  }","a._follow_me_button:active i { background-position: 100% -40px; }","a._follow_me_button_tall, a._follow_me_button_tall i { background-size: 400px 84px; background: transparent url(_cdn/images/pidgets/bft_rez.png) 0 0 no-repeat }",'a._follow_me_button_tall { color: #444; display: inline-block; font: bold normal normal 13px/28px "Helvetica Neue",helvetica,arial,san-serif; height: 28px; margin: 0; padding: 0; position: relative; text-decoration: none; text-indent: 33px; vertical-align: baseline;}',"a._follow_me_button_tall:hover { background-position: 0 -28px}","a._follow_me_button_tall:active  { background-position: 0 -56px}","a._follow_me_button_tall b { position: absolute; top: 5px; left: 10px; height: 18px; width: 18px; background-size: 18px 18px; background-image: url(_cdn/images/pidgets/smt_rez.png); }","a._follow_me_button_tall i { position: absolute; top: 0; right: -10px; height: 28px; width: 10px; background-position: 100% 0px; }","a._follow_me_button_tall:hover i { background-position: 100% -28px;  }","a._follow_me_button_tall:active i { background-position: 100% -56px; }","span._embed_pin { display: inline-block; text-align: center; width: 237px; overflow: hidden; vertical-align: top; }","span._embed_pin._fancy { background: #fff; box-shadow: 0 0 3px #aaa; border-radius: 3px; }","span._embed_pin a._embed_pin_link { display: block;  margin: 0 auto; padding: 0; position: relative;  line-height: 0}","span._embed_pin img { border: 0; margin: 0; padding: 0;}","span._embed_pin a._embed_pin_link i._repin { left: 10px; top: 10px; position: absolute; height: 33px; width: 64px; background: transparent url(_cdn/images/pidgets/repin_rez.png); background-size: 64px 99px; }","span._embed_pin a._embed_pin_link i._repin:hover { background-position: 0 -33px; }","span._embed_pin a._embed_pin_link i._repin._hazClick { background-position: 0 -66px; }","span._embed_pin a._embed_pin_link i._getThis { display: none }","span._embed_pin a._embed_pin_link:hover i._getThis, span._embed_pin a._embed_pin_link:hover i._getThis i { background: transparent url(_cdn/images/pidgets/bfs1.png) }",'span._embed_pin a._embed_pin_link:hover i._getThis { color: #555; display: inline-block; font: normal normal normal 11px/20px "Helvetica Neue",helvetica,arial,san-serif; height: 20px; margin: 0; padding: 0 1px 0 5px; position: absolute; bottom: 10px; right: 10px; text-decoration: none;  }',"span._embed_pin a._embed_pin_link:hover i._getThis:hover { background-position: 0 -20px }","span._embed_pin a._embed_pin_link:hover i._getThis i { position: absolute; top: 0; right: -4px; height: 20px; width: 5px; background-position: 100% 0px }","span._embed_pin a._embed_pin_link:hover i._getThis:hover i { background-position: 100% -20px }",'span._embed_pin span._embed_pin_desc { color: #333; white-space: normal; border-bottom: 1px solid #eee; display: block; font-family: "Helvetica Neue", arial, sans-serif; font-size: 12px; line-height: 17px; padding: 10px; text-align: left; }','span._embed_pin span._embed_pin_attrib, span._embed_pin span._embed_pin_text_container { color: #a7a7a7; font-family: "Helvetica", sans-serif; font-size: 10px; line-height: 18px; font-weight: bold; display: block;}',"span._embed_pin span._embed_pin_attrib img._embed_pin_attrib_icon { height: 16px; width: 16px; vertical-align: middle; margin-right: 5px; float: left;}","span._embed_pin span._embed_pin_attrib a { color: #a7a7a7; text-decoration: none;}",'span._embed_pin a._embed_pin_text, span._embed_pin a._embed_pin_text span._embed_pin_text_container { position: relative; text-decoration: none; display: block; font-weight: bold; color: #b7b7b7; font-family: "Helvetica Neue", arial, sans-serif; font-size: 11px; line-height: 14px; height: 39px; text-align: left; }',"span._embed_pin a._embed_pin_text { padding: 5px 0 0 7px; }","span._embed_pin a._embed_pin_text:hover { background: #eee;}","span._embed_pin a._embed_pin_text img._embed_pin_text_avatar { border-radius: 2px; overflow: hidden; height: 30px; width: 30px; vertical-align: middle; margin-right: 5px; float: left;}","span._embed_pin a._embed_pin_text span._embed_pin_text_container em._embed_pin_text_container_em { font-family: inherit; display: block; color: #717171; font-style: normal; width: 180px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }","span._embed_pin a._embed_pin_text b._embed_pin_link_shield { position: absolute; top: 0; left: 0; height: 100%; width: 100%; }","span._embed_board { display: inline-block; margin: 0; padding:10px 0; position: relative; text-align: center}","span._embed_board._fancy { background: #fff; box-shadow: 0 0 3px #aaa; border-radius: 3px; }","span._embed_board span._embed_board_hd { display: block; margin: 0 10px; padding: 0; line-height: 20px; height: 25px; position: relative;  }","span._embed_board span._embed_board_hd a { cursor: pointer; background: inherit; text-decoration: none; width: 48%; white-space: nowrap; position: absolute; top: 0; overflow: hidden;  text-overflow: ellipsis; }","span._embed_board span._embed_board_hd a:hover { text-decoration: none; background: inherit; }","span._embed_board span._embed_board_hd a:active { text-decoration: none; background: inherit; }","span._embed_board span._embed_board_hd a._embed_board_title { width: 100%; position: absolute; left: 0; text-align: left; font-family: Georgia; font-size: 16px; color:#2b1e1e;}","span._embed_board span._embed_board_hd a._embed_board_name { position: absolute; left: 0; text-align: left; font-family: Georgia; font-size: 16px; color:#2b1e1e;}","span._embed_board span._embed_board_hd a._embed_board_author { position: absolute; right: 0; text-align: right; font-family: Helvetica; font-size: 11px; color: #746d6a; font-weight: bold;}","span._embed_board span._embed_board_hd a._embed_board_author span { font-weight: normal; }","span._embed_board span._embed_board_bd { display:block; margin: 0 10px; overflow: hidden; border-radius: 2px; position: relative; }","span._embed_board span._embed_board_bd a._embed_board_th { cursor: pointer; display: inline-block; position: absolute; overflow: hidden; }",'span._embed_board span._embed_board_bd a._embed_board_th::before { position: absolute; content:""; z-index: 2; top: 0; left: 0; right: 0; bottom: 0; box-shadow: inset 0 0 2px #888; }',"span._embed_board span._embed_board_bd a._embed_board_th img._embed_board_img { border: none; position: absolute; top: 50%; left: 0; }","a._embed_board_ft { text-shadow: 0 1px #fff; display: block; text-align: center; border: 1px solid #ccc; margin: 10px 10px 0; height: 31px; line-height: 30px;border-radius: 2px; text-decoration: none; font-family: Helvetica; font-weight: bold; font-size: 13px; color: #746d6a; background: #f4f4f4 url(_cdn/images/pidgets/board_button_link.png) 0 0 repeat-x}","a._embed_board_ft:hover { text-decoration: none; background: #fefefe url(_cdn/images/pidgets/board_button_hover.png) 0 0 repeat-x}","a._embed_board_ft:active { text-decoration: none; background: #e4e4e4 url(_cdn/images/pidgets/board_button_active.png) 0 0 repeat-x}","a._embed_board_ft span._embed_board_ft_logo { vertical-align: top; display: inline-block; margin-left: 2px; height: 30px; width: 66px; background: transparent url(_cdn/images/pidgets/board_button_logo.png) 50% 48% no-repeat; }","._hidden { display:none; }"]});