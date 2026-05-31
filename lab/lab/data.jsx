/* lab/data.jsx — datos de mejoras + iconos SVG */

// ---- Iconos line (Lucide-style) como componentes ----
const I = {
  home:   "M3 10.2 12 3l9 7.2V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z",
  cart:   "M3 4h2l2.4 11.2a1 1 0 0 0 1 .8h8.7a1 1 0 0 0 1-.78L20.5 8H6M9 20a1 1 0 1 0 0 .01M17 20a1 1 0 1 0 0 .01",
  clock:  "M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z",
  cloud:  "M7 18a4 4 0 0 1 .6-7.96A5.5 5.5 0 0 1 18 9.5a3.5 3.5 0 0 1-.5 8.5z",
  tag:    "M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9zM7.5 7.5h.01",
  star:   "M12 3.5l2.6 5.3 5.9.86-4.25 4.14 1 5.86L12 16.9l-5.25 2.76 1-5.86L3.5 9.66l5.9-.86z",
  chat:   "M21 11.5a8.38 8.38 0 0 1-8.5 8.5 8.5 8.5 0 0 1-3.8-.9L3 21l1.9-5.7A8.5 8.5 0 1 1 21 11.5z",
  ticket: "M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z M13 5v14",
  gear:   "M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 13a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V20a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 6.8 18l-.1.1A2 2 0 1 1 3.9 15.3l.1-.1a1.6 1.6 0 0 0-1.1-2.7H2a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 3.3 6.8l-.1-.1A2 2 0 1 1 6 3.9l.1.1a1.6 1.6 0 0 0 1.8.3H8a1.6 1.6 0 0 0 1-1.5V2a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V8a1.6 1.6 0 0 0 1.5 1H22a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z",
  flask:  "M9 3h6M10 3v6.5L5.2 18a2 2 0 0 0 1.8 3h10a2 2 0 0 0 1.8-3L14 9.5V3M8.5 14h7",
  // categorías
  visual: "M3 5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 15l4-4 5 5M14 11l3-3 4 4",
  ux:     "M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01",
  perf:   "M13 2 4.5 13.5H11l-1 8L19 9.5h-6z",
  admin:  "M3 4h18M3 4v16a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V4M8 11l2.5 2.5L16 8",
  // varios
  bell:   "M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0",
  eye:    "M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z",
  plus:   "M12 5v14M5 12h14",
  check:  "M20 6 9 17l-5-5",
  x:      "M18 6 6 18M6 6l12 12",
  arrow:  "M5 12h14M13 6l6 6-6 6",
  bulb:   "M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5c.8.8 1 1.3 1 2.5h6c0-1.2.2-1.7 1-2.5A6 6 0 0 0 12 3z",
  spark:  "M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18",
  gauge:  "M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM13.5 10.5 17 7M4 18a8 8 0 1 1 16 0z",
};

function Icon({n, style}){
  const sub = n==="ticket"||n==="visual"||n==="ux"||n==="admin"||n==="spark"||n==="gear";
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7"
         strokeLinecap="round" strokeLinejoin="round" style={style}>
      {I[n].split("M").filter(Boolean).map((d,i)=><path key={i} d={"M"+d}/>)}
    </svg>
  );
}

// ---- catálogo de mejoras (tcocina / Emilio) ----
const CAT_META = {
  visual:      {label:"Visual",      icon:"visual", color:"#a4d65e"},
  ux:          {label:"UX",          icon:"ux",     color:"#38b6ff"},
  performance: {label:"Performance", icon:"perf",   color:"#a78bfa"},
  admin:       {label:"Admin",       icon:"admin",  color:"#f5a623"},
};

// imágenes placeholder (gradientes) para antes/después
const ph = (a,b,deg=135)=>`linear-gradient(${deg}deg,${a},${b})`;

