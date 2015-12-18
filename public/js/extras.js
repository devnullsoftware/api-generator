jQuery(document).ready(function() {
    jQuery('.toggle-input').change(function() {
        jQuery('.user-input').toggleClass('hidden');
    });
});