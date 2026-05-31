/* lab/app.jsx — composición principal del Laboratorio */

const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "greet": "Emilio",
  "accion": "#ff4c0c",
  "burbujeo": "mix",
  "tono": "tranquilo",
  "regalo": true
}/*EDITMODE-END*/;

const COPY = {
  tranquilo: {
    add:"Sumar", added:"Sumada", featuredAdd:"Sumar a mi web", previewAdd:"Esta la sumo",
    pedido:"Lo que vas a sumar", whats:"Pasármelo por WhatsApp",
  },
  entusiasta: {
    add:"La quiero", added:"Sumada", featuredAdd:"La quiero en mi web", previewAdd:"¡Esta la quiero!",
    pedido:"Tu selección", whats:"Mandar por WhatsApp",
  },
};

function OrderModal({items, subtotal, freeOff, total, onClose, copy}){
  const freeItem = freeOff>0 ? items.slice().sort((a,b)=>a.price-b.price)[0] : null;
  return (
    <div className="scrim" onClick={onClose}>
      <div className="modal lab-scroll" style={{width:"min(620px,100%)"}} onClick={e=>e.stopPropagation()}>
        <div className="modal-head">
          <div>
            <h3 style={{fontFamily:"var(--font-display)",fontWeight:800,fontSize:22,margin:0}}>{copy.pedido}</h3>
            <p style={{color:"var(--mut)",fontSize:13.5,margin:"6px 0 0"}}>Sin compromiso. Lo mirás, y si querés avanzás vos cuando quieras.</p>
          </div>
          <button className="modal-x" onClick={onClose}><Icon n="x"/></button>
        </div>
        <div className="modal-body">
          <div style={{display:"flex",flexDirection:"column",gap:10,marginBottom:18}}>
            {items.map(it=>{
              const isFree = freeItem && it.id===freeItem.id;
              return (
              <div key={it.id} style={{display:"flex",alignItems:"center",justifyContent:"space-between",gap:12,
                padding:"14px 16px",borderRadius:14,background:"var(--surf)",border:"1px solid "+(isFree?"rgba(139,195,74,.35)":"var(--line)")}}>
                <div style={{display:"flex",alignItems:"center",gap:12}}>
                  <span className="cat-ic" style={{width:38,height:38}}><Icon n={it.icon}/></span>
                  <div>
                    <div style={{fontWeight:600,fontSize:14.5}}>{it.name}</div>
                    {isFree && <div style={{fontFamily:"var(--font-mono)",fontSize:11,color:"var(--lime-soft)",marginTop:3}}>tu primera gratis ✨</div>}
                  </div>
                </div>
                <div className="price">
                  {isFree
                    ? <div className="amt" style={{fontSize:16,color:"var(--lime-soft)"}}>gratis</div>
                    : <div className="amt" style={{fontSize:18}}><span>USD </span>{it.price}</div>}
                </div>
              </div>
            );})}
          </div>
          <div style={{padding:"4px 4px 0"}}>
            <div style={{display:"flex",justifyContent:"space-between",fontFamily:"var(--font-mono)",fontSize:13,color:"var(--mut)",marginBottom:8}}>
              <span>Subtotal</span><span>USD {subtotal}</span>
            </div>
            {freeOff>0 && <div style={{display:"flex",justifyContent:"space-between",fontFamily:"var(--font-mono)",fontSize:13,color:"var(--lime-soft)",marginBottom:8}}>
              <span>Primera mejora gratis</span><span>− USD {freeOff}</span>
            </div>}
          </div>
          <div style={{display:"flex",alignItems:"center",justifyContent:"space-between",padding:"16px 4px",
            borderTop:"1px solid var(--line)"}}>
            <span style={{fontFamily:"var(--font-mono)",fontSize:13,color:"var(--mut)"}}>Total</span>
            <div className="price big"><div className="amt"><span>USD </span>{total}</div></div>
          </div>
          <div style={{display:"flex",gap:10,marginTop:14,flexWrap:"wrap"}}>
            <button className="btn btn-primary" style={{flex:1,justifyContent:"center"}}>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.2 14.8l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 0 1 12 4zm-2.7 4c-.2 0-.5 0-.7.4-.2.4-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.8 4.3 3.8 2.1.8 2.6.7 3 .6.5 0 1.4-.5 1.6-1.1.2-.6.2-1 .1-1.1l-.6-.3c-.3-.1-1.4-.7-1.6-.8-.2 0-.4-.1-.5.2l-.7.8c-.1.2-.3.2-.5.1-.6-.3-1.4-.5-2.3-1.4-.6-.6-1-1.3-1.2-1.5-.1-.3 0-.4.1-.5l.4-.5.3-.4v-.4c0-.1-.5-1.3-.7-1.8-.2-.4-.4-.4-.5-.4z"/></svg>
              {copy.whats}
            </button>
            <button className="btn btn-ghost" onClick={onClose}>Sigo mirando</button>
          </div>
          <p style={{fontFamily:"var(--font-mono)",fontSize:11.5,color:"var(--mut-2)",textAlign:"center",margin:"14px 0 0"}}>
            te abre tu WhatsApp con el detalle listo — no se envía nada solo
          </p>
        </div>
      </div>
    </div>
  );
}

