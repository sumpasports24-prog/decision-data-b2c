import { apiFetch } from './client';

export function firmarConsentimiento(casoId) {
  return apiFetch(`/casos/${casoId}/consentimiento`, { method: 'POST' });
}

export function revocarConsentimiento(consentimientoId) {
  return apiFetch(`/consentimientos/${consentimientoId}/revocar`, { method: 'POST' });
}
