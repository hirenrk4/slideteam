 require(['jquery'], function ($) {
 $(document).ready(function () {

 try 
    {
        var added="";
        setTimeout(function(){
        $('img.normal-img').css('display', 'none');
        $('img.hover-img').css('display', 'none');
        $('img.loader-img').css('display', '');
        var Wishlist=jQuery.parseJSON(localStorage.getItem('mage-cache-storage')).wishlist;

        if (typeof Wishlist !== 'undefined' && (Wishlist.hasOwnProperty('added'))
            ) {
        added = Wishlist.added;
}
      // vriable is set and isnt falsish so it can be used;
         if(added != ""){

            if(added.length !== 0)
            {
                  productId=$(".currentProduct").val();
                  counter=0;
                 $.each( added, function( key, value ) {
                        if(productId == key)
                        {
                            counter++;
                            wishlistitem=value;
                        }
                     })

                    if(counter==1 && wishlistitem !== ""){
                        $('img.hover-img').css('display','');
                       if($('.towishlist').find('img').hasClass('normal-img'))
                       {
                            $('img.normal-img').css('display','none');
                            $('.towishlist').removeClass('action');
                       }
                        if($('.towishlist').find('img').hasClass('hover-img'))
                        {
                            $('img.loader-img').css('display','none');
                            $('img.hover-img').css('display','inline-block');
                        }
                         $('.towishlist').removeClass('action');
                         $('.towishlist').removeAttr('data-action');
                         $('.towishlist').removeAttr('data-post');

                         actionData=wishlistitem;

                         $('.favourite .delete').on('click', function () {
                             dataItem = actionData;
                            $.magnificPopup.open({
                                mainClass: 'favourite-remove-popup',
                                items: {
                                    src:'#confirm_id',
                                },
                                type: 'inline'
                            });
                         });

                    $('.towishlist').on('hover', function () {
                        $(this).prop('title', 'Added to Favourites');
                        var myTitle = $(this).prop('Added to Favourites');
                    });
                    
                    $('body').on('click','#confirm-remove', function(){
                        var data= dataItem; 
                        var item=$.trim(data.data.item);
                        var uenc=$.trim(data.data.uenc);
                        var form_key= $("input[name=form_key]").val();

                        $.ajax({
                              method: "POST",
                              url:data.action,
                              data: { item: item, uenc: uenc , form_key:form_key , productDetailPage: true},
                              showLoader:true,
                             success: function (data, status, jqXHR) {
                              $('#popupContent').hide();
                               $('#confirm-remove').hide();
                                $('#confirm-no').hide();
                              var url=$.trim(data.redirectUrl);
                              window.location.href = url; 
                              },
                              error: function (jqXHR, status, err) {
                                 $('#confirm-remove').show();
                                $('#confirm-no').show();
                                $('#popupContent').show();
                              }
                            })
                    })
                    }
                    else{
                         $('img.normal-img').css('display','');
                        $('img.hover-img').css('display','');
                        $('img.loader-img').css('display','none');
                        var addparam=$('.addParam').val();
                         $('.towishlist').addClass('action');
                         $('.towishlist').attr('data-action',"add-to-wishlist");
                         $('.towishlist').attr('data-post',addparam);
                         $('.towishlist').on('hover', function () {
                             $('.hover-img').prop('title', 'Add to Favourites');
                             var myTitle = $('.hover-img').prop('Add to Favourites');
                         });
                    }
           }
        }
     else{
        $('img.normal-img').css('display','');
        $('img.hover-img').css('display','');
        $('img.loader-img').css('display','none');
        var loginUrl = $('.LoginUrl').val();
        $('.towishlist').attr('href',loginUrl);
        
        $('.towishlist').on('hover', function () {
             $('.hover-img').prop('title', 'Add to Favourites');
             var myTitle = $('.hover-img').prop('Add to Favourites');
         });
     }
   }, 7000);
        } 
    catch (err) {
        alert(err);
    }
 });
});
