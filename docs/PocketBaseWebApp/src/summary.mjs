import { esc } from "./core.mjs";

function renderMiniOverview(id,items,note="") {
  const target=document.getElementById(id);
  if(!target)
    return;
  target.innerHTML=items.map(item => `<div class="mini-stat"><span class="mini-label">${esc(item.label)}</span><strong>${esc(item.value)}</strong></div>`).join("")+(note? `<span class="mini-note">${esc(note)}</span>`:"");
}

export { renderMiniOverview };
