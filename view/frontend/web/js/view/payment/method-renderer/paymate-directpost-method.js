/**
 * Created by Linh on 6/8/2016.
 */
define(
    [
        'ko',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Marcwatts_Paymate/js/action/set-payment-method',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (ko, $, Component, setPaymentMethod) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Marcwatts_Paymate/payment/paymate-directpost'
            },
            getCode: function() {
                return 'paymate';
            },

            isActive: function() {
                return true;
            },
            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
            afterPlaceOrder: function () {
                setPaymentMethod(this.messageContainer);
                return false;
            }
        });
    }
);
