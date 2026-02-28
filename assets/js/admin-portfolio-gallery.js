(function ($) {
  function parseIds(value) {
    return String(value || "")
      .split(",")
      .map((v) => Number(String(v).trim()))
      .filter((n) => Number.isFinite(n) && n > 0);
  }

  function uniqueIds(ids) {
    return Array.from(
      new Set(ids.map((n) => Number(n)).filter((n) => Number.isFinite(n) && n > 0))
    );
  }

  function setIds($input, ids) {
    const uniq = uniqueIds(ids);
    $input.val(uniq.join(","));
    return uniq;
  }

  function collectIdsFromDom($list) {
    return $list
      .find(".slm-portfolio-thumb")
      .toArray()
      .map((el) => Number($(el).attr("data-id")))
      .filter((n) => Number.isFinite(n) && n > 0);
  }

  function esc(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function fallbackThumbHtml(id, type) {
    if (type === "video") {
      return `
        <div style="width:84px; height:84px; border-radius:8px; background:#12253f; color:#fff; display:flex; align-items:center; justify-content:center; text-align:center; padding:8px; font-size:10px; line-height:1.2;">
          <div>
            <span style="display:block; font-size:18px; margin-bottom:4px;">&#9654;</span>
            <span style="display:block; max-width:68px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Video ${id}</span>
          </div>
        </div>
      `;
    }

    return `<div style="width:84px; height:84px; border-radius:8px; background:#eef3fb; display:grid; place-items:center; color:#173963; font-size:11px;">Image ${id}</div>`;
  }

  function thumbHtmlFromModel(model, type) {
    const attrs = (model && model.attributes) || {};
    if (type === "video") {
      const icon = attrs.icon || "";
      const title = attrs.title || attrs.filename || "Video";
      const iconHtml = icon
        ? `<img src="${esc(icon)}" alt="" style="width:28px; height:28px; display:block; margin:0 auto 6px; opacity:.9;">`
        : `<span style="display:block; font-size:18px; margin-bottom:4px;">&#9654;</span>`;

      return `
        <div style="width:84px; height:84px; border-radius:8px; background:#12253f; color:#fff; display:flex; align-items:center; justify-content:center; text-align:center; padding:8px; font-size:10px; line-height:1.2;">
          <div>
            ${iconHtml}
            <span style="display:block; max-width:68px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${esc(title)}</span>
          </div>
        </div>
      `;
    }

    const sizes = attrs.sizes || {};
    const thumb = sizes.thumbnail || sizes.medium || sizes.full || null;
    const url = thumb ? thumb.url : attrs.url || "";
    const alt = attrs.alt || "";
    if (!url) return "";
    return `<img src="${esc(url)}" alt="${esc(alt)}" style="width:84px; height:84px; object-fit:cover; display:block; border-radius:8px;">`;
  }

  function renderThumbs($list, ids, byIdToThumbHtml, type) {
    $list.empty();

    ids.forEach((id) => {
      const thumbHtml = byIdToThumbHtml[id] || fallbackThumbHtml(id, type);
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

  function initMediaPicker(config) {
    const $idsInput = $(config.idsSelector);
    if (!$idsInput.length) return;

    const $list = $(config.listSelector);
    const $add = $(config.addSelector);
    const $clear = $(config.clearSelector);
    if (!$list.length || !$add.length || !$clear.length) return;

    $list.sortable({
      items: ".slm-portfolio-thumb",
      tolerance: "pointer",
      update: function () {
        setIds($idsInput, collectIdsFromDom($list));
      },
    });

    $list.on("click", ".slm-portfolio-thumb-remove", function () {
      $(this).closest(".slm-portfolio-thumb").remove();
      setIds($idsInput, collectIdsFromDom($list));
    });

    $clear.on("click", function () {
      setIds($idsInput, []);
      $list.empty();
    });

    let frame = null;
    $add.on("click", function () {
      if (!frame) {
        frame = wp.media({
          title: config.type === "video" ? "Select Portfolio Videos" : "Select Portfolio Images",
          button: {
            text: config.type === "video" ? "Use these videos" : "Use these images",
          },
          library: { type: config.type },
          multiple: true,
        });

        frame.on("open", function () {
          const currentIds = parseIds($idsInput.val());
          const selection = frame.state().get("selection");
          selection.reset();
          currentIds.forEach((id) => {
            const attachment = wp.media.attachment(id);
            if (attachment) {
              attachment.fetch();
              selection.add(attachment);
            }
          });
        });

        frame.on("select", function () {
          const selection = frame.state().get("selection");
          const models = selection ? selection.toArray() : [];

          const selectedIds = models
            .map((m) => m && m.id)
            .filter((id) => Number.isFinite(id) && id > 0);
          const finalIds = setIds($idsInput, selectedIds);

          const byIdToThumbHtml = {};
          models.forEach((m) => {
            const id = m && m.id;
            if (!id) return;
            const html = thumbHtmlFromModel(m, config.type);
            if (html) byIdToThumbHtml[id] = html;
          });

          renderThumbs($list, finalIds, byIdToThumbHtml, config.type);
        });
      }

      frame.open();
    });
  }

  $(function () {
    initMediaPicker({
      type: "image",
      idsSelector: "#slm-portfolio-gallery-ids",
      listSelector: "#slm-portfolio-gallery-list",
      addSelector: "#slm-portfolio-gallery-add",
      clearSelector: "#slm-portfolio-gallery-clear",
    });

    initMediaPicker({
      type: "video",
      idsSelector: "#slm-portfolio-video-ids",
      listSelector: "#slm-portfolio-video-list",
      addSelector: "#slm-portfolio-video-add",
      clearSelector: "#slm-portfolio-video-clear",
    });
  });
})(jQuery);
