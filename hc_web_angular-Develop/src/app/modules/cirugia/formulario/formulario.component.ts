import { Component, ElementRef, Injectable } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { iModal } from '../../common/load-modal/interfaces/IModal';
import { FormGroup, FormControl, Validators} from '@angular/forms';
import { IContenido } from '../../common/modal-dinamico/interfaces/contenidoModal';
import { consumoMedicamentoFiltro } from './interfaces/IFiltrosConsumoMedicamentos';
import { ServiceConsumoMedicamentos } from './services/consumoMedicamentos.service';
import { medicamentos } from './interfaces/IMedicamentos';
import { formatDate } from '@angular/common';
import { procedimientos } from './interfaces/IProcedimientos';
import { informacionGuardar } from './interfaces/IGuardarDescripcionQuirurgica';
import { validacionProcedimiento } from './interfaces/IvalidacionProcedimiento';
import { miPres } from './interfaces/IMiPres';
import { iModalEstilos } from '../../common/modal-dinamico/interfaces/TipoModal';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { IContenidoPDF } from '../../common/pdf-view/interfaces/IContenidoPDF';
import { IContenidoVistaPrevia } from '../../common/vista-previa-pdf/interfaces/IContenidoVistaPrevia';



@Component({
  selector: 'app-formulario',
  templateUrl: './formulario.component.html',
  styleUrl: './formulario.component.css'
})

export class FormularioCirugiaComponent {

  constructor(
    private _route : ActivatedRoute, 
    private consumoMedicamentos : ServiceConsumoMedicamentos,
    private el: ElementRef,
    private _sanitizer: DomSanitizer,
  ){}

  public modalContenido: iModal ={
    titulo: 'Se está preparando el entorno de trabajo',
    descripcion: 'Espere por favor'
  }

  public modalDinamicoContenido: IContenido={
    titulo: "",
    contenido: "",
    boton1: "",
    boton2: ''
  }


  public modalDinamiMiPres: IContenido={
    titulo: "",
    contenido: "",
    boton1: "",
    boton2: ''
  }


  public miPres : miPres={
    ruta: '',
    isMipre: '',
    texto: '',
    subtitulo: '',
    alerta: '',
    confirmacion1: '',
    confirmacion2: ''
  }

  public modalContenidoGuardar: IContenido={
    titulo: "<b>Guardar Control Quirurgico</b>",
    contenido: "¿Está seguro de guardar el procedimiento Quirurgico?",
    boton1: "Guardar",
    boton2: 'Cancelar'
  }
  
  public modalContenidoValidarProcedimiento : IContenido={
    titulo: "<b>La especialidad del médico no coincide con la especialidad del procedimiento.</b>",
    contenido: "¿Está seguro de adicionar el procedimiento?",
    boton1: "Adicionar",
    boton2: 'Cancelar'
  }

  public modalContenidoJustificacion  : IContenido={
    titulo: "",
    contenido: "",
    boton1: "",
    boton2: ''
  }

  public modalDinamicoContenidoEliminar: IContenido={
    titulo: "",
    contenido: "",
    boton1: "",
    boton2: ''
  }

  public miPresPersonalizado: iModalEstilos ={
    'icono': "fa fa-book",
    'color': "#74b4ac"
}

  public validacionProcedimiento: validacionProcedimiento={
    procedimiento: '',
  }


  
  public contenidoPDF : IContenidoPDF =
  {
    nIngreso: 0,
    cTipDocPac: '',
    nNumDocPac: 0,
    cRegMedico: '',
    cTipoDocum: '',
    cTipoProgr: '',
    tFechaHora: '',
    nConsecCita: '',
    nConsecCons: '',
    nConsecEvol: '',
    nConsecDoc: '',
    cCUP: '',
    cCodVia: '',
    cSecHab: '',
    isBase64: false
  };
  
  public vistaPrevias : IContenidoVistaPrevia ={
    nIngreso: 0,
    cTipDocPac: '',
    nNumDocPac: 0,
    cRegMedico: '',
    cTipoDocum: '',
    cTipoProgr: '',
    tFechaHora: '',
    nConsecCita: '',
    nConsecCons: '',
    nConsecEvol: '',
    nConsecDoc: '',
    cCUP: '',
    cCodVia: '',
    cSecHab: '',
    isBase64: false
  }


  public tipoModal: string="";
  public tipoModalEliminar : string = "advertencia";

  public isModalDinamico : boolean = false;
  public isEliminar : boolean  = false;
  public isEliminarMedicamento : boolean = false;
  public isModalPatologia : boolean = false;
  public isQueyMedicamnetos: boolean = true;
  public isBorrarListaMedicamentos : boolean = false;
  public activarValidations: boolean  = false;
  public isGuardar : boolean = false;
  public isComplicacion  : boolean= true;
  public activePerfusion : boolean = false;
  public isEditar: boolean = false;
  public guardarDisable: boolean = false;
  public isValidarProce: boolean = false;
  public isNoPos : boolean = false;
  public errorMipres : boolean = false;
  public modalMiPres : boolean = false;
  public enableVistaPrevia : boolean = true;
  public enableVistaPDF : boolean = true;


