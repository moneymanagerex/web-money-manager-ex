import { state,$,setStatus,esc,showScreen } from "./core.mjs";
import { api } from "./api.mjs?v=20261123-investments-assets";
import { readSchemaVersion,checkSchemaVersion,ensureSchemaFields,markSchemaVersion } from "./schema.mjs?v=20261123-investments-assets";
import { seedDefaults,loadDefaultData } from "./seed.mjs?v=20261123-investments-assets";
import { MMEX_COLLECTIONS,listCollections,loadBundledSchema,inspectReset,resetCollections } from "./schema-reset.mjs?v=20261123-investments-assets";
let resetPreview=null;
function clearResetPreview() {
  resetPreview=null;
  $("resetPreview").textContent="";
  $("resetPhrase").textContent="shown after inspection";
  $("resetBackup").checked=false;
  $("resetConfirm").value="";
}
function setBusy(busy) {
  $("initializeButton").disabled=busy;
  $("inspectResetButton").disabled=busy;
  $("adminBack").disabled=busy;
  $("resetButton").disabled=busy||!resetPreview?.entries.length;
}
async function authenticateAdmin() {
  if(!$("adminEmail").value.trim()||!$("adminEmail").validity.valid||!$("adminPassword").value)
    throw Error("Enter a valid superuser email and password above.");
  return api(state.base,"/api/collections/_superusers/auth-with-password",{
    method: "POST",
    body: JSON.stringify({ identity: $("adminEmail").value.trim(),password: $("adminPassword").value })
  });
}
function renderReport(collections) {
  const names=new Set(collections.map(collection => collection.name));
  const found=MMEX_COLLECTIONS.filter(name => names.has(name));
  const missing=MMEX_COLLECTIONS.filter(name => !names.has(name));
  let html=`<p><b>MMEX collections:</b> ${found.length}/${MMEX_COLLECTIONS.length}</p>`;
  if(missing.length===MMEX_COLLECTIONS.length) {
    html+='<div class="success">No MMEX collections found. Fresh initialization is available.</div>';
  }
  else if(missing.length===0) {
    html+='<div class="success">All MMEX collections exist. Setup will check fields and version without re-importing them.</div>';
  }
  else {
    html+=`<div class="warning">Partial schema detected. Missing: ${missing.map(esc).join(", ")}. Automatic initialization is disabled.</div>`;
  }
  $("schemaReport").innerHTML=html;
  return { found,missing };
}
async function ensureUserCollection(collections,token) {
  const users=collections.find(collection => collection.name==="users");
  if(users) {
    if(users.type!=="auth")
      throw Error("The users collection is not an authentication collection.");
    return;
  }
  await api(state.base,"/api/collections",{
    method: "POST",
    body: JSON.stringify({
      name: "users",type: "auth",listRule: "id = @request.auth.id",
      viewRule: "id = @request.auth.id",createRule: null,
      updateRule: "id = @request.auth.id",deleteRule: "id = @request.auth.id"
    })
  },token);
}
$("setupForm").addEventListener("submit",async (event) => {
  event.preventDefault();
  clearResetPreview();
  setBusy(true);
  try {
    setStatus("setupStatus","Authenticating PocketBase superuser…");
    const auth=await authenticateAdmin();
    const token=auth.token;
    const collections=await listCollections(state.base,token);
    const { found,missing }=renderReport(collections);
    if(missing.length!==0&&missing.length!==MMEX_COLLECTIONS.length) {
      throw Error("Partial MMEX schema detected. No changes were made.");
    }
    if(found.length)
      checkSchemaVersion(await readSchemaVersion(state.base,token),true,true);
    const schema=await loadBundledSchema();
    let currentCollections=collections;
    if(found.length) {
      setStatus("setupStatus","Checking collections and PocketBase audit fields…");
      const ensured=await ensureSchemaFields(state.base,token,collections,schema,() => listCollections(state.base,token));
      currentCollections=ensured.collections;
      if(ensured.applied.length)
        setStatus("setupStatus",`Added PocketBase created/updated fields to ${ensured.applied.length} collection(s). Existing records keep empty timestamps.`);
    }
    else {
      setStatus("setupStatus",`Creating ${schema.length} collections and indexes…`);
      await api(state.base,"/api/collections/import",{
        method: "PUT",body: JSON.stringify({ collections: schema,deleteMissing: false })
      },token);
      if($("seedDefaults").checked)
        await seedDefaults(state.base,token);
    }
    await ensureUserCollection(collections,token);
    const users=await api(state.base,"/api/collections/users/records?perPage=1",{},token);
    if(users.totalItems===0) {
      const email=$("newUserEmail").value.trim(),password=$("newUserPassword").value;
      if(!email||password.length<10)
        throw Error("No MMEX user exists. Enter the first user's email and a password of at least 10 characters.");
      await api(state.base,"/api/collections/users/records",{
        method: "POST",body: JSON.stringify({ email,password,passwordConfirm: password,verified: true })
      },token);
    }
    await markSchemaVersion(state.base,token,found.length? collections:undefined,found.length? schema:undefined);
    $("adminPassword").value=$("newUserPassword").value=$("adminEmail").value="";
    showScreen("loginScreen");
    if($("newUserEmail").value)
      $("email").value=$("newUserEmail").value.trim();
    setStatus("loginStatus","Schema inspection completed. Sign in with your MMEX user.","ok");
  }
  catch(error) {
    $("adminPassword").value="";
    setStatus("setupStatus",error.message,"error");
  }
  finally {
    setBusy(false);
  }
});

