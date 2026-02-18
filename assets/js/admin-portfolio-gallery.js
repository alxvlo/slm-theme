(function ($) {
  function parseIds(value) {
    return String(value || '')
      .split(',')
      .map((v) => Number(String(v).trim()))
      .filter((n) => Number.isFinite(n) && n > 0);
  }

  function setIds(ids) {
    const uniq = Array.from(new Set(ids.map((n) => Number(n)).filter((n) => n > 0)));
    $('#slm-portfolio-gallery-ids').val(uniq.join(','));
    return uniq;
  }

  function renderThumbs(ids, byIdToThumbHtml) {
    const $list = $('#slm-portfolio-gallery-list');
    $list.empty();

    ids.forEach((id) => {
      const thumbHtml = byIdToThumbHtml[id];
      if (!thumbHtml) return;

      const $li = $(`
        <li class="slm-portfolio-thumb" data-id="${id}" style="list-style:none; position:relative; width:84px;">
          <span style="cursor:move; display:block; border:1px solid rgba(0,0,0,.12); border-radius:10px; padding:4px; background:#fff;">
            ${thumbHtml}
          </span>
          <button type="button" class="button-link-delete slm-portfolio-thumb-remove" style="position:absolute; top:-6px; right:-4px; background:#fff; border:1px solid rgba(0,0,0,.18); border-radius:999px; width:22px; height:22px; line-height:20px; text-align:center; text-decoration:none;">&times;</button>
        </li>
      `);
      $list.append($li);
    });
  }

  $(function () {
    const $idsInput = $('#slm-portfolio-gallery-ids');
    if (!$idsInput.length) return;

    const $list = $('#slm-portfolio-gallery-list');
    const $add = $('#slm-portfolio-gallery-add');
    const $clear = $('#slm-portfolio-gallery-clear');

    function collectIdsFromDom() {
      return $list
        .find('.slm-portfolio-thumb')
        .toArray()
        .map((el) => Number($(el).attr('data-id')))
        .filter((n) => Number.isFinite(n) && n > 0);
    }

    $list.sortable({
      items: '.slm-portfolio-thumb',
      tolerance: 'pointer',
      update: function () {
        setIds(collectIdsFromDom());
      },
    });

    $list.on('click', '.slm-portfolio-thumb-remove', function () {
      const $li = $(this).closest('.slm-portfolio-thumb');
      $li.remove();
      setIds(collectIdsFromDom());
    });

    $clear.on('click', function () {
      setIds([]);
      $list.empty();
    });

    let frame = null;
    $add.on('click', function () {
      const currentIds = parseIds($idsInput.val());

      if (!frame) {
        frame = wp.media({
          title: 'Select Portfolio Images',
          button: { text: 'Use these images' },
          library: { type: 'image' },
          multiple: true,
        });

        frame.on('open', function () {
          const selection = frame.state().get('selection');
          selection.reset();
          currentIds.forEach((id) => {
            const attachment = wp.media.attachment(id);
            if (attachment) {
              attachment.fetch();
              selection.add(attachment);
            }
          });
        });

        frame.on('select', function () {
          const selection = frame.state().get('selection');
          const models = selection ? selection.toArray() : [];

          const ids = models.map((m) => m && m.id).filter((id) => Number.isFinite(id) && id > 0);
          const finalIds = setIds(ids);

          const byIdToThumbHtml = {};
          models.forEach((m) => {
            const id = m && m.id;
            if (!id) return;
            const sizes = (m.attributes && m.attributes.sizes) || {};
            const thumb = sizes.thumbnail || sizes.medium || sizes.full || null;
            const url = thumb ? thumb.url : (m.attributes ? m.attributes.url : '');
            const alt = (m.attributes && m.attributes.alt) || '';
            if (!url) return;
            byIdToThumbHtml[id] = `<img src="${url}" alt="${alt}" style="width:84px; height:84px; object-fit:cover; display:block; border-radius:8px;" />`;
          });

          // Re-render using the best available thumbs from the selection.
          renderThumbs(finalIds, byIdToThumbHtml);
        });
      }

      frame.open();
    });
  });
})(jQuery);

