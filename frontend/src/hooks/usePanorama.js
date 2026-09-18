import { useCallback, useEffect, useRef, useState } from 'react';
import { obtenerPanorama } from '../api/panorama';
import { SesionExpiradaError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { useToast } from '../context/ToastContext';

const ETIQUETAS_ESTADO = {
  detectado: 'Detectado',
  notificado: 'Notificado',
  autorizado: 'Autorizado',
  en_gestion: 'En gestión',
  escalado: 'Escalado',
  resuelto: 'Resuelto',
  descartado: 'Descartado',
};

const INTERVALO_ACTUALIZACION_MS = 8000;

/**
 * Carga el Panorama y lo mantiene actualizado en segundo plano: si el
 * Centinela abre un caso o alguno cambia de estado mientras la persona
 * sigue mirando la pantalla, se refresca solo y avisa con un toast.
 */
export function usePanorama() {
  const [datos, setDatos] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [ultimaActualizacion, setUltimaActualizacion] = useState(null);
  const { sesionExpirada } = useAuth();
  const { mostrarToast } = useToast();
  const casosConocidos = useRef(null);

  const cargar = useCallback(
    async ({ enSilencio = false } = {}) => {
      if (!enSilencio) setCargando(true);
      setError(null);
      try {
        const respuesta = await obtenerPanorama();

        if (casosConocidos.current) {
          for (const caso of respuesta.casos) {
            const estadoPrevio = casosConocidos.current.get(caso.id);
            if (estadoPrevio === undefined) {
              mostrarToast(`El Centinela detectó un caso nuevo: ${caso.consulta?.entidad_nombre}.`);
            } else if (estadoPrevio !== caso.estado) {
              mostrarToast(`${caso.consulta?.entidad_nombre} pasó a "${ETIQUETAS_ESTADO[caso.estado] ?? caso.estado}".`);
            }
          }
        }
        casosConocidos.current = new Map(respuesta.casos.map((c) => [c.id, c.estado]));

        setDatos(respuesta);
        setUltimaActualizacion(new Date());
      } catch (err) {
        if (err instanceof SesionExpiradaError) {
          sesionExpirada();
          return;
        }
        if (!enSilencio) setError(err);
      } finally {
        if (!enSilencio) setCargando(false);
      }
    },
    [sesionExpirada, mostrarToast],
  );

  useEffect(() => {
    cargar();
    const intervalo = setInterval(() => cargar({ enSilencio: true }), INTERVALO_ACTUALIZACION_MS);
    return () => clearInterval(intervalo);
  }, [cargar]);

  return { datos, error, cargando, ultimaActualizacion, recargar: cargar };
}
