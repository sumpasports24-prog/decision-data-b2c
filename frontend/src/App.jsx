import { Navigate, Route, Routes, useLocation } from 'react-router-dom';
import { useAuth } from './context/AuthContext';
import { LoginPage } from './pages/LoginPage';
import { PanoramaPage } from './pages/PanoramaPage';
import { CasoPage } from './pages/CasoPage';

function RutaProtegida({ children }) {
  const { persona, cargandoSesion } = useAuth();
  const location = useLocation();

  if (cargandoSesion) return null;

  if (!persona) {
    return <Navigate to="/login" replace state={{ desde: location.pathname }} />;
  }

  return children;
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route
        path="/"
        element={
          <RutaProtegida>
            <PanoramaPage />
          </RutaProtegida>
        }
      />
      <Route
        path="/casos/:id"
        element={
          <RutaProtegida>
            <CasoPage />
          </RutaProtegida>
        }
      />
    </Routes>
  );
}
