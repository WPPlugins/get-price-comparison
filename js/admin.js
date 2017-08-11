var validator;

jQuery(document).ready(function(e) {
  jQuery(document).on("click", ".btn-clipboard-copy", function(e) {
    e.preventDefault();
  });
  new Clipboard(".button");

  jQuery.validator.setDefaults(
    {
      // debug: true,
      //   onkeyup: true
    }
  );

  jQuery.validator.addMethod("pathRegex", function(value, element) {
    var allowedPathRegex = new RegExp("\/.+?");
    return allowedPathRegex.test(value) !== false;
  });

  if (typeof pathChecker === "undefined") {
    pathChecker = "";
  }

  validator = jQuery("#form-gpc-link").validate({
    rules: {
      product_id: {
        required: true
      },
      path: {
        required: true,
        pathRegex: true,
        remote: {
          url: pathChecker, // added in form.php
          type: "post",
          data: {
            path: function() {
              return getPathSelector().val();
            }
          }
        }
      },
      link_text: {
        required: true
      }
    },
    messages: {
      product_id: {
        required: '<span class="dashicons dashicons-no"></span><br/>This is a required field.'
      },
      path: {
        required: '<span class="dashicons dashicons-no"></span><br/>This is a required field.',
        pathRegex: '<span class="dashicons dashicons-no"></span><br/> Must start with a forward slash (<code>/</code>), and have at least one letter or number',
        remote: "" // this message comes from the JSON response in get-price-comparison-path-checker.php
      },
      link_text: '<span class="dashicons dashicons-no"></span><br/>This is a required field.'
    }
  });

  getProductIdSelector().bind("input", function() {
    enableSavingFormIfValid(validator.form());
  });
  getPathSelector().bind("input", function() {
    enableSavingFormIfValid(validator.form());
  });
  getLinkTextSelector().bind("input", function() {
    enableSavingFormIfValid(validator.form());
  });
});

var INVALID_PRODUCT_MESSAGE = "That doesn't look like a valid product ID";

function enableSavingFormIfValid(isValid) {
  getProductSubmitButtonSelector().prop("disabled", !isValid);
}

function getProductSubmitButtonSelector() {
  return jQuery("#gpc-product-submit-button");
}

function getAffiliateAccountSelector() {
  return jQuery("[name='affiliate_account_select']");
}

function getAffiliateAccountId() {
  return jQuery("[name='affiliate_account_id']");
}

function getMerchantSelector() {
  return jQuery("[name='merchant_id']");
}

function getProductIdSelector() {
  return jQuery("[name='product_id']");
}

function getProductUrlSelector() {
  return jQuery("[name='product_url']");
}

function getPathSelector() {
  return jQuery("[name='path']");
}

function getLinkTextSelector() {
  return jQuery("[name='link_text']");
}

function getAffiliateUrlSelector() {
  return jQuery("[name='affiliate_url']");
}

function addProductDropDownHelper(product) {
  var licenseKey = jQuery("#gpc-license-key").val();

  return jQuery
    .ajax({
      url: API_BASE_URL + "/affiliate-account?user_id=" + licenseKey
    })
    .done(function(affiliateAccountData) {
      var availableAffiliatePrograms = convertDataFromAffiliateAccountLookupIntoExpectedShape(
        affiliateAccountData
      );

      var affiliate_account_selector = populateAffiliateAccountDropDownList(
        availableAffiliatePrograms,
        product["affiliate_account_id"]
      );

      affiliate_account_selector.on("change", function() {
        var selectedValue = this.value;
        onChangeAffiliateAccount(availableAffiliatePrograms, selectedValue);

        PubSub.publish(CHANGED_SELECTED_AFFILIATE_ACCOUNT, {
          selectedAffiliateAccount: jQuery
            .grep(availableAffiliatePrograms, function(affAccount) {
              return affAccount.id == selectedValue; // loose type check is correct
            })
            .pop()
        });
      });

      affiliate_account_selector.trigger("change");

      pathHelper();
      linkTextHelper();
    });
}

