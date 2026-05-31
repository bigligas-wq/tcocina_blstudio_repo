/* lab/chrome.jsx — sidebar nativo tcocina (replica) + topbar del lab */

function Rail(){
  const nav = ["home","cart","clock","cloud","tag","star","chat","ticket"];
  const [hover,setHover] = React.useState(null);
  return (
    <aside className="rail">
      <div className="rail-logo" title="tcocina"><span className="mark"></span><span className="play"></span></div>
      <div className="rail-brand" title="tcocina">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2c1.5 3 .5 4.5-.8 6C9.4 10 8 11.6 8 14a4 4 0 0 0 8 0c0-1.2-.4-2.2-1-3 .2 1.4-.6 2.2-1.3 2.2-.9 0-1.2-.7-1-1.7.4-2-.2-4-.7-4.5.6 2-1.2 2.8-1.6 4 1.3-3.6-.2-6.5 1.6-9z"/>
        </svg>
      </div>
      <div className="rail-sep"></div>
      <nav className="rail-nav">
        {nav.map(n=>(
          <button key={n} className="rail-btn" onMouseEnter={()=>setHover(n)} onMouseLeave={()=>setHover(null)}>
            <Icon n={n}/>
          </button>
        ))}
        {/* Laboratorio — nuevo, activo, acento BLStudio */}
        <button className="rail-btn lab active" title="Laboratorio">
          <Icon n="flask"/>
          <span className="ndot"></span>
        </button>
      </nav>
      <button className="rail-btn"><Icon n="gear"/></button>
    </aside>
  );
}

// recolor del átomo chico para que combine con el tono mut del topbar
const ATOM_MINI_RECOLOR = {"0.071,0.075,0.192":[0.604,0.576,0.533,1]};

function LabTop({onIdea}){
  return (
    <div className="lab-top">
      <div className="crumb">
        <span>Panel</span><span className="sl">/</span><b>Fábrica de actualizaciones</b>
      </div>
      <div className="lab-top-right">
        <div className="wip-pill"><span className="dot"></span>en construcción · ya funciona</div>
        <div className="blstudio-tag">
          <span>by</span>
          <img src="assets/blstudio-logo.png" alt="blstudio"/>
          <Lottie src="assets/lottie/atom.json" className="atom-mini" recolor={ATOM_MINI_RECOLOR}/>
        </div>
        <button className="bell" title="Notificaciones"><Icon n="bell"/><span className="nd"></span></button>
      </div>
    </div>
  );
}

Object.assign(window, {Rail, LabTop});
