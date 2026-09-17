/**
 * `reconocida` es de 3 estados, no 2: null significa que todavía nadie la
 * revisó (así nace toda consulta nueva), no que ya está en disputa. Tratar
 * null igual que false alarmaría por algo que nadie confirmó todavía.
 */
export function estadoReconocimiento(reconocida) {
  if (reconocida === true) return { etiqueta: 'Reconocida', color: 'var(--verde)' };
  if (reconocida === false) return { etiqueta: 'En disputa', color: 'var(--ambar)' };
  return { etiqueta: 'Pendiente de revisión', color: 'var(--texto-secundario)' };
}