function populateAffiliateAccountDropDownList(
  data,
  selectedAffiliateAccountId
) {
  var affiliate_account_selector = getAffiliateAccountSelector();

  // add all available Affiliate Accounts to the drop down box
  data.map(function(item, index) {
    affiliate_account_selector.append(
      jQuery("<option></option>")
        .attr("value", item.id)
        .data("program-name", item.name)
        .text(item.name)
    );
  });

  if (selectedAffiliateAccountId !== "") {
    affiliate_account_selector.val(selectedAffiliateAccountId);
  }

  return affiliate_account_selector;
}

function populateMerchantDropDownList(data, selectedMerchantId) {
  var merchant_selector = getMerchantSelector();
  merchant_selector[0].options.length = 0;

  data.map(function(merchant) {
    merchant_selector.append(
      jQuery("<option></option>")
        .attr("value", merchant.id)
        .data("resource", merchant.resource)
        .data("affiliate-code", merchant.affiliate_code)
        .data("affiliate-account-id", merchant.affiliate_account_id)
        .text(merchant.name + " - " + merchant.affiliate_code)
    );
  });

  if (selectedMerchantId !== "") {
    merchant_selector.val(selectedMerchantId);
  }

  return merchant_selector;
}

function resetProductIdInput() {
  getProductIdSelector()[0].value = updateProduct["product_id"]; // reset when changing affiliate accounts
  getProductUrlSelector()[0].value = ""; // reset when changing affiliate accounts
  resetProductPreview();
  resetAffiliateUrl();
}

function resetProductLinkPath() {
  getPathSelector()[0].value = updateProduct["path"];
  pathHelper();
  linkTextHelper();
}

function resetAffiliateUrl() {
  getAffiliateUrlSelector().val(updateProduct["affiliate_url"]);
}

function onChangeAffiliateAccount(data, selectedItem) {
  resetProductIdInput();
  resetProductLinkPath();

  var selectedAffiliateAccount = data.find(function(affiliateAccount) {
    return affiliateAccount.id === parseInt(selectedItem);
  });

  var availableMerchants = selectedAffiliateAccount.merchants;

  var merchant_selector = populateMerchantDropDownList(
    availableMerchants,
    updateProduct["merchant_id"]
  );
  merchant_selector.on("change", function() {
    resetProductIdInput(); // reset when changing merchant inside an affiliate account
    resetProductLinkPath();
    pathHelper();
  });

  // getProductUrlSelector().bind('input', handleProductLookup);
  getProductIdSelector().bind("input", function() {
    resetProductUrlBoxIfChangingProductId();
    // handleProductLookup();
  });

  resetProductIdInput();
  pathHelper();
  linkTextHelper();
}

function resetProductUrlBoxIfChangingProductId() {
  var product_id_selector = getProductIdSelector();
  var product_url_selector = getProductUrlSelector();

  if (product_id_selector.val() === "") {
    return false;
  }

  if (product_url_selector.val() === "") {
    return false;
  }

  product_url_selector.val("");
}

function getPreviewArea() {
  return jQuery("#gpc-product-preview");
}

function resetProductPreview() {
  getPreviewArea().html("");
}

function generateProductPreview(previewData) {
  resetProductPreview();

  if (typeof previewData === "undefined") {
    return false;
  }

  var image = previewData.image_url
    ? previewData.image_url
    : "https://placeholdit.imgix.net/~text?txtsize=33&txt=Preview%20unavailable&w=200&h=200";

  var img = '<td width="205px">' +
    '<img src="' +
    image +
    '" width="200" height="200">' +
    "</td>";
  var info = '<td class="gpc-product-description">' +
    "<h3>" +
    previewData.name +
    "</h3>" +
    "<p>" +
    previewData.description +
    "</p>" +
    '<p>Current Price: <span style="font-weight: bold">' +
    previewData.price.new.formatted +
    "</span></p>" +
    '<p><a href="' +
    previewData.affiliate_url +
    '" target="_blank">Product Page</a></p>' +
    "</td>";

  getPreviewArea().append(img).append(info);
}

