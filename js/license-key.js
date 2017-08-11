var validateLicenseKey = function(key) {
  var saveButton = jQuery("#save-license-key-settings");
  saveButton.attr("disabled", true);

  if (key === "" || typeof key === "undefined") {
    key = jQuery("#license_key").val();
  }

  if (key === "" || typeof key === "undefined") {
    return;
  }

  var licenseKeyChecker = jQuery("#description-license_key");
  licenseKeyChecker.text("Checking...");

  var tick = '<span class="dashicons dashicons-yes"></span>';
  var cross = '<span class="dashicons dashicons-no"></span>';

  function looksGood() {
    licenseKeyChecker.text("");
    licenseKeyChecker.append(tick);
    saveButton.attr("disabled", false);
  }

  function looksBad(message) {
    licenseKeyChecker.text(message || "Invalid");
    licenseKeyChecker.append(cross);
    saveButton.attr("disabled", true);
  }

  jQuery
    .ajax({
      url: API_BASE_URL + "/affiliate-account?user_id=" + key
    })
    .done(function(result, textStatus, xhr) {
      if (xhr.status === 200) {
        looksGood();
      } else {
        looksBad();
      }
    })
    .fail(function(err) {
      if (err.status === 403) {
        looksBad();
      } else {
        looksBad("Unable to validate, refresh this page to try again.");
      }

      return false;
    });
};
