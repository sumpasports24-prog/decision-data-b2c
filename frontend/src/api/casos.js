import { apiFetch } from './client';

export function listarCasos() {
  return apiFetch('/casos');
}

export function obtenerCaso(id) {
  return apiFetch(`/casos/${id}`);
}

export function reconocerCaso(id) {
  return apiFetch(`/casos/${id}/reconocer`, { method: 'POST' });
}
