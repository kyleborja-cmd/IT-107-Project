let chart;
async function refreshSecurity(){
 const p=new URLSearchParams({username:document.querySelector('#filter-user').value,ip:document.querySelector('#filter-ip').value,event:document.querySelector('#filter-event').value});
 const r=await fetch('php/admin_data.php?'+p,{credentials:'same-origin',cache:'no-store'}); if(!r.ok)return;
 const d=await r.json();
 const labels=['successes','failures','locked','blocked','otp_sent','otp_failed','alerts'];
 const names=['Successful log-ins','Failed log-ins','Locked accounts','Blocked IPs','OTPs sent','OTPs failed','Alert emails'];
 document.querySelector('#admin-data').innerHTML='<div class="stats">'+labels.map((k,i)=>`<article><small>${names[i]}</small><b>${Number(d.summary[k]||0)}</b><span>Today / current</span></article>`).join('')+'</div>';
 const ctx=document.querySelector('#failsChart');
 const data={labels:d.hours.map(x=>x.label),datasets:[{label:'Failed log-ins',data:d.hours.map(x=>Number(x.total)),borderWidth:2}]};
 if(chart)chart.destroy(); chart=new Chart(ctx,{type:'bar',data,options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
 document.querySelector('#top-ips').innerHTML='<table><tr><th>IP address</th><th>Failures</th><th>Status</th></tr>'+d.ips.map(x=>`<tr><td>${esc(x.ip_address)}</td><td>${x.failures}</td><td>${esc(x.status)}</td></tr>`).join('')+'</table>';
 document.querySelector('#flags').innerHTML=d.flags.length?d.flags.map(x=>`<p><b>${esc(x.severity)} · ${esc(x.pattern_key)}</b> — ${esc(x.target_value)}<br><small>${esc(x.details)}</small></p>`).join(''):'<p>No flags recorded.</p>';
 document.querySelector('#events').innerHTML='<table><tr><th>Time</th><th>Event</th><th>Username</th><th>IP</th><th>Details</th></tr>'+d.events.map(x=>`<tr><td>${esc(x.created_at)}</td><td>${esc(x.event_type)}</td><td>${esc(x.username_typed||'—')}</td><td>${esc(x.ip_address)}</td><td>${esc(x.details||'')}</td></tr>`).join('')+'</table>';
}
function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
['#filter-user','#filter-ip','#filter-event'].forEach(s=>document.querySelector(s).addEventListener('input',refreshSecurity));
refreshSecurity();setInterval(refreshSecurity,60000);
