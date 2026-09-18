const ETIQUETAS = {
  detectado: 'Detectado',
  notificado: 'Esperando tu autorización',
  autorizado: 'Autorizado',
  en_gestion: 'En gestión',
  escalado: 'Escalado',
  resuelto: 'Resuelto',
  descartado: 'Descartado — la reconociste',
};

export function EstadoBadge({ estado }) {
  return <span className={`badge badge--${estado}`}>{ETIQUETAS[estado] ?? estado}</span>;
}
