
/**
 * Tabbable JavaScript codes & Initiate Color Picker
 *
 * This code uses localstorage for displaying active tabs
 * 
 * RWC Related Posts v1.0.0 - 22 December, 2024
 * By @realwebcare - https://www.realwebcare.com/
 */
jQuery(document).ready(function($) {
    //Initiate Color Picker
    $('.wp-color-picker-field').wpColorPicker();
    // Switches option sections
    $('.group').hide();
    var activetab = '';
    if (typeof(localStorage) != 'undefined' ) {
        activetab = localStorage.getItem("activetab");
    }
    //if url has section id as hash then set it as active or override the current local storage value
    if(window.location.hash){
        activetab = window.location.hash;
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("activetab", activetab);
        }
    }
    if (activetab != '' && $(activetab).length ) {
        $(activetab).fadeIn();
    } else {
        $('.group:first').fadeIn();
    }
    $('.group .collapsed').each(function(){
        $(this).find('input:checked').parent().parent().parent().nextAll().each(
        function(){
            if ($(this).hasClass('last')) {
                $(this).removeClass('hidden');
                return false;
            }
            $(this).filter('.hidden').removeClass('hidden');
        });
    });
    if (activetab != '' && $(activetab + '-tab').length ) {
        $(activetab + '-tab').addClass('nav-tab-active');
    }
    else {
        $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
    }
    $('.nav-tab-wrapper a').click(function(evt) {
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active').blur();
        var clicked_group = $(this).attr('href');
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("activetab", $(this).attr('href'));
        }
        $('.group').hide();
        $(clicked_group).fadeIn();
        evt.preventDefault();
    });
    $('body').on('click', '.wpsa-browse', function (event) {
        event.preventDefault();
        var self = $(this);
        // Create the media frame.
        var file_frame = wp.media.frames.file_frame = wp.media({
            title: self.data('uploader_title'),
            button: {
                text: self.data('uploader_button_text'),
            },
            multiple: false
        });
        file_frame.on('select', function () {
            attachment = file_frame.state().get('selection').first().toJSON();
            self.prev('.wpsa-url').val(attachment.url).change();
        });
        // Finally, open the modal
        file_frame.open();
    });

    // Get references to the short description checkbox and the description length input field.
    var $warpEnableCheckbox = $("input[name='warp_general[warp_enable]']");
    var $shortDescCheckbox = $("input[name='warp_general[short_desc]']");

    // Adjust the selector if it's not inside a table row.
    var $warpEnableField = $(".warp_columns, .warp_postno, .warp_order, .warp_orderby, .show_thumb, .show_date, .show_title, .short_desc, .desc_length, .show_author");
    var $descLengthField = $(".desc_length");

    // Function to toggle the visibility of the desc_length field based on the checkbox state.
    function toggleWarpEnableField() {
        if ($warpEnableCheckbox.is(':checked')) {
            $warpEnableField.show();

            if ($shortDescCheckbox.is(':checked')) {
                $descLengthField.show();
            } else {
                $descLengthField.hide();
            }
        } else {
            $warpEnableField.hide();
        }
    }

    function toggleDescLengthField() {
        if ($shortDescCheckbox.is(':checked')) {
            $descLengthField.show();
        } else {
            $descLengthField.hide();
        }
    }

    // Initial check when the page loads.
    toggleWarpEnableField();
    toggleDescLengthField();

    // Attach an event listener to the checkbox to toggle visibility dynamically.
    $warpEnableCheckbox.on('change', function () {
        toggleWarpEnableField();
    });

    $shortDescCheckbox.on('change', function () {
        toggleDescLengthField();
    });
});