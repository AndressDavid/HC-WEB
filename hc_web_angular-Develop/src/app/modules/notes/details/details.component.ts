import { CommonModule } from '@angular/common';
import { Component, Input, OnInit } from '@angular/core';
import { IContenido } from '../../common/modal-dinamico/interfaces/contenidoModal';
import { NotesService } from '../services/notes.service';
import { ModalDinamicoComponent } from "../../common/modal-dinamico/modal-dinamico.component";
import { FormsModule, NgModel } from '@angular/forms';

@Component({
    selector: 'app-details',
    standalone: true,
    templateUrl: './details.component.html',
    styleUrl: './details.component.css',
    imports: [
        CommonModule,
        ModalDinamicoComponent,
        FormsModule

    ]
})
export class DetailsComponent {

  public NotasAnt: String = "";
  public valorTextarea: string = "";
  public activar: boolean = false;
  public activarConfir: boolean = false;
  public contenidog!: IContenido;
  public contenidoConfir!: IContenido;
  public usuario: any;
  public especialidad: any;

  public tipoModald: String = "Alerta";
  public tipoModalConfir: String = "Confirmacion";

  get datosAnt() {
    return this.NotasService.datosAnt;
  }

  constructor(public NotasService: NotesService) { }

  ngOnInit(): void {
    this.ObtenerUsuario();
    this.ObtenerEspecialidad();
  }

  GuardarNuevaNota() {

    let textareaElement = document.getElementById('txtNotas') as HTMLTextAreaElement;
    this.valorTextarea = textareaElement.value;

    if (this.NotasService.tipo == '' || this.NotasService.documento == '' || this.valorTextarea == '') {

      let lcMensaje: String = '';

      if (this.NotasService.tipo === '') {
        lcMensaje = 'Dato tipo de documento es obligatorio.';
      } else {
        if (this.NotasService.documento === '') {
          lcMensaje = 'Dato número de documento es obligatorio.';
        } else {
          if (this.valorTextarea === '') {
            lcMensaje = 'Información Nota nueva es obligatorio.';
          }
        }
      }

      this.activar = true;
      this.tipoModald = "alerta";
      this.contenidog = {
        titulo: "Validación",
        contenido: lcMensaje,
        boton1: 'Aceptar',
        boton2: ''
      };
    } else {
      let lcMensaje = 'Si guarda los cambios, <b>NO</b> podra modificarlos después.<br><b>¿Está seguro que desea Guardar los datos?</b>';
      this.tipoModalConfir = "confirmacion";
      this.activarConfir = true;
      this.activar = false;
      this.contenidoConfir = {
        titulo: "Confirmacion",
        contenido: lcMensaje,
        boton1: 'Aceptar',
        boton2: 'Cancelar'
      };
    }
  }

  ValidarDatos() {
    this.activar = false;
  }

  ObtenerUsuario(){

    this.NotasService.ObtenerUsuario().subscribe((response) => {
      this.usuario = response;
     })
  }

  ObtenerEspecialidad(){

    this.NotasService.ObtenerEspecialidad().subscribe((response) => {
      this.especialidad = response;
    })
  }

  primerBotonEvento(): void {

    this.activar = false;
    this.activarConfir = false;
    this.NotasService.GuardarNotasAclaratorias(this.NotasService.tipo, this.NotasService.documento, this.valorTextarea, this.usuario, this.especialidad).subscribe(
      (respuesta: any) => {
        const datos = respuesta.data;
        this.activar = true;
        this.tipoModald = "alerta";
        this.contenidog = {
          titulo: "Validación",
          contenido: 'La nota aclaratoria se ha guardado',
          boton1: 'Aceptar',
          boton2: ''
        };

        this.valorTextarea = '';
        this.NotasService._datosAnt = '';
        this.NotasService.obtenerNotasAclaratorias(this.NotasService.tipo, this.NotasService.documento).subscribe(
          (respuesta:any) => {
          const datos  = respuesta.data;

            datos.forEach(({ titulo, texto, usuario}: any) => {
            if (usuario !== ''){
                let laDatos = usuario.split("-");
                const datosNota: String = titulo + '\n' + texto + '\n';
                this.NotasService._datosAnt =  this.NotasService._datosAnt.toString() + '\n' + datosNota + '\n' + laDatos[0].trim() + '\n' + laDatos[1].trim() + '\n' + laDatos[2].trim() + '\n\n' + "-".repeat(180) + '\n';
              }
            });

        });
     });
  }

  segundoBotonEvento(): void {
    console.log('segundo boton');
    this.activarConfir = false;
  }

 }
