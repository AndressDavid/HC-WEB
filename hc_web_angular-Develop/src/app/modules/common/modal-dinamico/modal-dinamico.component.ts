import { Component, EventEmitter, Input, Output, ViewEncapsulation } from '@angular/core';
import { iModalEstilos } from './interfaces/TipoModal';
import { IContenido } from './interfaces/contenidoModal';
import { StyleClassModule } from 'primeng/styleclass/styleclass';
import { NgClass, NgStyle } from '@angular/common';
import { BrowserModule } from '@angular/platform-browser';

@Component({
  standalone: true,
  imports: [NgStyle, NgClass, BrowserModule ],
  selector: 'app-modal-dinamico',
  templateUrl: './modal-dinamico.component.html',
  styleUrl: './modal-dinamico.component.css',
  encapsulation: ViewEncapsulation.None,
  
})
export class ModalDinamicoComponent {


  @Input()
  classDinamico:String ="";

  @Input()
  classDinamicoBody:String ="";

  @Input()
  calcularAltoBody: boolean = false;

  @Input()
  calcularAnchoBody: boolean = false;

  @Input()
  porcentajeAlto: number = 25;


  @Input()
  porcentajeAncho: number = 100;

  public estilosInformativo: iModalEstilos ={
    'icono': "fa fa-info-circle",
    'color': "#3498db"
  };

  public estilosAlerta: iModalEstilos ={
    'icono': "fas fa-exclamation-circle",
    'color': "#e74c3c"
  };

  public estilosAdvertencia: iModalEstilos ={
    'icono': "fas fa-exclamation-triangle",
    'color': "#ffc720"
  };

  public estilosConfirmacion: iModalEstilos ={
    'icono': "fas fa-check",
    'color': "#0d6efd"
  };

  public isActiveModal : boolean = true;
  public acepto : boolean = false;
  public cancelo : boolean = false;


  @Input()
  tipoModal: String | undefined


  @Input()
  personalizado : iModalEstilos | undefined;

  @Input()
  contenido!: IContenido;

  
  
  public estilos!: iModalEstilos;


  @Output() primerBotonEvento = new EventEmitter<boolean>();
  @Output() segundoBotonEvento = new EventEmitter<boolean>();
  @Output() isActivoModalEvento = new EventEmitter<boolean>();


  
  ngOnInit() {

    switch(this.tipoModal){
      case 'alerta':
        this.estilos = this.estilosAlerta;
        break;
      case 'info':
        this.estilos = this.estilosInformativo;
        break;
      case 'advertencia':
        this.estilos = this.estilosAdvertencia;
        break;
      case 'confirmacion':
        this.estilos = this.estilosConfirmacion
        break;

      case 'personalizado':
      this.estilos = this.personalizado ?? this.estilosInformativo;
        break;
    }
  }

  primerBoton() : void{

    this.primerBotonEvento.emit(true);
    this.isActivoModalEvento.emit(false);

  }

  segundoBoton() : void{
    this.segundoBotonEvento.emit(true);
    this.isActivoModalEvento.emit(false);
  }

  medidasBody():string{

    let altoEstilo: string ="";
    let altoPantallaUsuario : number = window.screen.height;
    let anchoPantallaUsuario : number = window.screen.width;

    if(this.calcularAltoBody){

      let multiplicacionAlto = altoPantallaUsuario* this.porcentajeAlto;
      let divicionAltp = multiplicacionAlto /100;
      altoEstilo = "height:"+ divicionAltp+"px; "

    }

    if(this.calcularAnchoBody){

      let multiplicacionAlto = anchoPantallaUsuario* this.porcentajeAncho;
      let divicionAltp = multiplicacionAlto /100;
      altoEstilo = "width:"+ divicionAltp+"px; "

    }
    return altoEstilo;
  }


}
