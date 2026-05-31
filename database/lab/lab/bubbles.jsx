/* lab/bubbles.jsx — burbujeo ambiental (canvas) + helper Lottie */

function BubbleField({intensity}){
  const ref = React.useRef(null);
  React.useEffect(()=>{
    if(intensity==="off") return;
    const cv = ref.current, ctx = cv.getContext("2d");
    let raf, W, H, dpr = Math.min(window.devicePixelRatio||1, 2);
    const count = intensity==="vivo" ? 70 : 42;
    const palette = [
      [139,195,74],   // lima
      [255,255,255],  // blanco cálido
      [255,76,12],    // rojo (poco)
      [56,182,255],   // azul (poco)
    ];
    const weight = [0.55,0.3,0.08,0.07];
    function pick(){ let r=Math.random(),a=0; for(let i=0;i<weight.length;i++){a+=weight[i]; if(r<=a) return palette[i];} return palette[0]; }
    let bubbles = [];
    function resize(){
      W = cv.clientWidth; H = cv.clientHeight;
      cv.width = W*dpr; cv.height = H*dpr; ctx.setTransform(dpr,0,0,dpr,0,0);
    }
    function mk(initial){
      const r = 2 + Math.pow(Math.random(),2)*22;
      return {
        x: Math.random()*W,
        y: initial ? Math.random()*H : H + r + Math.random()*40,
        r, c: pick(),
        sp: (intensity==="vivo"?0.35:0.22) + Math.random()*0.7 + r*0.012,
        drift: (Math.random()-0.5)*0.4,
        ph: Math.random()*Math.PI*2,
        wob: 0.3 + Math.random()*0.7,
        a: 0.10 + Math.random()*0.28,
      };
    }
    resize();
    bubbles = Array.from({length:count}, ()=>mk(true));
    let t=0;
    function frame(){
      t+=0.016;
      ctx.clearRect(0,0,W,H);
      ctx.globalCompositeOperation = "lighter";
      for(const b of bubbles){
        b.y -= b.sp;
        b.x += b.drift + Math.sin(t*b.wob + b.ph)*0.3;
        if(b.y < -b.r-30){ Object.assign(b, mk(false)); }
        const [r,g,bl]=b.c;
        const grd = ctx.createRadialGradient(b.x-b.r*0.3,b.y-b.r*0.3,b.r*0.1,b.x,b.y,b.r);
        grd.addColorStop(0,`rgba(${r},${g},${bl},${b.a*1.1})`);
        grd.addColorStop(0.55,`rgba(${r},${g},${bl},${b.a*0.4})`);
        grd.addColorStop(1,`rgba(${r},${g},${bl},0)`);
        ctx.fillStyle=grd;
        ctx.beginPath(); ctx.arc(b.x,b.y,b.r,0,Math.PI*2); ctx.fill();
        // rim highlight (look de burbuja)
        if(b.r>7){
          ctx.strokeStyle=`rgba(255,255,255,${b.a*0.5})`;
          ctx.lineWidth=0.8;
          ctx.beginPath(); ctx.arc(b.x-b.r*0.2,b.y-b.r*0.2,b.r*0.7,Math.PI*0.9,Math.PI*1.7); ctx.stroke();
        }
      }
      ctx.globalCompositeOperation = "source-over";
      raf = requestAnimationFrame(frame);
    }
    frame();
    const ro = new ResizeObserver(resize); ro.observe(cv);
    return ()=>{ cancelAnimationFrame(raf); ro.disconnect(); };
  },[intensity]);
  if(intensity==="off") return null;
  return <canvas ref={ref} className="bubble-canvas"/>;
}

// ---- Lottie host (carga animationData por fetch) ----
function Lottie({src, loop=true, autoplay=true, className, style, onDone}){
  const ref = React.useRef(null);
  React.useEffect(()=>{
    let anim, alive=true;
    fetch(src).then(r=>r.json()).then(data=>{
      if(!alive || !ref.current || !window.lottie) return;
      anim = window.lottie.loadAnimation({
        container: ref.current, renderer:"svg", loop, autoplay, animationData:data,
      });
      if(onDone) anim.addEventListener("complete", onDone);
    }).catch(()=>{});
    return ()=>{ alive=false; if(anim) anim.destroy(); };
  },[src]);
  return <div ref={ref} className={className} style={style}/>;
}

Object.assign(window, {BubbleField, Lottie});
