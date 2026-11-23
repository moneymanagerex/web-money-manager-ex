const state={ base: "",token: "",user: null,accounts: [],transactions: [],categories: [],payees: [],currencies: [],baseCurrencyInfo: [],protectedTransactions: new Map(),selected: null,view: "dashboard" };
const $=id => document.getElementById(id),INSTANCE_KEY="mmex_pb_instance";
function setStatus(id,msg,kind="muted") {
  $(id).className="hint status "+kind;
  $(id).textContent=msg;
}
function esc(v) {
  return String(v??"").replace(/[&<>\'"]/g,c => ({ "&": "&amp;","<": "&lt;",">": "&gt;","'": "&#39;",'"': "&quot;" }[c]));
}
function safeHttpUrl(v) {
  try {
    const u=new URL(String(v));
    return ["http:","https:"].includes(u.protocol)? u.href:"";
  }
  catch {
    return "";
  }
}
function tokenKey() {
  return "mmex_pb_token:"+state.base;
}
function showScreen(name) {
  ["instanceScreen","loginScreen","adminScreen","workspace"].forEach(id => $(id).classList.toggle("hidden",id!==name));
  if(name==="loginScreen")
    $("loginInstance").textContent=state.base||"No instance selected";
  if(name==="adminScreen")
    $("adminInstance").textContent=state.base||"No instance selected";
}
function setView(view) {
  state.view=view;
  document.querySelectorAll(".topnav [data-view]").forEach(b => b.setAttribute("aria-current",b.dataset.view===view? "page":"false"));
  document.querySelectorAll(".view").forEach(v => v.classList.toggle("active",v.id==="view-"+view));
}
function clearWorkspace() {
  state.accounts=[];
  state.transactions=[];
  state.categories=[];
  state.payees=[];
  state.currencies=[];
  state.protectedTransactions=new Map();
  state.selected=null;
  $("accountsBody").innerHTML=$("txBody").innerHTML=$("categoriesBody").innerHTML=$("payeesBody").innerHTML=$("currenciesBody").innerHTML=$("recentBody").innerHTML="";
}
function indexBy(rows,key) {
  return new Map(rows.map(x => [String(x[key]),x]));
}
function fmtDate(v) {
  if(!v)
    return "";
  return String(v).slice(0,10);
}
function fmtAmount(v) {
  const n=Number(v);
  return Number.isFinite(n)? n.toLocaleString(undefined,{ maximumFractionDigits: 20 }):String(v??"");
}
function newBusinessId() {
  const used=new Set([...state.accounts.map(x => x.ACCOUNTID),...state.transactions.map(x => x.TRANSID),...state.categories.map(x => x.CATEGID),...state.payees.map(x => x.PAYEEID),...state.currencies.map(x => x.CURRENCYID)].map(String));
  let id;
  do {
    id=Date.now()*1000+crypto.getRandomValues(new Uint16Array(1))[0]%1000;
  } while(used.has(String(id)));
  return id;
}
export { state,$,INSTANCE_KEY,setStatus,esc,safeHttpUrl,tokenKey,showScreen,setView,clearWorkspace,indexBy,fmtDate,fmtAmount,newBusinessId };