  public guardarControlCirugia = new FormGroup({
    dxdiag : new FormControl(''),
    procedimientos : new FormControl(''),
    dxPre : new FormControl(''),
    dxPost : new FormControl(''),
    dxRel : new FormControl(''),
    via : new FormControl(0),
    bilateralidad : new FormControl(false),
    incruento : new FormControl(false),
    cirujano : new FormControl(''),
    ayudante1 : new FormControl(''),
    ayudante2 : new FormControl(''),
    C1 : new FormControl(false),
    C2 : new FormControl(false),
    C3 : new FormControl(false),
    especialidad1 : new FormControl(''),
    especialidad2 : new FormControl(''),
    especialidad3 : new FormControl(''),
    Scomplicacion : new FormControl('', [
      Validators.required
    ]),
    salaProcedimiento : new FormControl(
      {
        value : "",
        disabled: false
      }
    ),
    otraSalas : new FormControl(
      {
        value : "",
        disabled: false
      }
    ),
    fechaIn : new FormControl('', [
      Validators.required
    ]),
    fechaFin : new FormControl('', [
      Validators.required
    ]),
    HoraIn : new FormControl('',[
      Validators.required
    ]),
    HoraFin : new FormControl('', [
      Validators.required
    ]),
    medicamentosQuirurgicos : new FormControl(''),
    cantMedicamento : new FormControl(0.1),
    hallazgos :new FormControl('',[
      Validators.required
    ]), 
    finalidad :new FormControl('',[
      Validators.required
    ]),
    anestesiologo : new FormControl('',[
      Validators.required
    ]),
    clasificacion : new FormControl('',[
      Validators.required
    ]),
    instrumentador : new FormControl(''),
    perfusionista : new FormControl(''),
    desCripQuiru  : new FormControl('',[
      Validators.required
    ]),
    complicacion : new FormControl(
    {
      value : "",
      disabled: true
    }),
    estadoSalida : new FormControl('',[
      Validators.required
    ]),
    envioPatologia : new FormControl("",[
        Validators.required
    ]),
    tipoCirugia : new FormControl(0),
    tipoAnestesia :  new FormControl(0),
    politraumatizado : new FormControl(false),
    riesgo : new FormControl(""),
    viaActo: new FormControl(1),
    asa :new FormControl(""),
    preoperatorio : new FormControl(false),
    interoperatorio : new FormControl(false),
    posoperatorio : new FormControl(false),
    Cardioplejia : new FormControl(false),
    anterogrado: new FormControl(1),
    retrograda : new FormControl(1),
    simultánea : new FormControl(1),
    pinzaAortica : new FormControl(1),
    perfusion : new FormControl(1),
    paroTotal : new FormControl(1),
    temperaturaRectal : new FormControl(1),
    justificacion : new FormControl("")
  });

  public tituloBusca1 : string= "Seleccione una especialidad";
  public tituloBusca2 : string= "Seleccione una especialidad";
  public tituloBusca3 : string= "Seleccione una especialidad";

  public activar : boolean = true;
  public ingreso: String ="";
  public especialidadCirujano : string ="00";
  public especialidadAyudante1 : string ="00";
  public especialidadAyudante2 : string ="00";
  public tablaProcedimientos : any [] =[];
  public tablaMedicamentoConsumo : medicamentos [] =[];
  public arrayValidacionProcedimientos : validacionProcedimiento [] =[];
  public procedimientos : procedimientos | undefined;
  public datosCabecera : any;
  public sexoPaciente : string = "";

  private consumoMedFiltro : consumoMedicamentoFiltro | undefined;
  public nombreCirujano : string= "";
  public nombreAyudante1 : string= "";
  public nombreAyudante2 : string= "";
  public codigoEliminar : string = "";
  public tipoModalGuardar: string ="info";
  public tipoModalValidarProcedimiento: string = "info";
  public miPreTipo : string="personalizado";

  
  private key : number =0;


  public estructuraGuardado! : informacionGuardar;
  public labelOtraSalaText : String ="";


  ngOnInit(): void{

    this.obtenerParametros();
    setTimeout(() => { 
      this.activar= false;
    }, 3000);  
  }

  datosIngreso(event : any){
    this.datosCabecera = event['datos'];
    this.sexoPaciente = this.datosCabecera.cDescSexo.trim();
    this.consumoMedicamentos.extaerMiPres(this.datosCabecera["cPlan"], this.datosCabecera["cTipoPlan"] ).subscribe((result:any) => {
      if( result["errorCode"] !=0 ){
        this.errorMipres = true;
      }else{
        this.miPres = result;
      }
    });

  }


  obtenerParametros(){
    this._route.queryParamMap.subscribe(params => {
      this.ingreso = params.get('ingreso') ?? "";
    });
  }

  cambioEspecialidadCirujano(eventEspecialidad:string){
    this.tituloBusca1 = "Buscando...";
    this.especialidadCirujano ="00";

    this.obtenerParametros();
    setTimeout(() => { 
      this.especialidadCirujano = eventEspecialidad;
    }, 400); 
 
  }

  cambioEspecialidadAyudante1(eventEspecialidad:string){
    this.tituloBusca2 = "Buscando...";
    this.especialidadAyudante1 ="00";

    this.obtenerParametros();
    setTimeout(() => { 
      this.especialidadAyudante1 = eventEspecialidad;
    }, 400); 
 
  }

  cambioEspecialidadAyudante2(eventEspecialidad:string){
    this.tituloBusca3 = "Buscando...";
    this.especialidadAyudante2 ="00";

    this.obtenerParametros();
    setTimeout(() => { 
      this.especialidadAyudante2 = eventEspecialidad;
    }, 400); 
 
  }

