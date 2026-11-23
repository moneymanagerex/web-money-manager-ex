import { api } from "./api.mjs?v=20261123-transfer-perspective";
import { readSchemaVersion,checkSchemaVersion } from "./schema.mjs?v=20261123-transfer-perspective";

export const MMEX_COLLECTIONS=["ACCOUNTLIST_V1","ASSETS_V1","ATTACHMENT_V1","BILLSDEPOSITS_V1","BUDGETSPLITTRANSACTIONS_V1","BUDGETTABLE_V1","BUDGETYEAR_V1","CATEGORY_V1","CHECKINGACCOUNT_V1","CURRENCYFORMATS_V1","CURRENCYHISTORY_V1","CUSTOMFIELDDATA_V1","CUSTOMFIELD_V1","INFOTABLE_V1","PAYEE_V1","REPORT_V1","SETTING_V1","SHAREINFO_V1","SPLITTRANSACTIONS_V1","STOCKHISTORY_V1","STOCK_V1","TAGLINK_V1","TAG_V1","TRANSLINK_V1","USAGE_V1","dbInfo"];

export async function listCollections(base,token) {
  const collections=[];
  let page=1,result;
  do {
    result=await api(base,`/api/collections?perPage=200&page=${page}`,{},token);
    if(!Array.isArray(result.items)||!Number.isInteger(result.totalPages))
      throw Error("Invalid collection listing from PocketBase.");
    collections.push(...result.items);
    page++;
  } while(page<=result.totalPages);
  return collections;
}
export async function loadBundledSchema() {
  const response=await fetch("schema-import.json");
  if(!response.ok)
    throw Error("Could not load schema-import.json from this frontend host.");
  const schema=await response.json();
  if(!Array.isArray(schema)||schema.length!==MMEX_COLLECTIONS.length||MMEX_COLLECTIONS.some(name => !schema.some(collection => collection.name===name)))
    throw Error("Bundled schema does not match the expected MMEX collection set.");
  return schema;
}
export async function inspectReset(base,token) {
  const collections=await listCollections(base,token);
  const targets=collections.filter(c => MMEX_COLLECTIONS.includes(c.name));
  if(targets.some(c => c.type!=="base"||c.system||!c.id))
    throw Error("An MMEX-named collection is not a regular base collection. Inspect it in PocketBase before resetting.");
  const ids=new Set(targets.map(c => c.id));
  for(const collection of targets) {
    if(!Array.isArray(collection.fields))
      throw Error(`Cannot inspect fields of ${collection.name}.`);
    if(collection.fields.some(f => f.type==="relation"&&ids.has(f.collectionId)))
      throw Error(`Collection ${collection.name} has an MMEX relation. Resolve the dependency before resetting.`);
  }
  for(const collection of collections.filter(c => !MMEX_COLLECTIONS.includes(c.name))) {
    if(!Array.isArray(collection.fields))
      throw Error(`Cannot inspect fields of ${collection.name}.`);
    if(collection.fields.some(f => f.type==="relation"&&ids.has(f.collectionId)))
      throw Error(`Collection ${collection.name} references an MMEX collection. Resolve the dependency before resetting.`);
  }
  if(targets.some(c => c.name==="dbInfo"))
    checkSchemaVersion(await readSchemaVersion(base,token),true,true);
  const entries=await Promise.all(targets.map(async c => {
    const result=await api(base,`/api/collections/${encodeURIComponent(c.id)}/records?perPage=1`,{},token);
    if(!Number.isSafeInteger(result.totalItems)||result.totalItems<0)
      throw Error(`Cannot determine record count for ${c.name}.`);
    return { name: c.name,id: c.id,count: result.totalItems };
  }));
  entries.sort((a,b) => a.name.localeCompare(b.name));
  return { base,entries };
}
export async function resetCollections(base,token,preview,schema,defaults,onProgress) {
  if(preview.base!==base||!preview.entries.length)
    throw Error("Inspect this instance before resetting it.");
  const current=await inspectReset(base,token);
  if(JSON.stringify(current)!==JSON.stringify(preview))
    throw Error("Collections or record counts changed since inspection. Inspect again before resetting.");
  let deleted=0;
  for(const entry of current.entries) {
    onProgress(`Deleting ${entry.name} (${deleted+1}/${current.entries.length})…`);
    try {
      await api(base,`/api/collections/${encodeURIComponent(entry.id)}`,{ method: "DELETE" },token);
      deleted++;
    }
    catch(error) {
      throw Error(`Reset stopped after ${deleted} confirmed deletions; deletion of ${entry.name} may have succeeded. Inspect the instance and restore the backup or inspect and reset again. ${error.message}`);
    }
  }
  try {
    onProgress("Creating fresh MMEX collections…");
    await api(base,"/api/collections/import",{ method: "PUT",body: JSON.stringify({ collections: schema,deleteMissing: false }) },token);
    if(defaults) {
      onProgress("Importing default categories and currencies…");
      const { seedDefaults }=await import("./seed.mjs");
      await seedDefaults(base,token,defaults);
    }
  }
  catch(error) {
    throw Error(`MMEX collections were deleted, but reinitialization did not complete. Inspect the instance and restore the backup or inspect and reset again. ${error.message}`);
  }
}
