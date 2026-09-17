/** Esqueleto con la forma del contenido real: nunca pantalla en blanco ni salto de layout. */
export function EstadoCarga({ lineas = 3 }) {
  return (
    <div aria-busy="true" aria-label="Cargando" style={{ display: 'grid', gap: 12 }}>
      <div className="esqueleto" style={{ height: 28, width: '60%' }} />
      {Array.from({ length: lineas }).map((_, i) => (
        <div key={i} className="esqueleto" style={{ height: 64, width: '100%' }} />
      ))}
    </div>
  );
}
