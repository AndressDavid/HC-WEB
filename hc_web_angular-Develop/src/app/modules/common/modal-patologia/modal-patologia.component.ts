import { Component, EventEmitter, Output } from '@angular/core';
import { ServicePatologias } from './services/modal-patologias.service';
import { IContenido } from '../modal-dinamico/interfaces/contenidoModal';
import { iModalEstilos } from '../modal-dinamico/interfaces/TipoModal';
import { ModalDinamicoComponent } from '../modal-dinamico/modal-dinamico.component';

@Component({
  standalone: true,
  imports: [ModalDinamicoComponent],
  selector: 'app-modal-patologia',
  templateUrl: './modal-patologia.component.html',
  styleUrl: './modal-patologia.component.css'
})
export class ModalPatologiaComponent {

  public modal = true;
  public tipoModal = 'personalizado';

  @Output() IsModalActive = new EventEmitter<boolean>();


  public modalContenido: IContenido={
    titulo: "",
    contenido: "",
    boton1: "",
    boton2: ''
  }

  public modalPersonalizado ?: iModalEstilos;


  constructor(private servicePatologias : ServicePatologias){}

  ngOnInit(){
    this.recuperarPatologias();
  }

  recuperarPatologias(): void{

    this.modalContenido ={
      titulo: "PATOLOGIA - PROCEDIMIENTOS",
      contenido: '<div style="padding: 10px; width: 100%; height:697px" *ngIf="carga" class="d-flex justify-content-center"><p><b>Cargando información, Por favor Espere...</b></p><div class="spinner-border" role="status" ></div></div>',
      boton1: "",
      boton2: ''
    }

    this.servicePatologias.recuperarPatologias().subscribe((result:any) => {

      let contentBodyPart =  [];
      let body1 ="";

      for (const key in result) {
        
        let cadena : string = "";
        for (const key2 in result[key]) {  

          cadena = '<div> <small>  <h6><b>'+result[key][key2].tipoPatologia+'* </b></h6>';
          let cadena2 : string = "";

          for (const key3 in result[key][key2].listaComun) {
            cadena2 = cadena2 + '<p class="m-0">' + result[key][key2].listaComun[key3].codcup + "-"+result[key][key2].listaComun[key3].descup+ '</p>';
          }

          contentBodyPart.push(cadena);
          contentBodyPart.push(cadena2);
          contentBodyPart.push('</small> </div>');
        }
      }
      body1 = contentBodyPart.join('');

      this.modalContenido ={
        titulo: "PATOLOGIA - PROCEDIMIENTOS",
        contenido: body1,
        boton1: "Aceptar",
        boton2: ''
      }

    });

    this.modalPersonalizado={
      'icono': "fas fa-microscope",
      'color': "#007bff"
    }
  }

  evento1(event: boolean): void{
    this.IsModalActive.emit(true);
  }

  cerrarModal(event: boolean): void {
    this.modal = event;
  }

}
