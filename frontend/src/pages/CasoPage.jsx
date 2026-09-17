import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { apiFetch, ApiError, SesionExpiradaError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { EstadoCarga } from '../components/EstadoCarga';
import { EstadoFalla } from '../components/EstadoFalla';
import { EstadoBadge } from '../components/EstadoBadge';
import { ModalConfirmacion } from '../components/ModalConfirmacion';

const ETIQUETAS_EVENTO = {
  caso_creado: 'El Centinela detectó esta consulta y abrió el caso',
  estado_cambiado: 'Cambio de estado',
  notificacion_enviada: 'Se envió una notificación',
  consentimiento_firmado: 'Firmaste la autorización',
  consentimiento_revocado: 'Revocaste la autorización',
  oposicion_generada: 'El Gestor redactó la oposición',
};

export function CasoPage() {
  const { id } = useParams();
  const [caso, setCaso] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [firmando, setFirmando] = useState(false);
  const [mostrarConfirmacion, setMostrarConfirmacion] = useState(false);
  const [revocando, setRevocando] = useState(false);
  const { sesionExpirada } = useAuth();
  const navigate = useNavigate();

  const cargar = useCallback(async () => {
    setCargando(true);
    setError(null);
    try {
      const respuesta = await apiFetch(`/casos/${id}`);
      setCaso(respuesta);
    } catch (err) {
      if (err instanceof SesionExpiradaError) {
        sesionExpirada();
        return;
      }
      setError(err);
    } finally {
      setCargando(false);
    }
  }, [id, sesionExpirada]);

  useEffect(() => {
    cargar();
  }, [cargar]);

  async function firmar() {
    setFirmando(true);
    try {
      await apiFetch(`/casos/${id}/consentimiento`, { method: 'POST' });
      await cargar();
    } catch (err) {
      setError(err);
    } finally {
      setFirmando(false);
    }
  }

  async function revocar(consentimientoId) {
    setRevocando(true);
    try {
      await apiFetch(`/consentimientos/${consentimientoId}/revocar`, { method: 'POST' });
      setMostrarConfirmacion(false);
      await cargar();
    } catch (err) {
      setError(err);
    } finally {
      setRevocando(false);
    }
  }

  if (cargando) {
    return (
      <div className="contenedor">
        <EstadoCarga lineas={5} />
      </div>
    );
  }

  if (error instanceof ApiError && error.status === 403) {
    return (
      <div className="contenedor">
        <EstadoFalla error={{ message: 'No tienes permiso para ver este caso.' }} />
        <Link to="/" className="boton boton--secundario" style={{ marginTop: 16 }}>
          Volver al panorama
        </Link>
      </div>
    );
  }

  if (error instanceof ApiError && error.status === 404) {
    return (
      <div className="contenedor">
        <EstadoFalla error={{ message: 'Este caso no existe o fue movido.' }} />
      </div>
    );
  }

  if (error) {
    return (
      <div className="contenedor">
        <EstadoFalla error={error} onReintentar={cargar} />
      </div>
    );
  }

  const consentimientoVigente = caso.consentimientos?.find((c) => c.firmado_en && !c.revocado_en);

  return (
    <div className="contenedor" style={{ display: 'grid', gap: 24 }}>
      <button type="button" onClick={() => navigate('/')} className="boton boton--secundario" style={{ justifySelf: 'start' }}>
        ← Panorama
      </button>

      <section className="tarjeta" style={{ display: 'grid', gap: 10 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12, flexWrap: 'wrap' }}>
          <div>
            <span className="mono texto-secundario" style={{ fontSize: '0.85rem' }}>
              {caso.codigo}
            </span>
            <h1 style={{ margin: '4px 0', fontSize: '1.2rem' }}>{caso.consulta.entidad_nombre}</h1>
          </div>
          <EstadoBadge estado={caso.estado} />
        </div>
        <p className="texto-secundario" style={{ margin: 0 }}>
          {caso.consulta.motivo} · consultado el{' '}
          {new Date(caso.consulta.consultada_en).toLocaleDateString('es-EC')}
        </p>
        {caso.vence_en && (
          <p className="texto-secundario mono" style={{ margin: 0, fontSize: '0.85rem' }}>
            Plazo: {new Date(caso.vence_en).toLocaleDateString('es-EC')}
          </p>
        )}
      </section>

      {caso.estado === 'notificado' && (
        <section className="tarjeta" style={{ borderColor: 'var(--ambar)', display: 'grid', gap: 12 }}>
          <strong>Esta consulta no la reconociste. ¿Autorizas a Decision Data a gestionarla por ti?</strong>
          <p className="texto-secundario" style={{ margin: 0, fontSize: '0.9rem' }}>
            {'v1: '}
            Autorizo a Decision Data a presentar, en mi nombre, una oposición al tratamiento de datos
            personales (LOPDP) ante la entidad reportante de este caso, y a dar seguimiento al trámite
            hasta su resolución o escalamiento.
          </p>
          {error && <EstadoFalla error={error} onReintentar={firmar} />}
          <button type="button" className="boton boton--primario" onClick={firmar} disabled={firmando}>
            {firmando ? 'Firmando…' : 'Autorizar y firmar'}
          </button>
        </section>
      )}

      {consentimientoVigente && caso.estado !== 'notificado' && (
        <section className="tarjeta" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
          <div>
            <strong>Autorización vigente</strong>
            <div className="texto-secundario" style={{ fontSize: '0.85rem' }}>
              Firmada el {new Date(consentimientoVigente.firmado_en).toLocaleDateString('es-EC')}
            </div>
          </div>
          <button type="button" className="boton boton--peligro" onClick={() => setMostrarConfirmacion(true)}>
            Revocar
          </button>
        </section>
      )}

      {caso.documentos?.length > 0 && (
        <section style={{ display: 'grid', gap: 12 }}>
          <h2 style={{ fontSize: '1.05rem', margin: 0 }}>Documentos generados</h2>
          {caso.documentos.map((doc) => (
            <details key={doc.id} className="tarjeta">
              <summary style={{ cursor: 'pointer', fontWeight: 600 }}>
                {doc.tipo} · {new Date(doc.creado_en).toLocaleDateString('es-EC')}
              </summary>
              <pre style={{ whiteSpace: 'pre-wrap', fontFamily: 'var(--fuente-tecnica)', fontSize: '0.85rem', marginTop: 12 }}>
                {doc.contenido}
              </pre>
            </details>
          ))}
        </section>
      )}

      <section style={{ display: 'grid', gap: 12 }}>
        <h2 style={{ fontSize: '1.05rem', margin: 0 }}>Bitácora del caso</h2>
        <div className="tarjeta" style={{ display: 'grid', gap: 16 }}>
          {caso.eventos.map((evento) => (
            <div key={evento.id} style={{ display: 'grid', gridTemplateColumns: '90px 1fr', gap: 12 }}>
              <span className="mono texto-secundario" style={{ fontSize: '0.78rem' }}>
                {new Date(evento.ocurrio_en).toLocaleDateString('es-EC')}
              </span>
              <span>{ETIQUETAS_EVENTO[evento.tipo] ?? evento.tipo}</span>
            </div>
          ))}
        </div>
      </section>

      {mostrarConfirmacion && (
        <ModalConfirmacion
          titulo="¿Revocar tu autorización?"
          descripcion="Decision Data dejará de actuar en tu nombre para este caso. El trámite ya iniciado no se deshace."
          textoConfirmar={revocando ? 'Revocando…' : 'Sí, revocar'}
          peligro
          onCancelar={() => setMostrarConfirmacion(false)}
          onConfirmar={() => revocar(consentimientoVigente.id)}
        />
      )}
    </div>
  );
}