  validarProcedimiento(){

    if(!this.validarProcedimientos()){
      return;
    }

    let usuarioEspecialidad = JSON.parse(atob(sessionStorage['userhcweb']))['especialidad'];
    let codigo = this.guardarControlCirugia.controls.procedimientos.value?.split("-")[0] ?? "";

    this.consumoMedicamentos.validarProcedimiento(codigo).subscribe((result:any) => {
      
      let especialidad: string =result.especialidad;

      if(result.pos === 'N'){
        this.isNoPos = true;
      }else{
        this.isNoPos = false;
      }

      if(usuarioEspecialidad !=especialidad){
        this.isValidarProce = true;
      }else{
        this.agregarProcedimientoTabla();
      }

    });

  }

  agregarProcedimientosPos(){

    let procedimiento: String = this.guardarControlCirugia.controls.procedimientos.value ??"";
    let valido: boolean = true;

    for (const key in this.tablaProcedimientos) {

      if(this.arrayValidacionProcedimientos[key].procedimiento === procedimiento){
        valido = false;
        break;
      }
    }

    if(valido){
      this.validacionProcedimiento={
        "procedimiento": procedimiento.toString()
      }
  
      this.arrayValidacionProcedimientos.push(this.validacionProcedimiento);
      this.isNoPos = false;
    }

  }

  eventoValidarProcedimiento(event : boolean): void{
    this.agregarProcedimientoTabla();
    this.isValidarProce = false;
  }

  eventoCancelarValidarProcedimiento( event : boolean): void{
    this.isValidarProce = false;
  }

  agregarProcedimientoTabla() : void{

    let cobro1:string = (this.guardarControlCirugia.controls.C1.value) ? "S" : "N";
    let cobro2:string = (this.guardarControlCirugia.controls.C2.value) ? "S" : "N";
    let cobro3:string = (this.guardarControlCirugia.controls.C3.value) ? "S" : "N";
    let codigo = this.guardarControlCirugia.controls.procedimientos.value?.split("-")[0] ?? "";


    let bilateralidad: string = (this.guardarControlCirugia.controls.bilateralidad.value) ? "S" : "N";
    let incruento: string = (this.guardarControlCirugia.controls.incruento.value) ? "S" : "N";


    if(!this.validarProcedimientos()){
      return;
    }

    if(this.isNoPos){
      this.agregarProcedimientosPos();
    }

    this.procedimientos ={
      "procedimiento": this.guardarControlCirugia.controls.procedimientos.value ?? "",
      "codigoProcedimiento": codigo,

      "cirujano": this.nombreCirujano,
      "registro_cirujano": this.guardarControlCirugia.controls.cirujano.value ?? "",
      "c1": cobro1,

      "ayudante1": this.nombreAyudante1,
      "registro_ayudante1": this.guardarControlCirugia.controls.ayudante1.value ?? "",
      "c2": cobro2,

      "ayudante2": this.nombreAyudante2,
      "registro_ayudante2": this.guardarControlCirugia.controls.ayudante2.value ?? "",
      "c3": cobro3,

      "prequirurgico":  this.guardarControlCirugia.controls.dxPre.value ?? "",
      "postquirurgico": this.guardarControlCirugia.controls.dxPost.value ?? "",
      "relacionado": this.guardarControlCirugia.controls.dxRel.value ?? "",
      "via": this.guardarControlCirugia.controls.via.value ?? 0,
      "bilateralidad": bilateralidad,
      "incruento": incruento,
      "especialdad_cirujano": this.guardarControlCirugia.controls.especialidad1.value?? "",
      "especialdad_ayudante1": this.guardarControlCirugia.controls.especialidad2.value ?? "",
      "especialdad_ayudante2": this.guardarControlCirugia.controls.especialidad3.value ?? ""
    }

    for (const key in this.tablaProcedimientos) {

      if(this.tablaProcedimientos[key].codigoProcedimiento == codigo && !this.isEditar){

        this.modalDinamicoContenido ={
          titulo: "Error al agregar el código de procedimiento",
          contenido: "Error el código de procedimiento <b>"+codigo+"</b> ya esta agregado",
          boton1: "Aceptar",
          boton2: ''
        }
        this.tipoModal ="alerta";
        this.isModalDinamico = true;

        return;

      }
    }

    if(this.isEditar){
      this.tablaProcedimientos[this.key] = this.procedimientos;
    }else{
      this.tablaProcedimientos.push(this.procedimientos);
    }


    this.restablecerProcedimientos();
  }

