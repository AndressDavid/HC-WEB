import { Component, EventEmitter, Input, Output } from '@angular/core';
import { DatosPacienteService } from './services/datosPaciente.service';
import { datosControlCirugia } from '../../cirugia/inicio/interfaces/IDatosControlCirugia';
import { IFiltrosBusqueda } from './interfaces/IFiltrosBusqueda';
import { IContenido } from '../modal-dinamico/interfaces/contenidoModal';
import { iModalEstilos } from '../modal-dinamico/interfaces/TipoModal';
import { ModalDinamicoComponent } from '../modal-dinamico/modal-dinamico.component';

@Component({
  standalone: true,
  imports: [ ModalDinamicoComponent ],
  selector: 'app-modal-datos-paciente',
  templateUrl: './modal-datos-paciente.component.html',
  styleUrl: './modal-datos-paciente.component.css'
})
export class ModalDatosPacienteComponent {

  constructor(public datosPacienteService : DatosPacienteService){}

  @Input()
  datosPaciente!: datosControlCirugia;
  filtrosBusqueda!: IFiltrosBusqueda;

  public contentBody: any = "";

  public modal = false;
  public tipoModal = 'personalizado';


  @Output() IsModalActive = new EventEmitter<boolean>();


  public modalContenido: IContenido={
    titulo: "",
    contenido: "",
    boton1: "",
    boton2: ''
  }

  public modalPersonalizado ?: iModalEstilos;