function App(){
  const [t,setTweak] = useTweaks(TWEAK_DEFAULTS);
  const [loaded,setLoaded] = React.useState(false);
  const [tab,setTab] = React.useState("todas");
  const [cart,setCart] = React.useState([]);
  const [preview,setPreview] = React.useState(null);
  const [idea,setIdea] = React.useState(false);
  const [order,setOrder] = React.useState(false);
  const [signals,setSignals] = React.useState(false);
  const [ratings,setRatings] = React.useState(()=>{
    try{ return JSON.parse(localStorage.getItem("bl_lab_signals_v1"))||{}; }catch(e){ return {}; }
  });
  const rate = (id,n)=> setRatings(r=>{
    const nx = n ? {...r,[id]:{stars:n,at:Date.now()}} : (()=>{const c={...r};delete c[id];return c;})();
    try{ localStorage.setItem("bl_lab_signals_v1",JSON.stringify(nx)); }catch(e){}
    return nx;
  });
  const clearRatings = ()=>{ setRatings({}); try{localStorage.removeItem("bl_lab_signals_v1");}catch(e){} };

  const copy = COPY[t.tono] || COPY.tranquilo;
  React.useEffect(()=>{ document.documentElement.style.setProperty("--accent", t.accion); },[t.accion]);

  const buyable = IMPROVEMENTS.filter(i=>!i.activa && !i.proceso);
  const activas = IMPROVEMENTS.filter(i=>i.activa).length;
  const nuevas = buyable.filter(i=>i.nuevo).length + buyable.filter(i=>!i.nuevo).length;
  const featured = IMPROVEMENTS.find(i=>i.featured);
  const rest = IMPROVEMENTS.filter(i=>!i.featured);

  const counts = {todas:rest.length};
  ["visual","ux","performance","admin"].forEach(c=>{counts[c]=rest.filter(i=>i.cat===c).length;});

  const shown = tab==="todas" ? rest : rest.filter(i=>i.cat===tab);

  const toggle = (imp)=> setCart(c=> c.includes(imp.id) ? c.filter(x=>x!==imp.id) : [...c,imp.id]);
  const cartItems = IMPROVEMENTS.filter(i=>cart.includes(i.id));
  const subtotal = cartItems.reduce((s,i)=>s+i.price,0);
  const freeOff = (t.regalo!==false && cartItems.length>0) ? Math.min(...cartItems.map(i=>i.price)) : 0;
  const total = subtotal - freeOff;

  return (
    <div className="app">
      <Rail/>
      <main className="lab lab-scroll">
        <BubbleField intensity={t.burbujeo}/>
        <LabTop onIdea={()=>setIdea(true)}/>
        <div className="lab-inner">
          <Hero greet={t.greet} activas={activas} nuevas={nuevas}/>
          {featured && <Featured imp={featured} inCart={cart.includes(featured.id)} labels={copy}
            rating={(ratings[featured.id]&&ratings[featured.id].stars)||0} onRate={rate}
            onAdd={toggle} onPreview={setPreview}/>}

          {t.regalo!==false && <FreeBanner/>}

          <div className="section-head" style={{marginBottom:6}}>
            <h2>Todo lo que hay en el banco</h2>
            <span className="sub">{rest.length} mejoras en el lab</span>
          </div>
          <Tabs active={tab} onChange={setTab} counts={counts} onIdea={()=>setIdea(true)}/>
          <Grid items={shown} cart={cart} labels={copy} ratings={ratings} onRate={rate} onAdd={toggle} onPreview={setPreview}/>
        </div>

        <CartDock count={cart.length} total={total} freeOff={freeOff} onOpen={()=>setOrder(true)}/>
      </main>

      {preview && <PreviewModal imp={preview} inCart={cart.includes(preview.id)} labels={copy}
        rating={(ratings[preview.id]&&ratings[preview.id].stars)||0} onRate={rate}
        onAdd={(i)=>{toggle(i);}} onClose={()=>setPreview(null)}/>}
      {idea && <IdeaModal onClose={()=>setIdea(false)}/>}
      {order && <OrderModal items={cartItems} subtotal={subtotal} freeOff={freeOff} total={total} copy={copy} onClose={()=>setOrder(false)}/>}
      {signals && <SignalsPanel ratings={ratings} onClose={()=>setSignals(false)} onClear={clearRatings}/>}

      <ChatFloat/>

      {!loaded && <Loader onDone={()=>setLoaded(true)}/>}

      <TweaksPanel>
        <TweakSection label="Identidad"/>
        <TweakText label="Saludar a" value={t.greet} onChange={v=>setTweak("greet",v)}/>
        <TweakColor label="Color de acción" value={t.accion}
          options={["#ff4c0c","#8bc34a","#3ecf8e","#38b6ff","#f5a623"]} onChange={v=>setTweak("accion",v)}/>
        <TweakSection label="Atmósfera"/>
        <TweakRadio label="Burbujeo" value={t.burbujeo} options={["off","mix","vivo"]} onChange={v=>setTweak("burbujeo",v)}/>
        <TweakRadio label="Tono del copy" value={t.tono} options={["tranquilo","entusiasta"]} onChange={v=>setTweak("tono",v)}/>
        <TweakToggle label="Primera mejora gratis" value={t.regalo!==false} onChange={v=>setTweak("regalo",v)}/>
        <TweakSection label="Para vos (dev)"/>
        <TweakButton label="Ver señales de interés" onClick={()=>setSignals(true)}/>
      </TweaksPanel>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App/>);
