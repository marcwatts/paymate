
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paymate',
                component: 'Marcwatts_Paymate/js/view/payment/method-renderer/paymate'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);