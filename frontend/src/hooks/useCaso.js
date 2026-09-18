import { useCallback, useEffect, useState } from 'react';
import { obtenerCaso, reconocerCaso } from '../api/casos';
import { firmarConsentimiento, revocarConsentimiento } from '../api/consentimientos';
import { SesionExpiradaError } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { useToast } from '../context/ToastContext';

/**
 * Carga el detalle de un caso y lo mantiene vivo mientras pueda moverse
 * solo (agente trabajando o plazo corriendo): si el Gestor termina de
 * redactar o el caso escala, se refresca y avisa sin que nadie recargue.
 * También expone las acciones de firmar y revocar sobre ese caso.
 */
export function useCaso(id) {
  const [caso, setCaso] = useState(null);
  const [error, setError] = useState(null);
  const [cargando, setCargando] = useState(true);
  const [firmando, setFirmando] = useState(false);
  const [revocando, setRevocando] = useState(false);
  const [reconociendo, setReconociendo] = useState(false);
  const { sesionExpirada } = useAuth();
  const { mostrarToast } = useToast();

  const cargar = useCallback(
    async ({ enSilencio = false } = {}) => {
      if (!enSilencio) setCargando(true);
      setError(null);
      try {
        const respuesta = await obtenerCaso(id);
        setCaso((anterior) => {
          if (enSilencio && anterior && anterior.estado !== respuesta.estado) {
            if (respuesta.estado === 'en_gestion') {
              mostrarToast('El Gestor terminó de redactar la oposición.');
            } else if (respuesta.estado === 'escalado') {
              mostrarToast('El plazo venció: el caso se escaló a la Superintendencia.');
            }
          }
          return respuesta;
        });
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
    [id, sesionExpirada, mostrarToast],
  );

  useEffect(() => {
    cargar();
  }, [cargar]);

  useEffect(() => {
    if (!caso || caso.estado === 'resuelto' || caso.estado === 'descartado') return undefined;

    const intervalo = setInterval(() => cargar({ enSilencio: true }), 4000);
    return () => clearInterval(intervalo);
  }, [caso, cargar]);

  async function firmar(contexto) {
    setFirmando(true);
    try {
      await firmarConsentimiento(id, contexto);
      await cargar();
      mostrarToast('Autorización firmada. Decision Data ya está gestionando tu caso.');
    } catch (err) {
      setError(err);
    } finally {
      setFirmando(false);
    }
  }

  async function revocar(consentimientoId) {
    setRevocando(true);
    try {
      await revocarConsentimiento(consentimientoId);
      await cargar();
      mostrarToast('Autorización revocada.');
    } catch (err) {
      setError(err);
    } finally {
      setRevocando(false);
    }
  }

  async function reconocer() {
    setReconociendo(true);
    try {
      await reconocerCaso(id);
      await cargar();
      mostrarToast('Listo, quedó marcada como reconocida.');
    } catch (err) {
      setError(err);
    } finally {
      setReconociendo(false);
    }
  }

  return { caso, error, cargando, firmando, revocando, reconociendo, recargar: cargar, firmar, revocar, reconocer };
}
