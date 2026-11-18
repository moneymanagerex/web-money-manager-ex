import { fmtDate } from "./core.mjs";

function localToday() {
  const now=new Date();
  return new Date(now.getTime()-now.getTimezoneOffset()*60000).toISOString().slice(0,10);
}
function decimal(value) {
  if(value===null||value===undefined||value===""||!Number.isFinite(Number(value)))
    throw Error("Invalid monetary amount");
  const text=String(value).replace(/^([+-]?)\./,"$10.");
  if(text.length>800)
    throw Error("Monetary amount is too long");
  const match=text.match(/^([+-]?)(\d+)(?:\.(\d*))?(?:e([+-]?\d+))?$/i);
  if(!match)
    throw Error("Invalid monetary amount");
  const fraction=match[3]||"",exponent=Number(match[4]||0);
  if(Math.abs(exponent)>400)
    throw Error("Monetary amount exponent is out of range");
  let scale=fraction.length-exponent;
  let units=BigInt((match[1]==="-"? "-":"")+match[2]+fraction);
  if(scale<0) {
    units*=10n**BigInt(-scale);
    scale=0;
  }
  return { units,scale };
}
function add(total,value,sign=1n) {
  const n=decimal(value),scale=Math.max(total.scale,n.scale);
  return { units: total.units*10n**BigInt(scale-total.scale)+sign*n.units*10n**BigInt(scale-n.scale),scale };
}
function parseStoredNumber(value) {
  const entered=decimal(value),number=Number(value),stored=decimal(number);
  const scale=Math.max(entered.scale,stored.scale);
  if(entered.units*10n**BigInt(scale-entered.scale)!==stored.units*10n**BigInt(scale-stored.scale))
    throw Error("Amount exceeds the supported numeric precision.");
  return number;
}
function formatDecimal(value,currency) {
  let { units,scale }=value;
  const negative=units<0n;
  if(negative)
    units=-units;
  let digits=units.toString().padStart(scale+1,"0");
  let whole=scale? digits.slice(0,-scale):digits;
  let fraction=scale? digits.slice(-scale):"";
  while(fraction.endsWith("0"))
    fraction=fraction.slice(0,-1);
  const currencyScale=Number(currency?.SCALE);
  const places=Number.isSafeInteger(currencyScale)&&/^10*$/.test(String(currencyScale))? String(currencyScale).length-1:0;
  fraction=fraction.padEnd(places,"0");
  whole=BigInt(whole).toLocaleString();
  const separator=new Intl.NumberFormat().formatToParts(1.1).find(x => x.type==="decimal")?.value||".";
  return (negative? "-":"")+whole+(fraction? separator+fraction:"");
}
function formatTransactionAmount(value) {
  try {
    return formatDecimal(decimal(value));
  }
  catch {
    return "Invalid amount";
  }
}
function accountBalance(account,transactions,today=localToday()) {
  let total=add({ units: 0n,scale: 0 },account.INITIALBAL??0);
  const id=String(account.ACCOUNTID);
  for(const t of transactions) {
    if(t.DELETEDTIME||t.STATUS==="V")
      continue;
    const source=String(t.ACCOUNTID)===id,destination=String(t.TOACCOUNTID)===id;
    if(!source&&!destination)
      continue;
    const date=fmtDate(t.TRANSDATE);
    if(!/^\d{4}-\d{2}-\d{2}$/.test(date)||Number.isNaN(Date.parse(date))||new Date(date).toISOString().slice(0,10)!==date)
      throw Error(`Transaction ${t.TRANSID} has an invalid date`);
    if(date>today)
      continue;
    if(!["","R","F","D"].includes(t.STATUS??""))
      throw Error(`Transaction ${t.TRANSID} has an unsupported status`);
    if(t.TRANSCODE==="Withdrawal"&&source)
      total=add(total,t.TRANSAMOUNT,-1n);
    else if(t.TRANSCODE==="Deposit"&&source)
      total=add(total,t.TRANSAMOUNT);
    else if(t.TRANSCODE==="Transfer"&&String(t.ACCOUNTID)!==String(t.TOACCOUNTID)) {
      if(source)
        total=add(total,t.TRANSAMOUNT,-1n);
      if(destination)
        total=add(total,t.TOTRANSAMOUNT);
    }
    else if(!["Withdrawal","Deposit","Transfer"].includes(t.TRANSCODE))
      throw Error(`Transaction ${t.TRANSID} has an unsupported type`);
  }
  return total;
}
export { accountBalance,formatDecimal,formatTransactionAmount,localToday,parseStoredNumber };
