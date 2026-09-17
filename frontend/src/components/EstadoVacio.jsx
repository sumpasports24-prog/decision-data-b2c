export function EstadoVacio({ titulo, descripcion, accion }) {
  return (
    <div className="tarjeta" style={{ textAlign: 'center', display: 'grid', gap: 10, justifyItems: 'center' }}>
      <strong>{titulo}</strong>
      <p className="texto-secundario" style={{ margin: 0 }}>
        {descripcion}
      </p>
      {accion}
    </div>
  );
}
