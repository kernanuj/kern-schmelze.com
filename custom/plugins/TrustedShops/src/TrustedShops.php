<?php declare(strict_types=1);

namespace TrustedShops;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class TrustedShops extends Plugin
{

    public function install(InstallContext $installContext): void
    {
        $this->installDefaultConfig();

        parent::install($installContext);
    }

    protected function installDefaultConfig(): void
    {
        /** @var SystemConfigService $systemConfig */
        $systemConfig = $this->container->get(SystemConfigService::class);
        $domain = $this->getName() . '.config.';

        $defaultConfig = [
            'tsId' => '',
            'tsTrustbadgeExpertMode' => false,
            'tsTrustbadgeVariant' => 'reviews',
            'tsTrustbadgeOffsetY' => 0,
            'tsExpertTrustbadeCode' => "<script type=\"text/javascript\">
(function () {
    var _tsid = '%tsid%';
    _tsConfig = {
        'yOffset': '0', /* offset from page bottom */
        'variant': 'default', /* reviews, default, custom, custom_reviews */
        'customElementId': '', /* required for variants custom and custom_reviews */
        'trustcardDirection': '', /* for custom variants: topRight, topLeft, bottomRight, bottomLeft */
        'customBadgeWidth': '', /* for custom variants: 40 - 90 (in pixels) */
        'customBadgeHeight': '', /* for custom variants: 40 - 90 (in pixels) */
        'disableResponsive': 'false', /* deactivate responsive behaviour */
        'disableTrustbadge': 'false', /* deactivate Trustbadge® */
        'responsive': {
            'variant': '', /* floating, custom */
            'customElementId': '' /* required for variant custom */
        }
    };
    var _ts = document.createElement('script');
    _ts.type = 'text/javascript';
    _ts.charset = 'utf-8';
    _ts.async = true;
    _ts.src = '//widgets.trustedshops.com/js/' + _tsid + '.js';
    var __ts = document.getElementsByTagName('script')[0];
    __ts.parentNode.insertBefore(_ts, __ts);
})();
</script>",
            'tsReviewStickerActive' => false,
            'tsReviewStickerExpertMode' => false,
            'tsReviewStickerFontType' => 'Arial',
            'tsReviewStickerReviewCount' => 5,
            'tsReviewStickerMinRating' => 3.0,
            'tsReviewStickerBackgroundColor' => '#ffdc0f',
            'tsExpertReviewStickerCode' => "<script type=\"text/javascript\">
(function() {
    _tsRatingConfig = {
        tsid: '%tsid%',
        variant: 'testimonial',
        theme: 'light',
        reviews: '5',
        betterThan: '3.0',
        richSnippets: 'off',
        backgroundColor: '#ffdc0f',
        linkColor: '#000000',
        quotationMarkColor: '#FFFFFF',
        fontFamily: 'Arial',
        reviewMinLength: '10'
    };
    var scripts = document.getElementsByTagName('SCRIPT'),
        me = scripts[scripts.length - 1];
    var _ts = document.createElement('SCRIPT');
    _ts.type = 'text/javascript';
    _ts.async = true;
    _ts.src =
        '//widgets.trustedshops.com/reviews/tsSticker/tsSticker.js';
    me.parentNode.insertBefore(_ts, me);
    _tsRatingConfig.script = _ts;
})();
</script>",
            'tsExpertReviewStickerJquerySelector' => '',
            'tsRichSnippetsActive' => false,
            'tsRichSnippetsPageTypeCategory' => false,
            'tsRichSnippetsPageTypeProduct' => false,
            'tsRichSnippetsPageTypeStart' => false,
            'tsRichSnippetsExpertMode' => false,
            'tsExpertRichSnippetsCode' => "<script type=\"application/ld+json\">
{
    \"@context\": \"http://schema.org\",
    \"@type\": \"Organization\",
    \"name\": \"%shopname%\",
    \"aggregateRating\" : {
        \"@type\": \"AggregateRating\",
        \"ratingValue\" : \"%result%\",
        \"bestRating\" : \"%max%\",
        \"ratingCount\" : \"%count%\"
    }
}
</script>",
            'tsProductReviewsActive' => false,
            'tsProductReviewsTabActive' => false,
            'tsProductReviewsTabName' => 'Trusted Shops Bewertungen',
            'tsProductReviewsTabExpertMode' => false,
            'tsProductReviewsTabBorderColor' => '#0DBEDC',
            'tsProductReviewsTabStarColor' => '#FFDC0F',
            'tsProductReviewsTabBackgroundColor' => '#FFFFFF',
            'tsProductReviewsTabStarSize' => 15,
            'tsExpertProductReviewsTabCode' => "<script type=\"text/javascript\">
(function () {
    _tsProductReviewsConfig = {
        tsid: '%tsid%',
        sku: ['%sku%'],
        variant: 'productreviews',
        borderColor: '#0DBEDC',
        backgroundColor: '#ffffff',
        element: '#ts_product_sticker',
        locale: '%locale%',
        starColor: '#FFDC0F',
        starSize: '15px',
        richSnippets: 'off',
        ratingSummary: 'false', 
        maxHeight: '1200px',
        hideEmptySticker: 'false',
        introtext: 'What our customers say about us:'
        /* optional */
    };
    var scripts = document.getElementsByTagName('SCRIPT'),
    me = scripts[scripts.length - 1];
    var _ts = document.createElement('SCRIPT');
    _ts.type = 'text/javascript';
    _ts.async = true;
    _ts.charset = 'utf-8';
    _ts.src ='//widgets.trustedshops.com/reviews/tsSticker/tsProductSticker.js';
    me.parentNode.insertBefore(_ts, me);
    _tsProductReviewsConfig.script = _ts;
})();
</script>",
            'tsExpertProductReviewsTabJquerySelector' => '',
            'tsProductRatingStarsCategoryActive' => false,
            'tsProductRatingStarsDetailsActive' => false,
            'tsProductRatingStarsExpertMode' => false,
            'tsProductRatingStarsHideEmptyRatings' => false,
            'tsProductRatingStarsStarColor' => '#FFDC0F',
            'tsProductRatingStarsStarSize' => 15,
            'tsProductRatingStarsFontSize' => 12,
            'tsExpertProductRatingStarsCode' => "<script type=\"text/javascript\" src=\"//widgets.trustedshops.com/reviews/tsSticker/tsProductStickerSummary.js\"></script>
<script type=\"text/javascript\">
(function() {
    var summaryBadge = new productStickerSummary();
    summaryBadge.showSummary(
        {
            'tsId': \"%tsid%\",
            'sku': ['%sku%'],
            'element': '#ts_product_widget_position',
            'starColor' : '#FFDC0F',
            'starSize' : '14px',
            'fontSize' : '12px',
            'showRating' : 'true' ,
            'scrollToReviews' : 'false' ,
            'enablePlaceholder': 'true'
        }
    );
})();
</script>",
            'tsExpertProductRatingStarsJquerySelector' => '',
            'tsAvailableProductDeliveryTime' => 'default',
            'tsNotAvailableProductDeliveryTime' => 'default',
            'tsAvailableProductDeliveryTimeDays' => 4,
            'tsNotAvailableProductDeliveryTimeDays' => 10,
        ];

        foreach( $defaultConfig as $configKey => $configValue ) {
            $currentValue = $systemConfig->get($domain . $configKey);
            if ($currentValue === null) {
                $systemConfig->set($domain . $configKey, $configValue );
            }
        }
    }

}