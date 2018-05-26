$(document).ready(function () {

    var cartPost = []; // массив для почты
    var resObject = {};// Объект товаров
        $.ajax({
            type: 'POST',
            dataType: 'json',
            data: {'cart': 1},
            url: '/plovme/cart.php',
            success: function (msg) {
                var product = []; // все товары корзины
                var resSumm = 0;  // стартовая итоговая сумма
                $.each(msg,function(name,value){
                    var wp = {};
                    var arr_weight = [];// все доступных порций для товара
                    var arr_price = []; // цены для доступных порций
                    var cart = '';      // рендер товара в корзине
                    var dropDown = '';  // рендер доступных порций
                    var price;          // стартовый вес товара
                    var weight;         // стартовая цена товара
                    var id;
                    $.each(value, function (key, val) {
                        if($.isArray(val)){
                            if(key == 'WEIGHT'){
                                weight = val[0]
                                var i = 0;
                                $.each(val, function (k, weight) {
                                    dropDown += "<option class='dropdown-item'>" + weight + "</option>";
                                    arr_weight.push(weight);
                                })
                            }
                            if(key == 'PRICE'){
                                resSumm += price = +val[0];
                                $.each(val, function (k, price) {
                                    arr_price.push(price);
                                })
                            }
                        }
                        if(key == 'ID') id = val;
                        for (var i = 0; i < arr_weight.length; i++) {
                            wp[arr_weight[i]] = arr_price[i];
                        }
                        resObject[id] = wp;
                    });
                    cart += "<div class='row kt-second' id = '" + id + "'>";
                    cart += "<div class='col-12 col-lg-5 naim prodName'>";
                    cart +=     "<p>" + name + "</p>";
                    cart += "</div>";
                    cart += "<div class='col-4 col-lg-2 kolvo'>";
                    cart +=     "<span class='remove-kol'>-</span>";
                    cart +=     "<p class='kplp'> 1 </p>";
                    cart +=     "<span class='add-kol'>+</span>";
                    cart += "</div>";
                    cart += "<div class='col-4 col-lg-2 ves'>";
                    cart +=     "<p>";
                    cart +=         "<select>";
                    cart +=             dropDown;
                    cart +=         "</select>";
                    cart +=     "</p>";
                    cart += "</div>";
                    cart += "<div class='col-4 col-lg-2 cena'>";
                    cart +=     "<p class='p_price'>" + price + " р</p>";
                    cart += "</div>";
                    cart += "<div class='remove-k'></div>";
                    cart +="</div>";
                    product.push(cart);
                });
                $.each(product, function (key, val) {
                    $('.korz-tabl').append(val);
                })
                $('.total-sad').text(resSumm + ' р');
            }
        })

    $(document).ajaxStop(function() {

        // увеличение кол-во товара
        $('.korz-tabl').on('click', '.remove-kol', function(){
           var count =  Number($(this).next("p").text());
           if(count > 1) $(this).next("p").text(--count);
           else return false;
           recalculation();
        });
        // уменьшение кол-во товара
        $('.korz-tabl').on('click', '.add-kol', function(){
            var count =  Number($(this).prev("p").text());
            if(count < 20) $(this).prev("p").text(++count);
            else return false;
            recalculation();
        });
        // удаление товара из корзины и cookie
        $('.korz-tabl').on('click', '.remove-k', function(){
            var key = 'product_' + $(this).parent('.kt-second').attr('id');
            $.cookie(key, '', {
                expires: -1,
                path:'/'
            });
            $(this).parent('.kt-second').remove();
            recalculation();
        });
        // изменение веса
        $('select').on('change',function(){
            var id = $(this).parents('.kt-second').attr('id');
            var ves = $(this).val();
            $(this).parents('.ves').next().find('.p_price').text(resObject[id][ves] + ' р');
            recalculation();
        })
        // пересчет итоговой суммы заказа
        function recalculation(){
            var recalc_price = 0;
            $.each($('.kt-second'),function(key, val){
                var count = Number($(val).find('.kplp').text());
                var price = Number($(val).find('.p_price').text().replace(' р', ''));
                recalc_price += count * price;
            })
            $('.total-sad').text(recalc_price + ' р');
        }
        // сборка и отправка зака на почту
        $('.sendCart').on('click',function(){
            event.preventDefault();
            $.each($('.kt-second'),function(key, val){
                var prodParam = [];
                prodParam.push($(val).find('.prodName').text());
                prodParam.push($(val).find('.kplp').text()) ;
                prodParam.push($(val).find('.p_price').text()) ;
                cartPost.push(prodParam);
            })
            $.post("/mail/order-mail.php", { 'cartPost'    : JSON.stringify(cartPost),
                                             'name_plovme' : $("input[name='name_plovme']").val(),
                                             'tel_plovme'  : $("input[name='tel_plovme']").val(),
                                             'adres_plovme': $("input[name='adres_plovme']").val()
                                             });
            // удаляем cookie
            $.each(resObject, function(key,val){
                $.cookie('product_' + key, '', {
                    expires: -1,
                    path:'/'
                });
            })
            window.location.href = '/plovme/';
        })
    })
});