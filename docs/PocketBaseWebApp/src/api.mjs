import { state } from "./core.mjs";
function normalizeUrl(v) {
  const u=new URL(v.trim());
  if(!["http:","https:"].includes(u.protocol)||u.username||u.password)
    throw Error("Enter a valid http(s) PocketBase URL without credentials.");
  if(u.protocol==="http:"&&!["localhost","127.0.0.1","[::1]"].includes(u.hostname))
    throw Error("Use HTTPS for remote PocketBase instances.");
  u.hash="";
  u.search="";
  return u.href.replace(/\/+$/,"");
}
async function api(base,path,options={},token="") {
  const headers={ ...(options.headers||{}) };
  if(options.body&&!headers["Content-Type"])
    headers["Content-Type"]="application/json";
  if(token)
    headers.Authorization=token;
  let r;
  try {
    r=await fetch(base+path,{ ...options,headers });
  }
  catch(e) {
    const error=Error("Cannot reach PocketBase. Check URL, server status, network and CORS. "+e.message);
    error.uncertain=true;
    throw error;
  }
  const d=await r.json().catch(() => ({}));
  if(!r.ok) {
    const details=Object.entries(d.data||{}).map(([field,error]) => `${field}: ${error.message||error.code}`).join("; ");
    const error=Error((d.message||`HTTP ${r.status}`)+(details? ` ${details}`:""));
    error.uncertain=r.status>=500;
    throw error;
  }
  return d;
}
async function fetchRecords(collection,sort="",filter="") {
  let url=`/api/collections/${collection}/records?perPage=200`;
  if(sort)
    url+="&sort="+encodeURIComponent(sort);
  if(filter)
    url+="&filter="+encodeURIComponent(filter);
  const items=[];
  let page=1,result;
  do {
    result=await api(state.base,url+"&page="+page,{},state.token);
    items.push(...result.items);
    page++;
  } while(page<=result.totalPages);
  return items;
}
export { normalizeUrl,api,fetchRecords };
