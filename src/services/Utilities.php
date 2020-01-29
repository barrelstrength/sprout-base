<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\services;

use barrelstrength\sproutbase\jobs\PurgeElements;
use Craft;
use yii\base\Component;

class Utilities extends Component
{
    public function purgeElements(PurgeElements $purgeElementsJob, $delay = null)
    {
        if ($delay) {
            Craft::$app->getQueue()->delay($delay)->push($purgeElementsJob);
        } else {
            Craft::$app->getQueue()->push($purgeElementsJob);
        }
    }

    /**
     * Adds open in new window icon to $nth li tag of subnav, finding it by label
     *
     * @param string $pluginHandle
     * @param string $navLabel
     */
    public function addSubNavIcon(string $pluginHandle, string $navLabel)
    {
        $js = <<<EOD
(function() {
    var navItem = $('#nav-{$pluginHandle}').find('li a');
    var cssSelector = null;
    navItem.each(function(index) {
        if ($(this).text().trim() === '{$navLabel}') {
        nth = index + 1;
        cssSelector = "#nav-{$pluginHandle} .subnav li:nth-child("+nth+") a:after";
        additionalCss = '<style>'+cssSelector+' { font-family: "Craft"; content: "circlerarr"; margin-left: 5px; }</style>';
        console.log(additionalCss);
        $('head').append(additionalCss);
        return false;
        }
    });
})();
EOD;

        Craft::$app->getView()->registerJs($js);
    }
}
