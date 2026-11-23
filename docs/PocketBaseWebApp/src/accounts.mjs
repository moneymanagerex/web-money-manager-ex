import { state,$,esc,setStatus,fmtDate,newBusinessId,indexBy } from "./core.mjs";
import { api } from "./api.mjs?v=20261123-investments";
import { accountBalance,formatDecimal,parseStoredNumber } from "./balances.mjs?v=20261123-investments";
import { renderMiniOverview } from "./summary.mjs?v=20261123-investments";
let reloadWorkspace;
function renderAccounts() {
  const cur=indexBy(state.currencies,"CURRENCYID");
  const query=$("accountSearch")?.value.trim().toLowerCase()??"";
  const rows=state.accounts.filter(a => {
    const c=cur.get(String(a.CURRENCYID));
    const currency=c? (c.CURRENCY_SYMBOL||c.CURRENCYNAME):a.CURRENCYID;
    return !query||[a.ACCOUNTID,a.ACCOUNTNAME,a.ACCOUNTTYPE,a.STATUS,a.ACCOUNTNUM,currency].some(v => String(v??"").toLowerCase().includes(query));
  });
  const open=state.accounts.filter(account => account.STATUS!=="Closed").length;
  const usedCurrencies=new Set(state.accounts.filter(account => account.STATUS!=="Closed").map(account => String(account.CURRENCYID)).filter(Boolean));
  renderMiniOverview("accountsMiniOverview",[
    { label:"Open",value:open },
    { label:"Closed",value:state.accounts.length-open },
    { label:"Currencies in use",value:usedCurrencies.size }
  ]);
  $("accountsBody").innerHTML=rows.length? rows.map(a => {
    const c=cur.get(String(a.CURRENCYID));
    const currency=c? (c.CURRENCY_SYMBOL||c.CURRENCYNAME):a.CURRENCYID;
    let balance;
    try {
      balance=formatDecimal(accountBalance(a,state.transactions),c);
    }
    catch(err) {
      balance="Unavailable: "+err.message;
    }
    return `<tr><td>${esc(a.ACCOUNTID)}</td><td>${esc(a.ACCOUNTNAME)}</td><td>${esc(a.ACCOUNTTYPE)}</td><td>${esc(a.STATUS)}</td><td>${esc(a.ACCOUNTNUM)}</td><td>${esc(currency)}</td><td class="num">${esc(balance)}</td><td><button type="button" class="editAccount" data-id="${esc(a.id)}">Edit</button> <button type="button" class="deleteAccount" data-id="${esc(a.id)}">Delete</button></td></tr>`;
  }).join("") : `<tr><td colspan="8" class="muted">No accounts match the search.</td></tr>`;
  $("accountsStatus").textContent=`${rows.length} shown / ${state.accounts.length} loaded (all pages)`;
  document.querySelectorAll(".editAccount").forEach(b => b.addEventListener("click",() => editAccount(b.dataset.id)));
  document.querySelectorAll(".deleteAccount").forEach(b => b.addEventListener("click",() => deleteAccount(b.dataset.id)));
}
function populateAccountCurrencies() {
  const sel=$("accountCurrency");
  const selected=sel.value;
  sel.innerHTML=state.currencies.map(c => `<option value="${esc(c.CURRENCYID)}">${esc(c.CURRENCYNAME)}${c.CURRENCY_SYMBOL? ` (${esc(c.CURRENCY_SYMBOL)})`:""}</option>`).join("");
  if(selected&&state.currencies.some(c => String(c.CURRENCYID)===selected))
    sel.value=selected;
  else if(state.currencies.some(c => Number(c.CURRENCYID)===Number(state.accounts[0]?.CURRENCYID)))
    sel.value=String(state.accounts[0].CURRENCYID);
}
function resetAccountForm() {
  $("accountForm").reset();
  $("accountRecordId").value="";
  $("accountFormTitle").textContent="New account";
  $("saveAccountButton").textContent="Create account";
  $("cancelAccountEdit").classList.add("hidden");
  if(state.currencies.length)
    populateAccountCurrencies();
  $("accountInitialBalance").value="0";
  setStatus("accountFormStatus","");
}
function editAccount(id) {
  const a=state.accounts.find(x => x.id===id);
  if(!a)
    return;
  $("accountFormPanel").open=true;
  $("accountFormPanel").scrollIntoView({ behavior:"smooth",block:"start" });
  $("accountRecordId").value=a.id;
  $("accountName").value=a.ACCOUNTNAME??"";
  $("accountType").value=a.ACCOUNTTYPE??"Checking";
  $("accountStatus").value=a.STATUS??"Open";
  $("accountNumber").value=a.ACCOUNTNUM??"";
  $("accountCurrency").value=String(a.CURRENCYID??"");
  $("accountInitialBalance").value=a.INITIALBAL??0;
  $("accountInitialDate").value=fmtDate(a.INITIALDATE);
  $("accountNotes").value=a.NOTES??"";
  $("accountFormTitle").textContent=`Edit account · ${a.ACCOUNTNAME}`;
  $("saveAccountButton").textContent="Save account";
  $("cancelAccountEdit").classList.remove("hidden");
  setStatus("accountFormStatus",`Editing MMEX account ${a.ACCOUNTID}. The existing primary key will not change.`);
}
async function deleteAccount(id) {
  const a=state.accounts.find(x => x.id===id);
  if(!a)
    return;
  if(!confirm(`Delete account “${a.ACCOUNTNAME}”? This cannot be undone.`))
    return;
  try {
    const key=Number(a.ACCOUNTID);
    const filter=encodeURIComponent(`ACCOUNTID = ${key} || TOACCOUNTID = ${key}`);
    const [tx,scheduled,stocks]=await Promise.all([api(state.base,`/api/collections/CHECKINGACCOUNT_V1/records?filter=${filter}&perPage=1`,{},state.token),api(state.base,`/api/collections/BILLSDEPOSITS_V1/records?filter=${filter}&perPage=1`,{},state.token),api(state.base,`/api/collections/STOCK_V1/records?filter=${encodeURIComponent(`HELDAT = ${key}`)}&perPage=1`,{},state.token)]);
    const refs=tx.totalItems+scheduled.totalItems+stocks.totalItems;
    if(refs)
      throw Error(`Cannot delete: ${refs} transaction, scheduled transaction or stock record(s) reference this account. Reassign or remove those records first.`);
    await api(state.base,"/api/collections/ACCOUNTLIST_V1/records/"+encodeURIComponent(id),{ method: "DELETE" },state.token);
    await reloadWorkspace();
    resetAccountForm();
    setStatus("accountFormStatus",`Deleted account ${a.ACCOUNTNAME}.`,"ok");
  }
  catch(err) {
    setStatus("accountFormStatus",err.message,"error");
  }
}
export function initAccounts(reload) {
  reloadWorkspace=reload;
  $("accountSearch").addEventListener("input",renderAccounts);
  $("accountForm").addEventListener("submit",async (e) => {
    e.preventDefault();
    const b=$("saveAccountButton");
    b.disabled=true;
    try {
      const recordId=$("accountRecordId").value;
      const payload={ ACCOUNTNAME: $("accountName").value.trim(),ACCOUNTTYPE: $("accountType").value,STATUS: $("accountStatus").value,ACCOUNTNUM: $("accountNumber").value.trim(),CURRENCYID: Number($("accountCurrency").value),INITIALBAL: parseStoredNumber($("accountInitialBalance").value||"0"),INITIALDATE: $("accountInitialDate").value,NOTES: $("accountNotes").value };
      if(!payload.ACCOUNTNAME)
        throw Error("Account name is required.");
      if(!payload.CURRENCYID)
        throw Error("Choose a currency.");
      if(recordId) {
        await api(state.base,"/api/collections/ACCOUNTLIST_V1/records/"+encodeURIComponent(recordId),{ method: "PATCH",body: JSON.stringify(payload) },state.token);
      }
      else {
        payload.ACCOUNTID=newBusinessId();
        payload.HELDAT="";
        payload.WEBSITE="";
        payload.CONTACTINFO="";
        payload.ACCESSINFO="";
        payload.FAVORITEACCT="FALSE";
        payload.STATEMENTLOCKED=0;
        payload.STATEMENTDATE="";
        payload.MINIMUMBALANCE=0;
        payload.CREDITLIMIT=0;
        payload.INTERESTRATE=0;
        payload.PAYMENTDUEDATE="";
        payload.MINIMUMPAYMENT=0;
        await api(state.base,"/api/collections/ACCOUNTLIST_V1/records",{ method: "POST",body: JSON.stringify(payload) },state.token);
      }
      await reloadWorkspace();
      resetAccountForm();
      $("accountFormPanel").open=false;
      setStatus("accountFormStatus",recordId? "Account updated.":"Account created.","ok");
    }
    catch(err) {
      setStatus("accountFormStatus",err.message,"error");
    }
    finally {
      b.disabled=false;
    }
  });
  $("cancelAccountEdit").addEventListener("click",resetAccountForm);
}
export { renderAccounts,populateAccountCurrencies };
