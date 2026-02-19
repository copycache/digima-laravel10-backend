var mlm         = new mlm();
var qouteData   = {};

var quantity    = 1;
var item_id     = 1;

function mlm()
{
    init();
    function init()
    {
        $(document).ready(function()
        {
            document_ready();
        });
    }

    function document_ready()
    {
        submit_qoute();
        cart_add_item();
        cart_remove_item();
        cart_add_quantity();
    }

    function cart_remove_item()
    {
        $('body').on('click','.remove-product',function()
        {
            qouteData.item_id      = $(this).data('item_id');
            $(this).closest('.product').remove();
            $.ajax({

                headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url:"/cart_item/remove",
                method: "POST",
                data: qouteData,
                dataType:"text",
                success: function(data)
                {
                    $('div.totals-value').text(data);
                }
            });
        });
    }

    function submit_qoute()
    {
        $('body').on('click','#qouteSubmit',function()
        {
            qouteData.name      = $('#name').val();
            qouteData.email     = $('#email').val();
            qouteData.phone     = $('#phone').val();
            qouteData.message   = $('#message').val();
            qouteData.item_id   = $('#item_id').val();
            $.ajax({

                headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url:"/products/view/request/qoute",
                method: "POST",
                data: qouteData,
                dataType:"text",
                success: function(data)
                {
                    console.log(data);
                    if(data == "SUCCESS")
                    {
                        $('body').find('.success-response').click();
                    }
                }
            });
        });
    }

    function cart_add_quantity()
    {
        $('body').on('click','.nice-number',function()
        {
            quantity = $('#view_quantity').val();
            item_id  = $('#item_id').val();
            $('button.add-to-cart').attr('link','/cart_item?item_id='+item_id+'&quantity='+quantity);
        });
        $('body').on('keyup','#view_quantity',function()
        {
            quantity = $('#view_quantity').val();
            item_id  = $('#item_id').val();
            $('button.add-to-cart').attr('link','/cart_item?item_id='+item_id+'&quantity='+quantity);
        });
        $('body').on('change','.change_quantity',function()
        {
            qouteData.item_id      = $(this).data('item_id');
            qouteData.quantity     = $(this).val();
            $.ajax({

                headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url:"/cart_item/change_quantity",
                method: "POST",
                data: qouteData,
                dataType:"text",
                success: function(data)
                {
                    
                }
            });
        });
    }

    function cart_add_item()
    {

    }
}