  ngOnInit(){
    this.recuperarInformacionPAciente();
  }

  
  recuperarInformacionPAciente(): void {


    this.modalContenido ={
      titulo: "Consulta Admisión",
      contenido: '<div style="padding: 10px; width: 100%; height:697px" *ngIf="carga" class="d-flex justify-content-center"><p><b>Cargando información, Por favor Espere...</b></p><div class="spinner-border" role="status" ></div></div>',
      boton1: "",
      boton2: ''
    }

    
    this.filtrosBusqueda ={
      'ingreso': this.datosPaciente.ingreso,
      'fechaHoraIngreso': this.datosPaciente.fechaIngreso
    }
 
    this.datosPacienteService.recuperarDocumento(this.filtrosBusqueda).subscribe((result:any) => {

      let nacimiento = result.DATOS.oPaciente.nNacio.toString();
      let lcLugarResidencia: string = result.DATOS.oPaciente.cCodigoPaisResidencia + ' / ' + result.DATOS.oPaciente.cDecrDptoResidencia  + '  / ' + result.DATOS.oPaciente.cDecrCiudadResidencia;
      let lcEdadPaciente = result.DATOS.aEdad.y+'A '+result.DATOS.aEdad.m+'M '+result.DATOS.aEdad.d+'D ';
      let fechaHoraIngreso: String = this.datosPaciente.fechaIngreso.substring(0, 4)+ "/" + this.datosPaciente.fechaIngreso.substring(4,6) + "/" + this.datosPaciente.fechaIngreso.substring(6,8);
      let fechaNaci: String = nacimiento.substring(0, 4)+ "/" + nacimiento.substring(4,6) + "/" + nacimiento.substring(6,8);

      this.contentBody =  [
              '<div class="container-fluid small>',
                '<div class="row">',
                  '<div class="col-12"><h5>Información Ingreso</h5></div>',
                  '<div class="col-6 col-md-4">Ingreso: <b>',result.DATOS.nIngreso,'</b></div>',
                  '<div class="col-6 col-md-4">Vía Ingreso: <b>',result.DATOS.cDescVia,'</b></div>',
                  '<div class="col-12 col-md-4">Fecha: <b>',fechaHoraIngreso,'</b></div>',
                '</div><hr>',
                '<div class="row">',
                  '<div class="col-12"><h5>Datos del paciente</h5></div>',
                  '<div class="col-12">Nombre: <b>', this.datosPaciente.nombres ," ", this.datosPaciente.apellidos ,'</b></div>',
                  '<div class="col-12 col-md-6 col-lg-4">Identificación: <b>', this.datosPaciente.tipoDocumento , ' - ' , this.datosPaciente.documento,'</b></div>',
                  '<div class="col-12 col-md-6 col-lg-4">Género: <b> ', result.DATOS.oPaciente.cSexo ,' ', result.DATOS.oPaciente.cDescSexo,'</b></div>',
                  '<div class="col-12 col-md-6 col-lg-4">Historia: <b>', result.DATOS.oPaciente.nNumHistoria,'</b></div>',
                  '<div class="col-12 col-md-6 col-lg-4">Fecha Nac.: <b>', fechaNaci ,'</b></div>',
                  '<div class="col-12 col-md-6 col-lg-4">Edad: <b>',lcEdadPaciente,'</b></div>',
                  '<div class="col-12 col-md-6 col-lg-4">Estado civil: <b>', result.DATOS.cEstadoCivil ,'</b></div>',
                '</div>',
                '<div class="row mt-1">',
                  '<div class="col-12">Pais/Dpto./Ciudad: ', lcLugarResidencia ,'</div>',
                  '<div class="col-12 col-lg-6">Dirección: ', result.DATOS.oPaciente.cDireccion ,'</div>',
                  '<div class="col-12 col-lg-6">Email: ', result.DATOS.oPaciente.cEmail ,'</div>',
                  '<div class="col-12 col-lg-6">Teléfono(s): ', result.DATOS.oPaciente.cTelefono ,'</div>',
                  '<div class="col-12 col-lg-6">Celular(s): ', result.DATOS.oPaciente.cCelular ,'</div>',
                '</div>',
                '<div class="row mt-1">',
                  '<div class="col-12">Ocupación: ', result.DATOS.oPaciente.cOcupacion ,'</div>',
                  '<div class="col-12">Nivel educativo: ', result.DATOS.oPaciente.cDecrNivelEducativo ,'</div>',
                  '<div class="col-12">Pertenecia étnica: ', result.DATOS.oPaciente.cDecrPertenenciaEtnica ,'</div>',
                '</div>',
                '<hr>'
                ,'<div class="row">',
                  '<div class="col-12"><h5>Responsable</h5></div>',
                  '<div class="col-12">Nombre: <b>', result.DATOS.oResponsable.cNombre1 , ' ' , result.DATOS.oResponsable.cNombre2 , ' ' , result.DATOS.oResponsable.cApellido1 , ' ' , result.DATOS.oResponsable.cApellido2 ,'</b></div>',
                  '<div class="col-12 col-lg-6">Dirección: <b>', result.DATOS.oResponsable.cDireccionResp ,'</b></div>',
                  '<div class="col-12 col-lg-6">Teléfono: <b>', result.DATOS.oResponsable.cTelefonoResp ,'</b></div>',
                '</div></div><hr>', 
                '<div class="row">',
                '<div class="col-12"><h5>Entidad</h5></div>',
                '<div class="col-12">Entidad: <b>'+ result.DATOS.cPlanDescripcion +'</b></div>',
                '<div class="col-12">Tipo usuario: <b>'+ result.DATOS.cDescripcionAfiliadoUsuario +'</b></div>',
                '<div class="col-12">Médico: <b>'+ result.DATOS.oMedicoTratante.NOMBRE +'</b></div>',
                '<div class="col-12">Atención: <b>'+ result.DATOS.oMedicoTratante.ESPECIALIDAD_NOMBRE +'</b></div>',
              '</div>'
          ].join('');
      

      this.modalContenido ={
        titulo: "Consulta Admisión",
        contenido: this.contentBody,
        boton1: "Aceptar",
        boton2: ''
      }

    });

    this.modalPersonalizado={
      'icono': "fa fa-hospital-user",
      'color': "#007bff"
    }
  }

  evento1(event: boolean): void{
    this.IsModalActive.emit(false);
  }

  cerrarModal(event: boolean): void {
    this.modal = event;
  }
  
}
