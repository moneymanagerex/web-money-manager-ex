import { state,$,esc,setStatus,indexBy,safeHttpUrl,newBusinessId } from "./core.mjs";
import { api } from "./api.mjs";
let reloadWorkspace;
function renderReferenceData() {
  const cats=indexBy(state.categories,"CATEGID");
  $("categoriesBody").innerHTML=state.categories.map(c => `<tr><td>${esc(c.CATEGID)}</td><td>${esc(c.CATEGNAME)}</td><td>${esc(c.PARENTID)}</td><td>${Number(c.ACTIVE)===0? "No":"Yes / unspecified"}</td></tr>`).join("");
  $("categoriesStatus").textContent=`${state.categories.length} category record(s) loaded`;
  renderPayees();
}
function renderPayees() {
  const q=$("payeeSearch").value.trim().toLowerCase();
  const rows=state.payees.filter(p => !q||[p.PAYEENAME,p.WEBSITE,p.NOTES,p.NUMBER].some(v => String(v??"").toLowerCase().includes(q)));
  $("payeesBody").innerHTML=rows.map(p => `<tr><td>${esc(p.PAYEEID)}</td><td>${esc(p.PAYEENAME)}</td><td>${esc(catsName(p.CATEGID))}</td><td>${safeHttpUrl(p.WEBSITE)? `<a href="${esc(safeHttpUrl(p.WEBSITE))}" target="_blank" rel="noopener noreferrer">${esc(p.WEBSITE)}</a>`:""}</td><td>${esc(p.NOTES)}</td></tr>`).join("");
  $("payeesStatus").textContent=`${rows.length} shown / ${state.payees.length} loaded`;
}
function catsName(id) {
  const c=state.categories.find(x => String(x.CATEGID)===String(id));
  return c? `${c.CATEGNAME} (${id})`:id??"";
}
export function initPayees(reload) {
  reloadWorkspace=reload;
  $("payeeSearch").addEventListener("input",renderPayees);
  $("newPayeeForm").addEventListener("submit",async (e) => {
    e.preventDefault();
    const b=$("createPayeeButton");
    b.disabled=true;
    try {
      const payeeId=newBusinessId(),payeeName=$("newPayeeName").value.trim();
      await api(state.base,"/api/collections/PAYEE_V1/records",{ method: "POST",body: JSON.stringify({ PAYEEID: payeeId,PAYEENAME: payeeName,CATEGID: null,NUMBER: "",WEBSITE: $("newPayeeWebsite").value.trim(),NOTES: $("newPayeeNotes").value,ACTIVE: 1,PATTERN: "" }) },state.token);
      await reloadWorkspace();
      $("newPayeeForm").reset();
      setStatus("createPayeeStatus",`Added payee ${payeeName||payeeId}.`,"ok");
    }
    catch(err) {
      setStatus("createPayeeStatus",err.message,"error");
    }
    finally {
      b.disabled=false;
    }
  });
}
export { renderReferenceData };
