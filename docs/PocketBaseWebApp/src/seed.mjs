import { api } from "./api.mjs";

export async function loadDefaultData() {
  const response = await fetch("defaults.json");
  if (!response.ok) throw Error(`Unable to load default data (HTTP ${response.status}).`);
  const defaults = await response.json();
  const names = ["CATEGORY_V1", "CURRENCYFORMATS_V1"];
  for (const name of names) {
    if (!Array.isArray(defaults[name]) || !defaults[name].length) {
      throw Error(`Default data is missing for ${name}.`);
    }
  }
  return defaults;
}

export async function seedDefaults(base, token, defaults = null) {
  defaults ??= await loadDefaultData();
  const names = ["CATEGORY_V1", "CURRENCYFORMATS_V1"];
  for (const name of names) {
    if (!Array.isArray(defaults[name]) || !defaults[name].length) {
      throw Error(`Default data is missing for ${name}.`);
    }
    const existing = await api(base, `/api/collections/${name}/records?perPage=1`, {}, token);
    if (existing.totalItems !== 0) throw Error(`${name} is not empty. No default data was imported.`);
  }
  let inserted = 0;
  try {
    for (const name of names) {
      for (const row of defaults[name]) {
        await api(base, `/api/collections/${name}/records`, {
          method: "POST", body: JSON.stringify(row)
        }, token);
        inserted++;
      }
    }
  } catch (error) {
    throw Error(`Default import stopped after ${inserted} records: ${error.message}. Inspect the partial data before retrying; setup will not automatically import into an existing schema.`);
  }
}
