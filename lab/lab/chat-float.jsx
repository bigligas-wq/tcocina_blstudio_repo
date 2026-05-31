/* lab/chat-float.jsx — Flotante de diálogo con átomo */

function ChatFloat(){
  const [open, setOpen] = React.useState(false);
  const [messages, setMessages] = React.useState([]);
  const [input, setInput] = React.useState("");
  const [file, setFile] = React.useState(null);
  const fileInputRef = React.useRef(null);
  const messagesEndRef = React.useRef(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  React.useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const handleSend = async () => {
    if (!input.trim() && !file) return;

    const userMessage = {
      id: Date.now(),
      type: "user",
      text: input,
      file: file ? { name: file.name, type: file.type } : null,
    };

    setMessages(prev => [...prev, userMessage]);
    setInput("");
    setFile(null);

    // Simular respuesta del servidor
    setTimeout(() => {
      const response = {
        id: Date.now() + 1,
        type: "system",
        text: "Gracias por tu mensaje. Tu aporte fue guardado y será revisado por el equipo.",
      };
      setMessages(prev => [...prev, response]);
    }, 800);

    // Aquí iría la llamada real al servidor para guardar en storage
    // await fetch('/api/lab/messages', { method: 'POST', body: ... })
  };

  const handleFileSelect = (e) => {
    const f = e.target.files?.[0];
    if (f && f.type.startsWith("image/")) {
      setFile(f);
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  };

  return (
    <div className="lab-chat-float">
      {/* Chat Modal */}
      <div className={`lab-chat-modal ${open ? "open" : ""}`}>
        <div className="lab-chat-header">
          <h3>💬 Diálogo con el Laboratorio</h3>
          <button className="lab-chat-close" onClick={() => setOpen(false)}>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <div className="lab-chat-messages">
          {messages.length === 0 && (
            <div className="lab-chat-message system">
              Escribe tu mensaje y comparte imágenes de tu propuesta. Se guardarán en nuestro almacén.
            </div>
          )}
          {messages.map(msg => (
            <div key={msg.id} className={`lab-chat-message ${msg.type}`}>
              {msg.text}
              {msg.file && <div style={{ fontSize: "10px", marginTop: "4px", opacity: 0.8 }}>📎 {msg.file.name}</div>}
            </div>
          ))}
          <div ref={messagesEndRef} />
        </div>

        <div className="lab-chat-input-area">
          {file && (
            <div style={{ fontSize: "12px", color: "var(--lime)", padding: "8px", background: "rgba(191,255,0,.08)", borderRadius: "6px", display: "flex", justifyContent: "space-between", alignItems: "center" }}>
              📸 {file.name}
              <button onClick={() => setFile(null)} style={{ background: "none", border: "none", color: "currentColor", cursor: "pointer" }}>✕</button>
            </div>
          )}
          <textarea
            className="lab-chat-input"
            placeholder="Escribe tu mensaje..."
            value={input}
            onChange={e => setInput(e.target.value)}
            onKeyDown={handleKeyDown}
            rows="2"
          />
          <input
            ref={fileInputRef}
            type="file"
            accept="image/*"
            onChange={handleFileSelect}
            className="lab-file-input"
          />
          <div className="lab-chat-actions">
            <button
              className="lab-chat-btn"
              onClick={() => fileInputRef.current?.click()}
              title="Subir imagen"
            >
              📎 Imagen
            </button>
            <button
              className="lab-chat-btn send"
              onClick={handleSend}
              disabled={!input.trim() && !file}
            >
              Enviar
            </button>
          </div>
        </div>
      </div>

      {/* Botón flotante del Átomo */}
      <button
        className={`lab-atom-btn ${open ? "active" : ""}`}
        onClick={() => setOpen(!open)}
        title="Abrir diálogo"
      >
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2c1.5 3 .5 4.5-.8 6C9.4 10 8 11.6 8 14a4 4 0 0 0 8 0c0-1.2-.4-2.2-1-3 .2 1.4-.6 2.2-1.3 2.2-.9 0-1.2-.7-1-1.7.4-2-.2-4-.7-4.5.6 2-1.2 2.8-1.6 4 1.3-3.6-.2-6.5 1.6-9z"/>
        </svg>
      </button>
    </div>
  );
}

Object.assign(window, {ChatFloat});
