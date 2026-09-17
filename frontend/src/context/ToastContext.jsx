import { createContext, useCallback, useContext, useRef, useState } from 'react';
import { CheckCircle2 } from 'lucide-react';

const ToastContext = createContext(null);

export function ToastProvider({ children }) {
  const [mensaje, setMensaje] = useState(null);
  const timeoutRef = useRef(null);

  const mostrarToast = useCallback((texto, duracionMs = 3200) => {
    clearTimeout(timeoutRef.current);
    setMensaje(texto);
    timeoutRef.current = setTimeout(() => setMensaje(null), duracionMs);
  }, []);

  return (
    <ToastContext.Provider value={{ mostrarToast }}>
      {children}
      {mensaje && (
        <div className="toast" role="status" aria-live="polite">
          <CheckCircle2 size={18} color="var(--teal)" aria-hidden="true" />
          <span>{mensaje}</span>
        </div>
      )}
    </ToastContext.Provider>
  );
}

export function useToast() {
  const contexto = useContext(ToastContext);
  if (!contexto) throw new Error('useToast debe usarse dentro de ToastProvider');
  return contexto;
}
