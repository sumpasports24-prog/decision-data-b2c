export function PuntajeGauge({ valor, maximo = 1000 }) {
  const porcentaje = Math.min(100, Math.max(0, (valor / maximo) * 100));

  return (
    <div
      className="gauge"
      role="meter"
      aria-valuenow={valor}
      aria-valuemin={0}
      aria-valuemax={maximo}
      aria-label={`Score de contexto: ${valor} de ${maximo}`}
    >
      <div className="gauge__relleno" style={{ width: `${porcentaje}%` }} />
    </div>
  );
}
