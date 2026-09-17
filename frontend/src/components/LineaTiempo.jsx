import { Bell, CheckCircle2, FileText, RadioTower, ShieldCheck, ShieldOff, Workflow } from 'lucide-react';

const ETIQUETAS = {
  caso_creado: 'El Centinela detectó esta consulta y abrió el caso',
  estado_cambiado: 'Cambio de estado',
  notificacion_enviada: 'Se envió una notificación',
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

export function LineaTiempo({ eventos }) {
  return (
    <div className="linea-tiempo entrada-escalonada">
      {eventos.map((evento) => {
        const Icono = ICONOS[evento.tipo] ?? CheckCircle2;
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
            </div>
          </div>
        );
      })}
    </div>
  );
}