function getProductPathFormField() {
  return jQuery("#gpc-product-path");
}

function getPathValidationOutputElement() {
  return jQuery("#gpc-path-validation-output");
}

function pathHelper(product) {
  var clickToUseSuggestedLinkPath = jQuery("#click-to-use-suggested-link-path");
  clickToUseSuggestedLinkPath.hide();

  var selectedMerchant = getMerchantSelector()
    .find(":selected")
    .data("resource");

  if (typeof product === "undefined") {
    getPathSelector()[0].placeholder = "e.g. /" +
      selectedMerchant +
      "/" +
      "your-chosen-product-name";
    return true;
  }

  getPathSelector().bind("input", function() {
    if (this.value === "") {
      clickToUseSuggestedLinkPath.show();
    }
  });

  if (getPathSelector()[0].value !== "") {
    return true;
  }

  var suggestedValue = "/" + selectedMerchant + "/" + slugify(product.name);
  getPathSelector()[0].placeholder = "suggested: " + suggestedValue;

  clickToUseSuggestedLinkPath.show();

  clickToUseSuggestedLinkPath.on("click", function(e) {
    e.preventDefault();
    clickToUseSuggestedLinkPath.hide();
    getPathSelector()[0].value = suggestedValue;
    enableSavingFormIfValid(validator.form());
  });

  return true;

  function slugify(text) {
    return text
      .toString()
      .toLowerCase()
      .replace(/\s+/g, "-") // Replace spaces with -
      .replace(/[^\w\-]+/g, "") // Remove all non-word chars
      .replace(/\-\-+/g, "-") // Replace multiple - with single -
      .replace(/^-+/, "") // Trim - from start of text
      .replace(/-+$/, ""); // Trim - from end of text
  }
}

function linkTextHelper(product) {
  var clickToUseSuggestedLinkText = jQuery("#click-to-use-suggested-link-text");
  clickToUseSuggestedLinkText.hide();

  if (typeof product === "undefined") {
    getLinkTextSelector()[0].placeholder = "e.g. Buy now, only __PRICE__";
    return true;
  }

  getLinkTextSelector().bind("input", function() {
    if (this.value === "") {
      clickToUseSuggestedLinkText.show();
    }
  });

  if (getLinkTextSelector()[0].value !== "") {
    return true;
  }

  var suggestedValue = "Buy now, only __PRICE__";
  getLinkTextSelector()[0].placeholder = "suggested: " + suggestedValue;

  clickToUseSuggestedLinkText.show();

  clickToUseSuggestedLinkText.on("click", function(e) {
    e.preventDefault();
    clickToUseSuggestedLinkText.hide();
    getLinkTextSelector()[0].value = suggestedValue;
    enableSavingFormIfValid(validator.form());
  });

  return true;
}

function handleInvalidProductState() {
  getAffiliateUrlSelector().val(INVALID_PRODUCT_MESSAGE);
  getAffiliateAccountId().val("");
  jQuery("[name='affiliate_program_name']").val("");
  jQuery("[name='affiliate_code']").val("");
  jQuery("[name='merchant_name']").val("");

  resetProductPreview();
}

function displayUntrackedProcessing() {
  resetProductPreview();

  var info = '<td class="gpc-awaiting-product" colspan="2">' +
    '<div align="center">' +
    '<img src="/wp-admin/images/loading.gif"><br />' +
    "<p>Oh no!</p>" +
    "<p>We aren't currently tracking this product.</p>" +
    "<p>Please wait a moment whilst the product is fetched for the first time...</p>" +
    "</div>" +
    "</td>";

  getPreviewArea().append(info);
}

function displayTrackedProcessing() {
  resetProductPreview();

  var info = '<td class="gpc-awaiting-product" colspan="2">' +
    '<div align="center">' +
    '<img src="/wp-admin/images/loading.gif"><br />' +
    "<p>Finding your selected product...</p>" +
    "</div>" +
    "</td>";

  getPreviewArea().append(info);
}
