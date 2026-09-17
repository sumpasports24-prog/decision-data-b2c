import { apiFetch } from './client';

export function obtenerPanorama() {
  return apiFetch('/panorama');
}
