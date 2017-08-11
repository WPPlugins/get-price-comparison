// console.log("amazon loaded");

jQuery( document ).ready( function ( e ) {
    getAffiliateAccountSelector().bind('change', function () {
        // console.log("value", this.value);
    });
});

PubSub.subscribe(
    CHANGED_SELECTED_AFFILIATE_ACCOUNT,
    function( msg, data ){

        // if (data.selectedAffiliateAccount.affiliate_program.name !== "Amazon") {
        //
        //     // remove listener
        //
        //     return false;
        // }
        //
        //
        // getProductIdSelector().bind('input', function () {
        //     // console.log("getProductIdSelector sss", this.value);
        //
        //     if (this.value === '') {
        //         // console.log("empty");
        //         return false;
        //     }
        //
        //     var asinRegex = new RegExp(/^[0-9A-Z]{10}$/);
        //
        //     if (asinRegex.test(this.value) === false) {
        //         // console.log("not an ASIN");
        //         return false;
        //     }
        //
        //     // console.log("looking good");
        //
        //     jQuery.ajax({
        //         url: API_BASE_URL + '/amazonasin',
        //         type: "POST",
        //         contentType: "application/json",
        //         dataType: "json",
        //         data : JSON.stringify({
        //             'ASIN': this.value
        //         })
        //     }).done(function (result) {
        //         console.log("result", result);
        //     }).error(function (err) {
        //         console.log("err", err);
        //     });
        //
        // });
    }
);