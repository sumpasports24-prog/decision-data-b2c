export function ModalConfirmacion({ titulo, descripcion, textoConfirmar, onConfirmar, onCancelar, peligro }) {
  return (
    <div
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-confirmacion-titulo"
      style={{
        position: 'fixed',
        inset: 0,
        background: 'rgba(4, 8, 20, 0.7)',
        display: 'grid',
        placeItems: 'center',
        padding: 20,
        zIndex: 50,
      }}
    >
      <div className="tarjeta" style={{ maxWidth: 420, width: '100%', display: 'grid', gap: 14 }}>
        <strong id="modal-confirmacion-titulo">{titulo}</strong>
        <p className="texto-secundario" style={{ margin: 0 }}>
          {descripcion}
        </p>
        <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end', flexWrap: 'wrap' }}>
          <button type="button" className="boton boton--secundario" onClick={onCancelar}>
            Cancelar
          </button>
          <button
            type="button"
            className={`boton ${peligro ? 'boton--peligro' : 'boton--primario'}`}
            onClick={onConfirmar}
          >
            {textoConfirmar}
          </button>
        </div>
      </div>
    </div>
  );
}
