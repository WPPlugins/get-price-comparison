// console.log("amazon loaded");

jQuery(document).ready(function(e) {
  getAffiliateAccountSelector().bind("change", function() {
    // console.log("value", this.value);
  });
});

PubSub.subscribe(CHANGED_SELECTED_AFFILIATE_ACCOUNT, function(msg, data) {
  if (data.selectedAffiliateAccount.name !== AFFILIATE_ACCOUNT.AMAZON) {
    // remove listeners
    // getProductUrlSelector().unbind('input', handleAmazonProductLookup);
    getProductIdSelector().unbind("input", handleInput);

    return false;
  }

  // getProductIdSelector().bind('input', handleAmazonProductLookup);

  getProductIdSelector().bind("input", handleInput);

  function handleInput() {
    var asin = this.value;

    if (asin === "") {
      // console.log("product id box looked empty");
      return false;
    }

    var asinRegex = new RegExp(/^[0-9A-Z]{10}$/);

    if (asinRegex.test(asin) === false) {
      // console.log("not an ASIN");
      return false;
    }

    jQuery
      .ajax({
        url: API_BASE_URL + "/amazonasin/" + asin,
        type: "GET",
        contentType: "application/json",
        beforeSend: function() {
          displayTrackedProcessing();
        }
      })
      .done(function(data, textStatus, jqXHR) {
        // console.log("asin lookup result", data, textStatus, jqXHR);

        // lookup product
        handleAmazonProductLookup();
      })
      .fail(function(jqXHR, textStatus, errorThrown) {
        // console.log("asin lookup err", jqXHR, textStatus, errorThrown);

        displayUntrackedProcessing();
        addNewAsin(asin);
      });
  }

  getProductUrlSelector().bind("input", function() {
    // we don't know the ASIN, so let's try and figure it out from the URL
    if (getProductUrlSelector().val() !== "") {
      var url = getProductUrlSelector().val();
      var asin = asinRegex(url);

      if (asin === [""]) {
        return false;
      }

      getProductIdSelector().val(asin[1]).trigger("input");

      // without this, in this instance a triggered change would have called 'resetProductUrlBoxIfChangingProductId'
      // which would then blank out the URL box - unwanted in this case
      getProductUrlSelector().val(url);
    }
  });
});

function addNewAsin(asin) {
  jQuery
    .ajax({
      url: API_BASE_URL + "/amazonasin",
      type: "POST",
      contentType: "application/json",
      dataType: "json",
      data: JSON.stringify({
        asin: asin
      })
    })
    .done(function(data, textStatus, jqXHR) {
      handleAmazonProductLookup();
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
      console.log("asin post err", jqXHR, textStatus, errorThrown);
    });
}

function pollServer(maxRetries, url, success, fail, timeout) {
  if (parseInt(maxRetries) <= 0) {
    return false;
  }

  if (typeof timeout === "undefined") {
    timeout = 2500;
  }

  window.setTimeout(
    function() {
      jQuery
        .ajax({
          url: url
        })
        .done(function(product) {
          if (product.data.length === 0) {
            return tryAgain();
          }

          success(product);

          pollServer(0);
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
          fail(jqXHR, textStatus, errorThrown);

          return tryAgain();
        });
    },
    timeout
  );

  function tryAgain() {
    pollServer(--maxRetries, url, success, fail, 3000);
  }
}

function handleAmazonProductLookup() {
  if (
    getProductIdSelector()[0].value === "" &&
    getProductUrlSelector()[0].value === ""
  ) {
    // if (getProductIdSelector()[0].value === "") {
    resetProductPreview();
    resetProductLinkPath();
    return;
  }

  var affiliate_account_selector = getAffiliateAccountSelector();
  var merchant_selector = getMerchantSelector();
  var product_id_selector = getProductIdSelector();
  var product_url_selector = getProductUrlSelector();

  var selectedAffiliateProgram = affiliate_account_selector
    .find(":selected")
    .data("program-name");
  var selectedAffiliateCode = merchant_selector
    .find(":selected")
    .data("affiliate-code");
  var selectedMerchant = merchant_selector.find(":selected").data("resource");
  var affiliateAccountId = merchant_selector
    .find(":selected")
    .data("affiliate-account-id");

  var baseUrl = API_BASE_URL +
    "/" +
    selectedMerchant +
    "?affiliateAccount=" +
    affiliateAccountId;
  var url;

  // we know the ASIN, so let's look it up
  if (product_id_selector.val() !== "") {
    url = baseUrl + "&product_id=" + product_id_selector.val();
  }

  // we don't know the ASIN, so let's try and figure it out from the URL
  if (product_url_selector.val() !== "") {
    var asin = asinRegex(product_url_selector.val());

    if (asin === [""]) {
      return false;
    }

    url = baseUrl + "&product_id=" + asin[1];
  }

  function successHandler(product) {
    if (typeof product.data === "undefined" || product.data.length === 0) {
      return handleInvalidProductState();
    }

    product = product.data[0].gpc_view;

    getAffiliateUrlSelector().val(product.affiliate_url);
    getAffiliateAccountId().val(affiliateAccountId);
    jQuery("[name='affiliate_program_name']").val(selectedAffiliateProgram);
    jQuery("[name='affiliate_code']").val(selectedAffiliateCode);
    jQuery("[name='merchant_name']").val(selectedMerchant);
    jQuery("[name='product_id']").val(product.id);

    generateProductPreview(product);
    pathHelper(product);
    linkTextHelper(product);
  }

  function failureHandler(jqXHR, textStatus, errorThrown) {
    handleInvalidProductState();

    if (jqXHR.status !== 404) {
      console.error("Product lookup error", jqXHR, textStatus, errorThrown);
    } else {
      console.info("Probably a bad product ID", jqXHR, textStatus, errorThrown);
    }
  }

  pollServer(10, url, successHandler, failureHandler);
}

function asinRegex(url) {
  return url.match("/([a-zA-Z0-9]{10})(?:[/?]|$)");
}
