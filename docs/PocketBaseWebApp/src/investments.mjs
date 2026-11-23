import { state,$,esc,indexBy,fmtDate,setStatus } from "./core.mjs";
import { fetchRecords } from "./api.mjs?v=20261123-investments-assets";
import { renderMiniOverview } from "./summary.mjs?v=20261123-investments-assets";
import { localToday } from "./balances.mjs?v=20261123-investments-assets";

let historyRequest=null;
async function loadStockHistory(force=false) {
  if(historyRequest) {
    if(!force)
      return historyRequest;
    await historyRequest.catch(()=>{});
  }
  if(state.stockHistoryLoaded&&!force)
    return state.stockHistory;
  if(!state.stocks.length) {
    state.stockHistory=[];
    state.stockHistoryLoaded=true;
    renderInvestments();
    return state.stockHistory;
  }
  setStatus("investmentsStatus","Loading saved price history…");
  historyRequest=fetchRecords("STOCKHISTORY_V1","-DATE").then(rows=>{
    state.stockHistory=rows;
    state.stockHistoryLoaded=true;
    renderInvestments();
    return rows;
  }).catch(error=>{
    setStatus("investmentsStatus","Could not load saved price history. "+error.message,"error");
    throw error;
  }).finally(()=>{ historyRequest=null; });
  return historyRequest;
}
let positionCache=null;
const number=value => {
  const parsed=Number(value);
  return Number.isFinite(parsed)? parsed:0;
};
function formatQuantity(value) {
  return Number.isFinite(value)? value.toLocaleString(undefined,{ maximumFractionDigits:8 }):"—";
}
function formatMoney(value,currency) {
  if(!Number.isFinite(value))
    return "—";
  const scale=String(currency?.SCALE??"100");
  const places=/^10*$/.test(scale)? Math.min(scale.length-1,10):2;
  return value.toLocaleString(undefined,{ minimumFractionDigits:places,maximumFractionDigits:places });
}
function stockRows() {
  const inputs=[state.stocks,state.transactions,state.shareInfo,state.transactionLinks,state.accounts,state.currencies,state.stockHistory];
  if(positionCache&&inputs.every((value,index)=>positionCache.inputs[index]===value))
    return positionCache.rows;
  const transactions=indexBy(state.transactions,"TRANSID"),shares=indexBy(state.shareInfo,"CHECKINGACCOUNTID");
  const accounts=indexBy(state.accounts,"ACCOUNTID"),currencies=indexBy(state.currencies,"CURRENCYID");
  const historyBySymbol=new Map();
  for(const point of [...state.stockHistory].sort((a,b)=>String(b.DATE??"").localeCompare(String(a.DATE??"")))) {
    const symbol=String(point.SYMBOL??"");
    if(!historyBySymbol.has(symbol))
      historyBySymbol.set(symbol,[]);
    historyBySymbol.get(symbol).push(point);
  }
  const positions=state.stocks.map(stock => {
    const links=state.transactionLinks.filter(link=>String(link.LINKTYPE).toLowerCase()==="stock"&&String(link.LINKRECORDID)===String(stock.STOCKID));
    const trades=links.map(link=> {
      const trx=transactions.get(String(link.CHECKINGACCOUNTID));
      const share=shares.get(String(link.CHECKINGACCOUNTID));
      return trx? { trx,share }:null;
    }).filter(Boolean).sort((a,b)=>String(a.trx.TRANSDATE??"").localeCompare(String(b.trx.TRANSDATE??""))||number(a.trx.TRANSID)-number(b.trx.TRANSID));
    const validTrades=trades.filter(({trx,share})=>share&&!trx.DELETEDTIME&&trx.STATUS!=="V");
    let quantity,basis;
    if(links.length) {
      quantity=0; basis=0;
      for(const {share} of validTrades) {
        const sharesMoved=number(share.SHARENUMBER),price=number(share.SHAREPRICE),fee=number(share.SHARECOMMISSION);
        const average=quantity>0? basis/quantity:0;
        if(sharesMoved>0)
          basis+=sharesMoved*price+fee;
        else
          basis+=sharesMoved*average;
        quantity=Math.max(0,quantity+sharesMoved);
        if(basis<0)
          basis=0;
      }
    }
    else {
      quantity=number(stock.NUMSHARES);
      const storedBasis=stock.VALUE;
      basis=storedBasis!==null&&storedBasis!==undefined&&storedBasis!==""&&Number.isFinite(Number(storedBasis))? Number(storedBasis):quantity*number(stock.PURCHASEPRICE);
    }
    const account=accounts.get(String(stock.HELDAT)),currency=currencies.get(String(account?.CURRENCYID));
    const history=historyBySymbol.get(String(stock.SYMBOL??""))??[];
    const latestHistory=history.find(point=>String(point.DATE??"")<=localToday());
    const storedPrice=stock.CURRENTPRICE;
    const price=storedPrice!==null&&storedPrice!==undefined&&storedPrice!==""&&Number.isFinite(Number(storedPrice))&&Number(storedPrice)>=0? Number(storedPrice):Number(latestHistory?.VALUE);
    const priceAvailable=Number.isFinite(price)&&price>=0;
    const marketValue=priceAvailable? quantity*price:NaN;
    const avgCost=quantity>0? basis/quantity:NaN;
    const gain=priceAvailable? marketValue-basis:NaN;
    return { stock,account,currency,quantity,basis,avgCost,price,priceAvailable,marketValue,gain,trades,history,latestHistory,linked:links.length>0 };
  });
  positionCache={inputs,rows:positions};
  return positions;
}
function renderTradeActivity(position) {
  const rows=position.trades.map(({trx,share})=>{
    if(!share)
      return `<li class="trade-missing">${esc(fmtDate(trx.TRANSDATE))} · Missing share details (transaction ${esc(trx.TRANSID)})</li>`;
    const qty=number(share.SHARENUMBER),action=qty>0? "Buy":qty<0? "Sell":"Adjustment";
    const state=trx.DELETEDTIME? "Deleted":trx.STATUS==="V"? "Void":action;
    return `<li><time>${esc(fmtDate(trx.TRANSDATE))}</time><span class="trade-action">${esc(state)}</span><span>${esc(formatQuantity(qty))} × ${esc(formatMoney(number(share.SHAREPRICE),position.currency))}</span><span>Fee ${esc(formatMoney(number(share.SHARECOMMISSION),position.currency))}</span><span>Lot ${esc(share.SHARELOT||"—")}</span></li>`;
  });
  if(!rows.length)
    return "";
  return `<details class="investment-details"><summary>Trades · ${rows.length}</summary><ul class="trade-list">${rows.slice(-8).reverse().join("")}</ul>${rows.length>8? `<small>Latest 8 of ${rows.length} linked trades shown.</small>`:""}</details>`;
}
function renderPriceHistory(position) {
  if(!position.history.length)
    return "";
  const points=position.history.slice(0,5);
  return `<details class="investment-details"><summary>Prices · ${position.history.length}</summary><ul class="price-list">${points.map(point=>`<li><time>${esc(fmtDate(point.DATE))}</time><span>${esc(formatMoney(number(point.VALUE),position.currency))}</span></li>`).join("")}</ul>${position.history.length>5? "<small>Latest 5 saved prices shown.</small>":""}</details>`;
}
function populateAccountFilter() {
  const select=$("investmentAccountFilter"),selected=select.value;
  select.innerHTML='<option value="">All accounts</option>'+state.accounts.map(account=>`<option value="${esc(account.ACCOUNTID)}">${esc(account.ACCOUNTNAME)}</option>`).join("");
  if([...select.options].some(option=>option.value===selected))
    select.value=selected;
}
function renderInvestments() {
  populateAccountFilter();
  const query=$("investmentSearch").value.trim().toLowerCase(),accountId=$("investmentAccountFilter").value;
  const all=stockRows();
  const rows=all.filter(position=>{
    const {stock,account}=position;
    return (!accountId||String(stock.HELDAT)===accountId)&&(!query||[stock.STOCKNAME,stock.SYMBOL,account?.ACCOUNTNAME].some(value=>String(value??"").toLowerCase().includes(query)));
  });
  renderMiniOverview("investmentsMiniOverview",[
    {label:"Securities",value:rows.length},
    {label:"Open positions",value:rows.filter(position=>position.quantity>0).length},
    {label:"Linked trades",value:rows.reduce((total,position)=>total+position.trades.length,0)},
    {label:"With price history",value:rows.filter(position=>position.history.length>0).length}
  ],"Summaries follow the current search and account filter.");
  const tableRows=rows.map(position=>{
    const {stock,account,currency,quantity,avgCost,price,priceAvailable,marketValue,gain,latestHistory,linked}=position;
    const costText=Number.isFinite(avgCost)?formatMoney(avgCost,currency):"—";
    const currentText=priceAvailable?formatMoney(price,currency):"—";
    const valueText=priceAvailable?formatMoney(marketValue,currency):"—";
    const gainText=priceAvailable?(gain>0?"+":"")+formatMoney(gain,currency):"—";
    const gainClass=priceAvailable?(gain>=0?"money-in":"money-out"):"money-neutral";
    const accountCell=account? `<span>${esc(account.ACCOUNTNAME)}</span><button type="button" class="investment-account-link" data-account-id="${esc(account.ACCOUNTID)}">Cash activity</button>`:`<span class="muted">Missing account (${esc(stock.HELDAT)})</span>`;
    const source=linked? '<span class="position-origin">Trade linked</span>':'<span class="position-origin">Legacy position</span>';
    const activity=[renderTradeActivity(position),renderPriceHistory(position)].filter(Boolean).join("");
    return `<tr><td><strong>${esc(stock.STOCKNAME)}</strong><div class="stock-symbol">${esc(stock.SYMBOL||"No ticker")}</div>${source}</td><td>${accountCell}</td><td class="num amount-cell">${esc(formatQuantity(quantity))}</td><td class="num">${esc(costText)}</td><td class="num">${esc(currentText)}</td><td class="num">${esc(valueText)}<div class="stock-currency">${esc(currency?.CURRENCY_SYMBOL||currency?.CURRENCYNAME||"")}</div></td><td class="num ${gainClass}">${esc(gainText)}</td><td>${esc(latestHistory?fmtDate(latestHistory.DATE):"—")}</td><td>${activity||'<span class="muted">No linked activity</span>'}</td></tr>`;
  });
  $("investmentsBody").innerHTML=tableRows.length? tableRows.join(""):'<tr><td colspan="9" class="muted">No investment positions match these filters.</td></tr>';
  $("investmentsStatus").textContent=`${rows.length} shown / ${state.stocks.length} loaded · saved MMEX prices only`;
}
export function initInvestments() {
  $("investmentSearch").addEventListener("input",renderInvestments);
  $("investmentAccountFilter").addEventListener("change",renderInvestments);
  document.querySelector('[data-view="investments"]').addEventListener("click",()=>loadStockHistory().catch(()=>{}));
}
export { renderInvestments,loadStockHistory };