  private validarProcedimientos () : boolean{

    if(this.guardarControlCirugia.controls.procedimientos.value === "" || this.guardarControlCirugia.controls.procedimientos.value === null){
      this.modalDinamicoContenido ={
        titulo: "Error al agregar el código de procedimiento",
        contenido: "El código de procedimiento es obligatorio",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;
      return false;
    }


    if(this.guardarControlCirugia.controls.dxPre.value === "" || this.guardarControlCirugia.controls.dxPre.value === null){
      this.modalDinamicoContenido ={
        titulo: "Error al agregar el Diagnóstico Prequirúrgico ",
        contenido: "El Diagnóstico Prequirúrgico  es obligatorio",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;
      return false;
    }

    if(this.guardarControlCirugia.controls.dxPost.value === "" || this.guardarControlCirugia.controls.dxPost.value === null){
      this.modalDinamicoContenido ={
        titulo: "Error al agregar el Diagnóstico Postquirúrgico",
        contenido: "El Diagnóstico Postquirúrgico es obligatorio",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;
      return false;
    }

    if(this.guardarControlCirugia.controls.via.value == 0){
      this.modalDinamicoContenido ={
        titulo: "Error al agregar la vía",
        contenido: "La vía debe ser mayor a 0",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;
      return false;
    }

    if(this.guardarControlCirugia.controls.cirujano.value === "" || this.guardarControlCirugia.controls.cirujano.value === null){
      this.modalDinamicoContenido ={
        titulo: "Error al agregar el Cirujano ",
        contenido: "El Cirujano es obligatorio",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;
      return false;
    }

    return true;
  }


  textoSeleccionadoCirujano(event: any): void{
    this.nombreCirujano = event;
  }

  textoSeleccionadoAyudante1(event: any): void{
    this.nombreAyudante1 = event;
  }

  textoSeleccionadoAyudante2(event: any): void{
    this.nombreAyudante2 = event;
  }
  evento1(event : any): void{

  }

  cerrarModal(event:any): void{
    this.isModalDinamico = event;
    this.isBorrarListaMedicamentos = event;
  }

  eliminar(codigo: string): void{
    this.isEditar = false;

    this.modalDinamicoContenidoEliminar ={
      titulo: "Eliminar Procedimiento",
      contenido: "¿Esta seguro de eliminar el procedimiento "+codigo+"?" ,
      boton1: "Eliminar",
      boton2: 'Cancelar'
    };
      
    this.isEliminar = true;
    this.codigoEliminar = codigo;
  }


  private restablecerProcedimientos(): void{
    this.isEditar = false;
    this.tituloBusca1 = "Seleccione una especialidad";
    this.especialidadCirujano ="00";

    this.tituloBusca2 = "Seleccione una especialidad";
    this.especialidadAyudante1 ="00";


    this.tituloBusca3 = "Seleccione una especialidad";
    this.especialidadAyudante2 ="00";

    this.guardarControlCirugia.controls.procedimientos.reset();
    this.guardarControlCirugia.controls.procedimientos.enable();

    this.guardarControlCirugia.controls.especialidad1.reset();
    this.guardarControlCirugia.controls.cirujano.reset();

    this.guardarControlCirugia.controls.especialidad2.reset();
    this.guardarControlCirugia.controls.ayudante1.reset();

    this.guardarControlCirugia.controls.especialidad3.reset();
    this.guardarControlCirugia.controls.ayudante2.reset();
    
    this.guardarControlCirugia.controls.dxPre.reset();
    this.guardarControlCirugia.controls.dxPost.reset();
    this.guardarControlCirugia.controls.dxRel.reset();

    this.guardarControlCirugia.controls.bilateralidad.reset();
    this.guardarControlCirugia.controls.incruento.reset();
    this.guardarControlCirugia.controls.via.reset();
    

  }


  cerrarModalEliminar(event: any): void{
    this.isEliminar = event;
  }

  eventoEliminar(event : any): void{
    
    if(this.codigoEliminar !== "" && event && this.isEliminar){
      for (const key in this.tablaProcedimientos) {
        if(this.tablaProcedimientos[key].codigoProcedimiento == this.codigoEliminar){
          this.tablaProcedimientos.splice(parseInt(key),1);
        }
      }

      for (const key in this.arrayValidacionProcedimientos) {
        if(this.arrayValidacionProcedimientos[key].procedimiento.split("-")[0] == this.codigoEliminar){
          this.arrayValidacionProcedimientos.splice(parseInt(key),1);
        }
      }
    }

    if(this.codigoEliminar !== "" && event && this.isEliminarMedicamento){
      for (const key in this.tablaMedicamentoConsumo) {
        if(this.tablaMedicamentoConsumo[key].codigo == this.codigoEliminar){
          this.tablaMedicamentoConsumo.splice(parseInt(key),1);
        }
      }
    }

    this.codigoEliminar ="";
    this.isEliminar = false;
    this.isEliminarMedicamento = false;

  }

  eventoCancelarEliminar(event : any): void{
    this.isEliminar = false;
    this.isEliminarMedicamento = false;
  }


  editar(datos: any): void{

    let bilateralidad : boolean = (datos.bilateralidad === 'S') ? true : false;
    let incruento : boolean = (datos.incruento === 'S') ? true : false;

    this.guardarControlCirugia.controls.procedimientos.setValue(datos.procedimiento);
    this.guardarControlCirugia.controls.dxPre.setValue(datos.prequirurgico);
    this.guardarControlCirugia.controls.dxPost.setValue(datos.postquirurgico);
    this.guardarControlCirugia.controls.dxRel.setValue(datos.relacionado);

    this.guardarControlCirugia.controls.via.setValue(datos.via);
    this.guardarControlCirugia.controls.bilateralidad.setValue(bilateralidad);
    this.guardarControlCirugia.controls.incruento.setValue(incruento);

    this.isEditar = true;
    this.guardarControlCirugia.controls.procedimientos.disable();


    for (const key in this.tablaProcedimientos) {
      if(this.tablaProcedimientos[key].codigoProcedimiento == datos.codigo){
        this.key = parseInt(key);
      }
    }
  }

  selectComplicacion(event : any): any{
    if( this.guardarControlCirugia.controls.Scomplicacion.value=== "" || this.guardarControlCirugia.controls.Scomplicacion.value === 'N' ){
      this.guardarControlCirugia.controls.complicacion.disable();
    }
    else{
      this.guardarControlCirugia.controls.complicacion.enable();
    }
  }

  modalPatologia(){
    this.isModalPatologia = true;
  }

  IsModalActivePatologia(event : any){
    this.isModalPatologia = !event;
  }


  activarPerfusion(event : any): any{
    if(event == '1'){
      this.activePerfusion = true;
    }
    else{
      this.activePerfusion = false;
    }
  }

  validarCampos(): any{

    if(this.guardarDisable){
      this.disabledFormMedicamentos();
      return;
    }

    this.disabledFormMedicamentos();

    let otraSala : boolean = this.guardarControlCirugia.controls.otraSalas.value == '' || this.guardarControlCirugia.controls.otraSalas.value == null ;
    let sala : boolean = this.guardarControlCirugia.controls.salaProcedimiento.value == '' || this.guardarControlCirugia.controls.salaProcedimiento.value == null;
    let fechaFin : boolean = this.guardarControlCirugia.controls.fechaFin.value == '' || this.guardarControlCirugia.controls.fechaFin.value == null;
    let fechaIn : boolean = this.guardarControlCirugia.controls.fechaIn.value == '' || this.guardarControlCirugia.controls.fechaIn.value == null;

    if( !otraSala ){
      this.enableFormMedicamentos();
      
      if(this.tablaMedicamentoConsumo.length > 0){
        this.tablaMedicamentoConsumo = this.tablaMedicamentoConsumo;
      }
      else{
        this.tablaMedicamentoConsumo =[];
      }

    }else{
      if( (sala || !otraSala) && !this.guardarDisable  ){
        this.modalDinamicoContenido ={
          titulo: "<b>Consulta de medicamentos</b>",
          contenido: "Es obligatorio seleccionar una <b>sala</b>",
          boton1: "Aceptar",
          boton2: ''
        }
        this.tipoModal ="alerta";
        this.isModalDinamico = true;
      }else if(fechaIn || !otraSala){
        this.modalDinamicoContenido ={
          titulo: "<b>Consulta de medicamentos</b>",
          contenido: "Es obligatorio seleccionar una <b>Fecha Inicio</b>",
          boton1: "Aceptar",
          boton2: ''
        }
        this.tipoModal ="alerta";
        this.isModalDinamico = true;
      }
      else if(fechaFin || !otraSala){
        this.modalDinamicoContenido ={
          titulo: "<b>Consulta de medicamentos</b>",
          contenido: "Es obligatorio seleccionar una <b>Fecha Fin</b>",
          boton1: "Aceptar",
          boton2: ''
        }
        this.tipoModal ="alerta";
        this.isModalDinamico = true;
      }else{
        this.enableFormMedicamentos();
        this.agregarConsumoMedicamentos();
      }
    }
  }

  disabledFormMedicamentos() : any{
    this.guardarControlCirugia.controls.medicamentosQuirurgicos.disable();
    this.guardarControlCirugia.controls.cantMedicamento.disable();
  }

  enableFormMedicamentos() : any{
    this.guardarControlCirugia.controls.medicamentosQuirurgicos.enable();
    this.guardarControlCirugia.controls.cantMedicamento.enable();
  }

  cambioSala(event : string) : any{

    if(event == null || event == ''){
      this.guardarControlCirugia.controls.otraSalas.enable();
    }
    else{
      this.guardarControlCirugia.controls.otraSalas.disable();
    }

    this.guardarControlCirugia.controls.otraSalas.reset();
  }

  cambioOtraSala(event : string): any{
   
    if(event == null || event == ''){
      this.guardarControlCirugia.controls.salaProcedimiento.enable();
    }
    else{
      this.guardarControlCirugia.controls.salaProcedimiento.disable();
    }

    this.guardarControlCirugia.controls.salaProcedimiento.reset();

  }

  adicionarMedicamento(): any{

    let codigo = this.guardarControlCirugia.controls.medicamentosQuirurgicos.value?.split("-")[0] ?? '';
    let descripcion = this.guardarControlCirugia.controls.medicamentosQuirurgicos.value?.replace(codigo+"-", '');

    let medicamentoAdd : medicamentos={
      'codigo': codigo,
      'descripcion': descripcion ?? '',
      'cantidad': this.guardarControlCirugia.controls.cantMedicamento.value ?? 0.0
    }

    if(!this.validarFormMedicamentos()){
      return;
    }

    if(this.isEditar){
      this.actualizarMedicamentos(medicamentoAdd);
      this.resetFormularioMedicamentos();
      return;
    }
    

    for (const key in this.tablaMedicamentoConsumo) {
      if(this.tablaMedicamentoConsumo[key].codigo == codigo){
        this.modalDinamicoContenido ={
          titulo: "Error al agregar el código de medicamento",
          contenido: "El medicamento <b>"+descripcion+"</b> ya esta agregado",
          boton1: "Aceptar",
          boton2: ''
        }
        this.tipoModal ="alerta";
        this.isModalDinamico = true;

        return;
      }
    }
    this.tablaMedicamentoConsumo.push(medicamentoAdd);
    this.resetFormularioMedicamentos();
  }

  validarFormMedicamentos() : boolean{

    if( this.guardarControlCirugia.controls.medicamentosQuirurgicos.value == '' || this.guardarControlCirugia.controls.medicamentosQuirurgicos.value == null ){
      this.modalDinamicoContenido ={
        titulo: "Error al agregar el código de medicamento",
        contenido: "El medicamento es de caracter obligatorio",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;
      return false;
    }

    let cantidad = this.guardarControlCirugia.controls.cantMedicamento.value ?? 0.0;

    if( cantidad < 0.1  ){
      this.modalDinamicoContenido ={
        titulo: "Error al agregar el código de medicamento",
        contenido: "El  campo cantidad debe ser mayor a cero",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;
      return false;
    }

    return true;
  }
  eliminarMedicamento(codigo : string) : void{
    this.isEditar = false;

    this.modalDinamicoContenidoEliminar ={
      titulo: "Eliminar Medicamento",
      contenido: "¿Esta seguro de eliminar el medicamento "+codigo+"?",
      boton1: "Eliminar",
      boton2: 'Cancelar'
    };
      
    this.isEliminarMedicamento = true;
    this.codigoEliminar = codigo;
  }

  resetFormularioMedicamentos(){
    this.guardarControlCirugia.controls.cantMedicamento.reset();
    this.guardarControlCirugia.controls.medicamentosQuirurgicos.reset();
  }


  formatoFecha(fecha : string) : number{
    return parseInt(formatDate(fecha, 'yyyyMMdd', 'en-US'));
  }
  formatoHora(hora: string ): number{
    return parseInt(hora.replace(":","")+"00");
  }

  validarFechas():boolean{

    let fechaIni:number = this.formatoFecha(this.guardarControlCirugia.controls.fechaIn.value?? '');
    let horaini:number = this.formatoHora(this.guardarControlCirugia.controls.HoraIn.value??"00000000");

    let fechaFin:number = this.formatoFecha(this.guardarControlCirugia.controls.fechaFin.value?? '');
    let horaFin:number = this.formatoHora(this.guardarControlCirugia.controls.HoraFin.value??"00000000");

    if(horaini >= horaFin){
      if( fechaIni >= fechaFin ){
        return false;
      }
    }
    return true;
  }

  agregarConsumoMedicamentos(){

    this.consumoMedFiltro ={
      "ingreso": this.ingreso.toString(),
      "sala": this.guardarControlCirugia.controls.salaProcedimiento.value?.toString() ?? '',
      "tipoconsumo": "500",
      "fechainicio": this.formatoFecha(this.guardarControlCirugia.controls.fechaIn.value?? ''),
      "horainicio": this.formatoHora(this.guardarControlCirugia.controls.HoraIn.value??"00000000"),
      "fechafinal": this.formatoFecha(this.guardarControlCirugia.controls.fechaFin.value?? ''),
      "horafinal": this.formatoHora(this.guardarControlCirugia.controls.HoraFin.value??"00000000")
    }

    if(!this.isQueyMedicamnetos){
      return;
    }


    this.consumoMedicamentos.recuperarConsumoMedicamentos(this.consumoMedFiltro).subscribe((result:any) => {

      if(result["errorCode"] == 0){

        this.isQueyMedicamnetos = false;
        let response = JSON.parse(result["datos"]);

        if(this.tablaMedicamentoConsumo.length < 1){
          this.tablaMedicamentoConsumo = response;
        }else{
          for (const key in response) {
            if(this.buscarMedicamento(response.codigo)){
              this.tablaMedicamentoConsumo.push(response[key]);
            }
          }
        }

      }else{
        this.tablaMedicamentoConsumo =[];
      }

    });
  }

  private buscarMedicamento(codigo: string) : boolean{
    for (let i = 0; i < this.tablaMedicamentoConsumo.length; i++) {
      const element = this.tablaMedicamentoConsumo[i];
      if (element.codigo === codigo) {
        return true;
      }
    }
    return false
  }

  public btnBorrarLista(){
    this.modalDinamicoContenidoEliminar ={
      titulo: "Eliminar lista Medicamentos",
      contenido: "¿Esta seguro de eliminar <b>toda la lista de medicamentos</b>?",
      boton1: "Eliminar",
      boton2: 'Cancelar'
    };
      
    this.tipoModal = "advertencia";
    this.isBorrarListaMedicamentos = true;
  }

  public eliminarListaMedicamentos(event : boolean){

    this.tablaMedicamentoConsumo =[];
    this.tipoModal = "";
    this.resetFormularioMedicamentos();
    this.isBorrarListaMedicamentos = false;

  }

  public actualizarMedicamentosBtn(medicamento : medicamentos){
    this.guardarControlCirugia.controls.medicamentosQuirurgicos.setValue( medicamento.codigo+"-"+medicamento.descripcion );
    this.guardarControlCirugia.controls.cantMedicamento.setValue( medicamento.cantidad );
    this.guardarControlCirugia.controls.medicamentosQuirurgicos.disable();
    this.isEditar = true;
  }

  private actualizarMedicamentos( dato : medicamentos){
    for (const key in this.tablaMedicamentoConsumo) {
      if(this.tablaMedicamentoConsumo[key].codigo == dato.codigo){
        this.tablaMedicamentoConsumo[key] = dato;
        this.isEditar = false;
        this.guardarControlCirugia.controls.medicamentosQuirurgicos.enable();
        return;
      } 
    }
  }

  public labelOtraSala( event : String): void{
      this.labelOtraSalaText = event;
  }

  public onSubmit(){

    this.guardarControlCirugia.controls.cantMedicamento.setValue(0.1);
    this.isGuardar= true;

  }

  public guardar(){

    if(this.guardarControlCirugia.controls.salaProcedimiento.value ==="" && this.guardarControlCirugia.controls.otraSalas.value ==="" ){
      this.modalDinamicoContenido ={
        titulo: "Error al guardar la información",
        contenido: "El campo <b>realizado en sala de cirugía</b> u <b>otra sala es obligatorio</b>. ",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;

      return;
    }


    if(this.guardarControlCirugia.valid){

      this.agregarConsumoMedicamentos();

      if(this.tablaMedicamentoConsumo.length > 0){
  
        for( let medicamento of this.tablaMedicamentoConsumo ){
        
          if(medicamento.cantidad <= 0){
              this.modalDinamicoContenido ={
                titulo: "Error al guardar la información",
                contenido: "Tiene medicamentos con <b>cantidad en 0.0</b>. ",
                boton1: "Aceptar",
                boton2: ''
              }
              this.tipoModal ="alerta";
              this.isModalDinamico = true;
              return;
          }
    
        }
      }

      if(this.validarCantidad()){
        return;
      }

      if(!this.validarFechas()){
        this.modalDinamicoContenido ={
          titulo: "Error al guardar la información",
          contenido: "La fecha y hora de inicio no pueden ser mayores a la fecha y hora final.",
          boton1: "Aceptar",
          boton2: ''
        }
        this.tipoModal ="alerta";
        this.isModalDinamico = true;
        return;
      }

      
      let usuario = JSON.parse(atob(sessionStorage['userhcweb']));

      this.estructuraGuardado ={
        ingreso: this.ingreso.toString(),
        actoQuirurgico : {
          salaCirugia: this.guardarControlCirugia.controls.salaProcedimiento.value ?? "",
          otraSala: this.guardarControlCirugia.controls.otraSalas.value ?? "",
          fechaInicio: this.formatoFecha(this.guardarControlCirugia.controls.fechaIn.value ?? ""),
          horoInicio: this.formatoHora(this.guardarControlCirugia.controls.HoraIn.value ?? "0000"),
          fechaFin: this.formatoFecha(this.guardarControlCirugia.controls.fechaFin.value ?? ""),
          horaFin: this.formatoHora(this.guardarControlCirugia.controls.HoraFin.value ?? "0000"),
          tipoCirugia: this.guardarControlCirugia.controls.tipoCirugia.value ?? 0,
          finalidad: this.guardarControlCirugia.controls.finalidad.value!,
          instrumentador: this.guardarControlCirugia.controls.instrumentador.value ?? "",
          anestesiologo: this.guardarControlCirugia.controls.anestesiologo.value!,
          perfusionista: this.guardarControlCirugia.controls.perfusionista.value ?? "",
          tipoAnestesia: this.guardarControlCirugia.controls.tipoAnestesia.value ?? 0,
          clasificacionCirugia: this.guardarControlCirugia.controls.clasificacion.value!,
          politraumatizado: this.guardarControlCirugia.controls.politraumatizado.value!,
          riesgo: this.guardarControlCirugia.controls.riesgo.value ?? "",
          via: this.guardarControlCirugia.controls.viaActo.value!,
          asa: this.guardarControlCirugia.controls.asa.value ?? "",
          dxComplicacion: this.guardarControlCirugia.controls.dxdiag.value ?? "",
          hallazgos: this.guardarControlCirugia.controls.hallazgos.value!,
          labelOtraSala: this.labelOtraSalaText ?? ''
        },
        procedimientos : this.tablaProcedimientos,
        descripcionQuirurgica : {
          descripcion: this.guardarControlCirugia.controls.desCripQuiru.value!,
          complicaciones: this.guardarControlCirugia.controls.Scomplicacion.value!,
          descripcionComplicacion: this.guardarControlCirugia.controls.complicacion.value ?? "",
          patologia: this.guardarControlCirugia.controls.envioPatologia.value!,
          estadoSalida: this.guardarControlCirugia.controls.estadoSalida.value!
        },
        perfucion :{
          envio : this.activePerfusion,
          preoperatorio: this.guardarControlCirugia.controls.preoperatorio.value!,
          interoperatorio: this.guardarControlCirugia.controls.interoperatorio.value!,
          posoperatorio: this.guardarControlCirugia.controls.posoperatorio.value!,
          cardioplejia: this.guardarControlCirugia.controls.Cardioplejia.value!,
          anterogrado: this.guardarControlCirugia.controls.anterogrado.value ?? 0,
          retrograda: this.guardarControlCirugia.controls.retrograda.value ?? 0,
          simultanea: this.guardarControlCirugia.controls.simultánea.value ?? 0,
          pinzaAortica: this.guardarControlCirugia.controls.pinzaAortica.value ?? 0,
          perfusion: this.guardarControlCirugia.controls.perfusion.value ?? 0,
          paroTotal: this.guardarControlCirugia.controls.paroTotal.value ?? 0,
          temperaturaRectal: this.guardarControlCirugia.controls.temperaturaRectal.value ?? 0
        },
        medicamentos : this.tablaMedicamentoConsumo,
        informacionPaciente : this.datosCabecera,
        programa: "RIA133",
        usuario: usuario['usuario'],
        tipoUsuario: usuario['tipo'],
        especialidadUsuario: usuario['especialidad'],
        justificacion: ""
      }

      this.modalContenido ={
        titulo: 'Se esta guardando la información',
        descripcion: 'Espere por favor'
      }
      this.activar=true;

      this.consumoMedicamentos.guardarInformacion(this.estructuraGuardado).subscribe((result:any) => {
        if(result.errorCode ==0){
          this.activar=false;

          this.modalDinamicoContenido ={
            titulo: "Control Quirurjico",
            contenido: "<b>"+ result.errorMessage +"</b>",
            boton1: "Aceptar",
            boton2: ''
          }
          this.tipoModal ="info";
          this.isModalDinamico = true;
          this.guardarDisable = true;
          this.guardarControlCirugia.disable();
          this.activarValidations = false;

          if( this.miPres.isMipre=='S' ){
            let safe : SafeHtml = this._sanitizer.bypassSecurityTrustHtml(this.contenidoMiPres());
            this.modalDinamiMiPres ={
              titulo: "Alerta procedimientos MiPres", 
              contenido: safe, 
              boton1 : "Aceptar", 
              boton2 : "" 
            }
      
            this.modalMiPres = true;
            this.isGuardar = false;

          }

          this.contenidoPDF ={
            nIngreso:  parseInt(this.ingreso.toString()),
            cTipDocPac: this.datosCabecera['cTipId'],
            nNumDocPac: this.datosCabecera['nNumId'],
            cRegMedico: this.datosCabecera['cRegistroMedicoTratante'],
            cTipoDocum: "1800",
            cTipoProgr: "RIA133",
            tFechaHora: result.fechaHora,
            nConsecCita: result.consecCons,
            nConsecCons: result.consecCita,
            nConsecEvol: "0",
            nConsecDoc: "",
            cCUP: "22",
            cCodVia: this.datosCabecera["cCodVia"],
            cSecHab: this.datosCabecera["cSeccion"]+"-"+this.datosCabecera["cHabita"],
            isBase64: true
          }

          this.vistaPrevias ={
            nIngreso:  parseInt(this.ingreso.toString()),
            cTipDocPac: this.datosCabecera['cTipId'],
            nNumDocPac: this.datosCabecera['nNumId'],
            cRegMedico: this.datosCabecera['cRegistroMedicoTratante'],
            cTipoDocum: "1800",
            cTipoProgr: "RIA133",
            tFechaHora: result.fechaHora,
            nConsecCita: result.consecCons,
            nConsecCons: result.consecCita,
            nConsecEvol: "0",
            nConsecDoc: "",
            cCUP: "22",
            cCodVia: this.datosCabecera["cCodVia"],
            cSecHab: this.datosCabecera["cSeccion"]+"-"+this.datosCabecera["cHabita"],
            isBase64: false
          }

          this.enableVistaPrevia = false;
          this.enableVistaPDF = false;

        }else{

          this.modalDinamicoContenido ={
            titulo: "Error al guardar la información",
            contenido: "<b>"+ result.errorMessage +"</b>",
            boton1: "Aceptar",
            boton2: ''
          }
          this.tipoModal ="alerta";
          this.isModalDinamico = true;
          this.activarValidations = false;
          this.activar=false;
        }

      });

    }else{
      this.activarValidations = true;
    }
    

  }

  private validarCantidad() : boolean{
    if(this.tablaProcedimientos.length < 1){

      this.modalDinamicoContenido ={
        titulo: "Error al guardar la información",
        contenido: "Se debe agregar mínimo un procedimiento.",
        boton1: "Aceptar",
        boton2: ''
      }
      this.tipoModal ="alerta";
      this.isModalDinamico = true;

      return true;
    }

    return false;
  }

  private contenidoMiPres() : string{
    let contentBodyPart =  [];
    let copiado: string ="";

    contentBodyPart.push( '<div><div class="container-fluid">' );
    contentBodyPart.push('<p class="text-justify text-danger"><b>'+this.miPres.alerta+'</b></p></div>');
    contentBodyPart.push('<div class="container-fluid"><p>'+this.miPres.texto+'</p></div>');
    contentBodyPart.push('<div class="container-fluid"><p>'+this.miPres.subtitulo+': </p>');

    let count:number =0;
    for( const key in this.arrayValidacionProcedimientos){
      count++;
      contentBodyPart.push("<p><b>"+count+".  </b>"+ this.arrayValidacionProcedimientos[key].procedimiento+"</p>");
      copiado = copiado + count + "). "+this.arrayValidacionProcedimientos[key].procedimiento+"\n"; 
    }
    contentBodyPart.push('</div><div class="container-fluid"><p>'+this.miPres.confirmacion2+' <b class="text-danger">*</b></p></div>');
    contentBodyPart.push('<div class="container-fluid w-25"><select id="acepto" class="form-control w-25"><option></option><option>SI</option></select></div></div>');
    
    navigator.clipboard.writeText(copiado)
    .catch(err => {
      console.error('Error al copiar al portapapeles:', err)
    })


    return contentBodyPart.join('');
  }

  public eventoGuardar(event: boolean){

    this.guardar();
    this.isGuardar = false;
    
  }

  public eventoCancelarGuardar(event: boolean){
    this.isGuardar = false;
  }

  aceptarMiPres(event: boolean): void{

    let textElement: any = document.getElementById('acepto');
    let isAcepto: string = textElement.value;

    if(isAcepto === 'SI'){
      this.modalMiPres = false;
      window.open(this.miPres.ruta, '_blank');
    }

  }

}
