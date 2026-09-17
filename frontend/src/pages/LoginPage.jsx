import { useState } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { ArrowRight, LoaderCircle, ShieldCheck } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { ApiError } from '../api/client';
import logo from '../assets/marca/dd-lockup-white.png';

function validarFormatoCedula(valor) {
  if (!/^\d{10}$/.test(valor)) return 'La cédula debe tener exactamente 10 dígitos numéricos.';
  return null;
}

export function LoginPage() {
  const [cedula, setCedula] = useState('');
  const [errorCampo, setErrorCampo] = useState(null);
  const [errorGeneral, setErrorGeneral] = useState(null);
  const [cargando, setCargando] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  async function enviar(evento) {
    evento.preventDefault();
    setErrorGeneral(null);

    const errorFormato = validarFormatoCedula(cedula);
    if (errorFormato) {
      setErrorCampo(errorFormato);
      return;
    }
    setErrorCampo(null);
    setCargando(true);

    try {
      await login(cedula);
      navigate(location.state?.desde ?? '/', { replace: true });
    } catch (error) {
      if (error instanceof ApiError && error.status === 404) {
        setErrorGeneral('No encontramos una identidad con esa cédula en este entorno de demo.');
      } else if (error instanceof ApiError && error.errores?.cedula) {
        setErrorCampo(error.errores.cedula[0]);
      } else {
        setErrorGeneral(error.message);
      }
    } finally {
      setCargando(false);
    }
  }

  return (
    <div
      style={{
        minHeight: '100%',
        display: 'grid',
        placeItems: 'center',
        padding: '40px 20px',
      }}
    >
      <div className="entrada" style={{ width: '100%', maxWidth: 400, display: 'grid', gap: 28 }}>
        <img src={logo} alt="Decision Data" style={{ width: 190, justifySelf: 'center' }} />

        <div className="tarjeta" style={{ display: 'grid', gap: 22 }}>
          <div style={{ display: 'grid', gap: 6 }}>
            <div
              style={{
                display: 'inline-flex',
                alignItems: 'center',
                gap: 8,
                color: 'var(--teal)',
                fontSize: '0.8rem',
                fontWeight: 600,
              }}
            >
              <ShieldCheck size={16} aria-hidden="true" />
              <span>Panorama</span>
            </div>
            <h1 style={{ fontSize: '1.3rem', margin: 0 }}>Identifícate para continuar</h1>
            <p className="texto-secundario" style={{ margin: 0, fontSize: '0.92rem' }}>
              Usamos tu cédula para encontrar tus casos y tu huella de consulta.
            </p>
          </div>

          <form onSubmit={enviar} style={{ display: 'grid', gap: 16 }} noValidate>
            <div className="campo">
              <label htmlFor="cedula">Cédula</label>
              <input
                id="cedula"
                name="cedula"
                inputMode="numeric"
                autoComplete="off"
                autoFocus
                maxLength={10}
                value={cedula}
                onChange={(e) => setCedula(e.target.value.replace(/\D/g, ''))}
                aria-invalid={Boolean(errorCampo)}
                aria-describedby={errorCampo ? 'cedula-error' : undefined}
                placeholder="1710034065"
              />
              {errorCampo && (
                <span id="cedula-error" className="error" role="alert">
                  {errorCampo}
                </span>
              )}
            </div>

            {errorGeneral && (
              <p className="error" role="alert" style={{ margin: 0 }}>
                {errorGeneral}
              </p>
            )}

            <button type="submit" className="boton boton--primario" disabled={cargando}>
              {cargando ? (
                <>
                  <LoaderCircle size={18} className="girando" aria-hidden="true" />
                  Verificando…
                </>
              ) : (
                <>
                  Continuar
                  <ArrowRight size={18} aria-hidden="true" />
                </>
              )}
            </button>
          </form>
        </div>

        <p className="texto-secundario" style={{ fontSize: '0.85rem', margin: 0, textAlign: 'center' }}>
          Demo: cédula sintética <span className="mono">1710034065</span>
        </p>
      </div>
    </div>
  );
}
