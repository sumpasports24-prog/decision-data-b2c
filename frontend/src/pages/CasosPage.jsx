import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiFetch, SesionExpiradaError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { EstadoCarga } from '../components/EstadoCarga';
import { EstadoVacio } from '../components/EstadoVacio';
import { EstadoFalla } from '../components/EstadoFalla';
import { EstadoStepper } from '../components/EstadoStepper';
import { Nav } from '../components/Nav';

export function CasosPage() {
  const [casos, setCasos] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(true);
  const { sesionExpirada } = useAuth();

  const cargar = useCallback(async () => {
    setCargando(true);
    setError(null);
    try {
      setCasos(await apiFetch('/casos'));
    } catch (err) {
      if (err instanceof SesionExpiradaError) {
        sesionExpirada();
        return;
      }
      setError(err);
    } finally {
      setCargando(false);
    }
  }, [sesionExpirada]);

  useEffect(() => {
    cargar();
  }, [cargar]);

  return (
    <div className="con-nav-inferior">
      <Nav />
      <div className="contenedor-ancho">
        <h1 style={{ fontSize: '1.4rem', marginBottom: 20 }}>Mis casos</h1>

        {cargando && <EstadoCarga lineas={4} />}
        {!cargando && error && <EstadoFalla error={error} onReintentar={cargar} />}

        {!cargando && !error && casos && (
          <div className="entrada-escalonada" style={{ display: 'grid', gap: 14 }}>
            {casos.length === 0 ? (
              <EstadoVacio
                titulo="No tienes casos abiertos"
                descripcion="El Centinela sigue revisando tu huella todos los días y abrirá un caso solo si aparece algo raro."
              />
            ) : (
              casos.map((caso) => (
                <Link
                  key={caso.id}
                  to={`/casos/${caso.id}`}
                  className="tarjeta"
                  style={{ display: 'grid', gap: 14, textDecoration: 'none', color: 'inherit' }}
                >
                  <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12, flexWrap: 'wrap' }}>
                    <div>
                      <div className="mono texto-secundario" style={{ fontSize: '0.78rem' }}>
                        {caso.codigo}
                      </div>
                      <strong style={{ fontSize: '1.05rem' }}>{caso.consulta?.entidad_nombre}</strong>
                      <div className="texto-secundario" style={{ fontSize: '0.85rem' }}>
                        {caso.consulta?.motivo}
                      </div>
                    </div>
                  </div>
                  <EstadoStepper estado={caso.estado} />
                </Link>
              ))
            )}
          </div>
        )}
      </div>
    </div>
  );
}
