import { Bell, CheckCircle2, FileText, MessageCircle, RadioTower, ShieldCheck, ShieldOff, Workflow } from 'lucide-react';

const ETIQUETAS = {
  caso_creado: 'El Centinela detectó esta consulta y abrió el caso',
  estado_cambiado: 'Cambio de estado',
  notificacion_enviada: 'Te avisamos por WhatsApp',
  consentimiento_firmado: 'Firmaste la autorización',
  consentimiento_revocado: 'Revocaste la autorización',
  oposicion_generada: 'El Gestor redactó la oposición',
};

const ICONOS = {
  caso_creado: RadioTower,
  estado_cambiado: Workflow,
  notificacion_enviada: Bell,
  consentimiento_firmado: ShieldCheck,
  consentimiento_revocado: ShieldOff,
  oposicion_generada: FileText,
};

/** Convierte el *énfasis* estilo WhatsApp (un asterisco) en <strong>. */
function conNegritas(texto) {
  return texto.split(/\*([^*]+)\*/g).map((parte, i) => (i % 2 === 1 ? <strong key={i}>{parte}</strong> : parte));
}

export function LineaTiempo({ eventos }) {
  return (
    <div className="linea-tiempo entrada-escalonada">
      {eventos.map((evento) => {
        const Icono = ICONOS[evento.tipo] ?? CheckCircle2;
        const mensajeWhatsApp = evento.tipo === 'notificacion_enviada' ? evento.carga?.mensaje : null;

        return (
          <div key={evento.id} className="linea-tiempo__item">
            <div className="linea-tiempo__riel">
              <span className="linea-tiempo__punto" />
              <span className="linea-tiempo__linea" />
            </div>
            <div className="linea-tiempo__contenido">
              <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                <Icono size={16} color="var(--texto-secundario)" aria-hidden="true" />
                <span>{ETIQUETAS[evento.tipo] ?? evento.tipo}</span>
              </div>
              <span className="mono texto-secundario" style={{ fontSize: '0.78rem' }}>
                {new Date(evento.ocurrio_en).toLocaleString('es-EC', {
                  day: '2-digit',
                  month: 'short',
                  hour: '2-digit',
                  minute: '2-digit',
                })}
              </span>

              {mensajeWhatsApp && (
                <div
                  style={{
                    marginTop: 10,
                    maxWidth: 360,
                    background: '#16203A',
                    border: '1px solid var(--borde)',
                    borderRadius: '4px 14px 14px 14px',
                    padding: '10px 14px',
                    display: 'grid',
                    gap: 6,
                  }}
                >
                  <div
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      gap: 6,
                      fontSize: '0.72rem',
                      color: 'var(--verde)',
                      fontWeight: 600,
                    }}
                  >
                    <MessageCircle size={13} aria-hidden="true" />
                    WHATSAPP · DECISION DATA
                  </div>
                  <p style={{ margin: 0, fontSize: '0.85rem', lineHeight: 1.5 }}>{conNegritas(mensajeWhatsApp)}</p>
                </div>
              )}
            </div>
          </div>
        );
      })}
    </div>
  );
}
