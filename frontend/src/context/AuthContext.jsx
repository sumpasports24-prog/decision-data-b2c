import { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { iniciarSesion, cerrarSesion as cerrarSesionApi } from '../api/auth';
import { getToken, setToken as guardarToken } from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [persona, setPersona] = useState(null);
  const [cargandoSesion, setCargandoSesion] = useState(true);

  useEffect(() => {
    const personaGuardada = sessionStorage.getItem('decision_data_persona');
    if (getToken() && personaGuardada) {
      setPersona(JSON.parse(personaGuardada));
    }
    setCargandoSesion(false);
  }, []);

  const login = useCallback(async (cedula) => {
    const persona = await iniciarSesion(cedula);
    sessionStorage.setItem('decision_data_persona', JSON.stringify(persona));
    setPersona(persona);
    return persona;
  }, []);

  const logout = useCallback(async () => {
    await cerrarSesionApi();
    sessionStorage.removeItem('decision_data_persona');
    setPersona(null);
  }, []);

  const sesionExpirada = useCallback(() => {
    guardarToken(null);
    sessionStorage.removeItem('decision_data_persona');
    setPersona(null);
  }, []);

  return (
    <AuthContext.Provider value={{ persona, cargandoSesion, login, logout, sesionExpirada }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const contexto = useContext(AuthContext);
  if (!contexto) throw new Error('useAuth debe usarse dentro de AuthProvider');
  return contexto;
}
