import { api } from "./api.mjs";
export const SCHEMA_VERSION="1";
const VERSION_KEY="MMEX_SCHEMA_VERSION";
export async function readSchemaVersion(base,token) {
  const filter=encodeURIComponent(`KEY = "${VERSION_KEY}"`);
  const result=await api(base,`/api/collections/dbInfo/records?perPage=2&filter=${filter}`,{},token);
  if(result.items.length>1)
    throw Error("Multiple schema version records found; inspect this instance.");
  return result.items[0]||null;
}
export function checkSchemaVersion(record,allowMissing=false) {
  if(!record) {
    if(allowMissing)
      return;
    throw Error("This instance has no MMEX schema version. An administrator must inspect it in schema setup.");
  }
  if(record.VALUE!==SCHEMA_VERSION) {
    throw Error(`Unsupported MMEX schema version ${record.VALUE}; this frontend supports version ${SCHEMA_VERSION}. No migration was performed.`);
  }
}
export async function requireCompatibleSchema(base,token) {
  checkSchemaVersion(await readSchemaVersion(base,token));
}
export async function markSchemaVersion(base,token) {
  const current=await readSchemaVersion(base,token);
  checkSchemaVersion(current,true);
  if(!current) {
    await api(base,"/api/collections/dbInfo/records",{
      method: "POST",
      body: JSON.stringify({ KEY: VERSION_KEY,VALUE: SCHEMA_VERSION })
    },token);
  }
}
