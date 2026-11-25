import { state,$,esc,fmtDate,indexBy } from "./core.mjs";
import { transactionAmountParts } from "./balances.mjs?v=20261123-investments-assets";
import { renderMiniOverview } from "./summary.mjs?v=20261123-investments-assets";
import { baseCurrencyId } from "./currencies.mjs?v=20261123-investments-assets";

function toBase(value,currency) {
  if(!Number.isFinite(value)||!currency)
    return NaN;
  const baseId=baseCurrencyId(state.baseCurrencyInfo);
  if(!baseId||Number(currency.CURRENCYID)===baseId)
    return value;
  const rate=Number(currency.BASECONVRATE??0);
  return rate>0? value/rate:NaN;
}

function assetActivity(asset,transactions,accounts,payees,categories,currencies) {
  const links=state.transactionLinks.filter(link=>String(link.LINKTYPE).toLowerCase()==="asset"&&String(link.LINKRECORDID)===String(asset.ASSETID));
  const rows=links.map(link=>transactions.get(String(link.CHECKINGACCOUNTID))).filter(Boolean)
    .sort((a,b)=>String(b.TRANSDATE??"").localeCompare(String(a.TRANSDATE??""))||Number(b.TRANSID)-Number(a.TRANSID));
  if(!rows.length)
    return '<span class="muted">No linked transactions</span>';
  const items=rows.slice(0,8).map(transaction=>{
    const account=accounts.get(String(transaction.ACCOUNTID)),currency=currencies.get(String(account?.CURRENCYID));
    const payee=transaction.TRANSCODE==="Transfer"
      ? `> ${accounts.get(String(transaction.TOACCOUNTID))?.ACCOUNTNAME??transaction.TOACCOUNTID}`
      : payees.get(String(transaction.PAYEEID))?.PAYEENAME??"";
    const category=categories.get(String(transaction.CATEGID))?.CATEGNAME;
    const amount=transactionAmountParts(transaction,String(transaction.ACCOUNTID))[0];
    const status=transaction.DELETEDTIME?"Deleted":transaction.STATUS==="V"?"Void":transaction.STATUS==="R"?"Reconciled":"";
    return `<li><time>${esc(fmtDate(transaction.TRANSDATE))}</time><span class="trade-action">${esc(transaction.TRANSCODE)}${status?` · ${esc(status)}`:""}</span><span>${esc(payee||category||account?.ACCOUNTNAME||"Transaction")}</span><span class="money-${amount.tone}">${esc(amount.text)} ${esc(currency?.CURRENCY_SYMBOL||currency?.CURRENCYNAME||"")}</span></li>`;
  });
  return `<details class="investment-details"><summary>Transactions · ${rows.length}</summary><ul class="asset-transaction-list">${items.join("")}</ul>${rows.length>8?`<small>Latest 8 of ${rows.length} linked transactions shown.</small>`:""}</details>`;
}

function renderAssets() {
  const currencies=indexBy(state.currencies,"CURRENCYID");
  const transactions=indexBy(state.transactions,"TRANSID"),accounts=indexBy(state.accounts,"ACCOUNTID"),payees=indexBy(state.payees,"PAYEEID"),categories=indexBy(state.categories,"CATEGID");
  const rows=[...state.assets].sort((a,b)=>String(a.ASSETNAME??"").localeCompare(String(b.ASSETNAME??"")));
  const open=rows.filter(asset=>asset.ASSETSTATUS==="Open");
  const activityCount=rows.reduce((total,asset)=>total+state.transactionLinks.filter(link=>String(link.LINKTYPE).toLowerCase()==="asset"&&String(link.LINKRECORDID)===String(asset.ASSETID)).length,0);
  const baseTotal=rows.reduce((sum,asset)=>{
    const currency=Number(asset.CURRENCYID)>0? currencies.get(String(asset.CURRENCYID)):null;
    const converted=toBase(Number(asset.VALUE),currency);
    return sum+(Number.isFinite(converted)? converted:0);
  },0);
  const baseCur=currencies.get(String(baseCurrencyId(state.baseCurrencyInfo)));
  renderMiniOverview("assetsMiniOverview",[
    {label:"Assets",value:rows.length},
    {label:"Open",value:open.length},
    {label:"Closed",value:rows.length-open.length},
    {label:"Linked transactions",value:activityCount},
    {label:"Total (base)",value:Number.isFinite(baseTotal)? `${baseCur?.CURRENCY_SYMBOL||""} ${baseTotal.toLocaleString(undefined,{ minimumFractionDigits:2, maximumFractionDigits:2 })}`:"—"}
  ]);
  $("assetsBody").innerHTML=rows.length? rows.map(asset=>{
    const currency=Number(asset.CURRENCYID)>0? currencies.get(String(asset.CURRENCYID)):null;
    return `<tr><td><strong>${esc(asset.ASSETNAME)}</strong></td><td>${esc(asset.ASSETTYPE)}</td><td>${esc(asset.ASSETSTATUS)}</td><td>${esc(fmtDate(asset.STARTDATE))}</td><td>${esc(currency?.CURRENCY_SYMBOL||currency?.CURRENCYNAME||"Base currency")}</td><td class="num amount-cell">${esc(asset.VALUE)}</td><td>${esc(asset.VALUECHANGE)} · ${esc(asset.VALUECHANGEMODE)} · ${esc(asset.VALUECHANGERATE)}</td><td>${esc(asset.NOTES)}</td><td>${assetActivity(asset,transactions,accounts,payees,categories,currencies)}</td></tr>`;
  }).join(""):'<tr><td colspan="9" class="muted">No assets in this PocketBase dataset.</td></tr>';
}
export { renderAssets };
