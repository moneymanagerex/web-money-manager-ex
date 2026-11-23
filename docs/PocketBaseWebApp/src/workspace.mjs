import { state,$,setStatus,esc,indexBy,fmtDate,setView } from "./core.mjs";
import { fetchRecords } from "./api.mjs?v=20261123-investments-assets";
import { initAccounts,renderAccounts,populateAccountCurrencies } from "./accounts.mjs?v=20261123-investments-assets";
import { initTransactions,renderTransactions,populateNewTx } from "./transactions.mjs?v=20261123-investments-assets";
import { initPayees,renderReferenceData } from "./payees.mjs?v=20261123-investments-assets";
import { initCurrencies,renderCurrencies,baseCurrencyText } from "./currencies.mjs?v=20261123-investments-assets";
import { accountBalance,formatDecimal,transactionAmountParts } from "./balances.mjs?v=20261123-investments-assets";
import { initInvestments,renderInvestments,loadStockHistory } from "./investments.mjs?v=20261123-investments-assets";
import { renderAssets } from "./assets.mjs?v=20261123-investments-assets";
async function loadWorkspace() {
  setStatus("recentStatus","Loading MMEX data…");
  let data;
  try {
    data=await Promise.all([fetchRecords("ACCOUNTLIST_V1","ACCOUNTID"),fetchRecords("CHECKINGACCOUNT_V1","-TRANSDATE"),fetchRecords("CATEGORY_V1","CATEGNAME"),fetchRecords("PAYEE_V1","PAYEENAME"),fetchRecords("CURRENCYFORMATS_V1","CURRENCYNAME"),fetchRecords("ASSETS_V1","ASSETNAME"),fetchRecords("STOCK_V1","STOCKNAME"),fetchRecords("SPLITTRANSACTIONS_V1"),fetchRecords("TRANSLINK_V1"),fetchRecords("SHAREINFO_V1"),fetchRecords("INFOTABLE_V1","",`INFONAME = "BASECURRENCYID"`)]);
  }
  catch(err) {
    setStatus("recentStatus","Refresh failed; displayed data may be out of date. "+err.message,"error");
    throw err;
  }
  const [accounts,transactions,categories,payees,currencies,assets,stocks,splits,links,shares,baseCurrency]=data;
  state.accounts=accounts;
  state.transactions=transactions;
  state.categories=categories;
  state.payees=payees;
  state.currencies=currencies;
  state.assets=assets;
  state.stocks=stocks;
  state.transactionLinks=links;
  state.shareInfo=shares;
  state.baseCurrencyInfo=baseCurrency;
  state.protectedTransactions=new Map([...splits.map(x => [String(x.TRANSID),"split"]),...links.map(x => [String(x.CHECKINGACCOUNTID),"linked asset/stock"]),...shares.map(x => [String(x.CHECKINGACCOUNTID),"linked stock"])]);
  const active=transactions.filter(t => !t.DELETEDTIME);
  $("statAccounts").textContent=accounts.length;
  $("statTransactions").textContent=active.length;
  $("statCategories").textContent=categories.length;
  $("tabAccountsCount").textContent=accounts.length;
  $("tabInvestmentsCount").textContent=stocks.length+assets.length;
  $("tabTransactionsCount").textContent=active.length;
  $("tabCategoriesCount").textContent=categories.length;
  $("tabPayeesCount").textContent=payees.length;
  $("tabCurrenciesCount").textContent=currencies.length;
  renderAccounts();
  renderReferenceData();
  renderCurrencies();
  renderInvestments();
  renderAssets();
  const baseCurrencyValue = $("statBaseCurrency");
  if (baseCurrencyValue) baseCurrencyValue.textContent = baseCurrencyText();
  const accountCurrencies=indexBy(currencies,"CURRENCYID");
  const overviewAccounts=accounts.filter(account => account.STATUS!=="Closed").slice(0,6);
  $("overviewAccountGrid").innerHTML=overviewAccounts.length? overviewAccounts.map(account => {
    const currency=accountCurrencies.get(String(account.CURRENCYID));
    let balance;
    try {
      balance=formatDecimal(accountBalance(account,transactions),currency);
    }
    catch {
      balance="Unavailable";
    }
    return `<article class="account-tile"><div class="account-tile-head"><span class="account-type">${esc(account.ACCOUNTTYPE||"Account")}</span><span class="account-currency">${esc(currency?.CURRENCY_SYMBOL||currency?.CURRENCYNAME||"")}</span></div><h4>${esc(account.ACCOUNTNAME)}</h4><div class="account-balance">${esc(balance)}</div><div class="account-subtitle">Current balance</div><button type="button" class="account-activity" data-account-id="${esc(account.ACCOUNTID)}">View activity</button></article>`;
  }).join(""): `<p class="muted hint empty-state">No open accounts yet. Create an account to start tracking your balances.</p>`;
  populateNewTx();
  renderTransactions();
  populateAccountCurrencies();
  const recent=[...active].sort((a,b) => String(b.TRANSDATE||"").localeCompare(String(a.TRANSDATE||""))).slice(0,10);
  const aidx=indexBy(accounts,"ACCOUNTID"),pidx=indexBy(payees,"PAYEEID"),cidx=indexBy(categories,"CATEGID");
  $("recentBody").innerHTML=recent.length? recent.map(t => {
    const amounts=transactionAmountParts(t).map(part => `<span class="money-${part.tone}">${esc(part.text)}</span>`).join("");
    const transfer=t.TRANSCODE==="Transfer",account=esc(aidx.get(String(t.ACCOUNTID))?.ACCOUNTNAME??t.ACCOUNTID);
    const typeClass=t.TRANSCODE==="Deposit"? "type-deposit":t.TRANSCODE==="Withdrawal"? "type-withdrawal":t.TRANSCODE==="Transfer"? "type-transfer":"type-other";
    const payee=transfer? `> ${aidx.get(String(t.TOACCOUNTID))?.ACCOUNTNAME??t.TOACCOUNTID}`:pidx.get(String(t.PAYEEID))?.PAYEENAME??(Number(t.PAYEEID)<=0? "":t.PAYEEID);
    return `<tr><td>${esc(fmtDate(t.TRANSDATE))}</td><td>${esc(payee)}</td><td>${esc(cidx.get(String(t.CATEGID))?.CATEGNAME??(Number(t.CATEGID)<=0? "":t.CATEGID))}</td><td>${account}</td><td><span class="type-badge ${typeClass}">${esc(t.TRANSCODE)}</span></td><td class="num amount-cell">${amounts}</td></tr>`;
  }).join(""): `<tr><td colspan="6"><div class="empty-state muted">No transactions yet. Add a withdrawal, deposit or transfer from Transactions.</div></td></tr>`;
  $("recentStatus").textContent=`Latest ${recent.length} of ${active.length} active transactions`;
}
export function initWorkspace() {
  initAccounts(loadWorkspace);
  initTransactions(loadWorkspace);
  initPayees(loadWorkspace);
  initCurrencies(loadWorkspace);
  initInvestments();
  document.querySelectorAll(".topnav [data-view]").forEach(b => b.addEventListener("click",() => setView(b.dataset.view)));
  document.querySelectorAll("[data-go]").forEach(b => b.addEventListener("click",() => setView(b.dataset.go)));
  $("overviewAccountGrid").addEventListener("click",event => {
    const button=event.target.closest(".account-activity");
    if(!button)
      return;
    $("txSearch").value="";
    $("txType").value="";
    $("txFrom").value="";
    $("txThrough").value="";
    $("txShowDeleted").checked=false;
    $("txFilterAccount").value=button.dataset.accountId;
    renderTransactions();
    setView("transactions");
  });
  $("investmentsBody").addEventListener("click",event => {
    const button=event.target.closest(".investment-account-link");
    if(!button)
      return;
    $("txSearch").value="";
    $("txType").value="";
    $("txFrom").value="";
    $("txThrough").value="";
    $("txShowDeleted").checked=false;
    $("txFilterAccount").value=button.dataset.accountId;
    renderTransactions();
    setView("transactions");
  });
  $("investmentSearch").addEventListener("input",renderInvestments);
  $("investmentAccountFilter").addEventListener("change",renderInvestments);
  $("refreshInvestments").addEventListener("click",async event => {
    const button=event.currentTarget;
    button.disabled=true;
    setStatus("investmentsStatus","Refreshing investment data…");
    try {
      await loadWorkspace();
      await loadStockHistory(true);
      setStatus("investmentsStatus","Investment data refreshed.","ok");
    }
    catch(error) {
      setStatus("investmentsStatus","Refresh failed; displayed data may be out of date. "+error.message,"error");
    }
    finally {
      button.disabled=false;
    }
  });
  document.querySelectorAll("[data-refresh]").forEach(button => button.addEventListener("click", async () => {
    const status = button.closest(".view")?.querySelector(".list-head .hint") ?? $("recentStatus");
    const show = (message, kind = "muted") => {
      if (!status) return;
      status.textContent = message;
      status.className = "hint status " + kind;
    };
    button.disabled = true;
    show("Refreshing MMEX data…");
    try {
      await loadWorkspace();
      show("Data refreshed.", "ok");
    } catch (error) {
      show("Refresh failed; displayed data may be out of date. " + error.message, "error");
    } finally {
      button.disabled = false;
    }
  }));
}
export { loadWorkspace };
