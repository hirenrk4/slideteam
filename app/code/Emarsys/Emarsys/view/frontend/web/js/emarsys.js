var ScarabQueue = ScarabQueue || [];
(function(subdomain, id) {
  if (document.getElementById(id)) return;
  var js = document.createElement('script'); js.id = id;
  js.src = subdomain + '.scarabresearch.com/js/13A7107D37E888A8/scarab-v2.js';
  var fs = document.getElementsByTagName('script')[0];
  fs.parentNode.insertBefore(js, fs);
})('https://cdn', 'scarab-js-api');


function getScrollBarWidth(){
 if(jQuery(document).height() > jQuery(window).height()){
  jQuery('body').append('<div id="fakescrollbar" style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"></div>');
  fakeScrollBar = jQuery('#fakescrollbar');
  fakeScrollBar.append('<div style="height:100px;">&nbsp;</div>');
  var w1 = fakeScrollBar.find('div').innerWidth();
  fakeScrollBar.css('overflow-y', 'scroll');
  var w2 = jQuery('#fakescrollbar').find('div').html('html is required to init new width.').innerWidth();
  fakeScrollBar.remove();
  return (w1-w2);
 }
 return 0;
}