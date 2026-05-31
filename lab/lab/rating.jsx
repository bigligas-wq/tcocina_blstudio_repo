/* lab/rating.jsx — estrellas de interés + panel de señales (dev) */

const RATE_WORDS = ["¿cuánto la puntuás?","no tanto","puede ser","me gusta","me gusta mucho","la quiero ya"];

function StarRate({value=0, onRate, size=18, className=""}){
  const [hover,setHover] = React.useState(0);
  const shown = hover || value;
  return (
    <div className={"star-rate "+className}>
      <span className={"star-label"+(shown?" act":"")}>{RATE_WORDS[shown]}</span>
      <div className="stars" onMouseLeave={()=>setHover(0)}>
        {[1,2,3,4,5].map(n=>(
          <button key={n} type="button" className={"star"+(n<=shown?" on":"")}
            style={{width:size,height:size}}
            onMouseEnter={()=>setHover(n)}
            onClick={(e)=>{e.stopPropagation(); onRate(n===value?0:n);}}
            aria-label={n+" estrellas"}>
            <svg viewBox="0 0 24 24"><path d="M12 3.4l2.6 5.3 5.8.85-4.2 4.1.99 5.8L12 21.8 6.8 19.1l1-5.8-4.2-4.1 5.8-.85z"/></svg>
          </button>
        ))}
      </div>
    </div>
  );
}

function SignalsPanel({ratings, onClose, onClear}){
  const rows = IMPROVEMENTS
    .map(i=>({id:i.id, name:i.name, cat:i.cat, st:(ratings[i.id]&&ratings[i.id].stars)||0,
              at:ratings[i.id]&&ratings[i.id].at}))
    .sort((a,b)=>b.st-a.st);
  const rated = rows.filter(r=>r.st>0);
  const avg = rated.length ? (rated.reduce((s,r)=>s+r.st,0)/rated.length).toFixed(1) : "—";
  return (
    <div className="scrim" onClick={onClose}>
      <div className="modal lab-scroll" style={{width:"min(640px,100%)"}} onClick={e=>e.stopPropagation()}>
        <div className="modal-head">
          <div>
            <div className="dev-tag"><span className="bd"></span>solo vos · no lo ve el cliente</div>
            <h3 style={{fontFamily:"var(--font-display)",fontWeight:800,fontSize:22,margin:"8px 0 0"}}>Señales de interés</h3>
            <p style={{color:"var(--mut)",fontSize:13.5,margin:"6px 0 0"}}>Qué le llama más la atención, para afinar las próximas propuestas.</p>
          </div>
          <button className="modal-x" onClick={onClose}><Icon n="x"/></button>
        </div>
        <div className="modal-body">
          <div className="sig-stats">
            <div className="sig-stat"><div className="n">{rated.length}<span>/{rows.length}</span></div><div className="l">calificadas</div></div>
            <div className="sig-stat"><div className="n">{avg}<span>★</span></div><div className="l">promedio</div></div>
            <div className="sig-stat"><div className="n">{rows.filter(r=>r.st>=4).length}</div><div className="l">con 4★ o más</div></div>
          </div>
          <div className="sig-list">
            {rows.map(r=>{
              const meta = CAT_META[r.cat];
              return (
                <div className="sig-row" key={r.id} style={{opacity:r.st?1:.45}}>
                  <span className="sig-dot" style={{background:meta.color}}></span>
                  <span className="sig-name">{r.name}</span>
                  <div className="sig-bar"><i style={{width:(r.st/5*100)+"%"}}></i></div>
                  <span className="sig-val">{r.st? r.st+"★" : "—"}</span>
                </div>
              );
            })}
          </div>
          <div style={{display:"flex",justifyContent:"space-between",alignItems:"center",marginTop:16,
            paddingTop:14,borderTop:"1px solid var(--line)"}}>
            <span style={{fontFamily:"var(--font-mono)",fontSize:11,color:"var(--mut-2)"}}>se guarda en este navegador</span>
            <button className="btn btn-soft" onClick={onClear} style={{fontSize:12.5,padding:"8px 10px"}}>Borrar señales</button>
          </div>
        </div>
      </div>
    </div>
  );
}

Object.assign(window, {StarRate, SignalsPanel});
