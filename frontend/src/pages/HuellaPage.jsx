import { Building2 } from 'lucide-react';
import { useHuella } from '../hooks/useHuella';
import { estadoReconocimiento } from '../utils/reconocimiento';
import { EstadoCarga } from '../components/EstadoCarga';
import { EstadoVacio } from '../components/EstadoVacio';
import { EstadoFalla } from '../components/EstadoFalla';
import { Nav } from '../components/Nav';

export function HuellaPage() {
  const { huella, error, cargando, recargar } = useHuella();

  return (
    <div className="con-nav-inferior">
      <Nav />
      <div className="contenedor-ancho">
        <h1 style={{ fontSize: '1.4rem', marginBottom: 6 }}>Huella de consulta</h1>
        <p className="texto-secundario" style={{ marginTop: 0, marginBottom: 20 }}>
          Cada vez que alguien pide tu reporte queda registrado aquí.
        </p>

        {cargando && <EstadoCarga lineas={5} />}
        {!cargando && error && <EstadoFalla error={error} onReintentar={recargar} />}

        {!cargando && !error && huella && (
          <>
            {huella.length === 0 ? (
              <EstadoVacio titulo="Sin consultas registradas" descripcion="Aquí aparecerá cada vez que alguien te consulte." />
            ) : (
              <div className="tarjeta entrada-escalonada" style={{ display: 'grid', gap: 2, padding: 6 }}>
                {huella.map((consulta) => (
                  <div
                    key={consulta.id}
                    className="tarjeta--interna"
                    style={{
                      background: 'transparent',
                      display: 'flex',
                      justifyContent: 'space-between',
                      alignItems: 'flex-start',
                      gap: 12,
                      flexWrap: 'wrap',
                    }}
                  >
                    <div style={{ display: 'flex', alignItems: 'flex-start', gap: 10, minWidth: 0 }}>
                      <Building2 size={16} color="var(--texto-secundario)" style={{ marginTop: 3, flex: 'none' }} aria-hidden="true" />
                      <div>
                        <strong>{consulta.entidad_nombre}</strong>
                        <div className="texto-secundario" style={{ fontSize: '0.85rem' }}>
                          {consulta.motivo}
                        </div>
                      </div>
                    </div>
                    <div style={{ textAlign: 'right', flex: 'none' }}>
                      <div className="texto-secundario mono" style={{ fontSize: '0.85rem' }}>
                        {new Date(consulta.consultada_en).toLocaleDateString('es-EC')}
                      </div>
                      <div
                        style={{
                          fontSize: '0.72rem',
                          fontWeight: 600,
                          color: estadoReconocimiento(consulta.reconocida).color,
                          marginTop: 2,
                        }}
                      >
                        {estadoReconocimiento(consulta.reconocida).etiqueta}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
