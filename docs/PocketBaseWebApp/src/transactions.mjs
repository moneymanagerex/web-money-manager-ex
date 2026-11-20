import { state,$,esc,setStatus,indexBy,fmtDate,newBusinessId } from "./core.mjs";
import { api,fetchRecords } from "./api.mjs?v=20261120-ux5";
import { localToday,formatTransactionAmount,parseStoredNumber } from "./balances.mjs?v=20261120-ux5";

let reloadWorkspace,pendingCreate=null,receivedManually=false,priorSameCurrency=false;
const statuses={ "": "Unreconciled",R: "Reconciled",V: "Void",F: "Follow up",D: "Duplicate" };
const timestamp=() => new Date().toISOString().replace("T"," ").slice(0,19);
const txPath=id => "/api/collections/CHECKINGACCOUNT_V1/records"+(id? "/"+encodeURIComponent(id):"");

function filterTransactions(transactions,{ query="",type="",account="",from="",through="",showDeleted=false },accounts,payees,categories) {
  const aidx=indexBy(accounts,"ACCOUNTID"),pidx=indexBy(payees,"PAYEEID"),cidx=indexBy(categories,"CATEGID"),q=query.trim().toLowerCase();
  return transactions.filter(t => {
    if((!showDeleted&&t.DELETEDTIME)||(type&&t.TRANSCODE!==type))
      return false;
    if(account&&String(t.ACCOUNTID)!==account&&String(t.TOACCOUNTID)!==account)
      return false;
    const date=fmtDate(t.TRANSDATE);
    if((from&&date<from)||(through&&date>through))
      return false;
    return !q||[t.TRANSID,t.TRANSDATE,t.TRANSCODE,t.NOTES,t.TRANSAMOUNT,aidx.get(String(t.ACCOUNTID))?.ACCOUNTNAME,aidx.get(String(t.TOACCOUNTID))?.ACCOUNTNAME,pidx.get(String(t.PAYEEID))?.PAYEENAME,cidx.get(String(t.CATEGID))?.CATEGNAME].some(x => String(x??"").toLowerCase().includes(q));
  });
}
function renderTransactions() {
  const filters={ query: $("txSearch").value,type: $("txType").value,account: $("txFilterAccount").value,from: $("txFrom").value,through: $("txThrough").value,showDeleted: $("txShowDeleted").checked };
  const rows=filterTransactions(state.transactions,filters,state.accounts,state.payees,state.categories);
  const accounts=indexBy(state.accounts,"ACCOUNTID"),payees=indexBy(state.payees,"PAYEEID"),cats=indexBy(state.categories,"CATEGID");
  $("txBody").innerHTML=rows.length? rows.map(t => {
    const a=accounts.get(String(t.ACCOUNTID)),dest=accounts.get(String(t.TOACCOUNTID)),p=payees.get(String(t.PAYEEID)),c=cats.get(String(t.CATEGID));
    const protectedReason=state.protectedTransactions.get(String(t.TRANSID));
    const unsupported=!["Withdrawal","Deposit","Transfer"].includes(t.TRANSCODE)||!Object.hasOwn(statuses,t.STATUS??"");
    const actions=protectedReason? `${esc(protectedReason)} (read-only)`:unsupported? "Unsupported (read-only)":t.DELETEDTIME? `<button type="button" class="restoreTx" data-id="${esc(t.id)}">Restore</button>`:`<button type="button" class="editTx" data-id="${esc(t.id)}">Edit</button> <button type="button" class="duplicateTx" data-id="${esc(t.id)}">Duplicate</button> <button type="button" class="deleteTx" data-id="${esc(t.id)}">Delete</button>`;
    const account=esc(a?.ACCOUNTNAME??t.ACCOUNTID)+(t.TRANSCODE==="Transfer"? " → "+esc(dest?.ACCOUNTNAME??t.TOACCOUNTID):"");
    const amount=esc(formatTransactionAmount(t.TRANSAMOUNT))+(t.TRANSCODE==="Transfer"? " → "+esc(formatTransactionAmount(t.TOTRANSAMOUNT)):"");
    return `<tr><td>${esc(t.TRANSID)}</td><td>${esc(fmtDate(t.TRANSDATE))}</td><td>${esc(t.TRANSCODE)} · ${esc(statuses[t.STATUS??""]??t.STATUS)}${t.DELETEDTIME? " · Deleted":""}</td><td>${account}</td><td>${esc(p?.PAYEENAME??(Number(t.PAYEEID)<=0? "":t.PAYEEID))}</td><td>${esc(c?.CATEGNAME??(Number(t.CATEGID)<=0? "":t.CATEGID))}</td><td class="num">${amount}</td><td>${esc(t.NOTES)}</td><td>${actions}</td></tr>`;
  }).join("") : `<tr><td colspan="9" class="muted">No transactions match the current filters.</td></tr>`;
  $("txStatus").textContent=filters.from&&filters.through&&filters.from>filters.through? "The start date must not be after the end date.":`${rows.length} shown / ${state.transactions.length} loaded (including deleted)`;
  document.querySelectorAll(".editTx").forEach(b => b.addEventListener("click",() => selectTxById(b.dataset.id)));
  document.querySelectorAll(".duplicateTx").forEach(b => b.addEventListener("click",() => selectTxById(b.dataset.id,true)));
  document.querySelectorAll(".deleteTx").forEach(b => b.addEventListener("click",() => setDeleted(b.dataset.id,true)));
  document.querySelectorAll(".restoreTx").forEach(b => b.addEventListener("click",() => setDeleted(b.dataset.id,false)));
}
function setOptions(id,rows,key,name,empty="") {
  const select=$(id),selected=select.value;
  select.innerHTML=empty+rows.map(x => `<option value="${esc(x[key])}">${esc(x[name])}</option>`).join("");
  if(selected&&![...select.options].some(o => o.value===selected)) {
    const option=document.createElement("option");
    option.value=selected;
    option.textContent=`Missing reference (${selected})`;
    select.append(option);
  }
  if(selected)
    select.value=selected;
}
function selectValue(id,value,label) {
  const select=$(id),v=String(value??"");
  if(v&&![...select.options].some(o => o.value===v)) {
    const option=document.createElement("option");
    option.value=v;
    option.textContent=`Missing ${label} (${v})`;
    select.append(option);
  }
  select.value=v;
}
function updateTransferFields() {
  const transfer=$("newTxCode").value==="Transfer";
  $("txDestinationLabel").classList.toggle("hidden",!transfer);
  $("txReceivedLabel").classList.toggle("hidden",!transfer);
  $("newTxDestination").required=transfer;
  const a=state.accounts.find(x => String(x.ACCOUNTID)===$("newTxAccount").value),b=state.accounts.find(x => String(x.ACCOUNTID)===$("newTxDestination").value);
  const sameCurrency=transfer&&a&&b&&String(a.CURRENCYID)===String(b.CURRENCYID);
  $("newTxReceived").required=transfer;
  if(sameCurrency&&!receivedManually)
    $("newTxReceived").value=$("newTxAmount").value;
  else if(transfer&&!sameCurrency&&priorSameCurrency&&!receivedManually)
    $("newTxReceived").value="";
  priorSameCurrency=Boolean(sameCurrency);
}
function populateNewTx() {
  setOptions("newTxAccount",state.accounts,"ACCOUNTID","ACCOUNTNAME",'<option value="">Select an account</option>');
  setOptions("newTxDestination",state.accounts,"ACCOUNTID","ACCOUNTNAME",'<option value="">Select an account</option>');
  setOptions("txFilterAccount",state.accounts,"ACCOUNTID","ACCOUNTNAME",'<option value="">All accounts</option>');
  setOptions("newTxCategory",state.categories,"CATEGID","CATEGNAME",'<option value="">No category</option>');
  setOptions("newTxPayee",state.payees,"PAYEEID","PAYEENAME",'<option value="-1">(No payee)</option>');
  if(!$("newTxDate").value)
    $("newTxDate").value=localToday();
  updateTransferFields();
}
function resetTransactionForm() {
  state.selected=null;
  pendingCreate=null;
  receivedManually=false;
  priorSameCurrency=false;
  $("newTxForm").reset();
  $("newTxDate").value=localToday();
  $("txFormTitle").textContent="New transaction";
  $("createTxButton").textContent="Create transaction";
  $("clearEdit").classList.add("hidden");
  populateNewTx();
  setStatus("createTxStatus","");
}
function selectTxById(id,duplicate=false) {
  const t=state.transactions.find(x => x.id===id);
  if(!t||t.DELETEDTIME||state.protectedTransactions.has(String(t.TRANSID))||
    !["Withdrawal","Deposit","Transfer"].includes(t.TRANSCODE)||!Object.hasOwn(statuses,t.STATUS??""))
    return;
  if(pendingCreate) {
    setStatus("createTxStatus","Resolve or cancel the unconfirmed creation before selecting another transaction.","error");
    return;
  }
  state.selected=duplicate? null:t;
  $("newTxCode").value=t.TRANSCODE;
  $("newTxDate").value=fmtDate(t.TRANSDATE);
  selectValue("newTxAccount",t.ACCOUNTID,"account");
  selectValue("newTxDestination",t.TOACCOUNTID||"","account");
  selectValue("newTxCategory",Number(t.CATEGID)>0? t.CATEGID:"","category");
  selectValue("newTxPayee",Number(t.PAYEEID)>0? t.PAYEEID:-1,"payee");
  $("newTxAmount").value=t.TRANSAMOUNT??"";
  $("newTxReceived").value=t.TOTRANSAMOUNT??"";
  receivedManually=true;
  $("newTxStatus").value=t.STATUS??"";
  $("newTxNotes").value=t.NOTES??"";
  $("txFormTitle").textContent=duplicate? `Duplicate transaction ${t.TRANSID}`:`Edit transaction ${t.TRANSID}`;
  $("createTxButton").textContent=duplicate? "Create duplicate":"Save changes";
  $("clearEdit").classList.remove("hidden");
  updateTransferFields();
  setStatus("createTxStatus",duplicate? "Review the copied fields before creating a new transaction; the original is unchanged.":"Editing existing transaction; other fields are preserved.");
  $("txFormTitle").scrollIntoView({ behavior: "smooth",block: "start" });
}
function defaultCategoryForPayee(payeeId,payees,categories) {
  const payee=payees.find(item => String(item.PAYEEID)===String(payeeId));
  const category=payee&&categories.find(item => Number(item.CATEGID)>0&&String(item.CATEGID)===String(payee.CATEGID));
  return category? String(category.CATEGID):null;
}
function transactionPayload() {
  const code=$("newTxCode").value,account=Number($("newTxAccount").value),destination=Number($("newTxDestination").value),amount=parseStoredNumber($("newTxAmount").value),received=code==="Transfer"? parseStoredNumber($("newTxReceived").value):null;
  if(!["Withdrawal","Deposit","Transfer"].includes(code)||!Object.hasOwn(statuses,$("newTxStatus").value))
    throw Error("Choose a supported transaction type and status.");
  if(!state.accounts.some(a => Number(a.ACCOUNTID)===account))
    throw Error("Choose an existing account.");
  if(!Number.isFinite(amount)||amount<=0)
    throw Error("Enter an amount greater than zero.");
  if(code==="Transfer"&&(!state.accounts.some(a => Number(a.ACCOUNTID)===destination)||account===destination))
    throw Error("Choose a different destination account.");
  if(code==="Transfer"&&(!Number.isFinite(received)||received<=0))
    throw Error("Enter an amount received greater than zero.");
  const payee=Number($("newTxPayee").value),category=$("newTxCategory").value? Number($("newTxCategory").value):null;
  if(payee!==-1&&!state.payees.some(p => Number(p.PAYEEID)===payee))
    throw Error("Choose an existing payee.");
  if(category!==null&&!state.categories.some(c => Number(c.CATEGID)===category))
    throw Error("Choose an existing category.");
  return { ACCOUNTID: account,TOACCOUNTID: code==="Transfer"? destination:null,PAYEEID: payee,TRANSCODE: code,TRANSAMOUNT: amount,TOTRANSAMOUNT: code==="Transfer"? received:null,STATUS: $("newTxStatus").value,NOTES: $("newTxNotes").value,CATEGID: category,TRANSDATE: $("newTxDate").value };
}
function changedFields(payload,selected) {
  const original={ ...selected,
    TOACCOUNTID: selected.TRANSCODE==="Transfer"? Number(selected.TOACCOUNTID):null,
    TOTRANSAMOUNT: selected.TRANSCODE==="Transfer"? Number(selected.TOTRANSAMOUNT):null,
    PAYEEID: Number(selected.PAYEEID)>0? Number(selected.PAYEEID):-1,
    CATEGID: Number(selected.CATEGID)>0? Number(selected.CATEGID):null,
    STATUS: selected.STATUS??"",
    NOTES: selected.NOTES??"",
    TRANSDATE: fmtDate(selected.TRANSDATE)
  };
  return Object.fromEntries(Object.entries(payload).filter(([field,value]) => value!==original[field]));
}
async function checkEditable(t) {
  const key=Number(t.TRANSID);
  if(!Number.isSafeInteger(key)||key<=0)
    throw Error("Invalid transaction ID; cannot verify related records.");
  const [current,splits,links,shares]=await Promise.all([api(state.base,txPath(t.id),{},state.token),fetchRecords("SPLITTRANSACTIONS_V1","",`TRANSID = ${key}`),fetchRecords("TRANSLINK_V1","",`CHECKINGACCOUNTID = ${key}`),fetchRecords("SHAREINFO_V1","",`CHECKINGACCOUNTID = ${key}`)]);
  if(splits.length||links.length||shares.length)
    throw Error("Split or asset/stock-linked transactions are read-only in this app.");
  const fields=["TRANSID","ACCOUNTID","TOACCOUNTID","PAYEEID","TRANSCODE","TRANSAMOUNT","TOTRANSAMOUNT","STATUS","NOTES","CATEGID","TRANSDATE","LASTUPDATEDTIME","DELETEDTIME"];
  if(fields.some(field => String(current[field]??"")!==String(t[field]??"")))
    throw Error("This transaction changed since loading. Refresh and review it before editing.");
}
async function reloadAfterMutation(message,statusId="createTxStatus") {
  try {
    await reloadWorkspace();
    setStatus(statusId,message,"ok");
  }
  catch(err) {
    setStatus(statusId,`${message} Refresh failed: ${err.message}. Use Refresh data before making further changes.`,"error");
  }
}
async function setDeleted(id,deleting) {
  const t=state.transactions.find(x => x.id===id);
  if(!t||!confirm(`${deleting? "Delete":"Restore"} transaction ${t.TRANSID}?`))
    return;
  try {
    await checkEditable(t);
    try {
      await api(state.base,txPath(id),{ method: "PATCH",body: JSON.stringify({ DELETEDTIME: deleting? timestamp():"",LASTUPDATEDTIME: timestamp() }) },state.token);
    }
    catch(err) {
      if(err.uncertain)
        throw Error(`The result is unknown. Refresh and check transaction ${t.TRANSID} before retrying. ${err.message}`);
      throw err;
    }
    if(state.selected?.id===id)
      resetTransactionForm();
    await reloadAfterMutation(`Transaction ${t.TRANSID} ${deleting? "deleted":"restored"}.`,"txActionStatus");
  }
  catch(err) {
    setStatus("txActionStatus",err.message,"error");
  }
}
export function initTransactions(reload) {
  reloadWorkspace=reload;
  ["txSearch","txType","txFilterAccount","txFrom","txThrough","txShowDeleted"].forEach(id => $(id).addEventListener(id==="txSearch"? "input":"change",renderTransactions));
  ["newTxCode","newTxAmount"].forEach(id => $(id).addEventListener(id==="newTxAmount"? "input":"change",updateTransferFields));
  ["newTxAccount","newTxDestination"].forEach(id => $(id).addEventListener("change",() => {
    receivedManually=false;
    $("newTxReceived").value="";
    updateTransferFields();
  }));
  $("newTxReceived").addEventListener("input",() => { receivedManually=true; });
  $("newTxPayee").addEventListener("change",() => {
    const category=defaultCategoryForPayee($("newTxPayee").value,state.payees,state.categories);
    if(category)
      $("newTxCategory").value=category;
  });
  $("clearEdit").addEventListener("click",() => {
    if(pendingCreate&&!confirm("The previous creation may have succeeded. Check the transaction list before creating another. Cancel this pending form?"))
      return;
    resetTransactionForm();
  });
  $("refreshTransactions").addEventListener("click",async () => {
    try {
      await reloadWorkspace();
      if(pendingCreate&&state.transactions.some(t => Number(t.TRANSID)===pendingCreate.id)) {
        const id=pendingCreate.id;
        resetTransactionForm();
        setStatus("createTxStatus",`The previous creation of transaction ${id} succeeded. Review it before creating another.`,"ok");
      }
      else
        setStatus("createTxStatus",pendingCreate? `Transaction ${pendingCreate.id} was not found; the earlier request may still be processing. Retry only with the same form.`:"Data refreshed.","ok");
    }
    catch(err) {
      setStatus("createTxStatus","Refresh failed: "+err.message,"error");
    }
  });
  $("newTxForm").addEventListener("submit",async e => {
    e.preventDefault();
    const button=$("createTxButton");
    button.disabled=true;
    try {
      const payload=transactionPayload(),selected=state.selected;
      if(selected) {
        await checkEditable(selected);
        const changes=changedFields(payload,selected);
        try {
          await api(state.base,txPath(selected.id),{ method: "PATCH",body: JSON.stringify({ ...changes,LASTUPDATEDTIME: timestamp() }) },state.token);
        }
        catch(err) {
          if(err.uncertain)
            throw Error(`The save result is unknown. Refresh and check transaction ${selected.TRANSID} before retrying. ${err.message}`);
          throw err;
        }
        resetTransactionForm();
        await reloadAfterMutation(`Transaction ${selected.TRANSID} saved.`);
      }
      else {
        const fingerprint=JSON.stringify(payload);
        const retrying=Boolean(pendingCreate);
        if(pendingCreate&&pendingCreate.fingerprint!==fingerprint)
          throw Error("The earlier creation is unconfirmed. Restore the original form or cancel it after checking the transaction list.");
        if(pendingCreate) {
          const existing=await fetchRecords("CHECKINGACCOUNT_V1","",`TRANSID = ${pendingCreate.id}`);
          if(existing.length) {
            const id=pendingCreate.id;
            resetTransactionForm();
            await reloadAfterMutation(`The previous creation of transaction ${id} succeeded; review it before creating another.`);
            return;
          }
        }
        const id=pendingCreate?.id??newBusinessId();
        pendingCreate={ id,fingerprint };
        try {
          await api(state.base,txPath(),{ method: "POST",body: JSON.stringify({ ...payload,TRANSID: id,TRANSACTIONNUMBER: "",LASTUPDATEDTIME: timestamp(),DELETEDTIME: "",FOLLOWUPID: null,COLOR: null }) },state.token);
        }
        catch(err) {
          if(err.uncertain||retrying||err.message.toLowerCase().includes("transid")) {
            $("clearEdit").classList.remove("hidden");
            throw Error(`The creation result is unknown for transaction ${id}. Refresh and check before retrying; the same ID will be reused. ${err.message}`);
          }
          pendingCreate=null;
          throw err;
        }
        resetTransactionForm();
        await reloadAfterMutation(`Transaction ${id} created.`);
      }
    }
    catch(err) {
      setStatus("createTxStatus",err.message,"error");
    }
    finally {
      button.disabled=false;
    }
  });
}
export { renderTransactions,populateNewTx,resetTransactionForm,filterTransactions,transactionPayload,changedFields,defaultCategoryForPayee };
