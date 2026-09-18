import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { Building2, Calendar, FileText, ShieldCheck } from 'lucide-react';
import { ApiError } from '../api/client';
import { useCaso } from '../hooks/useCaso';
import { EstadoCarga } from '../components/EstadoCarga';
import { EstadoFalla } from '../components/EstadoFalla';
import { EstadoBadge } from '../components/EstadoBadge';
import { ModalConfirmacion } from '../components/ModalConfirmacion';
import { Nav } from '../components/Nav';
import { LineaTiempo } from '../components/LineaTiempo';

export function CasoPage() {
  const { id } = useParams();
  const { caso, error, cargando, firmando, revocando, reconociendo, recargar, firmar, revocar, reconocer } = useCaso(id);
  const [mostrarConfirmacion, setMostrarConfirmacion] = useState(false);
  const [mostrarDisputa, setMostrarDisputa] = useState(false);
  const [contexto, setContexto] = useState('');

  if (cargando) {
    return (
      <div className="con-nav-inferior">
        <Nav />
        <div className="contenedor-ancho">
          <EstadoCarga lineas={5} />
        </div>
      </div>
    );
  }

  const mensajeError =
    error instanceof ApiError && error.status === 403
      ? 'No tienes permiso para ver este caso.'
      : error instanceof ApiError && error.status === 404
        ? 'Este caso no existe o fue movido.'
        : null;

  if (mensajeError || error) {
    return (
      <div className="con-nav-inferior">
        <Nav />
        <div className="contenedor-ancho">
          <EstadoFalla error={mensajeError ? { message: mensajeError } : error} onReintentar={mensajeError ? undefined : recargar} />
        </div>
      </div>
    );
  }

  const consentimientoVigente = caso.consentimientos?.find((c) => c.firmado_en && !c.revocado_en);

  return (
    <div className="con-nav-inferior">
      <Nav />
      <div className="contenedor-ancho">
        <div className="entrada" style={{ display: 'grid', gap: 20 }}>
          <div>
            <Link to="/" className="texto-secundario" style={{ fontSize: '0.85rem', textDecoration: 'none' }}>
              ← Volver al panorama
            </Link>
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'flex-end',
                gap: 16,
                flexWrap: 'wrap',
                marginTop: 10,
              }}
            >
              <div>
                <span className="mono texto-secundario" style={{ fontSize: '0.8rem' }}>
                  {caso.codigo}
                </span>
                <h1 style={{ margin: '6px 0 0', fontSize: '1.5rem', display: 'flex', alignItems: 'center', gap: 10 }}>
                  <Building2 size={22} color="var(--texto-secundario)" aria-hidden="true" />
                  {caso.consulta.entidad_nombre}
                </h1>
              </div>
              <EstadoBadge estado={caso.estado} />
            </div>
          </div>

          <div className="columnas">
            <div className="columna-flexible">
              {caso.estado === 'notificado' && !mostrarDisputa && (
                <section className="tarjeta" style={{ borderColor: 'var(--ambar)', display: 'grid', gap: 14 }}>
                  <strong>{caso.consulta.entidad_nombre} revisó tu historial. ¿La reconocés?</strong>
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.9rem' }}>
                    Motivo declarado: {caso.consulta.motivo}
                  </p>
                  {error && <EstadoFalla error={error} onReintentar={reconocer} />}
                  <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
                    <button type="button" className="boton boton--primario" onClick={reconocer} disabled={reconociendo}>
                      {reconociendo ? 'Confirmando…' : 'Sí, fui yo'}
                    </button>
                    <button type="button" className="boton boton--secundario" onClick={() => setMostrarDisputa(true)}>
                      Yo no autoricé esto
                    </button>
                  </div>
                </section>
              )}

              {caso.estado === 'notificado' && mostrarDisputa && (
                <section className="tarjeta" style={{ borderColor: 'var(--ambar)', display: 'grid', gap: 12 }}>
                  <strong>¿Autorizás a Decision Data a gestionarla por ti?</strong>
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.9rem' }}>
                    Autorizo a Decision Data a presentar, en mi nombre, una oposición al tratamiento de
                    datos personales (LOPDP) ante la entidad reportante de este caso, y a dar seguimiento
                    al trámite hasta su resolución o escalamiento.
                  </p>
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.78rem', lineHeight: 1.5 }}>
                    Esto no autoriza una nueva consulta a tu historial — esa ya se hizo y es justamente lo
                    que estás disputando. Autorizás a Decision Data a actuar en tu nombre frente a la
                    entidad, no a la entidad a consultarte de nuevo.
                  </p>
                  <label style={{ display: 'grid', gap: 6 }}>
                    <span className="texto-secundario" style={{ fontSize: '0.85rem' }}>
                      Contanos qué recordás de esa fecha (opcional)
                    </span>
                    <textarea
                      value={contexto}
                      onChange={(e) => setContexto(e.target.value)}
                      maxLength={2000}
                      rows={3}
                      placeholder="Ej: no estuve en esa ciudad, no solicité ningún crédito con esa entidad…"
                      style={{
                        resize: 'vertical',
                        fontFamily: 'inherit',
                        fontSize: '0.9rem',
                        padding: '10px 12px',
                        borderRadius: 'var(--radio-sm)',
                        border: '1px solid var(--borde)',
                        background: 'var(--superficie-2)',
                        color: 'var(--texto)',
                      }}
                    />
                    <span className="texto-secundario" style={{ fontSize: '0.78rem' }}>
                      Si lo escribís, el Gestor lo cita como argumento de hecho en el documento de oposición.
                    </span>
                  </label>
                  {error && <EstadoFalla error={error} onReintentar={() => firmar(contexto)} />}
                  <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
                    <button type="button" className="boton boton--primario" onClick={() => firmar(contexto)} disabled={firmando}>
                      {firmando ? 'Firmando…' : 'Autorizar a Decision Data'}
                    </button>
                    <button type="button" className="boton boton--secundario" onClick={() => setMostrarDisputa(false)}>
                      Volver
                    </button>
                  </div>
                </section>
              )}

              {caso.estado === 'descartado' && (
                <section className="tarjeta" style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                  <ShieldCheck size={18} color="var(--verde)" aria-hidden="true" />
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.9rem' }}>
                    Confirmaste que reconocés esta consulta. No hace falta ninguna acción más.
                  </p>
                </section>
              )}

              <section className="tarjeta" style={{ display: 'grid', gap: 18 }}>
                <strong style={{ fontSize: '1.05rem' }}>Línea de tiempo</strong>
                <LineaTiempo eventos={caso.eventos} />
              </section>

              {caso.documentos?.length > 0 && (
                <section style={{ display: 'grid', gap: 12 }}>
                  <h2 style={{ fontSize: '1.05rem', margin: 0 }}>Documentos generados</h2>
                  {caso.documentos.map((doc) => (
                    <details key={doc.id} className="tarjeta">
                      <summary style={{ cursor: 'pointer', fontWeight: 600, display: 'flex', alignItems: 'center', gap: 8 }}>
                        <FileText size={16} aria-hidden="true" />
                        {doc.tipo} · {new Date(doc.creado_en).toLocaleDateString('es-EC')}
                      </summary>
                      <pre
                        style={{
                          whiteSpace: 'pre-wrap',
                          fontFamily: 'var(--fuente-tecnica)',
                          fontSize: '0.85rem',
                          marginTop: 12,
                          background: 'var(--superficie-2)',
                          padding: 14,
                          borderRadius: 'var(--radio-sm)',
                        }}
                      >
                        {doc.contenido}
                      </pre>
                    </details>
                  ))}
                </section>
              )}
            </div>

            <div className="columna-fija">
              {caso.vence_en && (
                <section className="tarjeta" style={{ display: 'grid', gap: 14 }}>
                  <span className="texto-secundario">Plazo legal</span>
                  <div style={{ display: 'flex', alignItems: 'baseline', gap: 8 }}>
                    <span className="mono" style={{ fontSize: '2.6rem', fontWeight: 600, color: 'var(--ambar)', lineHeight: 1 }}>
                      {Math.max(0, Math.ceil((new Date(caso.vence_en) - new Date()) / 86400000))}
                    </span>
                    <span className="texto-secundario" style={{ fontSize: '0.9rem' }}>
                      días hábiles
                    </span>
                  </div>
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.85rem', display: 'flex', alignItems: 'center', gap: 6 }}>
                    <Calendar size={14} aria-hidden="true" />
                    Vence el {new Date(caso.vence_en).toLocaleDateString('es-EC')}
                  </p>
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.85rem', lineHeight: 1.6 }}>
                    Si no responden antes de esa fecha, escalamos a la Superintendencia de Bancos sin que
                    tengas que hacer nada.
                  </p>
                </section>
              )}

              {consentimientoVigente && (
                <section className="tarjeta" style={{ display: 'grid', gap: 14 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                    <ShieldCheck size={18} color="var(--verde)" aria-hidden="true" />
                    <strong>Tu autorización</strong>
                  </div>
                  <div style={{ display: 'grid', gap: 8, fontSize: '0.85rem' }}>
                    <FilaDato etiqueta="Alcance" valor="Solo este caso" />
                    <FilaDato etiqueta="Entidad" valor={caso.consulta.entidad_nombre} />
                    <FilaDato etiqueta="Firmado" valor={new Date(consentimientoVigente.firmado_en).toLocaleDateString('es-EC')} />
                    <FilaDato etiqueta="Canal" valor={consentimientoVigente.canal} />
                  </div>
                  <button type="button" className="boton boton--peligro" onClick={() => setMostrarConfirmacion(true)}>
                    Revocar autorización
                  </button>
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.78rem', lineHeight: 1.5 }}>
                    Si la revocas, los agentes se detienen de inmediato y el caso queda congelado.
                  </p>
                </section>
              )}

              <section className="tarjeta" style={{ display: 'grid', gap: 10 }}>
                <strong>Qué sigue</strong>
                <p className="texto-secundario" style={{ margin: 0, fontSize: '0.85rem', lineHeight: 1.6 }}>
                  No tienes que hacer nada más. Te avisamos apenas haya respuesta o si vence el plazo.
                </p>
                <div className="tarjeta--interna" style={{ display: 'grid', gap: 4 }}>
                  <span className="mono" style={{ fontSize: '0.7rem', color: 'var(--teal)', letterSpacing: '0.08em' }}>
                    FASE 2
                  </span>
                  <p className="texto-secundario" style={{ margin: 0, fontSize: '0.78rem', lineHeight: 1.5 }}>
                    El agente Vocero llamará a la entidad para dar seguimiento por voz. No está
                    implementado en esta entrega.
                  </p>
                </div>
              </section>
            </div>
          </div>
        </div>
      </div>

      {mostrarConfirmacion && (
        <ModalConfirmacion
          titulo="¿Revocar tu autorización?"
          descripcion="Decision Data dejará de actuar en tu nombre para este caso. El trámite ya iniciado no se deshace."
          textoConfirmar={revocando ? 'Revocando…' : 'Sí, revocar'}
          peligro
          onCancelar={() => setMostrarConfirmacion(false)}
          onConfirmar={() => revocar(consentimientoVigente.id).then(() => setMostrarConfirmacion(false))}
        />
      )}
    </div>
  );
}

function FilaDato({ etiqueta, valor }) {
  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', gap: 10 }}>
      <span className="texto-secundario">{etiqueta}</span>
      <span>{valor}</span>
    </div>
  );
}
