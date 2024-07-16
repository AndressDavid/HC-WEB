export interface IDatosKardex {
    nIngreso: number;
    cMedicoTratanteNombre: string;
    nIngresoFecha: string;
    fecha_ing_inst: string;
    fecha_ing_serv:string
    precau_hosp: string;
    usuario_crea: string;
    fecha_creacion:string;
    lcAlergias: String;
    lcAlergia: String;
    lcAntec: string
    laDx: {
        TIPO: string,
        CLASE: number,
        TRATAMIENTO: number,
        DIAGNOSTICO: string,
        DESCRIPCION_CIE: string,
        DESCARTE: string,
        FECEDC: number,
        HOREDC: number,
        OBLIGA: number,
        ANALISIS: string,
        TIPO_DESCARTE: string,
        DESCRIPCION_TIPO: string,
        DESCRIPCION_CLASE: string,
        DESCRIPCION_TRATAMIENTO: string
    },
    Nutricional: string;
    Aislamiento: string;
    Covid: string;
    TipoDocumento: string;
    Identificacion: number;
    Higiene: {
        "ConsNota": string,
        "ConsReg": string,
        "Tipo de Baño": string,
        "Higiene Oral": string,
        "Medias Antiembólicas": string,
        "Observaciones": string,
        "Usuario": string,
        "Fecha": string,
        "Hora": string
    },
    Seguridad: {
        "ConsNota": string;
        "ConsReg": string;
        "Tipo de Aislamiento": string;
        "Seguridad del Paciente": {
            "Barandas Lateral Cama": string,
            "Timbre a la Mano": string,
            "Compañia Permanente": string,
            "Cama Altura Minima": string,
            "Medidas de Sujeción": string,
            "Manilla de Identificación": string,
            "Manilla Alérgico": string,
            "Riesgo de Sangrado": string,
            "Riesgo de Fuga": string,
            "Riesgo de Caida": string,
            "Riesgo de Ulcera": string
        },
        "Observaciones": string;
        "Usuario": string;
        "Fecha": string;
        "Hora": string;
    },
    TipoRiesgoCaida: string;
    RiesgoCaida: string;
    RiesgoUlcera: string;
    RiesgoFuga: string;
    RiesgoSangrado: string;
    Caprini: string;
    Intervencion: string;
    Glasgow: string;
    Dolor: {
        "ConsNota": string,
        "ConsReg": string,
        "Escala Dolor": string,
        "Localización": string,
        "Manejo Dolor": string,
        "Respuesta": string,
        "Observaciones": string,
        "Usuario": string,
        "Fecha": string,
        "Hora": string
    },
    PielHeridas: string,
    Liquidos: string,
    Pañal: string,
    AseoGenital: string,
    Procedimientos: {
        "LABORATORIOS": any,
        "INTERCONSULTAS":any,
        "PROCEDIMIENTOS":any,
        "IMÁGENES": any,
        "TRANSFUSIONES": any,
        "TERAPIAS": any
    },
    Medicamentos: any
}
