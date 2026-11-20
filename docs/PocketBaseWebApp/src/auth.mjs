import { tokenKey } from "./core.mjs";

export function readStoredToken() {
  const session=sessionStorage.getItem(tokenKey());
  if(session)
    return { token: session,remember: false };
  const persistent=localStorage.getItem(tokenKey());
  return persistent? { token: persistent,remember: true }:null;
}
export function saveStoredToken(token,remember) {
  const target=remember? localStorage:sessionStorage;
  const other=remember? sessionStorage:localStorage;
  target.setItem(tokenKey(),token);
  other.removeItem(tokenKey());
}
export function clearStoredToken() {
  sessionStorage.removeItem(tokenKey());
  localStorage.removeItem(tokenKey());
}
