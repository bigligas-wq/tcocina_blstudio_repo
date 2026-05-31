/* lab/matrix-rain.jsx — cascada de código binario estilo Matrix, adaptado a BLStudio */

function MatrixRain(){
  const canvasRef = React.useRef(null);

  React.useEffect(()=>{
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    let animId;
    let w, h;

    const resize = ()=>{
      w = canvas.width = canvas.offsetWidth;
      h = canvas.height = canvas.offsetHeight;
    };
    resize();
    window.addEventListener("resize", resize);

    const chars = "01BLSTUDIO";
    const fontSize = 14;
    const columns = Math.floor(w / fontSize);
    const drops = new Array(columns).fill(1);

    const draw = ()=>{
      ctx.fillStyle = "rgba(14, 13, 12, 0.07)";
      ctx.fillRect(0, 0, w, h);

      ctx.font = `500 ${fontSize}px "DM Mono", monospace`;
      for (let i = 0; i < drops.length; i++) {
        const char = chars[Math.floor(Math.random() * chars.length)];
        const x = i * fontSize;
        const y = drops[i] * fontSize;

        // Colores del branding BLStudio: lime y soft glows
        const isHead = Math.random() > 0.95;
        if (isHead) {
          ctx.fillStyle = "#e8f5e9";
          ctx.shadowColor = "#8bc34a";
          ctx.shadowBlur = 10;
        } else {
          const dist = y / h;
          const r = Math.floor(139 * (1 - dist) + 20 * dist);
          const g = Math.floor(195 * (1 - dist) + 200 * dist);
          const b = Math.floor(74 * (1 - dist) + 50 * dist);
          ctx.fillStyle = `rgb(${r},${g},${b})`;
          ctx.shadowBlur = 0;
        }

        ctx.fillText(char, x, y);
        ctx.shadowBlur = 0;

        if (y > h && Math.random() > 0.975) drops[i] = 0;
        drops[i]++;
      }
      animId = requestAnimationFrame(draw);
    };
    draw();

    return ()=>{
      cancelAnimationFrame(animId);
      window.removeEventListener("resize", resize);
    };
  },[]);

  return <canvas ref={canvasRef} className="matrix-canvas" aria-hidden="true"/>;
}

Object.assign(window, {MatrixRain});
