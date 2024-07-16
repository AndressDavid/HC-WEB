import { SafeHtml, SafeResourceUrl, SafeUrl } from "@angular/platform-browser";

export interface IContenido {
    titulo: String,
    contenido: String | SafeHtml | SafeUrl  | SafeResourceUrl,
    boton1: String,
    boton2: string
}