/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

$(document).ready(function() {

    $("#sproutseo-preview-search").click(function() {
        $("#tab-sproutseo-preview-facebook").hide();
        $("#tab-sproutseo-preview-twitter").hide();

        $("#sproutseo-preview-facebook").removeClass("active");
        $("#sproutseo-preview-twitter").removeClass("active");
        $("#sproutseo-preview-search").addClass("active");

        $("#tab-sproutseo-preview-search").show("slow", function() {
            // Animation complete.
        });
    });

    $("#sproutseo-preview-facebook").click(function() {
        $("#tab-sproutseo-preview-search").hide();
        $("#tab-sproutseo-preview-twitter").hide();

        $("#sproutseo-preview-search").removeClass("active");
        $("#sproutseo-preview-twitter").removeClass("active");
        $("#sproutseo-preview-facebook").addClass("active");

        $("#tab-sproutseo-preview-facebook").show("slow", function() {
            // Animation complete.
        });
    });

    $("#sproutseo-preview-twitter").click(function() {
        $("#tab-sproutseo-preview-search").hide();
        $("#tab-sproutseo-preview-facebook").hide();

        $("#sproutseo-preview-facebook").removeClass("active");
        $("#sproutseo-preview-search").removeClass("active");
        $("#sproutseo-preview-twitter").addClass("active");

        $("#tab-sproutseo-preview-twitter").show("slow", function() {
            // Animation complete.
        });
    });

});