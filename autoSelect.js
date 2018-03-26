$(document).ready(function(){

    var _brand = '#brand';
    var _soket = '#soket';
    var _model = '#model';
    var _price = '#price';

    var object;
    var brand_list;
    var brand_obj;
    var price_obj = {};
    var cart = [];


    /**
     * Создание списка
     */
    $.ajax({
        async: false,
        url: '/modal/v_parse.php',
        type: "POST",
        data: {'ajax': '1'},
        error: function () {
            console.log('ajax error');
        },
        success: function (res) {
            object = JSON.parse(res);
        }
    });

    //Создание первичного списка
    $.each(object , function(index, value){
        $.each(value, function(i, v){
            brand_list += '<option>' + i + '</option>';
        });
    });
    $(_brand).append(brand_list);

    //Поиск сокетов у отмеченного бренда
    function searchSoket(val_brand) {
        var soket_list;
        for (var i = 0; i < object.length; i++) {
            if (object[i][val_brand]) {
                brand_obj = object[i][val_brand];
            }
        }
        $.each(brand_obj, function (key, val) {
            soket_list += '<option>' + key + '</option>';
        });
        $(_soket).append(soket_list);
    }

    //Поиск моделей у отмеченного сокета
    function searchModel(val_soket) {
        var modelAndPrice;
        var model_list;
        $.each(brand_obj, function (key, val) {
            if (key == val_soket) {
                modelAndPrice = val.split('&').slice(0, -1);

            }
        });
        //Формирование глоб. обекта модель => цена
        for (var i = 0; i < modelAndPrice.length; i++) {
            var arr = [];
            arr = modelAndPrice[i].split('#');
            model_list += '<option>' + arr[0] + '</option>';
            price_obj[arr[0]] = arr[1];
        }
        $(_model).append(model_list);
    }
    //События ---------------------------------------------------
    $(_brand).on('change',function(){
        var brand = $(this).val();
        $(_soket).children('option:not([value = 0])').remove();
        $(_model).children('option:not([value = 0])').remove();
        searchSoket(brand);
    });

    $(_soket).on('change',function(){
        var soket = $(this).val();
        $(_model).children('option:not([value = 0])').remove();
        searchModel(soket);
    });

    $(_model).on('change',function(){
        var model = $(this).val();
        $(_price).children('option:not([value = 0])').remove();
        $(_price).val(price_obj[model]);
    });
    //Элементы корзины ---------------------------------------------------
    var c = 0;
    $('#priceAll').val(function(){
        $.each(cart, function (key, val) {
            c += +val;
        })
    });

    $('#cart').on('click',function(){
        var summ = 0;
        var c = {};
        c[$(_model).val()] = $(_price).val();
        cart.push(c);
        for(var i = 0; i < cart.length; i++){
            $.each(cart[i], function (key, val) {
                summ += Number(val);
            });
        }
        $('#priceAll').val(summ);
        $('#count').val(cart.length);

    });

    $('#cartDel').on('click',function(){
        cart.length = 0;
        $('#priceAll').val('');
        $('#count').val('');
    });
});
