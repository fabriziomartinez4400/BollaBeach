jQuery(function($){
  var $search = $('#wcu-settings-search');
  if(!$search.length) return;
  var $wrap = $('#wcu-settings-search-right');
  var $resultsWrap = $('#wcu-settings-search-results');
  var $resultsList = $('#wcu-settings-search-results ul');
  var $empty = $('#wcu-settings-search-empty');

  // Close results on outside click
  $(document).on('click', function(e){
    if($(e.target).closest('#wcu-settings-search-right').length === 0){
      $resultsWrap.hide();
    }
  });

  // Map row class -> tab info
  var tabMap = {};
  $('.nav-tab[id^="tab-"]').each(function(){
    var $tab = $(this);
    var tabId = $tab.attr('id');
    var rowKey = tabId.replace('tab-','').replace(/-/g,'_');
    var rowClass = 'wcusage_row_' + rowKey;
    tabMap[rowClass] = { id: '#'+tabId, $el: $tab, title: $.trim($tab.text()) };
  });

  // Unique ID generator for headings to avoid collisions across searches
  var wcuHeadingAutoSeq = 0;
  function ensureHeadingId($h){
    var hid = $h.attr('id');
    if(hid){
      // If this ID is duplicated or points to a different element, reassign a unique one
      var isThis = document.getElementById(hid) === $h[0];
      var dupCount = jQuery('[id="'+hid.replace(/"/g,'\\"')+'"]').length; // count elements with same id
      if(isThis && dupCount === 1){
        return hid;
      }
    }
    var candidate;
    do {
      candidate = 'wcu_heading_auto_' + (wcuHeadingAutoSeq++);
    } while(document.getElementById(candidate));
    $h.attr('id', candidate);
    return candidate;
  }

  // Helper: open any collapsed Show/Hide ancestors for a given element
  function openShowhideAncestors($elem){
    // Safety check: if this is a heading element, don't open any toggles
    if($elem.is('h1, h2, h3, h4, h5, h6')){
      return;
    }
    
    // Only open ancestors that actually contain this element as a child
    var ancestors = $elem.parents().filter(function(){
      var id = this.id;
      if(!id) return false;
      var btnSelector = '#wcu_show_' + id.replace(/^wcu_/, '');
      return jQuery(this).is(':hidden') && jQuery(btnSelector).length > 0;
    });
    
    jQuery(ancestors.get().reverse()).each(function(){
      var id = this.id;
      var btnSelector = '#wcu_show_' + id.replace(/^wcu_/, '');
      var $btn = jQuery(btnSelector);
      if($btn.length && jQuery(this).is(':hidden')){
        $btn.trigger('click');
      } else if(jQuery(this).is(':hidden')) {
        // Try alternative button patterns
        var altBtnSelector = '#wcu_show_' + id;
        var $altBtn = jQuery(altBtnSelector);
        if($altBtn.length){
          $altBtn.trigger('click');
        } else {
          // If no button found but element is hidden, just show it
          jQuery(this).show();
        }
      }
    });
  }

  function render(matches){
    $resultsList.empty();
    if(!matches.length){
      $resultsWrap.hide();
      $empty.show();
      $empty.css('display', 'block');
      return;
    }
    $empty.hide();
    $empty.css('display', 'none');
    $.each(matches, function(i, m){
      var tabTitle = m.tab ? m.tab.title : 'Other';
      var $li = $('<li/>');
      var typeLabel = 'Setting';
      if(m.type === 'heading') typeLabel = 'Section';
      if(m.type === 'tab') typeLabel = 'Tab';
      var $info = $('<div/>').append(
        $('<div/>', { 'class': 'wcu-search-item-label', text: m.label })
      ).append(
        $('<div/>', { 'class': 'wcu-search-item-meta', text: tabTitle + ' • ' + typeLabel + (m.type !== 'heading' && m.type !== 'tab' ? ' (#' + m.fieldId + ')' : '') })
      );
      var $btn = $('<button/>', { 'class': 'button button-primary wcu-search-jump', text: 'Go to settings' });
      $btn.on('click', function(ev){
        ev.preventDefault();
        var $target = $('#'+m.pid);
        if($target.length){
          var $row = $target.closest('.wcusage_row');
          // Get the ID of the closest row, if any
          var rowId = $row.length ? $row.attr('id') : null;

          // Detect tab from row class and auto-click tab
          var tabId = null;
          if($row.length){
            var classes = ($row.attr('class')||'').split(/\s+/);
            for(var i=0;i<classes.length;i++){
              var match = classes[i].match(/^wcusage_row_([a-z0-9_-]+)$/);
              if(match){
                tabId = '#tab-' + match[1].replace(/_/g,'-');
                break;
              }
            }
          }
          if(tabId && $(tabId).length){
            $(tabId).trigger('click');
            $('.wcu-sidebar-link').removeClass('active');
            // Add .active to the item with ID matching the tab (without #tab-)
            $(tabId).addClass('active');
          }
          $row.show();

          var $scrollTarget = $target;

          if(m.type === 'heading' || m.type === 'tab'){
            $scrollTarget = $target;
          } else {
            openShowhideAncestors($target);
            var $hiddenParent = $target.closest('div[style*="display:none"], div[style*="display: none"]');
            if($hiddenParent.length){
              var parentId = $hiddenParent.attr('id');
              if(parentId){
                var $controlBtn = jQuery('#wcu_show_' + parentId.replace(/^wcu_/, ''), 
                                        '#wcu_show_' + parentId,
                                        'button[onclick*="wcusage_toggle_settings(\''+parentId+'\')"]',
                                        'button[onclick*="' + parentId + '"]').first();
                if($controlBtn.length){
                  $controlBtn.trigger('click');
                }
              }
            }
            var $tabSettingsParent = $target.closest('div[id^="wcu_tab_settings_"]');
            if($tabSettingsParent.length && $tabSettingsParent.is(':hidden')){
              var tabSettingsId = $tabSettingsParent.attr('id');
              var $tabToggleBtn = jQuery('button[onclick*="wcusage_toggle_settings(\''+tabSettingsId+'\')"]').first();
              if($tabToggleBtn.length){
                $tabToggleBtn.trigger('click');
              }
            }
            var $tabItemParent = $target.closest('.wcusage-tab-item');
            if($tabItemParent.length){
              var $hiddenSection = $tabItemParent.find('div[id^="wcu_tab_settings_"][style*="display:none"], div[id^="wcu_tab_settings_"][style*="display: none"]').first();
              if($hiddenSection.length && $target.closest($hiddenSection).length){
                var sectionId = $hiddenSection.attr('id');
                var $sectionToggleBtn = $tabItemParent.find('button[onclick*="wcusage_toggle_settings(\''+sectionId+'\')"]').first();
                if($sectionToggleBtn.length){
                  $sectionToggleBtn.trigger('click');
                }
              }
            }
            var $section = $target.closest('div[id], span[class*="wcu-field-section"]');
            while($section.length && !$target.is(':visible')){
              var sectionId = $section.attr('id');
              var sectionClass = $section.attr('class');
              if(sectionId){
                var $btn = jQuery('#wcu_show_' + sectionId.replace(/^wcu_/, ''), 
                                 '#wcu_show_' + sectionId,
                                 'button[onclick*="wcusage_toggle_settings(\''+sectionId+'\')"]').first();
                if($btn.length && $section.is(':hidden')){
                  $btn.trigger('click');
                  break;
                }
              }
              if(sectionClass && sectionClass.indexOf('wcu-field-section-') !== -1){
                var match = sectionClass.match(/wcu-field-section-([a-z0-9_-]+)/);
                if(match){
                  var key = match[1];
                  var $enableBtn = jQuery('#wcu_show_' + key, 'button[onclick*="' + key + '"]').first();
                  if($enableBtn.length){
                    $enableBtn.trigger('click');
                    break;
                  }
                }
              }
              $section = $section.parent().closest('div[id], span[class*="wcu-field-section"]');
            }
            if(!$target.is(':visible')){
              function findConditionalController($elem){
                var $gatingInput = null;
                var $gatingP = null;
                var $r = $elem.closest('.wcusage_row');
                $elem.parents().each(function(){
                  var cls = this.className || '';
                  var match = cls.match(/wcu-field-section-([a-z0-9_-]+)/);
                  if(match){
                    var key = match[1];
                    var sel = '.wcusage_field_' + key + '_enable';
                    var $ctrl = $r.find(sel).first();
                    if($ctrl.length){
                      $gatingInput = $ctrl;
                      $gatingP = $ctrl.closest('p[id$="_p"]');
                      return false;
                    }
                  }
                });
                if($gatingInput){ return { $input: $gatingInput, $p: $gatingP }; }
                return null;
              }
              var ctrl = findConditionalController($target);
              if(ctrl){
                $scrollTarget = (ctrl.$p && ctrl.$p.length) ? ctrl.$p : ctrl.$input;
              }
            }
          }
          var offset = $scrollTarget.offset().top - 80;
          jQuery('html, body').animate({ scrollTop: offset }, 250);
          $scrollTarget.addClass('wcu-highlight-jump');
          setTimeout(function(){ $scrollTarget.removeClass('wcu-highlight-jump'); }, 1700);
          $resultsWrap.hide();
          $search.blur();
        }
      });
      $li.append($info).append($btn);
      $resultsList.append($li);
    });
    $resultsWrap.show();
  }

  // Debounced search
  var timer = null;
  function normalizeLabelKey(str){
  if(!str) return '';
  var s = String(str);
  // lower-case and normalize unicode (remove diacritics)
  try { s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, ''); } catch(e) {}
  s = s.toLowerCase();
  // normalize smart quotes to straight, then strip quotes
  s = s.replace(/[\u2018\u2019]/g, "'").replace(/[\u201c\u201d]/g, '"');
  // replace all non-alphanumeric with spaces (drops punctuation incl. quotes/colons/dots)
  s = s.replace(/[^a-z0-9]+/g, ' ');
  // collapse and trim
  s = s.replace(/\s+/g,' ').trim();
  return s;
  }
  function doSearch(){
    var q = $.trim($search.val() || '').toLowerCase();
    $resultsList.empty();
    $resultsWrap.hide();
    $empty.hide();
    if(!q){ return; }

    // Force a small delay to ensure any dynamic content has loaded
    setTimeout(function(){
      performSearch(q);
    }, 50);
  }

  function performSearch(q){
    var matches = [];
    $('.wcusage_row p[id$="_p"]').each(function(){
      var $p = $(this);
      // Do NOT skip hidden rows; allow searching in all tabs
      var pid = $p.attr('id');
      if(!pid){ return; }
      var labelText = '';
      var $strong = $p.children('strong').first();
      if($strong.length){ labelText = $.trim($strong.text()); }
      if(!labelText){
        var $lbl = $p.find('label').first();
        if($lbl.length){ labelText = $.trim($lbl.text()); }
      }
      if(!labelText){ labelText = $.trim($p.text()); }
      // Also search in description text (italic text or text after the main label)
      var fullText = labelText;
      var $italic = $p.find('i');
      if($italic.length){
        $italic.each(function(){
          var italicText = $.trim($(this).text());
          if(italicText && fullText.toLowerCase().indexOf(italicText.toLowerCase()) === -1){
            fullText += ' ' + italicText;
          }
        });
      }
      var hay = fullText.toLowerCase();
      if(hay.indexOf(q) === -1){ return; }
      var $row = $p.closest('.wcusage_row');
      var rowClass = '';
      if($row.length){
        var classes = ($row.attr('class')||'').split(/\s+/);
        for(var i=0;i<classes.length;i++){
          if(classes[i].indexOf('wcusage_row_') === 0){ rowClass = classes[i]; break; }
        }
      }
      var tabInfo = tabMap[rowClass] || null;
      var fieldId = ($p.find('input,select,textarea').first().attr('id')) || pid.replace(/_p$/,'');
      matches.push({ label: labelText, pid: pid, rowClass: rowClass, tab: tabInfo, fieldId: fieldId });
    });

    // Also index section headings (h1, h2, h3) and link to their sections
    $('.wcusage_row h1, .wcusage_row h2, .wcusage_row h3').each(function(){
      var $h = $(this);
      // Do NOT skip hidden rows; allow searching in all tabs
      var textOnly = $.trim($h.clone().children().remove().end().text());
      if(!textOnly) return;
      var hay = textOnly.toLowerCase();
      if(hay.indexOf(q) === -1) return;
      var $row = $h.closest('.wcusage_row');
      var rowClass = '';
      if($row.length){
        var classes = ($row.attr('class')||'').split(/\s+/);
        for(var i=0;i<classes.length;i++){
          if(classes[i].indexOf('wcusage_row_') === 0){ rowClass = classes[i]; break; }
        }
      }
      var tabInfo = tabMap[rowClass] || null;
      // Ensure heading has an id for scrolling (unique across searches)
      var hid = ensureHeadingId($h);
      // Prefer deriving section from the Show/Hide button within the heading
      var sectionId = '';
      var $btn = $h.find('.wcu-showhide-button[id^="wcu_show_"]').first();
      if($btn.length){
        var btnId = $btn.attr('id');
        sectionId = 'wcu_' + btnId.replace(/^wcu_show_/, '');
      }
      // Fallback: find the next section container after the heading (within reasonable distance)
      if(!sectionId){
        var $section = $h.nextAll('div[id^="wcu_section_"]').first();
        if($section.length){
          // Make sure the section is reasonably close (within 3 siblings)
          var distance = $h.nextAll().index($section);
          if(distance >= 0 && distance <= 2){
            sectionId = $section.attr('id');
          }
        }
      }
      // Additional fallback: look for show/hide buttons after the heading (within next 2 elements)
      if(!sectionId){
        var $nextBtn = $h.nextAll().slice(0, 2).find('.wcu-showhide-button[id^="wcu_show_"]').first();
        if($nextBtn.length){
          var btnId = $nextBtn.attr('id');
          sectionId = 'wcu_' + btnId.replace(/^wcu_show_/, '');
        }
      }
      // Look for dynamic toggle buttons (onclick handlers) - also within next 2 elements
      if(!sectionId){
        var $toggleBtn = $h.nextAll().slice(0, 2).find('button[onclick*="wcusage_toggle_settings"]').first();
        if($toggleBtn.length){
          var onclick = $toggleBtn.attr('onclick') || '';
          var match = onclick.match(/wcusage_toggle_settings\('([^']+)'\)/);
          if(match){
            sectionId = match[1];
          }
        }
      }
      matches.push({ type: 'heading', label: textOnly, pid: hid, rowClass: rowClass, tab: tabInfo, fieldId: hid, sectionId: sectionId });
    });

    // Also search within dynamic tab headers and labels
    $('.wcusage-tab-item').each(function(){
      var $tabItem = $(this);
      // Do NOT skip hidden rows; allow searching in all tabs
      var tabId = $tabItem.attr('id');
      if(!tabId) return;
      var labelText = $.trim($tabItem.find('strong').first().text());
      if(!labelText) return;
      var hay = labelText.toLowerCase();
      if(hay.indexOf(q) === -1) return;
      var $row = $tabItem.closest('.wcusage_row');
      var rowClass = '';
      if($row.length){
        var classes = ($row.attr('class')||'').split(/\s+/);
        for(var i=0;i<classes.length;i++){
          if(classes[i].indexOf('wcusage_row_') === 0){ rowClass = classes[i]; break; }
        }
      }
      var tabInfo = tabMap[rowClass] || null;
      // Look for the settings section ID
      var sectionId = '';
      var $toggleBtn = $tabItem.find('button[onclick*="wcusage_toggle_settings"]').first();
      if($toggleBtn.length){
        var onclick = $toggleBtn.attr('onclick') || '';
        var match = onclick.match(/wcusage_toggle_settings\('([^']+)'\)/);
        if(match){
          sectionId = match[1];
        }
      }
      matches.push({ 
        type: 'tab', 
        label: labelText, 
        pid: tabId, 
        rowClass: rowClass, 
        tab: tabInfo, 
        fieldId: tabId, 
        sectionId: sectionId 
      });
    });

    // Deduplicate by normalized label key WITHIN THE SAME TAB, keeping the first occurrence
    var seen = {};
    var deduped = [];
    for(var k=0;k<matches.length;k++){
      var m = matches[k];
      var key = normalizeLabelKey(m.label) + '::' + (m.tab && m.tab.id ? m.tab.id : 'no-tab');
      if(!seen[key]){
        seen[key] = 1;
        deduped.push(m);
      } else {
        seen[key]++;
      }
    }

    deduped.sort(function(a,b){
      var at = (a.type === 'heading') ? 0 : (a.type === 'tab') ? 1 : 2;
      var bt = (b.type === 'heading') ? 0 : (b.type === 'tab') ? 1 : 2;
      if(at !== bt) return at - bt;
      return (a.label||'').localeCompare(b.label||'');
    });
    render(deduped);
  }

  $search.on('input', function(){
    clearTimeout(timer);
    timer = setTimeout(doSearch, 200);
  });
});
