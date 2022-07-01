 require(['jquery', 'magnific_popup'], function ($) {
        $(document).ready(function () {

            $('.favourite .delete').on('click', function () {
                 dataItem = $(this).data('item');
                $.magnificPopup.open({
                    mainClass: 'favourite-remove-popup',
                    items: {
                        src:'#confirm_id',
                    },
                    type: 'inline'
                });
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
                           $('#confirm-remove').hide();
                          $('#confirm-no').hide();
                          $('#popupContent').hide();
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


        });
    });
