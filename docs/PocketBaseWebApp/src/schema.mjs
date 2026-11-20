import { api } from "./api.mjs?v=20261120-ux5";
export const SCHEMA_VERSION="21";
const LEGACY_SCHEMA_VERSION="1";
const VERSION_KEY="MMEX_SCHEMA_VERSION";
export async function readSchemaVersion(base,token) {
  const filter=encodeURIComponent(`KEY = "${VERSION_KEY}"`);
  const result=await api(base,`/api/collections/dbInfo/records?perPage=2&filter=${filter}`,{},token);
  if(result.items.length>1)
    throw Error("Multiple schema version records found; inspect this instance.");
  return result.items[0]||null;
}
export function checkSchemaVersion(record,allowMissing=false,allowLegacy=false) {
  if(!record) {
    if(allowMissing)
      return;
    throw Error("This instance has no MMEX schema version. An administrator must inspect it in schema setup.");
  }
  if(record.VALUE!==SCHEMA_VERSION&&!(allowLegacy&&record.VALUE===LEGACY_SCHEMA_VERSION)) {
    throw Error(`Unsupported MMEX schema version ${record.VALUE}; this frontend supports version ${SCHEMA_VERSION}. No migration was performed.`);
  }
}
export async function requireCompatibleSchema(base,token) {
  checkSchemaVersion(await readSchemaVersion(base,token));
}
export function checkSchemaFields(actualCollections,expectedCollections) {
  const byName=new Map(actualCollections.map(collection => [collection.name,collection]));
  const problems=[];
  for(const expected of expectedCollections) {
    const actual=byName.get(expected.name);
    if(!actual||actual.type!=="base"||!Array.isArray(actual.fields)) {
      problems.push(`${expected.name} missing or not a base collection`);
      continue;
    }
    const fields=new Map(actual.fields.map(field => [field.name,field.type]));
    for(const field of expected.fields) {
      if(fields.get(field.name)!==field.type)
        problems.push(`${expected.name}.${field.name} missing/type mismatch`);
    }
  }
  if(problems.length)
    throw Error(`Existing schema mismatch; no changes made: ${problems.slice(0,12).join("; ")}`);
}
export async function markSchemaVersion(base,token,actualCollections,expectedCollections) {
  const current=await readSchemaVersion(base,token);
  checkSchemaVersion(current,true,true);
  if(current?.VALUE===LEGACY_SCHEMA_VERSION) {
    if(!Array.isArray(actualCollections)||!Array.isArray(expectedCollections)||!expectedCollections.length)
      throw Error("Cannot upgrade legacy schema without checking its collections.");
    checkSchemaFields(actualCollections,expectedCollections);
    if(!current.id)
      throw Error("Cannot upgrade legacy schema: version record ID is missing.");
    try {
      await api(base,`/api/collections/dbInfo/records/${encodeURIComponent(current.id)}`,{
        method: "PATCH",
        body: JSON.stringify({ VALUE: SCHEMA_VERSION })
      },token);
    }
    catch(error) {
      if(error.uncertain)
        throw Error(`The version update may have succeeded. Inspect the instance before retrying. ${error.message}`);
      throw error;
    }
    try {
      checkSchemaVersion(await readSchemaVersion(base,token));
    }
    catch(error) {
      throw Error(`The version update could not be verified. Inspect the instance before retrying. ${error.message}`);
    }
    return;
  }
  if(!current) {
    await api(base,"/api/collections/dbInfo/records",{
      method: "POST",
      body: JSON.stringify({ KEY: VERSION_KEY,VALUE: SCHEMA_VERSION })
    },token);
  }
}
