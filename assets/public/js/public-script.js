(function($) {
    'use strict';

    // تحميل سكريبت AdSense
    function loadAdSenseScript(callback) {
        var script = document.createElement('script');
        script.async = true;
        script.src = adsrevshareData.adsenseUrl;
        script.onload = callback;  // استدعاء callback عند التحميل
        document.head.appendChild(script);
    }

    // تهيئة إعلانات AdSense
    function initializeAdSense() {
        window.adsbygoogle = window.adsbygoogle || [];
        window.adsbygoogle.push({});
    }

    // تحميل الإعلانات
    function loadAds() {
        $('.adsbygoogle').each(function() {
            initializeAdSense();  // تهيئة الإعلان فقط
        });
    }

    // تنفيذ عند تحميل المستند
    $(document).ready(function() {
        loadAdSenseScript(function() {
            loadAds();  // تحميل الإعلانات بعد تحميل سكريبت AdSense
        });
    });

})(jQuery);
