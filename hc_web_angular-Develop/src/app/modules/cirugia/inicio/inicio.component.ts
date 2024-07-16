import { NgStyle } from "@angular/common";
import { HttpClientModule } from "@angular/common/http";
import { Component } from "@angular/core";
import { BrowserModule } from "@angular/platform-browser";
import { AutoCompleteModule } from "primeng/autocomplete";
import { ButtonModule } from "primeng/button";
import { StyleClassModule } from "primeng/styleclass";
import { TableModule } from "primeng/table";
import { AppRoutingModule } from "../../../app-routing.module";
import { ConvencionesComponent } from "../../common/convenciones/convenciones.component";
import { ColoresEstados } from "../../common/convenciones/services/estadosColores.service";
import { DocumentoPacienteComponent } from "../../common/documento-paciente/documento-paciente.component";
import { EspecialidadesComponent } from "../../common/especialidades/especialidades.component";
import { IFiltroBusqueda } from "../../common/especialidades/interfaces/IEspecialidades";
import { LibroHCWebComponent } from "../../common/libro-hcweb/libro-hcweb.component";
import { ModalDatosPacienteComponent } from "../../common/modal-datos-paciente/modal-datos-paciente.component";
import { IContenido } from "../../common/modal-dinamico/interfaces/contenidoModal";
import { ModalDinamicoComponent } from "../../common/modal-dinamico/modal-dinamico.component";
import { ViasComponent } from "../../common/vias/vias.component";
import { datosControlCirugia } from "./interfaces/IDatosControlCirugia";
import { IFiltrosControlCirugia } from "./interfaces/IFiltrosControlCirugia";
import { TablaControlCirugia } from "./services/tabla.service";
import { FormControl, FormGroup} from "@angular/forms";


@Component({
  selector: 'app-inicio',
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css'
})
export class InicioCirugiaComponent {

  public filtrosEspecialidad : IFiltroBusqueda = {
    bodesp: '',
    ubiesp: '',
    ccsesp: '',
    pgcesp: ''
  }
  public mostrarDatos: boolean = false;
  public modal: boolean = false;
  public tipoModal: string ="";
  public datosPaciente : boolean = false;
  public isDatos = false;
  public ingreso : number =0;

  public datosTabla: datosControlCirugia | any;   

  public datosTablafiltro! : IFiltrosControlCirugia;
  
  public formularioBusqueda = new FormGroup({
    selTipDoc : new FormControl(''),
    documento : new FormControl(''),
    selectEspecialidad: new FormControl(''),
    selectVias : new FormControl(''),
    formNombreC: new FormControl({
      value:'',
      disabled: true
    }),
    formApellidoC: new FormControl({
      value:'',
      disabled: true
    }),
    formIngresoC: new FormControl(0),
    formFechaC: new FormControl(''),
    formTodasC: new FormControl(false),
  });

  public modalContenido: IContenido={
    titulo: "",
    contenido: "",
    boton1: "",
    boton2: ''
  }

  constructor(public tablaControlCirugia : TablaControlCirugia, public coloresEstados : ColoresEstados){}

  // ngOnInit(){
  //   this.colorEstados('3');
  // }

  buscarCirugias(): void{

    this.isDatos = false;

    this.mostrarDatos = false;

    this.datosTablafiltro = {
      "ingreso": this.formularioBusqueda.controls.formIngresoC.value ?? 0,
      "tipoDocumento": this.formularioBusqueda.controls.selTipDoc.value ?? "",
      "documento": this.formularioBusqueda.controls.documento.value ?? "",
      "via": this.formularioBusqueda.controls.selectVias.value ?? "",
      "especialidad": this.formularioBusqueda.controls.selectEspecialidad.value ?? "",
      "fecha": this.formularioBusqueda.controls.formFechaC.value ?? "",
      "todas": this.formularioBusqueda.controls.formTodasC.value ?? false
    }
    
    this.tablaControlCirugia.recuperarDatosCirugia(this.datosTablafiltro).subscribe((result:any) => {
      
      if(result['errorCode'] != 0){
        this.datosTabla = [];
        this.mostrarDatos = false;

        this.modalContenido ={
          titulo: "Error en los <b>filtros de busqueda</b>",
          contenido: result["errorMessage"],
          boton1: "Aceptar",
          boton2: ''
        }

        this.modal = true;
        this.datosTabla = false;

        this.tipoModal = "alerta";

      }else{


        if(result['datos'].length > 0){
          this.ingreso = this.formularioBusqueda.controls.formIngresoC.value ?? 0;
          this.formularioBusqueda.controls.formNombreC.setValue(result['datos'][0]['nombres'] ?? "");
          this.formularioBusqueda.controls.formApellidoC.setValue(result['datos'][0]['apellidos'] ??"");
          this.isDatos = true;
  
  
          this.coloresEstados.recuperarColores().subscribe((resultColor:any) => {
            let color: string ="";
            for (const keyPaciente in result['datos']) {
  
              for (let keyColor in resultColor['estados']) {
  
                let estado : string = result['datos'][keyPaciente]['estado'].toString();
  
                if(estado == keyColor ){
                  color = resultColor['estados'][keyColor]['COLOR'].toString();
                  result['datos'][keyPaciente]['color'] = "#"+color;
                }
  
              }
          }
        });
  
          this.datosTabla = result['datos'];
          this.mostrarDatos = true;
        }


      }

      

    });
  }

  evento1(evento : boolean) : void{
  }

  evento2(evento : boolean) : void{
  }

  cerrarModal(evento : boolean) : void{
    this.modal = evento;
  }


  buscarDatosPaciente() : void{
    
    this.datosPaciente = true;
  }


  modalPaciente(event: boolean): void{
    this.datosPaciente = event;
  }

  limpiarTodo(): void{
    this.formularioBusqueda.reset();
    this.formularioBusqueda.controls.formIngresoC.setValue(0);
    this.mostrarDatos = false;
    this.datosTabla = undefined;
  }

}
