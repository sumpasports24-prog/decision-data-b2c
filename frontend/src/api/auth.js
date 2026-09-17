import { apiFetch, setToken } from './client';

export async function iniciarSesion(cedula) {
  const datos = await apiFetch('/auth/login', { method: 'POST', body: { cedula } });
  setToken(datos.token);
  return datos.persona;
}

export async function cerrarSesion() {
  try {
    await apiFetch('/auth/logout', { method: 'POST' });
  } catch {
    // Si el token ya no sirve o no hay red, igual se limpia la sesión local.
  } finally {
    setToken(null);
  }
}
