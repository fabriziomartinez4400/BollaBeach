(function($){
  $(function(){
    if (typeof window.wcusageAffUsers === 'undefined') return;

    var $add = $('<a>', {
      href: wcusageAffUsers.addAffiliateUrl,
      class: 'wcusage-settings-button',
      id: 'wcu-admin-create-registration-link',
      text: wcusageAffUsers.addLabel
    });

    var $manage = $('<a>', {
      href: wcusageAffUsers.manageAffiliatesUrl,
      class: 'wcusage-settings-button',
      id: 'wcu-admin-manage-affiliates-link',
      text: wcusageAffUsers.manageLabel
    });

    var $target = $(".wrap .wcusage-settings-button").eq(0);
    if ($target.length) {
      $target.after($manage).after($add);
    } else {
      var $h1 = $('.wrap h1').first();
      if ($h1.length) {
        $h1.after($manage).after($add);
      }
    }
  });
})(jQuery);
