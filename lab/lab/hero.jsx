/* lab/hero.jsx — hero con mascota átomo + featured banner */

// recolor: el azul casi-negro de la lottie wired-outline se cambia por un blanco cálido
// para que el ícono se lea sobre fondo oscuro sin perder el lima/rojo brand.
const FACTORY_RECOLOR = {"0.071,0.075,0.192":[0.957,0.937,0.906,1]};

function Hero({greet, activas, nuevas}){
  return (
    <header className="hero">
      <div className="fade-up" style={{animationDelay:".05s"}}>
        <span className="eyebrow"><span className="ln"></span><b>blstudio</b> · fábrica</span>
        <h1>Fábrica de<br/><em>actualizaciones</em><span className="dot">.</span></h1>
        <p className="lead">
          Hola {greet}. Acá fabrico mejoras para tu plataforma — entra cada tanto, mirá lo que salió de la línea y sumá a tu web lo que te cierre.
          <span className="soft"> Lo que no elijas sigue en stock, sin apuro.</span>
        </p>
        <div className="hero-stats">
          <div className="stat green">
            <span className="ic"><Icon n="check"/></span>
            <div><div className="n">{activas}</div><div className="l">ya corriendo en tu web</div></div>
          </div>
          <div className="stat amber">
            <span className="ic"><Icon n="spark"/></span>
            <div><div className="n">{nuevas}</div><div className="l">recién salidas de la línea</div></div>
          </div>
        </div>
      </div>

      <div className="hero-vis fade-up" style={{animationDelay:".2s"}}>
        <div className="factory-stage">
          <div className="glow"></div>
          <div className="ring"></div>
          <div className="ring r2"></div>
          <Lottie src="assets/lottie/nuke.json" className="factory-host" recolor={FACTORY_RECOLOR}/>
          <div className="label">línea de producción · activa</div>
        </div>
      </div>
    </header>
  );
}

function Featured({imp, inCart, labels, rating, onRate, onAdd, onPreview}){
  const meta = CAT_META[imp.cat];
  return (
    <section>
      <div className="section-head">
        <h2>Lo último que salió del laboratorio</h2>
        <span className="sub">la última evolución de la plataforma</span>
      </div>
      <div className="featured">
        <div className="fglow"></div>
        <div className="featured-grid">
          <div className="featured-body">
            <div className="tagrow">
              <span className="badge featured"><span className="bd" style={{background:"var(--red)"}}></span>Destacada</span>
              {imp.nuevo && <span className="badge nuevo">Nuevo</span>}
              <span className="badge cat"><span className="bd" style={{background:meta.color}}></span>{meta.label}</span>
            </div>
            <h3>{imp.name}</h3>
            <p className="desc">{imp.long}</p>
            <div className="diffs">
              {imp.diffs.map((d,i)=>(
                <div className="diff" key={i}><span className="d" style={{background:d.color}}></span>{d.t}</div>
              ))}
            </div>
            <div className="featured-foot">
              <div className="price big">
                <div className="amt"><span>USD </span>{imp.price}</div>
                <div className="meta">pago único · si te sirve, queda online en 24 h</div>
              </div>
              <button className="btn btn-ghost" onClick={()=>onPreview(imp)}><Icon n="eye" style={{width:18,height:18}}/>Ver cómo queda</button>
              <button className={"btn btn-primary"+(inCart?" added":"")} onClick={()=>onAdd(imp)}>
                {inCart ? <><Icon n="check" style={{width:18,height:18}}/>{labels.added}</> : <><Icon n="plus" style={{width:18,height:18}}/>{labels.featuredAdd}</>}
              </button>
            </div>
            <div className="reaction-bar" style={{maxWidth:420}}>
              <StarRate value={rating} onRate={(n)=>onRate(imp.id,n)}/>
            </div>
          </div>
          <div className="featured-vis">
            <div className="fv-frame">
              <div className="fv-after" style={{background:imp.after}}></div>
              <span className="fv-tag">después</span>
              <Lottie src="assets/lottie/atom.json" style={{position:"absolute",right:14,bottom:10,width:88,height:88,opacity:.9}}/>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

function FreeBanner(){
  return (
    <div className="free-banner fade-up">
      <span className="fb-ic"><Icon n="spark"/></span>
      <div className="fb-txt">
        <b>La primera corre por mi cuenta.</b> Elegí cualquier mejora y estrenala gratis — así ves de qué va esto sin poner un peso.
      </div>
      <span className="fb-pill"><span className="bd"></span>primera gratis</span>
    </div>
  );
}

Object.assign(window, {Hero, Featured, FreeBanner});
