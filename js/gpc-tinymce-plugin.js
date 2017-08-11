(function() {
  var rawData = jQuery("#gpc-products-list-as-json").html();

  if (!rawData) {
    console.log("Unable to load raw data");
    return false;
  }

  var data = JSON.parse(rawData);

  var sorted = [];
  var merchants = [];
  var products = [];
  var chosenProduct = {};
  var gpcView;

  data.map(function(item) {
    var programName = item.affiliate_program_name + " - " + item.affiliate_code;

    if (!sorted.hasOwnProperty(programName)) {
      sorted[programName] = [];
    }

    sorted[programName].push(item);
  });

  // console.log("sorted", sorted);

  tinymce.PluginManager.add("gpc_tinymce_widget", function(editor, url) {
    // Add a button that opens a window
    editor.addButton("gpc_tinymce_widget", {
      text: "GPC",
      icon: false,
      onclick: function() {
        function getRootForm() {
          return win.find("form")[0];
        }

        function getAffiliateProgramNameDropDown() {
          return win.find("#affiliate_program_name")[0];
        }

        function getMerchantNameDropDown() {
          return win.find("#merchant_name")[0];
        }

        function getProductIdDropDown() {
          return win.find("#product_id")[0];
        }

        function getProductDisplayContainer() {
          return jQuery("#gpc-product-container-body");
        }

        function getProductLinkDisplayContainer() {
          return jQuery("#gpc-link-preview-container");
        }

        function getProductLinkDisplayTextBox() {
          return jQuery("#gpc-link-text-box");
        }

        function resetMerchantNameListBox() {
          merchants = [];
          products = [];
          chosenProduct = {};

          var form = getRootForm();
          form.settings.items[2].values.length = 0;

          var merchantNameListBox = getMerchantNameDropDown();
          resetDropDown(merchantNameListBox);
        }

        function resetAvailableProductsListBox() {
          products = [];
          chosenProduct = {};

          var form = getRootForm();
          form.settings.items[3].values.length = 0;

          var availableProductsListBox = getProductIdDropDown();
          resetDropDown(availableProductsListBox);
        }

        function resetDropDown(element) {
          element.state.data.text = "";
          element.state.data.value = "";

          /**
                     * Note that this garbage is in place due to the "listbox" being some nasty combo
                     * of rendered divs, rather than the visually expected "select"... I mean... ffs?!?
                     * @type {RegExp}
                     */
          var regex = /<span class="mce-txt">.*?<\/span>/g;
          element.getEl().innerHTML = element
            .getEl()
            .innerHTML.replace(regex, "");
        }

        var affiliateProgramValues = Object.keys(sorted).map(function(item, i) {
          return { text: item, value: i };
        });

        var getMerchantNameValues = function(keyName) {
          sorted[keyName].map(function(item) {
            if (!merchants[item.merchant_name]) {
              merchants[item.merchant_name] = [];
            }
            merchants[item.merchant_name].push(item);
          });

          return Object.keys(merchants).map(function(item, i) {
            return { text: item, value: i };
          });
        };

        var getProductValues = function(merchantName) {
          merchants[merchantName].map(function(item) {
            if (item.merchant_name === merchantName) {
              products.push(item);
            }
          });

          return products.map(function(product, i) {
            return {
              text: product.product_id,
              value: i,
              product: product
            };
          });
        };

        function addAvailableMerchantNameOptions() {
          var form = getRootForm();

          // get the value selected in the Affiliate Program Name drop down
          var affiliateProgramNameListBox = getAffiliateProgramNameDropDown();
          var selectedAffiliateProgramKey = affiliateProgramNameListBox.state.data.text;

          // use that value to determine which Merchants should be available in this dropdown
          var merchants = getMerchantNameValues(selectedAffiliateProgramKey);

          // add each one, because it's not a real selectbox
          for (var i = 0; i < merchants.length; ++i) {
            form.settings.items[2].values.push(merchants[i]);
          }

          var merchantNameListBox = getMerchantNameDropDown();
          var activeButtonId = merchantNameListBox.getEl().children[0].id;

          if (
            !collectionContains(
              document.getElementById(activeButtonId).children,
              "Please select..."
            )
          ) {
            var spanNode = document.createElement("span");
            var textNode = document.createTextNode("Please select...");
            spanNode.appendChild(textNode);
            var element = document.getElementById(activeButtonId);
            element.appendChild(spanNode);
          }
        }

        function addAvailableProductOptions() {
          var form = getRootForm();

          // get the value selected in the Merchant Name drop down
          var merchantNameListBox = getMerchantNameDropDown();
          var selectedMerchantNameKey = merchantNameListBox.state.data.text;

          // use that value to determine which Products should be available in this dropdown
          var products = getProductValues(selectedMerchantNameKey);

          // add each one, because it's not a real selectbox
          for (var i = 0; i < products.length; ++i) {
            form.settings.items[3].values.push(products[i]);
          }

          var productsListBox = getProductIdDropDown();
          var activeButtonId = productsListBox.getEl().children[0].id;

          // garbage but - loop back through the populated menu items and async add the product's title
          // to the drop down list item (if the ajax call succeeds)
          form.settings.items[3].values.map(function(productMenuItem) {
            // the .product is stored onto the list item object in getProductValues() - hackery. It's all hackery though.
            var product = productMenuItem.product;
            jQuery
              .ajax({
                url: API_BASE_URL +
                  "/" +
                  product.merchant_name.toLowerCase() +
                  "/" +
                  product.product_id +
                  "?affiliateAccount=" +
                  product.affiliate_account_id
              })
              .done(function(result) {
                productMenuItem.text = product.product_id +
                  " - " +
                  result.gpc_view.name;
              });
          });

          if (
            !collectionContains(
              document.getElementById(activeButtonId).children,
              "Please select..."
            )
          ) {
            var spanNode = document.createElement("span");
            var textNode = document.createTextNode("Please select...");
            spanNode.appendChild(textNode);
            var element = document.getElementById(activeButtonId);
            element.appendChild(spanNode);
          }
        }

        function collectionContains(collection, searchText) {
          return findIndexInCollection(collection, searchText) > -1;
        }

        function findIndexInCollection(collection, searchText) {
          for (var i = 0; i < collection.length; i++) {
            if (
              collection[i].innerText
                .toLowerCase()
                .indexOf(searchText.toLowerCase()) > -1
            ) {
              return i;
            }
          }
          return -1;
        }

        function productPreview(product) {
          var container = getProductDisplayContainer();
          container.html("");

          jQuery
            .ajax({
              url: API_BASE_URL +
                "/" +
                product.merchant_name.toLowerCase() +
                "/" +
                product.product_id +
                "?affiliateAccount=" +
                product.affiliate_account_id
            })
            .done(function(result) {
              gpcView = result.gpc_view;

              var image = gpcView.image_url
                ? gpcView.image_url
                : "https://placeholdit.imgix.net/~text?txtsize=33&txt=Preview%20unavailable&w=200&h=200";

              var table = '<table id="gpc-tinymce-product-table">';
              var img = '<td width="210px" class="gpc-tinymce-product-image">' +
                '<img src="' +
                image +
                '" width="200" height="200">' +
                "</td>";
              var info = '<td class="gpc-tinymce-product-description">' +
                '<h3 class="gpc-product-preview-heading">' +
                gpcView.name +
                "</h3>" +
                '<p class="gpc-product-preview-description">' +
                truncate(gpcView.description) +
                "</p>" +
                '<p class="gpc-product-preview-price">Current Price: <span class="gpc-product-preview-formatted-price">' +
                gpcView.price.new.formatted +
                "</span></p>" +
                '<p class="gpc-product-preview-aff-link"><a href="' +
                gpcView.affiliate_url +
                '" target="_blank">Product Page Link</a></p>' +
                "</td>";

              container.append(table);

              jQuery("#gpc-tinymce-product-table").append(img).append(info);

              productLinkPreview();
            })
            .fail(function(err) {
              container.html("");
            });

          function truncate(string) {
            if (string.length > 50) {
              return string.substring(0, 50) + "...";
            } else {
              return string;
            }
          }
        }

        function productLinkPreview() {
          if (typeof gpcView === "undefined") {
            return false;
          }

          var container = getProductLinkDisplayContainer();
          container.html("");

          chosenProduct.link_text = getProductLinkDisplayTextBox().val();

          var pricePreview = replacePricePlaceholder(
            getProductLinkDisplayTextBox().val(),
            gpcView.price.new.formatted
          );

          container.html(
            'What your visitor will see: <div id="gpc-product-link-preview-display">' +
              pricePreview +
              "</div>"
          );
        }

        var windowBody = [
          {
            type: "label",
            name: "gpc-label-helper",
            id: "gpc-label-helper",
            label: "."
          },
          {
            type: "listbox",
            name: "affiliate_program_name",
            label: "Program",
            values: affiliateProgramValues,
            onselect: function() {
              resetMerchantNameListBox();
              resetAvailableProductsListBox();

              addAvailableMerchantNameOptions();

              // http://archive.tinymce.com/forum/viewtopic.php?pid=119112
              // if only that worked !
              win.renderNew();
              win.repaint();
              win.reflow();
            }
          },
          {
            type: "listbox",
            name: "merchant_name",
            label: "Merchant",
            value: "",
            values: [],
            onselect: function() {
              var merchantNameListBox = getMerchantNameDropDown();
              var activeButtonId = merchantNameListBox.getEl().children[0].id;

              // Remove the 'Please select..." placeholder text on choosing an option
              // total hack, shouldn't be needed, WTF :/
              if (
                collectionContains(
                  document.getElementById(activeButtonId).children,
                  "Please select..."
                )
              ) {
                var index = findIndexInCollection(
                  document.getElementById(activeButtonId).children,
                  "Please select..."
                );
                document
                  .getElementById(activeButtonId)
                  .removeChild(
                    document.getElementById(activeButtonId).children[index]
                  );
              }
              resetAvailableProductsListBox();

              addAvailableProductOptions();
            }
          },
          {
            type: "listbox",
            name: "product_id",
            label: "Product",
            value: "",
            values: [],
            onselect: function() {
              var productListBox = getProductIdDropDown();
              var activeButtonId = productListBox.getEl().children[0].id;

              // Remove the 'Please select..." placeholder text on choosing an option
              // total hack, shouldn't be needed, WTF :/
              if (
                collectionContains(
                  document.getElementById(activeButtonId).children,
                  "Please select..."
                )
              ) {
                var index = findIndexInCollection(
                  document.getElementById(activeButtonId).children,
                  "Please select..."
                );
                document
                  .getElementById(activeButtonId)
                  .removeChild(
                    document.getElementById(activeButtonId).children[index]
                  );
              }

              chosenProduct = products[this.value()];

              productPreview(chosenProduct);

              // var divNodedivNode = document.createElement("div");
              // var imgNode = document.createElement("img");
              // imgNode.src = 'http://placekitten.com.s3.amazonaws.com/homepage-samples/200/138.jpg';
              //
              // var element = document.getElementById(activeButtonId).parentNode.parentNode.parentNode;
              // element.appendChild(imgNode);
              // element.appendChild(imgNode);
            }
          },
          {
            type: "container",
            name: "gpc-product-container",
            id: "gpc-product-container"
          },
          {
            type: "textbox",
            name: "link_text",
            label: "Link Text",
            tooltip: "The link text you would like your visitors to click",
            value: "Buy now, only __PRICE__",
            id: "gpc-link-text-box",
            oninput: function() {
              productLinkPreview();
            }
          },
          // {
          //     type   : 'radio',
          //     name   : 'link_opens_in',
          //     label  : 'Open In',
          //     text   : 'My Radio Button'
          // },
          {
            type: "container",
            name: "gpc-link-preview-container",
            id: "gpc-link-preview-container"
          }
        ];

        // Open window
        var win = editor.windowManager.open({
          title: "Get Price Comparison",
          width: 600,
          height: 450,
          body: windowBody,
          onsubmit: function(e) {
            if (jQuery.isEmptyObject(chosenProduct)) {
              alert("You must select a product, or click Cancel");
              e.stopPropagation();
              return false;
            }

            var content = '<span class="gpc-single-price" ' +
              'data-pc-affiliate-account="' +
              chosenProduct.affiliate_account_id +
              '" ' +
              'data-pc-merchant="' +
              chosenProduct.merchant_name +
              '" ' +
              'data-pc-product="' +
              chosenProduct.product_id +
              '" ' +
              'data-pc-link="' +
              chosenProduct.path +
              '" ' +
              'data-pc-link-text="' +
              chosenProduct.link_text +
              '">' +
              "Finding the latest price...</span>&nbsp;";

            // console.log("content", content);

            // Insert content when the window form is submitted
            editor.insertContent(content);

            // var iframeBodyContents = editor.iframeElement.contentDocument || editor.iframeElement.contentWindow.document;
            // var elements = iframeBodyContents.getElementsByClassName('gpc-single-price');
            // renderAllSinglePriceElements(elements);
          }
        });

        jQuery("#gpc-label-helper").css("display", "none");
        jQuery("#gpc-label-helper-l")
          .css("width", "300px")
          .html(
            '<a href="/wp-admin/admin.php?page=get_price_comparison-products-add">Don\'t see your product? Click here to add it.</a>'
          );
        win.find("listbox")[0].fire("select");
      }
    });
  });
})();
