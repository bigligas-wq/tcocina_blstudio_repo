/* lab/cards.jsx — tabs de filtro + grid + card de mejora */

function Tabs({active, onChange, counts, onIdea}){
  const tabs = [
    {id:"todas", label:"Todas"},
    {id:"visual", label:"Visual"},
    {id:"ux", label:"UX"},
    {id:"performance", label:"Performance"},
    {id:"admin", label:"Admin"},
  ];
  return (
    <div className="tabs-row">
      <div className="tabs">
        {tabs.map(t=>(
          <button key={t.id} className={"tab"+(active===t.id?" active":"")} onClick={()=>onChange(t.id)}>
            {t.label}<span className="cnt">{counts[t.id]||0}</span>
          </button>
        ))}
      </div>
      <button className="btn btn-soft" onClick={onIdea}><Icon n="bulb" style={{width:18,height:18}}/>¿Se te ocurre algo? Tirámela</button>
    </div>
  );
}

function CardBubbles(){
  // burbujas css para el burst en hover (posiciones/tamaños fijos)
  const seeds = [
    {l:"12%",s:10,d:0},{l:"24%",s:6,d:.5},{l:"38%",s:14,d:.2},{l:"50%",s:8,d:.9},
    {l:"63%",s:11,d:.35},{l:"75%",s:6,d:.7},{l:"86%",s:13,d:.15},{l:"30%",s:5,d:1.2},
    {l:"57%",s:7,d:1.4},{l:"80%",s:9,d:1.1},
  ];
  return (
    <div className="cbubbles">
      {seeds.map((b,i)=>(
        <i key={i} style={{left:b.l,width:b.s,height:b.s,animationDelay:b.d+"s",
          animationDuration:(2.2+b.s*0.06)+"s"}}/>
      ))}
    </div>
  );
}

function Card({imp, inCart, labels, rating, onRate, onAdd, onPreview}){
  const meta = CAT_META[imp.cat];
  const state = imp.activa ? "activa" : imp.proceso ? "proceso" : null;
  return (
    <article className={"card cat-"+imp.cat}>
      <div className="meniscus"></div>
      <CardBubbles/>
      <div className="card-top">
        <span className="cat-ic"><Icon n={imp.icon}/></span>
        <div style={{display:"flex",gap:6,flexWrap:"wrap",justifyContent:"flex-end"}}>
          {imp.nuevo && <span className="badge nuevo">Nuevo</span>}
          {imp.activa && <span className="badge activa"><Icon n="check" style={{width:11,height:11}}/>Activa</span>}
          {imp.proceso && <span className="badge proceso">En proceso</span>}
          {!state && <span className="badge cat"><span className="bd" style={{background:meta.color}}></span>{meta.label}</span>}
        </div>
      </div>
      <h4>{imp.name}</h4>
      <p className="cdesc">{imp.short}</p>

      <div className="card-foot">
        {imp.activa ? (
          <div className="state-line activa"><Icon n="check" style={{width:14,height:14}}/>Funcionando · {imp.activadaEl}</div>
        ) : imp.proceso ? (
          <div className="state-line proceso"><Icon n="flask" style={{width:14,height:14}}/>La estoy cocinando</div>
        ) : (
          <>
            <div className="price"><div className="amt"><span>USD </span>{imp.price}</div></div>
            <div className="card-actions">
              <button className="icon-btn" title="Ver cómo queda" onClick={()=>onPreview(imp)}><Icon n="eye"/></button>
              <button className={"add-btn"+(inCart?" added":"")} onClick={()=>onAdd(imp)}>
                {inCart ? <><Icon n="check" style={{width:16,height:16}}/>{labels.added}</> : <><Icon n="plus" style={{width:16,height:16}}/>{labels.add}</>}
              </button>
            </div>
          </>
        )}
      </div>

      <div className="reaction-bar">
        <StarRate value={rating} onRate={(n)=>onRate(imp.id,n)}/>
      </div>
    </article>
  );
}

function Grid({items, cart, labels, ratings, onRate, onAdd, onPreview}){
  return (
    <div className="grid">
      {items.map((imp,i)=>(
        <div key={imp.id} className="fade-up" style={{animationDelay:(0.04*i)+"s"}}>
          <Card imp={imp} inCart={cart.includes(imp.id)} labels={labels}
            rating={(ratings[imp.id]&&ratings[imp.id].stars)||0} onRate={onRate}
            onAdd={onAdd} onPreview={onPreview}/>
        </div>
      ))}
    </div>
  );
}

Object.assign(window, {Tabs, Grid, Card});
