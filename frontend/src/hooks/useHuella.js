import { useCallback, useEffect, useState } from 'react';
import { obtenerPanorama } from '../api/panorama';
import { SesionExpiradaError } from '../api/client';
import { useAuth } from '../context/AuthContext';

/** La huella no tiene endpoint propio: viene incluida en /panorama. */
export function useHuella() {
  const [huella, setHuella] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(true);
  const { sesionExpirada } = useAuth();

  const cargar = useCallback(async () => {
    setCargando(true);
    setError(null);
    try {
      const panorama = await obtenerPanorama();
      setHuella(panorama.huella_de_consulta);
    } catch (err) {
      if (err instanceof SesionExpiradaError) {
        sesionExpirada();
        return;
      }
      setError(err);
    } finally {
      setCargando(false);
    }
  }, [sesionExpirada]);

  useEffect(() => {
    cargar();
  }, [cargar]);

  return { huella, error, cargando, recargar: cargar };
}
