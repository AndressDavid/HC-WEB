export interface IDatosEncabezado {
    datos: any;
    nIngreso: number;
    cNombre: string;
    cDescSexo: string;
    nNumId: number;
    cDesVia: string;
    cPesoUnidad: string;
    cHabita: string;
    aEdad: {
        y: string;
        m: string;
        d: string;
    };
    nHistoria: string;
    cTipId: string;
    mensaje: string;
}
