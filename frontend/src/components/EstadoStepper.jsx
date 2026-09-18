const PASOS = ['detectado', 'notificado', 'autorizado', 'en_gestion', 'resuelto'];

const ETIQUETAS = {
  detectado: 'Detectado',
  notificado: 'Notificado',
  autorizado: 'Autorizado',
  en_gestion: 'En gestión',
  escalado: 'Escalado',
  resuelto: 'Resuelto',
};

/**
 * Stepper visual de los 5 pasos del caso. `escalado` se muestra en el lugar
 * de `en_gestion`, en rojo. `descartado` no es un paso del pipeline — es la
 * rama corta desde `notificado` cuando la persona reconoce la consulta —
 * así que se muestra aparte, sin píldoras.
 */
export function EstadoStepper({ estado }) {
  if (estado === 'descartado') {
    return <div className="stepper-descartado">Descartado — la reconociste</div>;
  }

  const pasos = estado === 'escalado' ? PASOS.map((p) => (p === 'en_gestion' ? 'escalado' : p)) : PASOS;
  const indiceActual = pasos.indexOf(estado);

  return (
    <div role="img" aria-label={`Estado del caso: ${ETIQUETAS[estado] ?? estado}`}>
      {/* 5 píldoras: se esconde en pantallas angostas, donde el texto se corta feo. */}
      <div className="stepper stepper--completo" aria-hidden="true">
        {pasos.map((paso, i) => {
          let clase = 'stepper__paso';
          if (i === indiceActual) clase += ` stepper__paso--actual stepper__paso--${paso}`;
          else if (i < indiceActual) clase += ' stepper__paso--hecho';

          return (
            <div key={paso} className={clase}>
              {ETIQUETAS[paso]}
            </div>
          );
        })}
      </div>

      {/* Compacto para móvil: paso actual + barra de progreso, nada de texto cortado. */}
      <div className="stepper-compacto" aria-hidden="true">
        <div className={`stepper-compacto__etiqueta stepper__paso--${estado}`}>{ETIQUETAS[estado]}</div>
        <div className="stepper-compacto__barra">
          <div
            className="stepper-compacto__relleno"
            style={{ width: `${((indiceActual + 1) / pasos.length) * 100}%` }}
          />
        </div>
        <span className="texto-secundario mono" style={{ fontSize: '0.7rem' }}>
          {indiceActual + 1}/{pasos.length}
        </span>
      </div>
    </div>
  );
}
