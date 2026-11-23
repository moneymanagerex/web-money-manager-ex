import { state,$,esc,fmtDate,indexBy } from "./core.mjs";
import { renderMiniOverview } from "./summary.mjs?v=20261123-investments-assets";

function renderAssets() {
  const currencies=indexBy(state.currencies,"CURRENCYID");
  const rows=[...state.assets].sort((a,b)=>String(a.ASSETNAME??"").localeCompare(String(b.ASSETNAME??"")));
  const open=rows.filter(asset=>asset.ASSETSTATUS==="Open");
  renderMiniOverview("assetsMiniOverview",[
    {label:"Assets",value:rows.length},
    {label:"Open",value:open.length},
    {label:"Closed",value:rows.length-open.length}
  ]);
  $("assetsBody").innerHTML=rows.length? rows.map(asset=>{
    const currency=Number(asset.CURRENCYID)>0? currencies.get(String(asset.CURRENCYID)):null;
    return `<tr><td><strong>${esc(asset.ASSETNAME)}</strong></td><td>${esc(asset.ASSETTYPE)}</td><td>${esc(asset.ASSETSTATUS)}</td><td>${esc(fmtDate(asset.STARTDATE))}</td><td>${esc(currency?.CURRENCY_SYMBOL||currency?.CURRENCYNAME||"Base currency")}</td><td class="num amount-cell">${esc(asset.VALUE)}</td><td>${esc(asset.VALUECHANGE)} · ${esc(asset.VALUECHANGEMODE)} · ${esc(asset.VALUECHANGERATE)}</td><td>${esc(asset.NOTES)}</td></tr>`;
  }).join(""):'<tr><td colspan="8" class="muted">No assets in this PocketBase dataset.</td></tr>';
}
export { renderAssets };
