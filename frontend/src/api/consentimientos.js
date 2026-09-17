import { apiFetch } from './client';

export function firmarConsentimiento(casoId, contexto) {
  return apiFetch(`/casos/${casoId}/consentimiento`, { method: 'POST', body: { contexto: contexto || null } });
}

export function revocarConsentimiento(consentimientoId) {
  return apiFetch(`/consentimientos/${consentimientoId}/revocar`, { method: 'POST' });
}
