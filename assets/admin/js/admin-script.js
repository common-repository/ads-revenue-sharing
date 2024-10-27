jQuery(document).ready(function($) {
    $('.ad-tab').click(function() {
        var tabId = $(this).data('tab');
        $('.ad-tab').removeClass('active');
        $('.ad-content').removeClass('active');
        $(this).addClass('active');
        $('#' + tabId).addClass('active');
    });

    // Activate the first tab by default
    $('.ad-tab:first').click();
});