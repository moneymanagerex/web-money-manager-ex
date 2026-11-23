import { state,$,setStatus,esc,indexBy,fmtDate,setView } from "./core.mjs";
import { fetchRecords } from "./api.mjs?v=20261123-transaction-readability";
import { initAccounts,renderAccounts,populateAccountCurrencies } from "./accounts.mjs?v=20261123-transaction-readability";
import { initTransactions,renderTransactions,populateNewTx } from "./transactions.mjs?v=20261123-transaction-readability";
import { initPayees,renderReferenceData } from "./payees.mjs?v=20261123-transaction-readability";
import { initCurrencies,renderCurrencies,baseCurrencyText } from "./currencies.mjs?v=20261123-transaction-readability";
import { transactionAmountParts } from "./balances.mjs?v=20261123-transaction-readability";
async function loadWorkspace() {
  setStatus("recentStatus","Loading MMEX data…");
  let data;
  try {
    data=await Promise.all([fetchRecords("ACCOUNTLIST_V1","ACCOUNTID"),fetchRecords("CHECKINGACCOUNT_V1","-TRANSDATE"),fetchRecords("CATEGORY_V1","CATEGNAME"),fetchRecords("PAYEE_V1","PAYEENAME"),fetchRecords("CURRENCYFORMATS_V1","CURRENCYNAME"),fetchRecords("SPLITTRANSACTIONS_V1"),fetchRecords("TRANSLINK_V1"),fetchRecords("SHAREINFO_V1"),fetchRecords("INFOTABLE_V1","",`INFONAME = "BASECURRENCYID"`)]);
  }
  catch(err) {
    setStatus("recentStatus","Refresh failed; displayed data may be out of date. "+err.message,"error");
    throw err;
  }
  const [accounts,transactions,categories,payees,currencies,splits,links,shares,baseCurrency]=data;
  state.accounts=accounts;
  state.transactions=transactions;
  state.categories=categories;
  state.payees=payees;
  state.currencies=currencies;
  state.baseCurrencyInfo=baseCurrency;
  state.protectedTransactions=new Map([...splits.map(x => [String(x.TRANSID),"split"]),...links.map(x => [String(x.CHECKINGACCOUNTID),"linked asset/stock"]),...shares.map(x => [String(x.CHECKINGACCOUNTID),"linked stock"])]);
  const active=transactions.filter(t => !t.DELETEDTIME);
  $("statAccounts").textContent=accounts.length;
  $("statTransactions").textContent=active.length;
  $("statCategories").textContent=categories.length;
  $("tabAccountsCount").textContent=accounts.length;
  $("tabTransactionsCount").textContent=active.length;
  $("tabCategoriesCount").textContent=categories.length;
  $("tabPayeesCount").textContent=payees.length;
  $("tabCurrenciesCount").textContent=currencies.length;
  renderAccounts();
  renderReferenceData();
  renderCurrencies();
  const baseCurrencyValue = $("statBaseCurrency");
  if (baseCurrencyValue) baseCurrencyValue.textContent = baseCurrencyText();
  populateNewTx();
  renderTransactions();
  populateAccountCurrencies();
  const recent=[...active].sort((a,b) => String(b.TRANSDATE||"").localeCompare(String(a.TRANSDATE||""))).slice(0,10);
  const aidx=indexBy(accounts,"ACCOUNTID"),pidx=indexBy(payees,"PAYEEID"),cidx=indexBy(categories,"CATEGID");
  $("recentBody").innerHTML=recent.map(t => {
    const amounts=transactionAmountParts(t).map(part => `<span class="money-${part.tone}">${esc(part.text)}</span>`).join(t.TRANSCODE==="Transfer"? ' <span class="muted">→</span> ':'');
    const typeClass=t.TRANSCODE==="Deposit"? "type-deposit":t.TRANSCODE==="Withdrawal"? "type-withdrawal":t.TRANSCODE==="Transfer"? "type-transfer":"type-other";
    return `<tr><td>${esc(fmtDate(t.TRANSDATE))}</td><td>${esc(pidx.get(String(t.PAYEEID))?.PAYEENAME??(Number(t.PAYEEID)<=0? "":t.PAYEEID))}</td><td>${esc(cidx.get(String(t.CATEGID))?.CATEGNAME??(Number(t.CATEGID)<=0? "":t.CATEGID))}</td><td>${esc(aidx.get(String(t.ACCOUNTID))?.ACCOUNTNAME??t.ACCOUNTID)}</td><td><span class="type-badge ${typeClass}">${esc(t.TRANSCODE)}</span></td><td class="num amount-cell">${amounts}</td></tr>`;
  }).join("");
  $("recentStatus").textContent=`Latest ${recent.length} of ${active.length} active transactions`;
}
export function initWorkspace() {
  initAccounts(loadWorkspace);
  initTransactions(loadWorkspace);
  initPayees(loadWorkspace);
  initCurrencies(loadWorkspace);
  document.querySelectorAll(".topnav [data-view]").forEach(b => b.addEventListener("click",() => setView(b.dataset.view)));
  document.querySelectorAll("[data-go]").forEach(b => b.addEventListener("click",() => setView(b.dataset.go)));
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
