import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiFetch, SesionExpiradaError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { EstadoCarga } from '../components/EstadoCarga';
import { EstadoVacio } from '../components/EstadoVacio';
import { EstadoFalla } from '../components/EstadoFalla';
import { EstadoBadge } from '../components/EstadoBadge';
import logo from '../assets/marca/dd-lockup-white.png';

export function PanoramaPage() {
  const [datos, setDatos] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(true);
  const { logout, sesionExpirada } = useAuth();

  const cargar = useCallback(async () => {
    setCargando(true);
    setError(null);
    try {
      const respuesta = await apiFetch('/panorama');
      setDatos(respuesta);
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
    <div className="contenedor" style={{ display: 'grid', gap: 24 }}>
      <header style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
        <img src={logo} alt="Decision Data" style={{ width: 150 }} />
        <button type="button" className="boton boton--secundario" onClick={logout}>
          Cerrar sesión
        </button>
      </header>

      {cargando && <EstadoCarga lineas={4} />}
      {!cargando && error && <EstadoFalla error={error} onReintentar={cargar} />}

      {!cargando && !error && datos && (
        <>
          <section className="tarjeta" style={{ display: 'grid', gap: 6 }}>
            <span className="texto-secundario">Hola, {datos.persona.nombre.split(' ')[0]}</span>
            <div style={{ display: 'flex', alignItems: 'baseline', gap: 10 }}>
              <span className="mono" style={{ fontSize: '2rem', fontWeight: 600 }}>
                {datos.score}
              </span>
              <span className="texto-secundario">/1000 · score de contexto</span>
            </div>
          </section>

          {datos.alertas.length > 0 && (
            <section className="tarjeta" style={{ borderColor: 'var(--ambar)', display: 'grid', gap: 12 }}>
              <strong>Necesitan tu autorización</strong>
              {datos.alertas.map((caso) => (
                <FilaCaso key={caso.id} caso={caso} />
              ))}
            </section>
          )}

          <section style={{ display: 'grid', gap: 12 }}>
            <h2 style={{ fontSize: '1.05rem', margin: 0 }}>Tus casos</h2>
            {datos.casos.length === 0 ? (
              <EstadoVacio
                titulo="Todavía no tienes casos abiertos"
                descripcion="El Centinela sigue vigilando tu huella de consulta. Si aparece algo que no reconoces, vas a verlo aquí primero."
              />
            ) : (
              datos.casos.map((caso) => <FilaCaso key={caso.id} caso={caso} />)
            )}
          </section>

          <section style={{ display: 'grid', gap: 12 }}>
            <h2 style={{ fontSize: '1.05rem', margin: 0 }}>Huella de consulta</h2>
            {datos.huella_de_consulta.length === 0 ? (
              <EstadoVacio titulo="Sin consultas registradas" descripcion="Aquí aparecerá cada vez que alguien te consulte." />
            ) : (
              <div className="tarjeta" style={{ display: 'grid', gap: 10, padding: 0 }}>
                {datos.huella_de_consulta.map((consulta, i) => (
                  <div
                    key={consulta.id}
                    className="tarjeta--interna"
                    style={{
                      borderRadius: 0,
                      borderBottom: i === datos.huella_de_consulta.length - 1 ? 'none' : '1px solid var(--borde)',
                      background: 'transparent',
                      display: 'flex',
                      justifyContent: 'space-between',
                      gap: 12,
                      flexWrap: 'wrap',
                    }}
                  >
                    <div>
                      <strong>{consulta.entidad_nombre}</strong>
                      <div className="texto-secundario" style={{ fontSize: '0.85rem' }}>
                        {consulta.motivo}
                      </div>
                    </div>
                    <span className="texto-secundario mono" style={{ fontSize: '0.85rem' }}>
                      {new Date(consulta.consultada_en).toLocaleDateString('es-EC')}
                    </span>
                  </div>
                ))}
              </div>
            )}
          </section>
        </>
      )}
    </div>
  );
}

function FilaCaso({ caso }) {
  return (
    <Link
      to={`/casos/${caso.id}`}
      className="tarjeta--interna"
      style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        gap: 12,
        flexWrap: 'wrap',
        textDecoration: 'none',
        color: 'inherit',
      }}
    >
      <div>
        <strong>{caso.consulta?.entidad_nombre}</strong>
        <div className="mono texto-secundario" style={{ fontSize: '0.8rem' }}>
          {caso.codigo}
        </div>
      </div>
      <EstadoBadge estado={caso.estado} />
    </Link>
  );
}