$("inspectResetButton").addEventListener("click",async () => {
  clearResetPreview();
  setBusy(true);
  setStatus("resetStatus","");
  try {
    setStatus("inspectResetStatus","Authenticating and inspecting MMEX collections…");
    const { token }=await authenticateAdmin();
    const preview=await inspectReset(state.base,token);
    resetPreview=preview;
    if(preview.entries.length)
      $("resetPhrase").textContent=`RESET ${preview.base}`;
    $("resetPreview").innerHTML=preview.entries.length? `<p>Will delete ${preview.entries.length} collections on <strong>${esc(preview.base)}</strong>:</p><ul>${preview.entries.map(c => `<li>${esc(c.name)}: ${c.count} record(s)</li>`).join("")}</ul>`:"No MMEX collections exist. Use normal initialization instead.";
    setStatus("inspectResetStatus",preview.entries.length? "Review the list, confirm the backup, type the exact phrase, and re-enter the superuser password above.":"");
  }
  catch(error) {
    setStatus("inspectResetStatus",error.message,"error");
  }
  finally {
    $("adminPassword").value="";
    setBusy(false);
  }
});

$("resetButton").addEventListener("click",async () => {
  if(!resetPreview||resetPreview.base!==state.base||!$("resetBackup").checked||$("resetConfirm").value!==`RESET ${state.base}`) {
    setStatus("resetStatus","Inspect this instance, confirm the backup, and type the exact reset phrase.","error");
    return;
  }
  const importDefaults=$("seedDefaults").checked;
  const firstEmail=$("newUserEmail").value.trim(),firstPassword=$("newUserPassword").value,validFirstEmail=$("newUserEmail").validity.valid;
  setBusy(true);
  let resetStarted=false;
  try {
    setStatus("resetStatus","Authenticating and checking prerequisites…");
    const { token }=await authenticateAdmin();
    const [schema,defaults,collections]=await Promise.all([loadBundledSchema(),importDefaults? loadDefaultData():Promise.resolve(null),listCollections(state.base,token)]);
    const users=collections.find(c => c.name==="users");
    if(users&&users.type!=="auth")
      throw Error("The users collection is not an authentication collection.");
    const userCount=users? (await api(state.base,"/api/collections/users/records?perPage=1",{},token)).totalItems:0;
    if(!Number.isSafeInteger(userCount)||userCount<0)
      throw Error("Cannot determine the number of users.");
    if(userCount===0&&(!firstEmail||!validFirstEmail||firstPassword.length<10))
      throw Error("No MMEX user exists. Enter the first user's email and a password of at least 10 characters above before resetting.");
    resetStarted=true;
    await resetCollections(state.base,token,resetPreview,schema,defaults,msg => setStatus("resetStatus",msg));
    await ensureUserCollection(collections,token);
    if(userCount===0) {
      await api(state.base,"/api/collections/users/records",{ method: "POST",body: JSON.stringify({ email: firstEmail,password: firstPassword,passwordConfirm: firstPassword,verified: true }) },token);
    }
    await markSchemaVersion(state.base,token);
    clearResetPreview();
    $("newUserPassword").value="";
    $("schemaReport").textContent="MMEX schema reinitialized. Inspect again to view its current state.";
    setStatus("resetStatus","MMEX schema reinitialized. Existing users and unrelated collections were preserved.","ok");
  }
  catch(error) {
    if(resetStarted) {
      clearResetPreview();
    }
    setStatus("resetStatus",resetStarted? `${error.message} Reset is not transactional; inspect the instance before continuing.`:error.message,"error");
  }
  finally {
    $("adminPassword").value="";
    setBusy(false);
  }
});
$("adminBack").addEventListener("click",() => {
  clearResetPreview();
  setStatus("inspectResetStatus","");
  setStatus("resetStatus","");
  $("adminPassword").value="";
  setBusy(false);
});
