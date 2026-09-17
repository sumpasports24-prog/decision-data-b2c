// Si VITE_API_URL no está fijada explícitamente, se calcula a partir del host
// desde el que se cargó la página. "localhost" quedaría fijo en el build y
// apuntaría al propio dispositivo del usuario, no al servidor, cuando la app
// se abre desde otra máquina en la red (celular, laptop de otra persona, etc).
//
// Dos topologías posibles:
// - Acceso directo a Docker (puerto 5173 del frontend): la API está en el
//   mismo host, puerto 8000.
// - Detrás de un proxy reverso (aaPanel/nginx en :80 o :443 con dominio
//   propio): nginx reenvía /api al backend, así que la API queda en el mismo
//   origen, sin puerto explícito.
const { protocol, hostname, port } = window.location;
const BASE_URL =
  import.meta.env.VITE_API_URL ||
  (port === '5173' ? `${protocol}//${hostname}:8000/api` : `${protocol}//${window.location.host}/api`);

export class ApiError extends Error {
  constructor(message, { status, codigoReferencia, errores } = {}) {
    super(message);
    this.status = status;
    this.codigoReferencia = codigoReferencia;
    this.errores = errores;
  }
}

export class SesionExpiradaError extends ApiError {}

function generarCodigoReferencia() {
  return Math.random().toString(36).slice(2, 8).toUpperCase();
}

export function getToken() {
  return localStorage.getItem('decision_data_token');
}

export function setToken(token) {
  if (token) localStorage.setItem('decision_data_token', token);
  else localStorage.removeItem('decision_data_token');
}

export async function apiFetch(path, options = {}) {
  const token = getToken();

  let respuesta;
  try {
    respuesta = await fetch(`${BASE_URL}${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...options.headers,
      },
      body: options.body ? JSON.stringify(options.body) : undefined,
    });
  } catch {
    // Sin conexión con el backend: nada se perdió, solo no llegó la respuesta.
    throw new ApiError('No pudimos conectar con Decision Data. Los plazos legales de tus casos siguen corriendo igual.', {
      codigoReferencia: generarCodigoReferencia(),
    });
  }

  if (respuesta.status === 401) {
    setToken(null);
    throw new SesionExpiradaError('Tu sesión expiró. Vuelve a identificarte para continuar.', { status: 401 });
  }

  let cuerpo = null;
  try {
    cuerpo = await respuesta.json();
  } catch {
    cuerpo = null;
  }

  if (!respuesta.ok) {
    throw new ApiError(cuerpo?.message ?? 'Algo falló de nuestro lado. Nada se perdió: tus casos y plazos siguen intactos.', {
      status: respuesta.status,
      codigoReferencia: generarCodigoReferencia(),
      errores: cuerpo?.errors,
    });
  }

  return cuerpo;
}
