import { state,$,setStatus,esc,showScreen } from "./core.mjs";
import { api } from "./api.mjs";
import { readSchemaVersion,checkSchemaVersion,markSchemaVersion } from "./schema.mjs";
import { seedDefaults } from "./seed.mjs";
const MMEX_COLLECTIONS=["ACCOUNTLIST_V1","ASSETS_V1","ATTACHMENT_V1","BILLSDEPOSITS_V1","BUDGETSPLITTRANSACTIONS_V1","BUDGETTABLE_V1","BUDGETYEAR_V1","CATEGORY_V1","CHECKINGACCOUNT_V1","CURRENCYFORMATS_V1","CURRENCYHISTORY_V1","CUSTOMFIELDDATA_V1","CUSTOMFIELD_V1","INFOTABLE_V1","PAYEE_V1","REPORT_V1","SETTING_V1","SHAREINFO_V1","SPLITTRANSACTIONS_V1","STOCKHISTORY_V1","STOCK_V1","TAGLINK_V1","TAG_V1","TRANSLINK_V1","USAGE_V1","dbInfo"];
async function listCollections(token) {
  const collections=[];
  let page=1,result;
  do {
    result=await api(state.base,`/api/collections?perPage=200&page=${page}`,{},token);
    collections.push(...result.items);
    page++;
  } while(page<=result.totalPages);
  return collections;
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
function checkFields(actualCollections,expectedCollections) {
  const byName=new Map(actualCollections.map(collection => [collection.name,collection]));
  const problems=[];
  for(const expected of expectedCollections) {
    const fields=new Map((byName.get(expected.name).fields||[]).map(field => [field.name,field.type]));
    for(const field of expected.fields) {
      if(fields.get(field.name)!==field.type)
        problems.push(`${expected.name}.${field.name} missing/type mismatch`);
    }
  }
  if(problems.length)
    throw Error(`Existing schema mismatch; no changes made: ${problems.slice(0,12).join("; ")}`);
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
  const button=$("initializeButton");
  button.disabled=true;
  try {
    setStatus("setupStatus","Authenticating PocketBase superuser…");
    const auth=await api(state.base,"/api/collections/_superusers/auth-with-password",{
      method: "POST",
      body: JSON.stringify({ identity: $("adminEmail").value.trim(),password: $("adminPassword").value })
    });
    const token=auth.token;
    const collections=await listCollections(token);
    const { found,missing }=renderReport(collections);
    if(missing.length!==0&&missing.length!==MMEX_COLLECTIONS.length) {
      throw Error("Partial MMEX schema detected. No changes were made.");
    }
    if(found.length)
      checkSchemaVersion(await readSchemaVersion(state.base,token),true);
    const response=await fetch("schema-import.json");
    if(!response.ok)
      throw Error("Could not load schema-import.json from this frontend host.");
    const schema=await response.json();
    if(schema.length!==MMEX_COLLECTIONS.length||MMEX_COLLECTIONS.some(name => !schema.some(collection => collection.name===name))) {
      throw Error("Bundled schema does not match the expected MMEX collection set.");
    }
    if(found.length) {
      checkFields(collections,schema);
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
    await markSchemaVersion(state.base,token);
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
    button.disabled=false;
  }
});
