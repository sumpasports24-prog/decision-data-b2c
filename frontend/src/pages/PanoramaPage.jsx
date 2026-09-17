import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { AlertTriangle, Building2, RadioTower } from 'lucide-react';
import { usePanorama } from '../hooks/usePanorama';
import { EstadoCarga } from '../components/EstadoCarga';
import { EstadoVacio } from '../components/EstadoVacio';
import { EstadoFalla } from '../components/EstadoFalla';
import { EstadoStepper } from '../components/EstadoStepper';
import { Nav } from '../components/Nav';
import { PuntajeGauge } from '../components/PuntajeGauge';
import { VigilanciaActiva } from '../components/VigilanciaActiva';
import { diasRestantes } from '../utils/fechas';

export function PanoramaPage() {
  const { datos, error, cargando, ultimaActualizacion, recargar } = usePanorama();

  const casosActivos = datos?.casos.filter((c) => c.estado !== 'resuelto') ?? [];
  const casosResueltos = datos?.casos.filter((c) => c.estado === 'resuelto') ?? [];

  return (
    <div className="con-nav-inferior">
      <Nav />
      <div className="contenedor-ancho">
        {cargando && <EstadoCarga lineas={4} />}
        {!cargando && error && <EstadoFalla error={error} onReintentar={recargar} />}

        {!cargando && !error && datos && (
          <div className="entrada" style={{ display: 'grid', gap: 24 }}>
            {datos.alertas.map((caso) => (
              <section
                key={caso.id}
                className="tarjeta"
                style={{
                  borderColor: 'var(--ambar)',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'space-between',
                  gap: 20,
                  flexWrap: 'wrap',
                }}
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                  <span
                    style={{
                      width: 40,
                      height: 40,
                      borderRadius: 12,
                      background: 'var(--ambar)',
                      display: 'grid',
                      placeItems: 'center',
                      flex: 'none',
                    }}
                  >
                    <AlertTriangle size={20} color="var(--fondo)" aria-hidden="true" />
                  </span>
                  <div>
                    <strong>Hay una consulta que no reconociste</strong>
                    <p className="texto-secundario" style={{ margin: '3px 0 0', fontSize: '0.9rem' }}>
                      {caso.consulta?.entidad_nombre} revisó tu historial. Tú no reconociste esa consulta.
                    </p>
                  </div>
                </div>
                <Link to={`/casos/${caso.id}`} className="boton boton--primario" style={{ background: 'var(--ambar)' }}>
                  Revisar el caso
                </Link>
              </section>
            ))}

            <div className="columnas">
              <div className="columna-fija">
                <section className="tarjeta" style={{ display: 'grid', gap: 16 }}>
                  <span className="texto-secundario">Tu puntaje</span>
                  <div style={{ display: 'flex', alignItems: 'baseline', gap: 10 }}>
                    <span className="mono" style={{ fontSize: '2.6rem', fontWeight: 600, lineHeight: 1 }}>
                      {datos.score}
                    </span>
                    <span className="texto-secundario">/ 1000</span>
                  </div>
                  <PuntajeGauge valor={datos.score} />
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.9rem', lineHeight: 1.6 }}>
                    Este puntaje es contexto de tus casos, no el producto en sí: lo que importa es lo
                    que Decision Data gestiona por ti.
                  </p>
                </section>

                <section className="tarjeta" style={{ display: 'grid', gap: 14 }}>
                  <span className="texto-secundario">Quién te ha consultado</span>
                  <div style={{ display: 'grid', gap: 12 }}>
                    {datos.huella_de_consulta.slice(0, 3).map((consulta) => (
                      <div key={consulta.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 10 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10, minWidth: 0 }}>
                          <Building2 size={15} color="var(--texto-secundario)" style={{ flex: 'none' }} aria-hidden="true" />
                          <div style={{ minWidth: 0 }}>
                            <div style={{ fontSize: '0.9rem', fontWeight: 500 }}>{consulta.entidad_nombre}</div>
                            <div className="texto-secundario" style={{ fontSize: '0.78rem' }}>
                              {new Date(consulta.consultada_en).toLocaleDateString('es-EC')}
                            </div>
                          </div>
                        </div>
                        <span
                          style={{
                            fontSize: '0.72rem',
                            fontWeight: 600,
                            color: consulta.reconocida ? 'var(--verde)' : 'var(--ambar)',
                            whiteSpace: 'nowrap',
                          }}
                        >
                          {consulta.reconocida ? 'Reconocida' : 'En disputa'}
                        </span>
                      </div>
                    ))}
                  </div>
                  <Link to="/huella" className="boton boton--secundario" style={{ marginTop: 4 }}>
                    Ver las {datos.huella_de_consulta.length} consultas
                  </Link>
                </section>
              </div>

              <div className="columna-flexible">
                <section className="tarjeta" style={{ display: 'grid', gap: 18 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
                    <strong style={{ fontSize: '1.1rem' }}>Casos abiertos</strong>
                    <span className="texto-secundario" style={{ fontSize: '0.85rem' }}>
                      Nosotros los movemos. Tú solo autorizas.
                    </span>
                  </div>

                  {casosActivos.length === 0 ? (
                    <EstadoVacio
                      titulo="No tienes casos abiertos"
                      descripcion="El Centinela sigue revisando tu huella todos los días y abrirá un caso solo si aparece algo raro."
                    />
                  ) : (
                    casosActivos.map((caso) => <TarjetaCaso key={caso.id} caso={caso} />)
                  )}

                  {casosResueltos.map((caso) => (
                    <div
                      key={caso.id}
                      className="tarjeta--interna"
                      style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}
                    >
                      <div>
                        <div className="mono texto-secundario" style={{ fontSize: '0.72rem' }}>
                          {caso.codigo}
                        </div>
                        <strong style={{ fontSize: '0.95rem' }}>{caso.consulta?.entidad_nombre}</strong>
                      </div>
                      <span
                        style={{
                          fontSize: '0.72rem',
                          fontWeight: 600,
                          color: 'var(--verde)',
                          border: '1px solid var(--verde)',
                          borderRadius: 999,
                          padding: '4px 10px',
                        }}
                      >
                        RESUELTO
                      </span>
                    </div>
                  ))}

                  <div
                    style={{
                      marginTop: 4,
                      borderTop: '1px solid var(--borde)',
                      paddingTop: 16,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between',
                      gap: 16,
                      flexWrap: 'wrap',
                    }}
                  >
                    <p className="texto-secundario" style={{ margin: 0, fontSize: '0.85rem', maxWidth: 440 }}>
                      El Centinela revisa tu huella todos los días. Si aparece algo raro, abrimos el caso
                      solos y te escribimos.
                    </p>
                    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 4 }}>
                      <VigilanciaActiva />
                      {ultimaActualizacion && (
                        <span
                          className="texto-secundario mono"
                          style={{ fontSize: '0.68rem', display: 'flex', alignItems: 'center', gap: 4 }}
                        >
                          <RadioTower size={11} aria-hidden="true" />
                          <TiempoDesde fecha={ultimaActualizacion} />
                        </span>
                      )}
                    </div>
                  </div>
                </section>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

/** "actualizado hace Xs": prueba visible de que el panorama se refresca solo, sin recargar la página. */
function TiempoDesde({ fecha }) {
  const [, forzarTic] = useState(0);

  useEffect(() => {
    const tic = setInterval(() => forzarTic((n) => n + 1), 1000);
    return () => clearInterval(tic);
  }, []);

  const segundos = Math.max(0, Math.round((Date.now() - fecha.getTime()) / 1000));

  return <span>actualizado hace {segundos}s</span>;
}

function TarjetaCaso({ caso }) {
  return (
    <div className="tarjeta--interna" style={{ display: 'grid', gap: 14 }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12 }}>
        <div>
          <div className="mono texto-secundario" style={{ fontSize: '0.72rem' }}>
            {caso.codigo}
          </div>
          <strong style={{ fontSize: '1rem', display: 'block', marginTop: 4 }}>{caso.consulta?.entidad_nombre}</strong>
        </div>
        {caso.vence_en && (
          <div style={{ textAlign: 'right', flex: 'none' }}>
            <div className="mono" style={{ fontSize: '1.3rem', fontWeight: 600, color: 'var(--ambar)', lineHeight: 1 }}>
              {diasRestantes(caso.vence_en)}
            </div>
            <div className="texto-secundario" style={{ fontSize: '0.7rem' }}>
              días hábiles
            </div>
          </div>
        )}
      </div>
      <EstadoStepper estado={caso.estado} />
      <Link to={`/casos/${caso.id}`} className="boton boton--primario">
        Abrir el caso
      </Link>
    </div>
  );
}
