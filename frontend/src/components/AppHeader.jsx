import { ArrowLeft, LogOut } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import logo from '../assets/marca/dd-lockup-white.png';

export function AppHeader({ conVolver, onLogout }) {
  const navigate = useNavigate();

  return (
    <header
      style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        gap: 12,
      }}
    >
      {conVolver ? (
        <button
          type="button"
          onClick={() => navigate('/')}
          className="icono-boton"
          aria-label="Volver al panorama"
        >
          <ArrowLeft size={20} aria-hidden="true" />
        </button>
      ) : (
        <img src={logo} alt="Decision Data" style={{ width: 140 }} />
      )}

      {onLogout && (
        <button type="button" className="icono-boton" onClick={onLogout} aria-label="Cerrar sesión">
          <LogOut size={20} aria-hidden="true" />
        </button>
      )}
    </header>
  );
}
