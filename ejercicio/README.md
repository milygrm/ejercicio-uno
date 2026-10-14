# Cálculo de sueldo quincenal (PHP)

Aplicacion web de un solo archivo (`index.php`) que calcula el sueldo quincenal de un trabajador a partir de las horas trabajadas en tres turnos.

## Funcionamiento

1. El usuario ingresa **nombre**, **cedula** y las **horas** trabajadas en los turnos diurno, vespertino y nocturno.
2. Se calcula el **sueldo bruto** con las tarifas por hora:
   - Diurna: 675 Bs.
   - Vespertina: 700 Bs.
   - Nocturna: 956,23 Bs.
3. Según el sueldo bruto se aplican las retenciones:

   | Sueldo bruto            | Ahorro Habitacional | Seguro Social |
   |-------------------------|---------------------|---------------|
   | Menor a 85.000 Bs.      | 0,10 %              | 0,15 %        |
   | 85.000 a 150.000 Bs.    | 0,15 %              | 0,20 %        |
   | Mayor a 150.000 Bs.     | 0,30 %              | 0,25 %        |

4. Se muestran los datos del empleado, el detalle por turno, el sueldo bruto, cada descuento y el **sueldo neto** (bruto, Ahorro Habitacional,Seguro Social).

Incluye validacion basica (nombre obligatorio, cedula numerica de 5 a 9 digitos y horas ≥ 0) y escapado de la salida para evitar XSS.

## Ejecucion

Requiere PHP 7.4 o superior. 

Luego abra <http://localhost/emily/ejercicio/index.php/> en el navegador.
