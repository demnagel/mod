
    // Сообщения об ошибках при валидации
    var error_massage = {
        'error_email': 'Неверный email',
        'error_requared': 'Обязательное поле',
        'error_charCount': 'Менее двух символов',
        'error_pass':'пароли не совподают'
    };

    // Правила валидации
    var validation_rule = {
        'ruleEmail': function()
        {
            var r = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,6})+$/;
            return r.test(this.value);
        },
        'ruleRequared':function() {
            return this.value !== '';
        },
        'ruleCharCount':function() {
            var r = /.{2,}/;
            return r.test(this.value);
        },
        'rulePass':function() {
            var pass = document.querySelectorAll('[type = password]');
            return pass[0].value === pass[1].value;
        }
    };

    // Создание cообщения о ошибке
    function addError(massage) {
        if (this.nextElementSibling.getAttribute('class') !== 'span_error') {
            var element = document.createElement('span');
            element.innerHTML = massage;
            element.classList.add('span_error');
            this.parentNode.insertBefore(element, this.nextElementSibling);
        }
    }
    // Удаление ошибки
    function delError() {
        if (this.nextElementSibling.getAttribute('class') == 'span_error') {
            this.parentNode.removeChild(this.nextElementSibling);
        }
    }
    // Общая валидация полей
    function checkAll() {
        var fields = document.querySelectorAll('[data-validate]');
        fields.forEach(function (field) {
          check.call(field);
        });
    }
    //Валидация текущего поля
    function check() {
        var context = this;
        var field = $(context);
        var validation_attr = field.attr('data-validate');
        var validation_rules = validation_attr ? validation_attr.split(',') : [];
        validation_rules.forEach(function (rule) {
            var name_rule = 'rule' + rule.slice(0, 1).toUpperCase() + rule.slice(1);
            if(!validation_rule[name_rule]){
                console.log('Отсутствует правило валидации ' + rule);
            }
            var valid = validation_rule[name_rule].call(context);
            if (!valid) {
                addError.call(context, error_massage['error_' + rule]);
            }
            else {
                delError.call(context);
            }
        });

    }

    $('.modButJs').on('click',checkAll);
    $('.valid').on('change',function(){check.call(this)});
