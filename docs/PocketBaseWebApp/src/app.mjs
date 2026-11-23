import { state,$,INSTANCE_KEY,showScreen,setView,setStatus,clearWorkspace } from "./core.mjs";
import { readStoredToken,saveStoredToken,clearStoredToken } from "./auth.mjs?v=20261123-payee-management";
import { api,normalizeUrl } from "./api.mjs?v=20261123-payee-management";
import { initWorkspace,loadWorkspace } from "./workspace.mjs?v=20261123-payee-management";
import { requireCompatibleSchema } from "./schema.mjs?v=20261123-payee-management";
import { resetCurrencyForm } from "./currencies.mjs?v=20261123-payee-management";
import { resetTransactionForm } from "./transactions.mjs?v=20261123-payee-management";
import "./setup.mjs?v=20261123-payee-management";
initWorkspace();
async function chooseInstance(value) {
  const base=normalizeUrl(value);
  await api(base,"/api/health");
  state.base=base;
  localStorage.setItem(INSTANCE_KEY,base);
  $("instanceUrl").value=base;
  showScreen("loginScreen");
  setStatus("loginStatus","PocketBase connection verified. Sign in with your MMEX user account.","ok");
}
$("instanceUrl").value=localStorage.getItem(INSTANCE_KEY)||"https://ab0yt2hi73hitsy.pwz5.pocketbasecloud.com";
$("instanceForm").addEventListener("submit",async (e) => {
  e.preventDefault();
  const b=$("connectButton");
  b.disabled=true;
  try {
    setStatus("instanceStatus","Checking PocketBase…");
    await chooseInstance($("instanceUrl").value);
  }
  catch(err) {
    setStatus("instanceStatus",err.message,"error");
  }
  finally {
    b.disabled=false;
  }
});
$("cancelInstance").addEventListener("click",() => showScreen("loginScreen"));
$("openChangeInstance").addEventListener("click",() => {
  $("instanceUrl").value=state.base;
  $("cancelInstance").classList.remove("hidden");
  showScreen("instanceScreen");
  setStatus("instanceStatus","");
});
$("workspaceChangeInstance").addEventListener("click",() => {
  logout(false);
  $("instanceUrl").value=state.base;
  $("cancelInstance").classList.remove("hidden");
  showScreen("instanceScreen");
});
$("openAdmin").addEventListener("click",() => {
  showScreen("adminScreen");
  setStatus("setupStatus","");
  $("schemaReport").textContent="Authenticate as a PocketBase Superuser to inspect the schema.";
});
$("adminBack").addEventListener("click",() => showScreen("loginScreen"));
async function signIn() {
  const a=await api(state.base,"/api/collections/users/auth-with-password",{ method: "POST",body: JSON.stringify({ identity: $("email").value.trim(),password: $("password").value }) });
  $("password").value="";
  await requireCompatibleSchema(state.base,a.token);
  state.token=a.token;
  state.user=a.record;
  saveStoredToken(a.token,$("rememberLogin").checked);
  $("userEmail").textContent=a.record.email;
  $("connectedInstance").textContent=state.base;
  showScreen("workspace");
  setView("dashboard");
  try {
    await loadWorkspace();
  }
  catch(err) {
    setStatus("recentStatus",err.message,"error");
  }
}
$("loginForm").addEventListener("submit",async (e) => {
  e.preventDefault();
  const b=$("loginButton");
  b.disabled=true;
  try {
    await signIn();
  }
  catch(err) {
    setStatus("loginStatus",err.message,"error");
  }
  finally {
    b.disabled=false;
  }
});
function logout(showLogin=true) {
  if(state.base)
    clearStoredToken();
  state.token="";
  state.user=null;
  clearWorkspace();
  resetTransactionForm();
  $("txSearch").value=$("txFrom").value=$("txThrough").value="";
  $("txType").value=$("txFilterAccount").value="";
  $("txShowDeleted").checked=false;
  setStatus("txActionStatus","");
  resetCurrencyForm();
  $("password").value="";
  if(showLogin) {
    showScreen("loginScreen");
    setStatus("loginStatus","Signed out. Your PocketBase instance is unchanged.");
  }
}
$("logout").addEventListener("click",() => logout(true));
(async () => {
  const saved=localStorage.getItem(INSTANCE_KEY)||$("instanceUrl").value;
  $("instanceUrl").value=saved;
  try {
    state.base=normalizeUrl(saved);
    await api(state.base,"/api/health");
    showScreen("loginScreen");
    const savedToken=readStoredToken();
    if(savedToken) {
      let me;
      try {
        me=await api(state.base,"/api/collections/users/auth-refresh",{ method: "POST" },savedToken.token);
      }
      catch(e) {
        if(e.status===401||e.status===403)
          clearStoredToken();
        setStatus("loginStatus","Session or schema check failed: "+e.message,"error");
        return;
      }
      try {
        await requireCompatibleSchema(state.base,me.token);
      }
      catch(e) {
        setStatus("loginStatus","Session or schema check failed: "+e.message,"error");
        return;
      }
      state.token=me.token;
      saveStoredToken(me.token,savedToken.remember);
      state.user=me.record;
      $("userEmail").textContent=me.record.email;
      $("connectedInstance").textContent=state.base;
      showScreen("workspace");
      try {
        await loadWorkspace();
      }
      catch(e) {
        setStatus("recentStatus",e.message,"error");
      }
    }
  }
  catch(e) {
    showScreen("instanceScreen");
    setStatus("instanceStatus",e.message,"error");
  }
})();
