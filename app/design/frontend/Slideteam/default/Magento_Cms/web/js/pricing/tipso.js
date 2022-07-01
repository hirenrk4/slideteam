/*!
 * tipso - A Lightweight Responsive jQuery Tooltip Plugin v1.0.8
 * Copyright (c) 2014-2015 Bojan Petkovski
 * http://tipso.object505.com
 * Licensed under the MIT license
 * http://object505.mit-license.org/
 */
 // CommonJS, AMD or browser globals
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        require(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function(jQuery) {
  var pluginName = "tipso",
    defaults = {
      speed             : 400,          //Animation speed
      background        : '#dedede',
      titleBackground   : '#333333',
      color             : '#585858',
      titleColor        : '#ffffff',
      titleContent      : '',           //Content of the title bar
      showArrow         : true,
      position          : 'top',
      width             : 200,
      maxWidth          : '',
      delay             : 200,
      hideDelay         : 0,
      animationIn       : '',
      animationOut      : '',
      offsetX           : 0,
      offsetY           : 0,
      arrowWidth        : 8,
      tooltipHover      : false,
      content           : null,
      ajaxContentUrl    : null,
      ajaxContentBuffer : 0,
      contentElementId  : null,         //Normally used for picking template scripts
      useTitle          : false,        //Use the title tag as tooptip or not
      templateEngineFunc: null,         //A function that compiles and renders the content
      onBeforeShow      : null,
      onShow            : null,
      onHide            : null
    };

  function Plugin(element, options) {
    this.element = element;
    this.jQueryelement = jQuery(this.element);
    this.doc = jQuery(document);
    this.win = jQuery(window);
    this.settings = jQuery.extend({}, defaults, options);

    /*
     * Process and add data-attrs to settings as well for ease of use. Also, if
     * data-tipso is an object then use it as extra settings and if it's not
     * then use it as a title.
     */
    if (typeof(this.jQueryelement.data("tipso")) === "object")
    {
      jQuery.extend(this.settings, this.jQueryelement.data("tipso"));
    }

    var data_keys = Object.keys(this.jQueryelement.data());
    var data_attrs = {};
    for (var i = 0; i < data_keys.length; i++)
    {
      var key = data_keys[i].replace(pluginName, "");
      if (key === "")
      {
        continue;
      }
      //lowercase first letter
      key = key.charAt(0).toLowerCase() + key.slice(1);
      data_attrs[key] = this.jQueryelement.data(data_keys[i]);

      //We cannot use extend for data_attrs because they are automatically
      //lowercased. We need to do this manually and extend this.settings with
      //data_attrs
      for (var settings_key in this.settings)
      {
        if (settings_key.toLowerCase() == key)
        {
          this.settings[settings_key] = data_attrs[key];
        }
      }
    }

    this._defaults = defaults;
    this._name = pluginName;
    this._title = this.jQueryelement.attr('title');
    this.mode = 'hide';
    this.ieFade = !supportsTransitions;

    //By keeping the original prefered position and repositioning by calling
    //the reposition function we can make for more smart and easier positioning
    //in complex scenarios!
    this.settings.preferedPosition = this.settings.position;

    this.init();
  }

  jQuery.extend(Plugin.prototype, {
    init: function() {
      var obj = this,
        jQuerye = this.jQueryelement,
        jQuerydoc = this.doc;
      jQuerye.addClass('tipso_style').removeAttr('title');

      if (obj.settings.tooltipHover) {
        var waitForHover = null,
            hoverHelper = null;
        jQuerye.on('mouseover' + '.' + pluginName, function() {
          clearTimeout(waitForHover);
          clearTimeout(hoverHelper);
          hoverHelper = setTimeout(function(){
            obj.show();
          }, 150);
        });
        jQuerye.on('mouseout' + '.' + pluginName, function() {
          clearTimeout(waitForHover);
          clearTimeout(hoverHelper);
          waitForHover = setTimeout(function(){
            obj.hide();
          }, 200);

          obj.tooltip()
            .on('mouseover' + '.' + pluginName, function() {
              obj.mode = 'tooltipHover';
            })
            .on('mouseout' + '.' + pluginName, function() {
              obj.mode = 'show';
              clearTimeout(waitForHover);
              waitForHover = setTimeout(function(){
                obj.hide();
              }, 200);
            })
        ;
        });
      } else {
        jQuerye.on('mouseover' + '.' + pluginName, function() {
          obj.show();
        });
        jQuerye.on('mouseout' + '.' + pluginName, function() {
          obj.hide();
        });
      }
	  if(obj.settings.ajaxContentUrl)
	  {
		obj.ajaxContent = null;
	  }
    },
    tooltip: function() {
      if (!this.tipso_bubble) {
        this.tipso_bubble = jQuery(
          '<div class="tipso_bubble"><div class="tipso_title"></div><div class="tipso_content"></div><div class="tipso_arrow"></div></div>'
        );
      }
      return this.tipso_bubble;
    },
    show: function() {
      var tipso_bubble = this.tooltip(),
        obj = this,
        jQuerywin = this.win;

      if (obj.settings.showArrow === false) {
          tipso_bubble.find(".tipso_arrow").hide();
      }
      else {
          tipso_bubble.find(".tipso_arrow").show();
      }

      if (obj.mode === 'hide') {
        if (jQuery.isFunction(obj.settings.onBeforeShow)) {
          obj.settings.onBeforeShow(obj.jQueryelement, obj.element, obj);
        }
        if (obj.settings.size) {
            tipso_bubble.addClass(obj.settings.size);
        }
        if (obj.settings.width) {
          tipso_bubble.css({
            background: obj.settings.background,
            color: obj.settings.color,
            width: obj.settings.width
          }).hide();
        } else if (obj.settings.maxWidth){
          tipso_bubble.css({
            background: obj.settings.background,
            color: obj.settings.color,
            maxWidth: obj.settings.maxWidth
          }).hide();
        } else {
          tipso_bubble.css({
            background: obj.settings.background,
            color: obj.settings.color,
            width: 200
          }).hide();
        }
        tipso_bubble.find('.tipso_title').css({
            background: obj.settings.titleBackground,
            color: obj.settings.titleColor
        });
        tipso_bubble.find('.tipso_content').html(obj.content());
        tipso_bubble.find('.tipso_title').html(obj.titleContent());
        reposition(obj);

        jQuerywin.on('resize' + '.' + pluginName, function tipsoResizeHandler () {
            obj.settings.position = obj.settings.preferedPosition;
            reposition(obj);
        });

        window.clearTimeout(obj.timeout);
        obj.timeout = null;
        obj.timeout = window.setTimeout(function() {
          if (obj.ieFade || obj.settings.animationIn === '' || obj.settings.animationOut === ''){
            tipso_bubble.appendTo('body').stop(true, true).fadeIn(obj.settings
            .speed, function() {
              obj.mode = 'show';
              if (jQuery.isFunction(obj.settings.onShow)) {
                obj.settings.onShow(obj.jQueryelement, obj.element, obj);
              }
            });
          } else {
            tipso_bubble.remove().appendTo('body')
            .stop(true, true)
            .removeClass('animated ' + obj.settings.animationOut)
            .addClass('noAnimation')
            .removeClass('noAnimation')
            .addClass('animated ' + obj.settings.animationIn).fadeIn(obj.settings.speed, function() {
              jQuery(this).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                jQuery(this).removeClass('animated ' + obj.settings.animationIn);
              });
              obj.mode = 'show';
              if (jQuery.isFunction(obj.settings.onShow)) {
                obj.settings.onShow(obj.jQueryelement, obj.element, obj);
              }
              jQuerywin.off('resize' + '.' + pluginName, null, 'tipsoResizeHandler');
            });
          }
        }, obj.settings.delay);
      }
    },
    hide: function(force) {
      var obj = this,
        jQuerywin = this.win,
        tipso_bubble = this.tooltip(),
        hideDelay = obj.settings.hideDelay;

      if (force) {
        hideDelay = 0;
        obj.mode = 'show';
      }

      window.clearTimeout(obj.timeout);
      obj.timeout = null;
      obj.timeout = window.setTimeout(function() {
        if (obj.mode !== 'tooltipHover') {
          if (obj.ieFade || obj.settings.animationIn === '' || obj.settings.animationOut === ''){
            tipso_bubble.stop(true, true).fadeOut(obj.settings.speed,
            function() {
              jQuery(this).remove();
              if (jQuery.isFunction(obj.settings.onHide) && obj.mode === 'show') {
                obj.settings.onHide(obj.jQueryelement, obj.element, obj);
              }
              obj.mode = 'hide';
              jQuerywin.off('resize' + '.' + pluginName, null, 'tipsoResizeHandler');
            });
          } else {
            tipso_bubble.stop(true, true)
            .removeClass('animated ' + obj.settings.animationIn)
            .addClass('noAnimation').removeClass('noAnimation')
            .addClass('animated ' + obj.settings.animationOut)
            .one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
              jQuery(this).removeClass('animated ' + obj.settings.animationOut).remove();
              if (jQuery.isFunction(obj.settings.onHide) && obj.mode === 'show') {
                obj.settings.onHide(obj.jQueryelement, obj.element, obj);
              }
              obj.mode = 'hide';
              jQuerywin.off('resize' + '.' + pluginName, null, 'tipsoResizeHandler');
            });
          }
        }
      }, hideDelay);
    },
    close: function() {
      this.hide(true);
    },
    destroy: function() {
      var jQuerye = this.jQueryelement,
        jQuerywin = this.win,
        jQuerydoc = this.doc;
      jQuerye.off('.' + pluginName);
      jQuerywin.off('resize' + '.' + pluginName, null, 'tipsoResizeHandler');
      jQuerye.removeData(pluginName);
      jQuerye.removeClass('tipso_style').attr('title', this._title);
    },
    titleContent: function() {
        var content,
          jQuerye = this.jQueryelement,
          obj = this;
        if (obj.settings.titleContent)
        {
            content = obj.settings.titleContent;
        }
        else
        {
            content = jQuerye.data('tipso-title');
        }
        return content;
    },
    content: function() {
      var content,
        jQuerye = this.jQueryelement,
        obj = this,
        title = this._title;
      if (obj.settings.ajaxContentUrl)
      {
		if(obj._ajaxContent)
		{
			content = obj._ajaxContent;
		}
		else 
		{
			obj._ajaxContent = content = jQuery.ajax({
			  type: "GET",
			  url: obj.settings.ajaxContentUrl,
			  async: false
			}).responseText;
			if(obj.settings.ajaxContentBuffer > 0)
			{
				setTimeout(function(){ 
					obj._ajaxContent = null;
				}, obj.settings.ajaxContentBuffer);
			}
			else 
			{
				obj._ajaxContent = null;
			}
		}
      }
      else if (obj.settings.contentElementId)
      {
        content = jQuery("#" + obj.settings.contentElementId).text();
      }
      else if (obj.settings.content)
      {
        content = obj.settings.content;
      }
      else
      {
        if (obj.settings.useTitle === true)
        {
          content = title;
        }
        else
        {
          // Only use data-tipso as content if it's not being used for settings
          if (typeof(jQuerye.data("tipso")) === "string")
          {
            content = jQuerye.data('tipso');
          }
        }
      }
      if (obj.settings.templateEngineFunc !== null)
      {
          content = obj.settings.templateEngineFunc(content);
      }
      return content;
    },
    update: function(key, value) {
      var obj = this;
      if (value) {
        obj.settings[key] = value;
      } else {
        return obj.settings[key];
      }
    }
  });

  function realHeight(obj) {
    var clone = obj.clone();
    clone.css("visibility", "hidden");
    jQuery('body').append(clone);
    var height = clone.outerHeight();
    var width = clone.outerWidth();
    clone.remove();
    return {
      'width' : width,
      'height' : height
    };
  }

  var supportsTransitions = (function() {
    var s = document.createElement('p').style,
        v = ['ms','O','Moz','Webkit'];
    if( s['transition'] === '' ) return true;
    while( v.length )
        if( v.pop() + 'Transition' in s )
            return true;
    return false;
  })();

  function removeCornerClasses(obj) {
    obj.removeClass("top_right_corner bottom_right_corner top_left_corner bottom_left_corner");
    obj.find(".tipso_title").removeClass("top_right_corner bottom_right_corner top_left_corner bottom_left_corner");
  }

  function reposition(thisthat) {
    var tipso_bubble = thisthat.tooltip(),
      jQuerye = thisthat.jQueryelement,
      obj = thisthat,
      jQuerywin = jQuery(window),
      arrow = 10,
      pos_top, pos_left, diff;

    var arrow_color = obj.settings.background;
    var title_content = obj.titleContent();
    if (title_content !== undefined && title_content !== '') {
        arrow_color = obj.settings.titleBackground;
    }

    if (jQuerye.parent().outerWidth() > jQuerywin.outerWidth()) {
      jQuerywin = jQuerye.parent();
    }

    switch (obj.settings.position)
    {
      case 'top-right':
        pos_left = jQuerye.offset().left + (jQuerye.outerWidth());
        pos_top = jQuerye.offset().top - realHeight(tipso_bubble).height - arrow;
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: -obj.settings.arrowWidth,
          marginTop: '',
        });
        if (pos_top < jQuerywin.scrollTop())
        {
          pos_top = jQuerye.offset().top + jQuerye.outerHeight() + arrow;

          tipso_bubble.find('.tipso_arrow').css({
            'border-bottom-color': arrow_color,
            'border-top-color': 'transparent',
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });

          /*
           * Hide and show the appropriate rounded corners
           */
          removeCornerClasses(tipso_bubble);
          tipso_bubble.addClass("bottom_right_corner");
          tipso_bubble.find(".tipso_title").addClass("bottom_right_corner");
          tipso_bubble.find('.tipso_arrow').css({
            'border-left-color': arrow_color,
          });

          tipso_bubble.removeClass('top-right top bottom left right');
          tipso_bubble.addClass('bottom');
        }
        else
        {
          tipso_bubble.find('.tipso_arrow').css({
            'border-top-color': obj.settings.background,
            'border-bottom-color': 'transparent ',
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });

          /*
           * Hide and show the appropriate rounded corners
           */
          removeCornerClasses(tipso_bubble);
          tipso_bubble.addClass("top_right_corner");
          tipso_bubble.find('.tipso_arrow').css({
            'border-left-color': obj.settings.background,
          });

          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass('top');
        }
        break;
      case 'top-left':
        pos_left = jQuerye.offset().left - (realHeight(tipso_bubble).width);
        pos_top = jQuerye.offset().top - realHeight(tipso_bubble).height - arrow;
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: -obj.settings.arrowWidth,
          marginTop: '',
        });
        if (pos_top < jQuerywin.scrollTop())
        {
          pos_top = jQuerye.offset().top + jQuerye.outerHeight() + arrow;

          tipso_bubble.find('.tipso_arrow').css({
            'border-bottom-color': arrow_color,
            'border-top-color': 'transparent',
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });

          /*
           * Hide and show the appropriate rounded corners
           */
          removeCornerClasses(tipso_bubble);
          tipso_bubble.addClass("bottom_left_corner");
          tipso_bubble.find(".tipso_title").addClass("bottom_left_corner");
          tipso_bubble.find('.tipso_arrow').css({
            'border-right-color': arrow_color,
          });

          tipso_bubble.removeClass('top-right top bottom left right');
          tipso_bubble.addClass('bottom');
        }
        else
        {
          tipso_bubble.find('.tipso_arrow').css({
            'border-top-color': obj.settings.background,
            'border-bottom-color': 'transparent ',
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });

          /*
           * Hide and show the appropriate rounded corners
           */
          removeCornerClasses(tipso_bubble);
          tipso_bubble.addClass("top_left_corner");
          tipso_bubble.find('.tipso_arrow').css({
            'border-right-color': obj.settings.background,
          });

          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass('top');
        }
        break;

      /*
       * Bottom right position
       */
      case 'bottom-right':
       pos_left = jQuerye.offset().left + (jQuerye.outerWidth());
       pos_top = jQuerye.offset().top + jQuerye.outerHeight() + arrow;
       tipso_bubble.find('.tipso_arrow').css({
         marginLeft: -obj.settings.arrowWidth,
         marginTop: '',
       });
       if (pos_top + realHeight(tipso_bubble).height > jQuerywin.scrollTop() + jQuerywin.outerHeight())
       {
         pos_top = jQuerye.offset().top - realHeight(tipso_bubble).height - arrow;

         tipso_bubble.find('.tipso_arrow').css({
           'border-bottom-color': 'transparent',
           'border-top-color': obj.settings.background,
           'border-left-color': 'transparent',
           'border-right-color': 'transparent'
         });

         /*
          * Hide and show the appropriate rounded corners
          */
         removeCornerClasses(tipso_bubble);
         tipso_bubble.addClass("top_right_corner");
         tipso_bubble.find(".tipso_title").addClass("top_left_corner");
         tipso_bubble.find('.tipso_arrow').css({
           'border-left-color': obj.settings.background,
         });

         tipso_bubble.removeClass('top-right top bottom left right');
         tipso_bubble.addClass('top');
       }
       else
       {
         tipso_bubble.find('.tipso_arrow').css({
           'border-top-color': 'transparent',
           'border-bottom-color': arrow_color,
           'border-left-color': 'transparent',
           'border-right-color': 'transparent'
         });

         /*
          * Hide and show the appropriate rounded corners
          */
         removeCornerClasses(tipso_bubble);
         tipso_bubble.addClass("bottom_right_corner");
         tipso_bubble.find(".tipso_title").addClass("bottom_right_corner");
         tipso_bubble.find('.tipso_arrow').css({
           'border-left-color': arrow_color,
         });

         tipso_bubble.removeClass('top bottom left right');
         tipso_bubble.addClass('bottom');
       }
       break;

       /*
        * Bottom left position
        */
       case 'bottom-left':
        pos_left = jQuerye.offset().left - (realHeight(tipso_bubble).width);
        pos_top = jQuerye.offset().top + jQuerye.outerHeight() + arrow;
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: -obj.settings.arrowWidth,
          marginTop: '',
        });
        if (pos_top + realHeight(tipso_bubble).height > jQuerywin.scrollTop() + jQuerywin.outerHeight())
        {
          pos_top = jQuerye.offset().top - realHeight(tipso_bubble).height - arrow;

          tipso_bubble.find('.tipso_arrow').css({
            'border-bottom-color': 'transparent',
            'border-top-color': obj.settings.background,
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });

          /*
           * Hide and show the appropriate rounded corners
           */
          removeCornerClasses(tipso_bubble);
          tipso_bubble.addClass("top_left_corner");
          tipso_bubble.find(".tipso_title").addClass("top_left_corner");
          tipso_bubble.find('.tipso_arrow').css({
            'border-right-color': obj.settings.background,
          });

          tipso_bubble.removeClass('top-right top bottom left right');
          tipso_bubble.addClass('top');
        }
        else
        {
          tipso_bubble.find('.tipso_arrow').css({
            'border-top-color': 'transparent',
            'border-bottom-color': arrow_color,
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });

          /*
           * Hide and show the appropriate rounded corners
           */
          removeCornerClasses(tipso_bubble);
          tipso_bubble.addClass("bottom_left_corner");
          tipso_bubble.find(".tipso_title").addClass("bottom_left_corner");
          tipso_bubble.find('.tipso_arrow').css({
            'border-right-color': arrow_color,
          });

          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass('bottom');
        }
        break;
      /*
       * Top position
       */
      case 'top':
        pos_left = jQuerye.offset().left + (jQuerye.outerWidth() / 2) - (realHeight(tipso_bubble).width / 2);
        pos_top = jQuerye.offset().top - realHeight(tipso_bubble).height - arrow;
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: -obj.settings.arrowWidth,
          marginTop: '',
        });
        if (pos_top < jQuerywin.scrollTop())
        {
          pos_top = jQuerye.offset().top + jQuerye.outerHeight() + arrow;

          tipso_bubble.find('.tipso_arrow').css({
            'border-bottom-color': arrow_color,
            'border-top-color': 'transparent',
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });

          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass('bottom');
        }
        else
        {
          tipso_bubble.find('.tipso_arrow').css({
            'border-top-color': obj.settings.background,
            'border-bottom-color': 'transparent',
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });
          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass('top');
        }
        break;
      case 'bottom':
        pos_left = jQuerye.offset().left + (jQuerye.outerWidth() / 2) - (realHeight(tipso_bubble).width / 2);
        pos_top = jQuerye.offset().top + jQuerye.outerHeight() + arrow;
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: -obj.settings.arrowWidth,
          marginTop: '',
        });
        if (pos_top + realHeight(tipso_bubble).height > jQuerywin.scrollTop() + jQuerywin.outerHeight())
        {
          pos_top = jQuerye.offset().top - realHeight(tipso_bubble).height - arrow;
          tipso_bubble.find('.tipso_arrow').css({
            'border-top-color': obj.settings.background,
            'border-bottom-color': 'transparent',
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });
          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass('top');
        }
        else
        {
          tipso_bubble.find('.tipso_arrow').css({
            'border-bottom-color': arrow_color,
            'border-top-color': 'transparent',
            'border-left-color': 'transparent',
            'border-right-color': 'transparent'
          });
          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass(obj.settings.position);
        }
        break;
      case 'left':
        pos_left = jQuerye.offset().left - realHeight(tipso_bubble).width - arrow;
        pos_top = jQuerye.offset().top + (jQuerye.outerHeight() / 2) - (realHeight(tipso_bubble).height / 2);
        tipso_bubble.find('.tipso_arrow').css({
          marginTop: -obj.settings.arrowWidth,
          marginLeft: ''
        });
        if (pos_left < jQuerywin.scrollLeft())
        {
          pos_left = jQuerye.offset().left + jQuerye.outerWidth() + arrow;
          tipso_bubble.find('.tipso_arrow').css({
            'border-right-color': obj.settings.background,
            'border-left-color': 'transparent',
            'border-top-color': 'transparent',
            'border-bottom-color': 'transparent'
          });
          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass('right');
        }
        else
        {
          tipso_bubble.find('.tipso_arrow').css({
            'border-left-color': obj.settings.background,
            'border-right-color': 'transparent',
            'border-top-color': 'transparent',
            'border-bottom-color': 'transparent'
          });
          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass(obj.settings.position);
        }
        break;
      case 'right':
        pos_left = jQuerye.offset().left + jQuerye.outerWidth() + arrow;
        pos_top = jQuerye.offset().top + (jQuerye.outerHeight() / 2) - (realHeight(tipso_bubble).height / 2);
        tipso_bubble.find('.tipso_arrow').css({
          marginTop: -obj.settings.arrowWidth,
          marginLeft: ''
        });
        if (pos_left + arrow + obj.settings.width > jQuerywin.scrollLeft() + jQuerywin.outerWidth())
        {
          pos_left = jQuerye.offset().left - realHeight(tipso_bubble).width - arrow;
          tipso_bubble.find('.tipso_arrow').css({
            'border-left-color': obj.settings.background,
            'border-right-color': 'transparent',
            'border-top-color': 'transparent',
            'border-bottom-color': 'transparent'
          });
          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass('left');
        }
        else
        {
          tipso_bubble.find('.tipso_arrow').css({
            'border-right-color': obj.settings.background,
            'border-left-color': 'transparent',
            'border-top-color': 'transparent',
            'border-bottom-color': 'transparent'
          });
          tipso_bubble.removeClass('top bottom left right');
          tipso_bubble.addClass(obj.settings.position);
        }
        break;
    }
    /*
     * Set the position of the arrow for the corner positions
     */
    if (obj.settings.position === 'top-right')
    {
      tipso_bubble.find('.tipso_arrow').css({
        'margin-left': -obj.settings.width / 2
      });
    }
    if (obj.settings.position === 'top-left')
    {
      var tipso_arrow = tipso_bubble.find(".tipso_arrow").eq(0);
      tipso_arrow.css({
        'margin-left': obj.settings.width / 2 - 2 * obj.settings.arrowWidth
      });
    }
    if (obj.settings.position === 'bottom-right')
    {
      var tipso_arrow = tipso_bubble.find(".tipso_arrow").eq(0);
      tipso_arrow.css({
        'margin-left': -obj.settings.width / 2,
        'margin-top': ''
      });
    }
    if (obj.settings.position === 'bottom-left')
    {
      var tipso_arrow = tipso_bubble.find(".tipso_arrow").eq(0);
      tipso_arrow.css({
        'margin-left': obj.settings.width / 2 - 2 * obj.settings.arrowWidth,
        'margin-top': ''
      });
    }

    /*
     * Check out of boundness
     */
    if (pos_left < jQuerywin.scrollLeft() && (obj.settings.position === 'bottom' || obj.settings.position === 'top'))
    {
      tipso_bubble.find('.tipso_arrow').css({
        marginLeft: pos_left - obj.settings.arrowWidth
      });
      pos_left = 0;
    }
    if (pos_left + obj.settings.width > jQuerywin.outerWidth() && (obj.settings.position === 'bottom' || obj.settings.position === 'top'))
    {
      diff = jQuerywin.outerWidth() - (pos_left + obj.settings.width);
      tipso_bubble.find('.tipso_arrow').css({
        marginLeft: -diff - obj.settings.arrowWidth,
        marginTop: ''
      });
      pos_left = pos_left + diff;
    }
    if (pos_left < jQuerywin.scrollLeft() &&
       (obj.settings.position === 'left' ||
        obj.settings.position === 'right' ||
        obj.settings.position === 'top-right' ||
        obj.settings.position === 'top-left' ||
        obj.settings.position === 'bottom-right' ||
        obj.settings.position === 'bottom-left'))
    {
      pos_left = jQuerye.offset().left + (jQuerye.outerWidth() / 2) - (realHeight(tipso_bubble).width / 2);
      tipso_bubble.find('.tipso_arrow').css({
        marginLeft: -obj.settings.arrowWidth,
        marginTop: ''
      });
      pos_top = jQuerye.offset().top - realHeight(tipso_bubble).height - arrow;
      if (pos_top < jQuerywin.scrollTop())
      {
        pos_top = jQuerye.offset().top + jQuerye.outerHeight() + arrow;
        tipso_bubble.find('.tipso_arrow').css({
          'border-bottom-color': arrow_color,
          'border-top-color': 'transparent',
          'border-left-color': 'transparent',
          'border-right-color': 'transparent'
        });
        tipso_bubble.removeClass('top bottom left right');
        removeCornerClasses(tipso_bubble);
        tipso_bubble.addClass('bottom');
      }
      else
      {
        tipso_bubble.find('.tipso_arrow').css({
          'border-top-color': obj.settings.background,
          'border-bottom-color': 'transparent',
          'border-left-color': 'transparent',
          'border-right-color': 'transparent'
        });
        tipso_bubble.removeClass('top bottom left right');
        removeCornerClasses(tipso_bubble);
        tipso_bubble.addClass('top');
      }
      if (pos_left + obj.settings.width > jQuerywin.outerWidth())
      {
        diff = jQuerywin.outerWidth() - (pos_left + obj.settings.width);
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: -diff - obj.settings.arrowWidth,
          marginTop: ''
        });
        pos_left = pos_left + diff;
      }
      if (pos_left < jQuerywin.scrollLeft())
      {
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: pos_left - obj.settings.arrowWidth
        });
        pos_left = 0;
      }
    }

    /*
     * If out of bounds from the right hand side
     */
    if (pos_left + obj.settings.width > jQuerywin.outerWidth() &&
       (obj.settings.position === 'left' ||
        obj.settings.position === 'right' ||
        obj.settings.position === 'top-right' ||
        obj.settings.position === 'top-left' ||
        obj.settings.position === 'bottom-right' ||
        obj.settings.position === 'bottom-right'))
    {
      pos_left = jQuerye.offset().left + (jQuerye.outerWidth() / 2) - (realHeight(tipso_bubble).width / 2);
      tipso_bubble.find('.tipso_arrow').css({
        marginLeft: -obj.settings.arrowWidth,
        marginTop: ''
      });
      pos_top = jQuerye.offset().top - realHeight(tipso_bubble).height - arrow;
      if (pos_top < jQuerywin.scrollTop())
      {
        pos_top = jQuerye.offset().top + jQuerye.outerHeight() + arrow;
        tipso_bubble.find('.tipso_arrow').css({
          'border-bottom-color': arrow_color,
          'border-top-color': 'transparent',
          'border-left-color': 'transparent',
          'border-right-color': 'transparent'
        });

        removeCornerClasses(tipso_bubble);
        tipso_bubble.removeClass('top bottom left right');
        tipso_bubble.addClass('bottom');
      }
      else
      {
        tipso_bubble.find('.tipso_arrow').css({
          'border-top-color': obj.settings.background,
          'border-bottom-color': 'transparent',
          'border-left-color': 'transparent',
          'border-right-color': 'transparent'
        });

        /*
         * Hide and show the appropriate rounded corners
         */
        removeCornerClasses(tipso_bubble);
        tipso_bubble.removeClass('top bottom left right');
        tipso_bubble.addClass('top');
      }
      if (pos_left + obj.settings.width > jQuerywin.outerWidth())
      {
        diff = jQuerywin.outerWidth() - (pos_left + obj.settings.width);
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: -diff - obj.settings.arrowWidth,
          marginTop: ''
        });
        pos_left = pos_left + diff;
      }
      if (pos_left < jQuerywin.scrollLeft())
      {
        tipso_bubble.find('.tipso_arrow').css({
          marginLeft: pos_left - obj.settings.arrowWidth
        });
        pos_left = 0;
      }
    }
    tipso_bubble.css({
      left: pos_left + obj.settings.offsetX,
      top: pos_top + obj.settings.offsetY
    });

    // If positioned right or left and tooltip is out of bounds change position
    // This position change will be temporary, because preferedPosition is there
    // to help!!
    if (pos_top < jQuerywin.scrollTop() && (obj.settings.position === 'right' || obj.settings.position === 'left'))
    {
      jQuerye.tipso('update', 'position', 'bottom');
      reposition(obj);
    }
    if (pos_top + realHeight(tipso_bubble).height > jQuerywin.scrollTop() + jQuerywin.outerHeight() &&
        (obj.settings.position === 'right' || obj.settings.position === 'left'))
    {
      jQuerye.tipso('update', 'position', 'top');
      reposition(obj);
    }

  }
  jQuery[pluginName] = jQuery.fn[pluginName] = function(options) {
    var args = arguments;
    if (options === undefined || typeof options === 'object') {
      if (!(this instanceof jQuery)) {
        jQuery.extend(defaults, options);
      }
      return this.each(function() {
        if (!jQuery.data(this, 'plugin_' + pluginName)) {
          jQuery.data(this, 'plugin_' + pluginName, new Plugin(this, options));
        }
      });
    } else if (typeof options === 'string' && options[0] !== '_' && options !==
      'init') {
      var returns;
      this.each(function() {
        var instance = jQuery.data(this, 'plugin_' + pluginName);
        if (!instance) {
          instance = jQuery.data(this, 'plugin_' + pluginName, new Plugin(
            this, options));
        }
        if (instance instanceof Plugin && typeof instance[options] ===
          'function') {
          returns = instance[options].apply(instance, Array.prototype.slice
            .call(args, 1));
        }
        if (options === 'destroy') {
          jQuery.data(this, 'plugin_' + pluginName, null);
        }
      });
      return returns !== undefined ? returns : this;
    }
  };
}));
