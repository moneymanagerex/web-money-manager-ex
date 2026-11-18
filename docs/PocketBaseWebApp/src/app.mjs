import { state,$,INSTANCE_KEY,tokenKey,showScreen,setView,setStatus,clearWorkspace } from "./core.mjs";
import { api,normalizeUrl } from "./api.mjs";
import { initWorkspace,loadWorkspace } from "./workspace.mjs";
import { requireCompatibleSchema } from "./schema.mjs";
import { resetCurrencyForm } from "./currencies.mjs";
import { resetTransactionForm } from "./transactions.mjs";
import "./setup.mjs?v=20261118-reset";
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
  sessionStorage.setItem(tokenKey(),a.token);
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
    sessionStorage.removeItem(tokenKey());
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
    const tok=sessionStorage.getItem(tokenKey());
    if(tok) {
      let me;
      try {
        me=await api(state.base,"/api/collections/users/auth-refresh",{ method: "POST" },tok);
        await requireCompatibleSchema(state.base,me.token);
      }
      catch(e) {
        sessionStorage.removeItem(tokenKey());
        setStatus("loginStatus","Session or schema check failed: "+e.message,"error");
        return;
      }
      state.token=me.token;
      sessionStorage.setItem(tokenKey(),me.token);
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
