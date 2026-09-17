export function iniciales(nombre) {
  return nombre
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((palabra) => palabra[0])
    .join('')
    .toUpperCase();
}

/** Convierte el *énfasis* estilo WhatsApp (un asterisco) en <strong>. */
export function conNegritas(texto) {
  return texto.split(/\*([^*]+)\*/g).map((parte, i) => (i % 2 === 1 ? <strong key={i}>{parte}</strong> : parte));
}
