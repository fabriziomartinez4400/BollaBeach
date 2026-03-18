(function($){
  function toggleField(idx){
    var $typeSel = $('#wcusage_field_registration_custom_type_' + idx);
    if (!$typeSel.length) return;
    var selected = $typeSel.find(':selected').val();
    var $optionsWrap = $('.registration_custom_options_' + idx);
    var $requiredWrap = $('.registration_custom_required_' + idx);
    var $labelWrap = $('.registration_custom_label_' + idx + ' .reg-field-label');

    if (selected === 'dropdown' || selected === 'radio') {
      $optionsWrap.show();
    } else {
      $optionsWrap.hide();
    }
    if (selected === 'header' || selected === 'paragraph') {
      $requiredWrap.hide();
      if ($labelWrap.length) { $labelWrap.text((typeof wcuRegSettings !== 'undefined' && wcuRegSettings.textLabel) ? wcuRegSettings.textLabel : 'Text:'); }
    } else {
      $requiredWrap.show();
      if ($labelWrap.length) { $labelWrap.text((typeof wcuRegSettings !== 'undefined' && wcuRegSettings.fieldLabel) ? wcuRegSettings.fieldLabel : 'Field Label:'); }
    }
  }

  function bindType(idx){
    $(document).off('change.wcuType' + idx, '#wcusage_field_registration_custom_type_' + idx);
    $(document).on('change.wcuType' + idx, '#wcusage_field_registration_custom_type_' + idx, function(){
      toggleField(idx);
    });
  }

  function reindexClone($clone, newIdx){
    // wrapper class
    $clone
      .removeClass(function(i, cls){ return (cls || '').split(' ').filter(function(c){ return c.indexOf('registration_custom_') === 0; }).join(' '); })
      .addClass('registration_custom_' + newIdx);

    // label input
  // Remove any inline scripts copied from server-rendered blocks
  $clone.find('script').remove();

  var $label = $clone.find('[id^="wcusage_field_registration_custom_label_"]');
    $label.attr('id', 'wcusage_field_registration_custom_label_' + newIdx)
          .attr('name', 'wcusage_options[wcusage_field_registration_custom_label_' + newIdx + ']')
          .val('');

    // type select
    var $type = $clone.find('[id^="wcusage_field_registration_custom_type_"]');
    $type.attr('id', 'wcusage_field_registration_custom_type_' + newIdx)
        .attr('name', 'wcusage_options[wcusage_field_registration_custom_type_' + newIdx + ']')
        .removeClass(function(i, cls){ return (cls || '').split(' ').filter(function(c){ return c.indexOf('wcusage_field_registration_custom_type_') === 0; }).join(' '); })
        .addClass('wcusage_field_registration_custom_type_' + newIdx)
        .val('text');

    // options textarea
    var $opts = $clone.find('[id^="wcusage_field_registration_custom_options_"]');
    $opts.attr('id', 'wcusage_field_registration_custom_options_' + newIdx)
         .attr('name', 'wcusage_options[wcusage_field_registration_custom_options_' + newIdx + ']')
         .val('');

    // required checkbox
    var $req = $clone.find('[id^="wcusage_field_registration_custom_required_"]');
    $req.attr('id', 'wcusage_field_registration_custom_required_' + newIdx)
        .attr('name', 'wcusage_options[wcusage_field_registration_custom_required_' + newIdx + ']')
        .prop('checked', false)
        .val('1');

    // wrapper sub-classes
    $clone.find('[class*="registration_custom_options_"]')
          .removeClass(function(i, cls){ return (cls || '').split(' ').filter(function(c){ return c.indexOf('registration_custom_options_') === 0; }).join(' '); })
          .addClass('registration_custom_options_' + newIdx);
    $clone.find('[class*="registration_custom_required_"]')
          .removeClass(function(i, cls){ return (cls || '').split(' ').filter(function(c){ return c.indexOf('registration_custom_required_') === 0; }).join(' '); })
          .addClass('registration_custom_required_' + newIdx);
    $clone.find('[class*="registration_custom_label_"]')
          .removeClass(function(i, cls){ return (cls || '').split(' ').filter(function(c){ return c.indexOf('registration_custom_label_') === 0; }).join(' '); })
          .addClass('registration_custom_label_' + newIdx);

    // move up button id
    var $upBtn = $clone.find('button[id^="up-"]');
    if ($upBtn.length) {
      $upBtn.attr('id', 'up-' + newIdx);
    }

    return $clone;
  }

  function updateCount(newCount){
    $('#wcusage_field_registration_custom_fields').val(newCount);
    $.ajax({
      type: 'POST',
      url: (typeof wcuRegSettings !== 'undefined' ? wcuRegSettings.ajaxurl : ajaxurl),
      data: {
        action: 'wcusage_update_custom_fields_count',
        _ajax_nonce: (typeof wcuRegSettings !== 'undefined' ? wcuRegSettings.nonce : ''),
        count: newCount
      }
    });
  }

  $(function(){
    // Ensure each field container keeps its spacing by appending <br/><br/> after each block once
    var $list = $('#wcu-registration-custom-fields');
    if ($list.length){
      // Normalize existing DOM to ensure vertical stacking
      $list.children('div[class^="registration_custom_"]').each(function(){
        var $block = $(this);
        // if no spacer after, add one
        var $next = $block.next();
        if (!$next.is('br')){
          $block.after('<br/><br/>' );
        }
      });
    }

    // Init existing select handlers
    $('[id^="wcusage_field_registration_custom_type_"]').each(function(){
      var id = $(this).attr('id');
      var idx = id.replace('wcusage_field_registration_custom_type_', '');
      bindType(idx);
      toggleField(idx);
    });

    // Move up delegated handler for dynamically added fields (index > initialCount)
    $(document).on('click', 'button[id^="up-"]', function(){
      var id = $(this).attr('id');
      var idx = parseInt(id.replace('up-', ''), 10);
      if (isNaN(idx) || idx <= 1) return;
      var before = idx - 1;

      var label = $('#wcusage_field_registration_custom_label_' + before).val() || '';
      var type = $('#wcusage_field_registration_custom_type_' + before + ' option:selected').val() || '';
      var options = $('#wcusage_field_registration_custom_options_' + before).val() || '';
      var required = $('#wcusage_field_registration_custom_required_' + before).is(':checked') ? 1 : 0;

      var label_this = $('#wcusage_field_registration_custom_label_' + idx).val() || '';
      var type_this = $('#wcusage_field_registration_custom_type_' + idx + ' option:selected').val() || '';
      var options_this = $('#wcusage_field_registration_custom_options_' + idx).val() || '';
      var required_this = $('#wcusage_field_registration_custom_required_' + idx).is(':checked') ? 1 : 0;

      $.ajax({
        type: 'POST',
        url: (typeof wcuRegSettings !== 'undefined' ? wcuRegSettings.ajaxurl : ajaxurl),
        data: {
          action: 'wcusage_update_custom_fields',
          _ajax_nonce: (typeof wcuRegSettings !== 'undefined' ? wcuRegSettings.nonce : ''),
          label: label,
          type: type,
          options: options,
          required: required,
          label_this: label_this,
          type_this: type_this,
          options_this: options_this,
          required_this: required_this,
          current: idx,
          before: before
        },
        success: function(){
          $('#wcusage_field_registration_custom_label_' + before).val(label_this);
          $('#wcusage_field_registration_custom_type_' + before + ' option[value="' + type_this + '"]').prop('selected', true).change();
          $('#wcusage_field_registration_custom_options_' + before).val(options_this);
          $('#wcusage_field_registration_custom_required_' + before).prop('checked', !!required_this);

          $('#wcusage_field_registration_custom_label_' + idx).val(label);
          $('#wcusage_field_registration_custom_type_' + idx + ' option[value="' + type + '"]').prop('selected', true).change();
          $('#wcusage_field_registration_custom_options_' + idx).val(options);
          $('#wcusage_field_registration_custom_required_' + idx).prop('checked', !!required);
        }
      });
    });

    // Add New Field
    $('#wcu-add-custom-field').on('click', function(){
      var current = parseInt($('#wcusage_field_registration_custom_fields').val() || '0', 10);
      var next = current + 1;
      var $list = $('#wcu-registration-custom-fields');
      var $lastBlock = $list.find('> div[class*="registration_custom_"]').last();
      if (!$lastBlock.length) return;
      var $clone = $lastBlock.clone(true);
      $clone = reindexClone($clone, next);

      // ensure spacing: add block + spacer
      $list.append($clone);
      $list.append('<br/><br/>');

      bindType(next);
      toggleField(next);
      updateCount(next);
    });

    // Remove Last Field
  $('#wcu-remove-last-custom-field').on('click', function(){
      var current = parseInt($('#wcusage_field_registration_custom_fields').val() || '0', 10);
      if (current <= 0) return;
      var $list = $('#wcu-registration-custom-fields');
      // Remove trailing <br> spacers after the last block (typically two)
      var $lastBlock = $list.find('> div[class*="registration_custom_"]').last();
      // remove all trailing br's at the end
      var $lastChild = $list.children().last();
      while ($lastChild.is('br')) {
        $lastChild.remove();
        $lastChild = $list.children().last();
      }
      if ($lastBlock.length) $lastBlock.remove();
      var next = current - 1;
      updateCount(next);
    });
  });
})(jQuery);
