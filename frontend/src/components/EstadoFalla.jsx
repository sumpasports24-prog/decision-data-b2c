/** No culpa al usuario, aclara que nada se perdió, da código de referencia y botón de reintento. */
export function EstadoFalla({ error, onReintentar }) {
  return (
    <div className="tarjeta" role="alert" style={{ borderColor: 'var(--rojo)', display: 'grid', gap: 10 }}>
      <strong>No pudimos completar esto</strong>
      <p className="texto-secundario" style={{ margin: 0 }}>
        {error?.message ?? 'Algo falló de nuestro lado.'}
      </p>
      {error?.codigoReferencia && (
        <p className="mono texto-secundario" style={{ margin: 0, fontSize: '0.85rem' }}>
          Código de referencia: {error.codigoReferencia}
        </p>
      )}
      {onReintentar && (
        <button type="button" className="boton boton--secundario" onClick={onReintentar}>
          Reintentar
        </button>
      )}
    </div>
  );
}
