export function diasRestantes(fechaIso) {
  const dias = Math.ceil((new Date(fechaIso) - new Date()) / 86400000);
  return Math.max(0, dias);
}
