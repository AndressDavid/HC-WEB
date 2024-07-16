import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Output } from '@angular/core';
import { IDatosPaciente } from '../interfaces/Inotes';
import { IContenido } from '../../common/modal-dinamico/interfaces/contenidoModal';
import { FormBuilder, FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { NotesService } from '../services/notes.service';
import { DocumentoPacienteComponent } from "../../common/documento-paciente/documento-paciente.component";
import { ModalDinamicoComponent } from "../../common/modal-dinamico/modal-dinamico.component";

@Component({
    selector: 'app-header',
    standalone: true,
    templateUrl: './header.component.html',
    styleUrl: './header.component.css',
    imports: [
        CommonModule,
        DocumentoPacienteComponent,
        ModalDinamicoComponent,
        ReactiveFormsModule

    ]
})
export class HeaderComponent {

  public listaDatosPaciente: IDatosPaciente []= [];
  public notasAclaratorias: String = '';
  public contenidoa!: IContenido;
  public activar: boolean = false;
  public tipoAlerta:string="";

  public frmHeader = new FormGroup({
    selTipDoc : new FormControl(''),
    documento : new FormControl(''),
    nombrepac: new FormControl(''),
  });

  @Output() OutListaDatosPaciente:
  EventEmitter<IDatosPaciente[]>=new EventEmitter();
  IDatosPaciente: any;

  constructor (public NotasService: NotesService, private jg: FormBuilder){}

  ObtenerDatosPaciente(){

    let fieldTipoDocumento = this.frmHeader.controls.selTipDoc.value ??'';
    let fieldDocumento =  this.frmHeader.controls.documento.value ?? '';

    if (fieldTipoDocumento === '' || fieldDocumento === ''){
      let lcMensaje = '';
      if (fieldTipoDocumento === ''){
        lcMensaje = 'Debe digitar Tipo de documento'
       }else{
        if (fieldDocumento === ''){
          lcMensaje = 'Debe digitar Número de documento'
        }
      }

      this.activar = true;
      this.tipoAlerta = "alerta";
      this.contenidoa = {
        titulo:"Validación",
        contenido: lcMensaje,
        boton1: 'Aceptar',
        boton2: ''
      };

    }else{

      this.NotasService.obtenerListaDatosPaciente( fieldTipoDocumento, fieldDocumento).subscribe(
      (respuesta:any) => {
      const data  = respuesta.listPacientes;

      if(!data.length) {
        this.activar = true;
        this.tipoAlerta = "advertencia";
        this.contenidoa = {
          titulo:"Advertencia",
          contenido: 'Paciente no existe',
          boton1: 'Aceptar',
          boton2: ''
        };
        return ;
      }

      data.forEach(({  primerNombre, segundoNombre, primerApellido, segundoApellido}: any) => {
            const datosPaciente: IDatosPaciente = {
              PrimerNombre: primerNombre,
              SegundoNombre: segundoNombre,
              PrimerApellido: primerApellido,
              SegundoApellido: segundoApellido,
            }
            this.listaDatosPaciente.push(datosPaciente);
            this.OutListaDatosPaciente.emit(this.listaDatosPaciente);
            this.frmHeader.controls["nombrepac"].setValue( this.listaDatosPaciente[0]['PrimerApellido'] + ' ' + this.listaDatosPaciente[0]['SegundoApellido'] + ' ' + this.listaDatosPaciente[0]['PrimerNombre'] + ' ' + this.listaDatosPaciente[0]['SegundoNombre'] + ' ');
            this.BuscarNotasAclaratorias(fieldTipoDocumento, fieldDocumento);
          });
        },
        (error) =>{
          console.log(error);
        }
    );

    }
  }

  BuscarNotasAclaratorias(tipo: String, documento: String){

    this.NotasService.obtenerNotasAclaratorias( tipo, documento).subscribe(
      (respuesta:any) => {
      const datos  = respuesta.data;

      if(!datos.length) {
        this.activar = true;
        this.tipoAlerta = "advertencia";
        this.contenidoa = {
          titulo:"Advertencia",
          contenido: 'No existen notas aclaratorias registradas',
          boton1: 'Aceptar',
          boton2: ''
        };
        return;
      }else{

        datos.forEach(({ titulo, texto, usuario}: any) => {
        if (usuario !== ''){
            let laDatos = usuario.split("-");
            const datosNota: String = titulo + '\n' + texto + '\n';
            this.notasAclaratorias =  this.notasAclaratorias.toString() + '\n' + datosNota + '\n' + laDatos[0].trim() + '\n' + laDatos[1].trim() + '\n' + laDatos[2].trim() + '\n\n' + "-".repeat(180) + '\n';
          }
        });

      }
      this.NotasService.tipo = tipo;
      this.NotasService.documento = documento;
      this.NotasService._datosAnt = this.notasAclaratorias;
    });
  }

  onSubmit() {

  }

  get datosAnt(){
    return this.NotasService._datosAnt;
  }

  ValidarDatosAlerta(){
    this.activar = false;
  }

}
