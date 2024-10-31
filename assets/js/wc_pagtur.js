(function($){
    $(document).ready(function() {     

        $('#pagtur_cpf').mask('000.000.000-00');
        $('#pagtur_birthdate').mask('00/00/00000');
        $('#pagtur_expirationMonthYear').mask('99/99');
        $('#pagtur_creditcardnumber').mask('9999 9999 9999 9999',{
            placeholder: "•••• •••• •••• ••••"
        });
        
        
        $('#pagtur_cardholdername').blur(function() {
            if ($(this).val() == "" || $(this).val().length < 5) {
                pagtur_showError($(this), $(this).attr('data-alert'));
                $(this).addClass("pagtur-error");
            } else {
                pagtur_hideError($(this));
                $(this).removeClass("pagtur-error");
            }
        });
        
        $('#pagtur_cpf').blur(function() {
            if ($(this).val() == "" || $(this).val().length < 14 || !verifyCPF($(this).val())) {
                pagtur_showError($(this), $(this).attr('data-alert'));
                $(this).addClass("pagtur-error");
            } else {
                pagtur_hideError($(this));
                $(this).removeClass("pagtur-error");
            }
        });

        $('#pagtur_birthdate').blur(function() {
            if ($(this).val() == "" || $(this).val().length < 10 || !validateBirth($(this).val())) {
                pagtur_showError($(this), $(this).attr('data-alert'));
                $(this).addClass("pagtur-error");
            } else {
                pagtur_hideError($(this));
                $(this).removeClass("pagtur-error");
            }
        });

        $('#pagtur_creditcardnumber').blur(function() {
            if ($(this).val() == "" || !validateCreditCard($(this).val())) {
                pagtur_showError($(this), $(this).attr('data-alert'));
                $(this).addClass("pagtur-error");
            } else {
                pagtur_hideError($(this));
                $(this).removeClass("pagtur-error");
            }
        });

        $('#pagtur_expirationMonthYear').blur(function() {
            var isValid = moment($(this).val(), "MM-YY").isValid();
            var diffMonth = 0;
            if (isValid) diffMonth = moment($(this).val(), "MM-YY").diff(moment(), 'month');
            if ($(this).val() == "" || !isValid || diffMonth < 0) {
                pagtur_showError($(this), $(this).attr('data-alert'));
                $(this).addClass("pagtur-error");
            } else {
                pagtur_hideError($(this));
                $(this).removeClass("pagtur-error");
            }
        });

        $('#pagtur_cvv').blur(function() {
            if ($(this).val() == "" || $(this).val().length < 3 || $(this).val().length > 4) {
                pagtur_showError($(this), $(this).attr('data-alert'));
                $(this).addClass("pagtur-error");
            } else {
                pagtur_hideError($(this));
                $(this).removeClass("pagtur-error");
            }
        });
        

        $('#pagtur_installments').on('change', function(){
            if ($(this).val() == "0" || $(this).val() == "") {
                $('#pagtur_total_installment').html('0,00');
                pagtur_showError($('#pagtur_total_installment'), $(this).attr('data-alert'));
                $(this).addClass("pagtur-error");
            } else {
                var installment_amount = $(this).find(':selected').attr('data-amount')
                var total_amount = $(this).val() * installment_amount;
                total_amount = total_amount.toFixed(2);
                var nStr = total_amount.toString();
                x = nStr.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? ',' + x[1] : ',';
                x2 = x2.padEnd(3,'0');
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + '.' + '$2');
                }
                total_amount = x1 + x2;

                $('#pagtur_total_installment').html(total_amount);
                pagtur_hideError($('#pagtur_total_installment'));
                $(this).removeClass("pagtur-error");
            }
        });

        // HELPER FUNCTIONS
        function pagtur_showError(obj, message) {
            var msgHtml = "<p class=\"woocommerce-error\">" + message + "</p>";
            if (obj.next().html() != message) obj.after(msgHtml);
        }

        function pagtur_hideError(obj) {
            obj.next().remove();
        }


    })


    
}) (jQuery);


