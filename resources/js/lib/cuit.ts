export function isValidCuit(value: string): boolean {
    // 1. Comprobar exactamente 11 dígitos.
    let exito = /^\d{11}$/.test(value);

    if (exito) {
        const pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        let suma = 0;

        for (let i = 0; i <= 9; i++) {
            suma = Number(value[i]) * pesos[i] + suma;
        }

        const resto = suma % 11;
        const digitoVerificador = Number(value[10]);

        if (resto === 1) {
            exito = false;
        } else if (resto === 0) {
            exito = digitoVerificador === 0;
        } else {
            exito = digitoVerificador === 11 - resto;
        }
    }

    return exito;
}