const IMPROVEMENTS = [
  {
    id:"combo", featured:true, nuevo:true, cat:"visual", icon:"spark",
    name:"El combo del día, imposible de no ver",
    short:"Un bloque grande arriba de todo con la promo del día. Lo primero que ve el que entra.",
    long:"Hoy la promo del día queda perdida entre los demás productos. Le armo un bloque destacado arriba de todo, con la foto grande, el precio tachado y el ahorro bien claro. Es lo primero que ve el cliente al abrir la web.",
    price:35,
    before:ph("#1a1a1e","#0f0f12"), after:ph("#ff6a2b","#b5300a"),
    diffs:[
      {color:"#3ecf8e", t:"Combo del día arriba de todo, con foto grande"},
      {color:"#f5a623", t:"Precio anterior tachado + cuánto ahorra"},
      {color:"#38b6ff", t:"Se adapta solo en celular"},
    ],
  },
  {
    id:"carrito", nuevo:true, cat:"ux", icon:"cart",
    name:"Carrito que no te hace pensar",
    short:"Sumar, restar y ver el total sin abrir otra pantalla.",
    long:"El cliente edita el pedido sin saltar de página: suma, resta y ve el total actualizado al instante. Menos pasos, menos pedidos abandonados a mitad de camino.",
    price:45,
    before:ph("#16181c","#0e0f12"), after:ph("#1e3a52","#0e1f30"),
    diffs:[
      {color:"#38b6ff", t:"Editar cantidades sin recargar"},
      {color:"#3ecf8e", t:"Total siempre visible"},
    ],
  },
  {
    id:"fotos", cat:"performance", icon:"perf",
    name:"Fotos que cargan al toque",
    short:"La carta entra más rápido aunque la conexión sea mala.",
    long:"Optimizo y precargo las imágenes de los productos para que la carta aparezca casi al instante, incluso con datos móviles flojos. Menos espera = menos gente que se va.",
    price:30,
    before:ph("#17171b","#0e0e11"), after:ph("#3a2e52","#1a1330"),
    diffs:[
      {color:"#a78bfa", t:"Imágenes hasta 4× más livianas"},
      {color:"#3ecf8e", t:"La carta entra sin pantalla en blanco"},
    ],
  },
  {
    id:"buscador", nuevo:true, cat:"ux", icon:"ux",
    name:"Buscador que adivina",
    short:"Empezás a escribir y ya te sugiere el plato.",
    long:"Un buscador que muestra resultados a medida que se escribe, con las fotos al lado. El cliente encuentra lo que quiere en dos letras en vez de scrollear toda la carta.",
    price:40,
    before:ph("#16171b","#0e0f11"), after:ph("#1d3b50","#0f2030"),
    diffs:[
      {color:"#38b6ff", t:"Sugerencias mientras escribís"},
      {color:"#3ecf8e", t:"Con miniatura de cada plato"},
    ],
  },
  {
    id:"cupones", cat:"visual", icon:"tag",
    name:"Cupones con cuenta regresiva",
    short:"Un reloj que corre y da ganas de aprovecharlo ahora.",
    long:"Los cupones muestran un contador en vivo de cuánto les queda. El apuro suave ('quedan 2 h') empuja a usarlos en el momento en vez de dejarlos para después.",
    price:30,
    before:ph("#17171a","#0e0e11"), after:ph("#3d2e10","#1c1405"),
    diffs:[
      {color:"#f5a623", t:"Contador en vivo del cupón"},
      {color:"#3ecf8e", t:"Se aplica con un toque"},
    ],
  },
  {
    id:"oscuro", cat:"visual", icon:"visual",
    name:"Modo oscuro para tus clientes",
    short:"La carta de noche, sin encandilar a nadie.",
    long:"Un modo oscuro real para la web pública: más cómodo para pedir de noche desde la cama y un detalle que hace ver la marca más cuidada.",
    price:25,
    before:ph("#ededf0","#cfcfd6"), after:ph("#14201a","#0a120d"),
    diffs:[
      {color:"#a4d65e", t:"Cambia solo según el celular"},
      {color:"#38b6ff", t:"Recuerda la preferencia del cliente"},
    ],
  },
  {
    id:"vivo", cat:"admin", icon:"admin", activa:true, activadaEl:"hace 12 días",
    name:"Panel de pedidos en vivo",
    short:"Los pedidos nuevos entran solos, sin recargar.",
    long:"Ya está funcionando: los pedidos aparecen en tu panel apenas entran, con un sonidito opcional. No hace falta apretar F5 nunca más en la hora pico.",
    price:70,
    before:ph("#16171b","#0e0f11"), after:ph("#13241b","#0a140e"),
    diffs:[
      {color:"#3ecf8e", t:"Pedidos en tiempo real"},
      {color:"#f5a623", t:"Aviso sonoro opcional"},
    ],
  },
  {
    id:"checkout", cat:"ux", icon:"flask",
    name:"Checkout en un solo paso",
    short:"De carrito a 'pedido enviado' en una sola pantalla.",
    long:"Junto datos, dirección y forma de pago en una sola pantalla bien ordenada. Cada paso de más es gente que abandona; este flujo los saca casi todos.",
    price:60,
    before:ph("#16171b","#0e0f11"), after:ph("#1d3b50","#0f2030")
    , diffs:[
      {color:"#38b6ff", t:"Todo en una pantalla"},
      {color:"#3ecf8e", t:"Menos pedidos abandonados"},
    ],
  },
  {
    id:"reportes", cat:"admin", icon:"gauge", proceso:true,
    name:"Reportes que se entienden",
    short:"Qué se vende, a qué hora y qué conviene empujar.",
    long:"Estoy armando un panel de reportes claro: tus platos más vendidos, las horas pico y cuánto rinde cada combo. Para decidir con datos, no a ojo.",
    price:50,
    before:ph("#16171b","#0e0f11"), after:ph("#3a2e10","#1c1405"),
    diffs:[
      {color:"#f5a623", t:"Top de platos y horarios"},
      {color:"#a78bfa", t:"Rendimiento por combo"},
    ],
  },
];

Object.assign(window, {Icon, I, CAT_META, IMPROVEMENTS});
