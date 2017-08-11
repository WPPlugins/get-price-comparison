// console.log("amazon loaded");

jQuery(document).ready(function(e) {
  getAffiliateAccountSelector().bind("change", function() {
    // console.log("value", this.value);
  });
});

PubSub.subscribe(CHANGED_SELECTED_AFFILIATE_ACCOUNT, function(msg, data) {
  if (data.selectedAffiliateAccount.name !== AFFILIATE_ACCOUNT.WEBGAINS) {
    // remove listener
    getProductUrlSelector().unbind("input", handleWebgainsProductLookup);
    getProductIdSelector().unbind("input", handleWebgainsProductLookup);

    return false;
  }

  getProductUrlSelector().bind("input", handleWebgainsProductLookup);
  getProductIdSelector().bind("input", handleWebgainsProductLookup);
});

function handleWebgainsProductLookup() {
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

  // we know the ID, so let's look it up
  if (product_id_selector.val() !== "") {
    url = baseUrl + "&product_id=" + product_id_selector.val();
  }

  // we don't know the ID, so let's try and figure it out from the URL
  if (product_url_selector.val() !== "") {
    url = baseUrl + "&url=" + product_url_selector.val();
  }

  jQuery
    .ajax({
      url: url,
      beforeSend: function() {
        displayTrackedProcessing();
      }
    })
    .done(function(product) {
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
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
      handleInvalidProductState();

      if (jqXHR.status !== 404) {
        console.error("Product lookup error", jqXHR, textStatus, errorThrown);
      } else {
        console.info(
          "Probably a bad product ID",
          jqXHR,
          textStatus,
          errorThrown
        );
      }
    });
}
