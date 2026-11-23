import { state,$,esc,setStatus,indexBy,safeHttpUrl,newBusinessId } from "./core.mjs";
import { api } from "./api.mjs?v=20261123-payee-management";
let reloadWorkspace;
function renderReferenceData() {
  const cats=indexBy(state.categories,"CATEGID");
  const query=$("categorySearch")?.value.trim().toLowerCase()??"";
  const rows=state.categories.filter(c => !query||[c.CATEGID,c.CATEGNAME,cats.get(String(c.PARENTID))?.CATEGNAME].some(v => String(v??"").toLowerCase().includes(query)));
  $("categoriesBody").innerHTML=rows.length? rows.map(c => `<tr><td>${esc(c.CATEGID)}</td><td>${esc(c.CATEGNAME)}</td><td>${Number(c.PARENTID)>0? esc(cats.get(String(c.PARENTID))?.CATEGNAME??`Missing parent (${c.PARENTID})`):"—"}</td><td>${Number(c.ACTIVE)===0? "No":"Yes"}</td><td><button type="button" class="editCategory" data-id="${esc(c.id)}">Edit</button></td></tr>`).join("") : `<tr><td colspan="5" class="muted">No categories match the search.</td></tr>`;
  $("categoriesStatus").textContent=`${rows.length} shown / ${state.categories.length} category record(s) loaded`;
  populateCategoryParents();
  populatePayeeCategories();
  document.querySelectorAll(".editCategory").forEach(button => button.addEventListener("click",() => editCategory(button.dataset.id)));
  renderPayees();
}
function populateCategoryParents() {
  const select=$("categoryParent"), selected=select.value, currentId=$("categoryRecordId").value;
  const options=state.categories.filter(category => category.id!==currentId)
    .map(category => `<option value="${esc(category.CATEGID)}">${esc(category.CATEGNAME)} (${esc(category.CATEGID)})</option>`).join("");
  select.innerHTML='<option value="-1">No parent</option>'+options;
  if(selected&&[...select.options].some(option => option.value===selected)) select.value=selected;
}
function resetCategoryForm() {
  $("categoryForm").reset();
  $("categoryRecordId").value="";
  $("categoryFormTitle").textContent="New category";
  $("saveCategoryButton").textContent="Create category";
  $("cancelCategoryEdit").classList.add("hidden");
  $("categoryFormPanel").open=false;
  populateCategoryParents();
  setStatus("categoryFormStatus","");
}
function editCategory(id) {
  const category=state.categories.find(item => item.id===id);
  if(!category) return;
  $("categoryRecordId").value=category.id;
  $("categoryName").value=category.CATEGNAME??"";
  $("categoryParent").value=String(Number(category.PARENTID)>0? category.PARENTID:-1);
  $("categoryActive").checked=Number(category.ACTIVE)!==0;
  $("categoryFormTitle").textContent=`Edit category · ${category.CATEGNAME}`;
  $("saveCategoryButton").textContent="Save category";
  $("cancelCategoryEdit").classList.remove("hidden");
  $("categoryFormPanel").open=true;
  $("categoryFormPanel").scrollIntoView({ behavior:"smooth",block:"start" });
  setStatus("categoryFormStatus",`Editing MMEX category ${category.CATEGID}. The existing ID will not change.`);
}
function parentCreatesCycle(categoryId,parentId) {
  const visited=new Set();
  let current=parentId;
  while(current>0) {
    if(current===categoryId||visited.has(current)) return true;
    visited.add(current);
    const parent=state.categories.find(item => Number(item.CATEGID)===current);
    if(!parent) return false;
    current=Number(parent.PARENTID)||-1;
  }
  return false;
}
function renderPayees() {
  const q=$("payeeSearch").value.trim().toLowerCase();
  const rows=state.payees.filter(p => !q||[p.PAYEENAME,p.WEBSITE,p.NOTES,p.NUMBER].some(v => String(v??"").toLowerCase().includes(q)));
  $("payeesBody").innerHTML=rows.length? rows.map(p => `<tr><td>${esc(p.PAYEEID)}</td><td>${esc(p.PAYEENAME)}</td><td>${esc(catsName(p.CATEGID))}</td><td>${esc(p.NUMBER)}</td><td>${Number(p.ACTIVE)===0? "No":"Yes"}</td><td>${safeHttpUrl(p.WEBSITE)? `<a href="${esc(safeHttpUrl(p.WEBSITE))}" target="_blank" rel="noopener noreferrer">${esc(p.WEBSITE)}</a>`:""}</td><td>${esc(p.NOTES)}</td><td><button type="button" class="editPayee" data-id="${esc(p.id)}">Edit</button></td></tr>`).join("") : `<tr><td colspan="8" class="muted">No payees match the search.</td></tr>`;
  $("payeesStatus").textContent=`${rows.length} shown / ${state.payees.length} loaded`;
  document.querySelectorAll(".editPayee").forEach(button => button.addEventListener("click",() => editPayee(button.dataset.id)));
}
function populatePayeeCategories() {
  const select=$("newPayeeCategory"),selected=select.value;
  select.innerHTML='<option value="">No default category</option>'+state.categories.map(category => `<option value="${esc(category.CATEGID)}">${esc(category.CATEGNAME)} (${esc(category.CATEGID)})${Number(category.ACTIVE)===0? " · inactive":""}</option>`).join("");
  if(selected&&[...select.options].some(option => option.value===selected)) select.value=selected;
}
function resetPayeeForm() {
  $("newPayeeForm").reset();
  $("payeeRecordId").value="";
  $("payeeFormTitle").textContent="New payee";
  $("createPayeeButton").textContent="Create payee";
  $("cancelPayeeEdit").classList.add("hidden");
  $("payeeFormPanel").open=false;
  populatePayeeCategories();
  setStatus("createPayeeStatus","");
}
function editPayee(id) {
  const payee=state.payees.find(item => item.id===id);
  if(!payee) return;
  $("payeeRecordId").value=payee.id;
  $("newPayeeName").value=payee.PAYEENAME??"";
  $("newPayeeNumber").value=payee.NUMBER??"";
  $("newPayeeCategory").value=Number(payee.CATEGID)>0? String(payee.CATEGID):"";
  $("newPayeeWebsite").value=payee.WEBSITE??"";
  $("newPayeeNotes").value=payee.NOTES??"";
  $("newPayeeActive").checked=Number(payee.ACTIVE)!==0;
  $("payeeFormTitle").textContent=`Edit payee · ${payee.PAYEENAME}`;
  $("createPayeeButton").textContent="Save payee";
  $("cancelPayeeEdit").classList.remove("hidden");
  $("payeeFormPanel").open=true;
  $("payeeFormPanel").scrollIntoView({ behavior:"smooth",block:"start" });
  setStatus("createPayeeStatus",`Editing MMEX payee ${payee.PAYEEID}. The existing ID will not change.`);
}
function catsName(id) {
  const c=state.categories.find(x => String(x.CATEGID)===String(id));
  return c? `${c.CATEGNAME} (${id})`:id??"";
}
export function initPayees(reload) {
  reloadWorkspace=reload;
  $("payeeSearch").addEventListener("input",renderPayees);
  $("categorySearch").addEventListener("input",renderReferenceData);
  $("cancelPayeeEdit").addEventListener("click",resetPayeeForm);
  $("categoryForm").addEventListener("submit",async event => {
    event.preventDefault();
    const button=$("saveCategoryButton"),recordId=$("categoryRecordId").value;
    const name=$("categoryName").value.trim(),parentId=Number($("categoryParent").value),active=$("categoryActive").checked? 1:0;
    button.disabled=true;
    try {
      if(!name) throw Error("Enter a category name.");
      if(parentId>0&&!state.categories.some(item => Number(item.CATEGID)===parentId)) throw Error("Choose an existing parent category.");
      const current=state.categories.find(item => item.id===recordId);
      const categoryId=current? Number(current.CATEGID):newBusinessId();
      if(parentId>0&&parentCreatesCycle(categoryId,parentId)) throw Error("A category cannot be its own parent or a descendant of itself.");
      const duplicate=state.categories.find(item => item.id!==recordId&&item.CATEGNAME.trim().toLowerCase()===name.toLowerCase()&&Number(item.PARENTID||-1)===parentId);
      if(duplicate) throw Error("A category with this name already exists under the selected parent.");
      const payload={ CATEGNAME:name,PARENTID:parentId,ACTIVE:active };
      if(current) await api(state.base,`/api/collections/CATEGORY_V1/records/${encodeURIComponent(recordId)}`,{ method:"PATCH",body:JSON.stringify(payload) },state.token);
      else await api(state.base,"/api/collections/CATEGORY_V1/records",{ method:"POST",body:JSON.stringify({ ...payload,CATEGID:categoryId }) },state.token);
      await reloadWorkspace();
      resetCategoryForm();
      setStatus("categoryFormStatus",current? "Category updated.":`Category ${name} created.` ,"ok");
    }
    catch(error) {
      setStatus("categoryFormStatus",error.message,"error");
    }
    finally {
      button.disabled=false;
    }
  });
  $("cancelCategoryEdit").addEventListener("click",resetCategoryForm);
  $("newPayeeForm").addEventListener("submit",async (e) => {
    e.preventDefault();
    const b=$("createPayeeButton");
    b.disabled=true;
    try {
      const recordId=$("payeeRecordId").value,payeeName=$("newPayeeName").value.trim(),categoryId=$("newPayeeCategory").value? Number($("newPayeeCategory").value):null;
      const current=state.payees.find(item => item.id===recordId);
      if(!payeeName) throw Error("Enter a payee name.");
      const duplicate=state.payees.find(item => item.id!==recordId&&item.PAYEENAME.trim().toLowerCase()===payeeName.toLowerCase());
      if(duplicate) throw Error("A payee with this name already exists.");
      if(categoryId!==null&&!state.categories.some(item => Number(item.CATEGID)===categoryId)) throw Error("Choose an existing default category.");
      const payload={ PAYEENAME:payeeName,CATEGID:categoryId,NUMBER:$("newPayeeNumber").value.trim(),WEBSITE:$("newPayeeWebsite").value.trim(),NOTES:$("newPayeeNotes").value,ACTIVE:$("newPayeeActive").checked? 1:0 };
      if(current) await api(state.base,`/api/collections/PAYEE_V1/records/${encodeURIComponent(recordId)}`,{ method:"PATCH",body:JSON.stringify(payload) },state.token);
      else await api(state.base,"/api/collections/PAYEE_V1/records",{ method:"POST",body:JSON.stringify({ ...payload,PAYEEID:newBusinessId(),PATTERN:"" }) },state.token);
      await reloadWorkspace();
      resetPayeeForm();
      setStatus("createPayeeStatus",current? "Payee updated.":`Payee ${payeeName} created.` ,"ok");
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
