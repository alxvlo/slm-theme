(function ($) {
  function parseIds(value) {
    return String(value || '')
      .split(',')
      .map(function (v) {
        return Number(String(v).trim());
      })
      .filter(function (n) {
        return Number.isFinite(n) && n > 0;
      });
  }

  function uniqueIds(ids) {
    return Array.from(
      new Set(
        (ids || [])
          .map(function (n) {
            return Number(n);
          })
          .filter(function (n) {
            return Number.isFinite(n) && n > 0;
          })
      )
    );
  }

  function setIds($input, ids) {
    var normalized = uniqueIds(ids);
    $input.val(normalized.join(','));
    return normalized;
  }

  function esc(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function thumbHtmlFromModel(model) {
    if (!model || !model.attributes) return '';
    var attrs = model.attributes;
    var sizes = attrs.sizes || {};
    var thumb = sizes.thumbnail || sizes.medium || sizes.full || null;
    var url = thumb && thumb.url ? thumb.url : attrs.url || '';
    if (!url) return '';
    var alt = attrs.alt || attrs.title || '';
    return '<img class="order-delivery-thumb__image" src="' + esc(url) + '" alt="' + esc(alt) + '">';
  }

  function fallbackThumbHtml(id) {
    return '<div class="order-delivery-thumb__fallback">Image ' + esc(String(id)) + '</div>';
  }

  function renderThumbs($list, ids, thumbHtmlById) {
    $list.empty();
    ids.forEach(function (id) {
      var html = thumbHtmlById[id] || fallbackThumbHtml(id);
      var $li = $(
        '<li class="slm-delivery-thumb order-delivery-thumb" data-id="' + esc(String(id)) + '">' +
          '<span class="order-delivery-thumb__handle">' + html + '</span>' +
          '<button type="button" class="button-link-delete slm-delivery-thumb-remove order-delivery-thumb__remove">&times;</button>' +
        '</li>'
      );
      $list.append($li);
    });
  }

  function collectIdsFromDom($list) {
    return $list
      .find('.slm-delivery-thumb')
      .toArray()
      .map(function (el) {
        return Number($(el).attr('data-id'));
      })
      .filter(function (id) {
        return Number.isFinite(id) && id > 0;
      });
  }

  function initPicker($root) {
    var $idsInput = $root.find('[data-delivery-image-ids]');
    var $list = $root.find('[data-delivery-image-list]');
    var $addBtn = $root.find('[data-delivery-image-add]');
    var $clearBtn = $root.find('[data-delivery-image-clear]');
    if (!$idsInput.length || !$list.length || !$addBtn.length || !$clearBtn.length) {
      return;
    }

    $list.sortable({
      items: '.slm-delivery-thumb',
      tolerance: 'pointer',
      update: function () {
        setIds($idsInput, collectIdsFromDom($list));
      }
    });

    $list.on('click', '.slm-delivery-thumb-remove', function () {
      $(this).closest('.slm-delivery-thumb').remove();
      setIds($idsInput, collectIdsFromDom($list));
    });

    $clearBtn.on('click', function () {
      setIds($idsInput, []);
      $list.empty();
    });

    var frame = null;
    $addBtn.on('click', function () {
      if (!frame) {
        frame = wp.media({
          title: 'Select Delivery Images',
          button: { text: 'Use selected images' },
          library: { type: 'image' },
          multiple: true
        });

        frame.on('open', function () {
          var ids = parseIds($idsInput.val());
          var selection = frame.state().get('selection');
          selection.reset();
          ids.forEach(function (id) {
            var attachment = wp.media.attachment(id);
            if (!attachment) return;
            attachment.fetch();
            selection.add(attachment);
          });
        });

        frame.on('select', function () {
          var selection = frame.state().get('selection');
          var models = selection ? selection.toArray() : [];
          var ids = models
            .map(function (m) { return m && m.id; })
            .filter(function (id) { return Number.isFinite(id) && id > 0; });

          var normalized = setIds($idsInput, ids);
          var thumbHtmlById = {};
          models.forEach(function (m) {
            var id = m && m.id;
            if (!id) return;
            var html = thumbHtmlFromModel(m);
            if (html) thumbHtmlById[id] = html;
          });
          renderThumbs($list, normalized, thumbHtmlById);
        });
      }

      frame.open();
    });
  }

  $(function () {
    if (typeof wp === 'undefined' || !wp.media) return;
    $('[data-order-delivery-picker]').each(function () {
      initPicker($(this));
    });
  });
})(jQuery);
