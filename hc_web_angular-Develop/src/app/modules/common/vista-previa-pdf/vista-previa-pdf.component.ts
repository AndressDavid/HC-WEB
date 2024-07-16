import { Component, Input } from '@angular/core';
import { IContenido } from '../modal-dinamico/interfaces/contenidoModal';
import { BrowserModule, DomSanitizer } from '@angular/platform-browser';
import { ServiceRecuperarVistaPrevia } from './services/vistaPrevia.service';
import { IContenidoPDF } from '../pdf-view/interfaces/IContenidoPDF';
import { ModalDinamicoComponent } from '../modal-dinamico/modal-dinamico.component';

@Component({
  standalone: true,
  imports: [ModalDinamicoComponent, BrowserModule  ],
  selector: 'app-vista-previa-pdf',
  templateUrl: './vista-previa-pdf.component.html',
  styleUrl: './vista-previa-pdf.component.css'
})
export class VistaPreviaPdfComponent {



  constructor(
    private _sanitizer: DomSanitizer,
    private serviceRecuperarVistaPrevia : ServiceRecuperarVistaPrevia
  ){}

  public modalActivo = false;
  public tipoModal = 'info';


  @Input()
  public disabled : boolean = true;

  public modalContenido: IContenido={
    titulo: "",
    contenido: "",
    boton1: "",
    boton2: ''
  }
  
  @Input() contenidoVistaPrevia! : IContenidoPDF;

  ngOnInit(): void{
  }

  abrirModalVistaPrevia(): void{

    this.modalContenido ={
      titulo: 'Vista Previa',
      contenido: '<div style="padding: 10px; width: 100%; height:697px" *ngIf="carga" class="d-flex justify-content-center"><p><b>Cargando información, Por favor Espere...</b></p><div class="spinner-border" role="status" ></div></div>',
      boton1: '',
      boton2: ''
    }

    this.modalActivo = true;

    this.serviceRecuperarVistaPrevia.recuperarVistaPrevia(this.contenidoVistaPrevia).subscribe((result:any) => {
      if(result['success']){
        this.modalContenido ={
          titulo: "Vista Previa",
          contenido: result["documento"],
          boton1: "ACEPTAR",
          boton2: ''
        }
      }
    });
  }

  aceptar(event : boolean): void{
    this.modalActivo = false;
  }

}
