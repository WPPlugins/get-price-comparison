(function() {


    getInsertProductDialogContent()
        .load('/wp-content/plugins/get-price-comparison/html/dialog.html', function (response) {
            getInsertProductDialogContent().html(response);
            populateAffiliateProgramNameDropDown();
        })
    ;


    var rawData = jQuery('#gpc-products-list-as-json').html();

    if ( ! rawData) {
        console.log("Unable to load raw data");
        return false;
    }

    var data = JSON.parse(rawData);

    // console.log("data", data);

    var sorted = [];
    var merchants = [];
    var products = [];
    var chosenProduct = {};

    data.map(function (item) {
        var programName = item.affiliate_program_name + ' - ' + item.affiliate_code;

        if ( ! sorted.hasOwnProperty(programName)) {
            sorted[programName] = [];
        }

        sorted[programName].push(item);
    });

    // console.log("sorted", sorted);


    function getInsertProductDialogContent() {
        return jQuery("#gpc-insert-product-dialog-content");
    }

    function getAffiliateProgramNameDropDown() {
        return jQuery("#affiliate_program_name");
    }

    function getMerchantNameDropDown() {
        return jQuery("#merchant_name");
    }


    // function resetMerchantNameListBox() {
    //     merchants = [];
    //     products = [];
    //     chosenProduct = {};
    //
    //     var form = getRootForm();
    //     form.settings.items[2].values.length = 0;
    //
    //     var merchantNameListBox = getMerchantNameDropDown();
    //     resetDropDown(merchantNameListBox);
    // }
    //
    // function resetAvailableProductsListBox() {
    //     products = [];
    //     chosenProduct = {};
    //
    //     var form = getRootForm();
    //     form.settings.items[3].values.length = 0;
    //
    //     var availableProductsListBox = getProductIdDropDown();
    //     resetDropDown(availableProductsListBox);
    // }




    function populateAffiliateProgramNameDropDown() {
        var dropDown = getAffiliateProgramNameDropDown();
        Object.keys(sorted).map(function(item, i) {
            dropDown
                .append(jQuery("<option></option>")
                    .attr("value", item)
                    .text(item)
                );
        });

        jQuery(document).on('change','#affiliate_program_name', function () {
            // resetMerchantNameListBox();
            // resetAvailableProductsListBox();

            addAvailableMerchantNameOptions(this.value);
        })
    }


    function addAvailableMerchantNameOptions(selectedAffiliateProgramKey) {

        // use that value to determine which Merchants should be available in this dropdown
        var merchants = getMerchantNameValues(selectedAffiliateProgramKey);

        var dropDown = getMerchantNameDropDown();

        dropDown.append('<option value="" disabled selected hidden>Please Select...</option>');

        // add each one, because it's not a real selectbox
        for (var i = 0; i < merchants.length; ++i) {
            dropDown
                .append(jQuery("<option></option>")
                    .attr("value", merchants[i].value)
                    .text(merchants[i].text)
                );
        }
    }


    function getMerchantNameValues(keyName) {
        sorted[keyName].map(function (item) {
            if ( ! merchants[item.merchant_name]) {
                merchants[item.merchant_name] = [];
            }
            merchants[item.merchant_name].push(item);
        });

        return Object.keys(merchants).map(function(item, i) {
            return { text: item, value: i }
        })
    }






    tinymce.PluginManager.add('gpc_tinymce_widget', function(editor, url) {

        // Add a button that opens a window
        editor.addButton('gpc_tinymce_widget', {
            text: 'GPC',
            icon: false,
            onclick: function() {

                // Open window
                var win = editor.windowManager.open({
                    title: 'Get Price Comparison',
                    width: 600,
                    height: 400,
                    body: [{
                        type: 'container',
                        html: getInsertProductDialogContent().html()
                    }],
                    onsubmit: function(e) {
                        if (jQuery.isEmptyObject(chosenProduct)) {
                            alert('You must select a product, or click Cancel');
                            e.stopPropagation();
                            return false;
                        }

                        var content = '<span class="gpc-single-price" ' +
                            'data-pc-affiliate-account="' + chosenProduct.affiliate_account_id + '" ' +
                            'data-pc-merchant="' + chosenProduct.merchant_name + '" ' +
                            'data-pc-product="' + chosenProduct.product_id  + '" ' +
                            'data-pc-link="' + chosenProduct.path + '">' +
                            '&nbsp;</span>';

                        // console.log("content", content);

                        // Insert content when the window form is submitted
                        editor.insertContent(content);
                    }
                });
            }
        });
    });
})();



