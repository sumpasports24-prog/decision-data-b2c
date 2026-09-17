import { NavLink } from 'react-router-dom';
import { LayoutDashboard, FolderClock, LogOut, Radar as RadarIcon } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { iniciales } from '../utils/texto.jsx';
import logo from '../assets/marca/dd-lockup-white.png';

const ENLACES = [
  { a: '/', etiqueta: 'Panorama', Icono: LayoutDashboard },
  { a: '/casos', etiqueta: 'Mis casos', Icono: FolderClock },
  { a: '/huella', etiqueta: 'Huella', Icono: RadarIcon },
];

export function Nav() {
  const { persona, logout } = useAuth();

  return (
    <>
      <header className="nav-superior">
        <div className="nav-superior__marca">
          <img src={logo} alt="Decision Data" style={{ width: 130 }} />
          <nav className="nav-superior__enlaces" aria-label="Principal">
            {ENLACES.map(({ a, etiqueta }) => (
              <NavLink key={a} to={a} end={a === '/'} className="nav-superior__enlace">
                {etiqueta}
              </NavLink>
            ))}
          </nav>
        </div>
        {persona && (
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <span className="texto-secundario" style={{ fontSize: '0.85rem' }}>
              {persona.nombre}
            </span>
            <span className="avatar" aria-hidden="true">
              {iniciales(persona.nombre)}
            </span>
            <button type="button" className="icono-boton" onClick={logout} aria-label="Cerrar sesión" style={{ width: 36, height: 36 }}>
              <LogOut size={16} aria-hidden="true" />
            </button>
          </div>
        )}
      </header>

      <nav className="nav-inferior" aria-label="Principal">
        {ENLACES.map(({ a, etiqueta, Icono }) => (
          <NavLink key={a} to={a} end={a === '/'} className="nav-inferior__enlace">
            <Icono size={20} aria-hidden="true" />
            {etiqueta}
          </NavLink>
        ))}
      </nav>
    </>
  );
}
