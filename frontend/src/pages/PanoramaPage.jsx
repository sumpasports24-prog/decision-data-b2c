import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AlertTriangle, Building2, ChevronRight, Radar } from 'lucide-react';
import { apiFetch, SesionExpiradaError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { EstadoCarga } from '../components/EstadoCarga';
import { EstadoVacio } from '../components/EstadoVacio';
import { EstadoFalla } from '../components/EstadoFalla';
import { EstadoBadge } from '../components/EstadoBadge';
import { AppHeader } from '../components/AppHeader';
import { PuntajeGauge } from '../components/PuntajeGauge';

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
      <AppHeader onLogout={logout} />

      {cargando && <EstadoCarga lineas={4} />}
      {!cargando && error && <EstadoFalla error={error} onReintentar={cargar} />}

      {!cargando && !error && datos && (
        <div className="entrada-escalonada" style={{ display: 'grid', gap: 24 }}>
          <section className="tarjeta" style={{ display: 'grid', gap: 12 }}>
            <span className="texto-secundario">Hola, {datos.persona.nombre.split(' ')[0]}</span>
            <div style={{ display: 'flex', alignItems: 'baseline', gap: 10 }}>
              <span className="mono" style={{ fontSize: '2.1rem', fontWeight: 600, lineHeight: 1 }}>
                {datos.score}
              </span>
              <span className="texto-secundario">/1000 · score de contexto</span>
            </div>
            <PuntajeGauge valor={datos.score} />
          </section>

          {datos.alertas.length > 0 && (
            <section className="tarjeta" style={{ borderColor: 'var(--ambar)', display: 'grid', gap: 12 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                <AlertTriangle size={18} color="var(--ambar)" aria-hidden="true" />
                <strong>Necesitan tu autorización</strong>
              </div>
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
              <div style={{ display: 'grid', gap: 10 }}>
                {datos.casos.map((caso) => (
                  <FilaCaso key={caso.id} caso={caso} />
                ))}
              </div>
            )}
          </section>

          <section style={{ display: 'grid', gap: 12 }}>
            <h2 style={{ fontSize: '1.05rem', margin: 0 }}>Huella de consulta</h2>
            {datos.huella_de_consulta.length === 0 ? (
              <EstadoVacio titulo="Sin consultas registradas" descripcion="Aquí aparecerá cada vez que alguien te consulte." />
            ) : (
              <div className="tarjeta" style={{ display: 'grid', gap: 2, padding: 6 }}>
                {datos.huella_de_consulta.map((consulta) => (
                  <div
                    key={consulta.id}
                    className="tarjeta--interna"
                    style={{ background: 'transparent', display: 'flex', flexDirection: 'column', gap: 6 }}
                  >
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12 }}>
                      <div style={{ display: 'flex', alignItems: 'flex-start', gap: 10, minWidth: 0 }}>
                        <Building2 size={16} color="var(--texto-secundario)" style={{ marginTop: 2, flex: 'none' }} aria-hidden="true" />
                        <strong>{consulta.entidad_nombre}</strong>
                      </div>
                      <span className="texto-secundario mono" style={{ fontSize: '0.85rem', flex: 'none' }}>
                        {new Date(consulta.consultada_en).toLocaleDateString('es-EC')}
                      </span>
                    </div>
                    <div className="texto-secundario" style={{ fontSize: '0.85rem', paddingLeft: 26 }}>
                      {consulta.motivo}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </section>

          <p
            className="texto-secundario"
            style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: '0.85rem', justifyContent: 'center' }}
          >
            <Radar size={15} aria-hidden="true" />
            El Centinela sigue vigilando tu huella de consulta en segundo plano.
          </p>
        </div>
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
      <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
        <EstadoBadge estado={caso.estado} />
        <ChevronRight size={18} color="var(--texto-secundario)" aria-hidden="true" />
      </div>
    </Link>
  );
}
