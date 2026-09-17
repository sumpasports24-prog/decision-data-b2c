import { useCallback, useEffect, useState } from 'react';
import { listarCasos } from '../api/casos';
import { SesionExpiradaError } from '../api/client';
import { useAuth } from '../context/AuthContext';

export function useCasos() {
  const [casos, setCasos] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(true);
  const { sesionExpirada } = useAuth();

  const cargar = useCallback(async () => {
    setCargando(true);
    setError(null);
    try {
      setCasos(await listarCasos());
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

  return { casos, error, cargando, recargar: cargar };
}
