/* lab/modal.jsx — preview antes/después + idea + carrito + loader */

function PreviewModal({imp, inCart, labels, rating, onRate, onClose, onAdd}){
  const [view,setView] = React.useState("despues");
  const meta = CAT_META[imp.cat];
  React.useEffect(()=>{
    const h = e=>{ if(e.key==="Escape") onClose(); };
    window.addEventListener("keydown",h); return ()=>window.removeEventListener("keydown",h);
  },[]);
  return (
    <div className="scrim" onClick={onClose}>
      <div className="modal lab-scroll" onClick={e=>e.stopPropagation()}>
        <div className="modal-head">
          <div className="tagrow" style={{margin:0,display:"flex",gap:8,alignItems:"center",flexWrap:"wrap"}}>
            <span className="cat-ic" style={{width:42,height:42}}><Icon n={imp.icon}/></span>
            <div>
              <h3 style={{fontFamily:"var(--font-display)",fontWeight:800,fontSize:22,margin:0,letterSpacing:"-.01em"}}>{imp.name}</h3>
              <span className="badge cat" style={{marginTop:6}}><span className="bd" style={{background:meta.color}}></span>{meta.label}</span>
            </div>
          </div>
          <button className="modal-x" onClick={onClose}><Icon n="x"/></button>
        </div>
        <div className="modal-body">
          <div className="ba-toggle">
            <button className={view==="antes"?"on":""} onClick={()=>setView("antes")}>Antes</button>
            <button className={view==="despues"?"on":""} onClick={()=>setView("despues")}>Después</button>
          </div>
          <div className="ba">
            <div className="pane" style={{background:imp.before,opacity:view==="antes"?1:0}}>
              <span className="ptag">como está hoy</span>
            </div>
            <div className="pane" style={{background:imp.after,opacity:view==="despues"?1:0}}>
              <span className="ptag" style={{background:"rgba(255,76,12,.85)",borderColor:"transparent"}}>con la mejora</span>
              <Lottie src="assets/lottie/atom.json" style={{position:"absolute",right:16,bottom:14,width:90,height:90,opacity:.85}}/>
            </div>
          </div>
          <p style={{fontSize:15,lineHeight:1.6,color:"var(--ink-2)",margin:"4px 0 18px"}}>{imp.long}</p>
          <div className="diffs" style={{marginBottom:22}}>
            {imp.diffs.map((d,i)=>(<div className="diff" key={i}><span className="d" style={{background:d.color}}></span>{d.t}</div>))}
          </div>
          <div style={{textAlign:"center",margin:"4px 0 20px",padding:"18px 0",borderTop:"1px solid var(--line)",borderBottom:"1px solid var(--line)"}}>
            <div style={{fontFamily:"var(--font-mono)",fontSize:12,color:"var(--mut)",marginBottom:12}}>¿qué tan a tu medida la ves?</div>
            <StarRate className="big" value={rating} onRate={(n)=>onRate(imp.id,n)} size={28}/>
          </div>
          <div style={{display:"flex",alignItems:"center",justifyContent:"space-between",gap:16,flexWrap:"wrap"}}>
            <div className="price big">
              <div className="amt"><span>USD </span>{imp.price}</div>
              <div className="meta">pago único · si te sirve, queda online en 24 h</div>
            </div>
            <button className={"btn btn-primary"+(inCart?" added":"")} onClick={()=>onAdd(imp)}>
              {inCart ? <><Icon n="check" style={{width:18,height:18}}/>{labels.added}</> : <>{labels.previewAdd}<Icon n="arrow" style={{width:18,height:18}}/></>}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function IdeaModal({onClose}){
  const [txt,setTxt] = React.useState("");
  const [sent,setSent] = React.useState(false);
  return (
    <div className="scrim" onClick={onClose}>
      <div className="modal" style={{width:"min(560px,100%)"}} onClick={e=>e.stopPropagation()}>
        <div className="modal-head">
          <div style={{display:"flex",gap:12,alignItems:"center"}}>
            <span className="cat-ic" style={{width:42,height:42,background:"rgba(245,166,35,.12)",color:"var(--amber)"}}><Icon n="bulb"/></span>
            <h3 style={{fontFamily:"var(--font-display)",fontWeight:800,fontSize:21,margin:0}}>Tirame la idea</h3>
          </div>
          <button className="modal-x" onClick={onClose}><Icon n="x"/></button>
        </div>
        <div className="modal-body">
          {!sent ? <>
            <p style={{fontSize:14.5,color:"var(--mut)",margin:"0 0 12px",lineHeight:1.55}}>
              ¿Algo que te gustaría cambiar o sumar? No hace falta que sea técnico ni prolijo. Escribilo como te salga.
            </p>
            <textarea className="idea-ta" maxLength={400} value={txt} onChange={e=>setTxt(e.target.value)}
              placeholder="Ej: me gustaría que el cliente pueda repetir el último pedido con un toque…"/>
            <div style={{display:"flex",alignItems:"center",justifyContent:"space-between",marginTop:6}}>
              <span className="cnote">{txt.length}/400</span>
              <button className="btn btn-primary" disabled={!txt.trim()} style={{opacity:txt.trim()?1:.5}} onClick={()=>setSent(true)}>
                Mandársela a Bruno<Icon n="arrow" style={{width:18,height:18}}/>
              </button>
            </div>
          </> : <div style={{textAlign:"center",padding:"14px 0 6px"}}>
            <div style={{width:54,height:54,borderRadius:16,margin:"0 auto 14px",display:"grid",placeItems:"center",
              background:"rgba(62,207,142,.14)",color:"var(--green)"}}><Icon n="check" style={{width:26,height:26}}/></div>
            <h3 style={{fontFamily:"var(--font-display)",fontWeight:800,fontSize:20,margin:"0 0 6px"}}>¡Anotada!</h3>
            <p style={{color:"var(--mut)",fontSize:14.5,margin:"0 0 18px"}}>Le llega a Bruno y, si tiene sentido, la vas a ver acá con su precio.</p>
            <button className="btn btn-ghost" onClick={onClose}>Listo</button>
          </div>}
        </div>
      </div>
    </div>
  );
}

function CartDock({count, total, freeOff, onOpen}){
  if(count===0) return null;
  return (
    <div className="cart-dock">
      <div className="ci"><b>{count}</b> {count===1?"mejora elegida":"mejoras elegidas"}
        {freeOff>0 && <span style={{color:"var(--lime-soft)"}}> · primera gratis</span>}</div>
      <div className="sep"></div>
      <div className="ctot"><span>USD </span>{total}</div>
      <button className="btn btn-primary" onClick={onOpen}>Ver lo que elegí<Icon n="arrow" style={{width:18,height:18}}/></button>
    </div>
  );
}

function Loader({onDone}){
  const [gone,setGone] = React.useState(false);
  React.useEffect(()=>{
    const t = setTimeout(()=>{ setGone(true); setTimeout(onDone,750); }, 2300);
    return ()=>clearTimeout(t);
  },[]);
  return (
    <div className={"loader"+(gone?" gone":"")}>
      <div className="loader-inner">
        <div className="lglow"></div>
        <Lottie src="assets/lottie/atom.json" className="loader-atom" loop={false}/>
        <div className="ltxt">destilando ideas</div>
        <div className="lbar"><i></i></div>
        <div className="lbrand">blstudio · laboratorio</div>
      </div>
    </div>
  );
}

Object.assign(window, {PreviewModal, IdeaModal, CartDock, Loader});
