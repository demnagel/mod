$(document).ready(function(){
    // Сообщения об ошибках при валидации
    var error_massage = {
        'error_email': 'Неверный email',
        'error_requared': 'Обязательное поле',
        'error_charCount': 'Менее двух символов'
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

    function chenge() {
        var rules = ($(this).attr('data-validate')).split(',');
        if(!rules) return false;
        rules.forEach(rule){
            var name_rule = 'rule' + rule.slice(0,1).toUpperCase() + rule.slice(1);
            var valid = validation_rule[name_rule].call(this);
            if (!valid) {
                addError.call(this, error_massage['error_' + rule]);
            }
            else {
                delError.call(this);
            }
        }
    }
});