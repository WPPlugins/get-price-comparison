// var _ = require('lodash');

function convertDataFromAffiliateAccountLookupIntoExpectedShape(dataFromApi) {
    var availableAffiliatePrograms = getAvailableAffiliatePrograms(dataFromApi);

    availableAffiliatePrograms.map(function (affProg) {
        affProg.merchants = [];
    });

    addMerchantData(dataFromApi, availableAffiliatePrograms);

    return availableAffiliatePrograms;
}



function getAvailableAffiliatePrograms(affiliateAccountData) {

    var availableAffiliatePrograms = [];

    affiliateAccountData.map(function (affAcc) {
        var found = _.find(availableAffiliatePrograms, function (affProg) {
            return affProg.id === affAcc.merchant.affiliate_program.id
        });

        if (found) { // already known, don't need duplicates
            return;
        }

        availableAffiliatePrograms.push(
            affAcc.merchant.affiliate_program
        );
    });

    return availableAffiliatePrograms;
}


function addMerchantData(dataFromApi, availableAffiliatePrograms) {
    dataFromApi.map(function (affAcc) {
        var program = affAcc.merchant.affiliate_program;

        var affiliateProgram = _.find(availableAffiliatePrograms, function (affProg) {
            return program.id === affProg.id
        });

        var merchant = affAcc.merchant;

        affiliateProgram.merchants.push({
            id: merchant.id,
            name: merchant.name,
            resource: merchant.resource,
            enabled: merchant.enabled,
            affiliate_code: affAcc.affiliate_code,
            affiliate_account_id: affAcc.id
        });
    });
}


// module.exports = {
//     convertDataFromAffiliateAccountLookupIntoExpectedShape: convertDataFromAffiliateAccountLookupIntoExpectedShape
// };