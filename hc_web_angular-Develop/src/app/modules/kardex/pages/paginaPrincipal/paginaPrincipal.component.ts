import { ChangeDetectorRef, Component, OnInit} from '@angular/core';
import { IDatosEncabezado } from './interfaces/IdatosEncabezado';
import { IDatosKardex } from './interfaces/IdatosSituacion';
import { IlistaPacientes } from './interfaces/IlistaPacientes';

@Component({
  selector: 'app-paginaPrincipal',
  templateUrl: './paginaPrincipal.component.html',
  // styleUrl: './paginaPrincipal.component.css'
})
export class PaginaPrincipalComponent implements OnInit {
  filtro: string = '';
  data: IlistaPacientes[] = [];
  dataFiltrada: IlistaPacientes[] = [];
  loading: boolean = true;
  totalRecords: number = 0; // Variable para el total de registros
  rows: number = 20; // Cantidad de filas por página
  constructor(private cdr: ChangeDetectorRef){}

  ngOnInit(): void {
    this.cdr.detectChanges();
  }

  CamposSituacion: boolean = false;
  CamposAntecedente: boolean = false;
  CamposEvaluacion: boolean = false;
  CamposRecomendacion: boolean = false;
  datosIngresoEncabezados: any;
  datosIngresoSituacion: any;
  datosIngresoAntecedentes: any;
  datosIngresoEvaluacion: any;
  datosIngresoRecomendacion: any;
  listaPacientes: any;
  datosIngresoEncabezadosData: any;
  mostrarTablaBus: boolean = true;
  mostrarModuloBusqueda:boolean = false;


  handleSeleccionSituacion(valor: boolean) {
    this.CamposSituacion = valor;
    this.CamposAntecedente = false;
    this.CamposEvaluacion = false;
    this.CamposRecomendacion = false;
    this.cdr.detectChanges();
  }

  handleSeleccionAntecedentes(valor: boolean) {
    this.CamposAntecedente = valor;
    this.CamposSituacion = false;
    this.CamposEvaluacion = false;
    this.CamposRecomendacion = false;
    this.cdr.detectChanges();
  }

  handleSeleccionEvaluacion(valor: boolean) {
    this.CamposEvaluacion = valor;
    this.CamposSituacion = false;
    this.CamposAntecedente = false;
    this.CamposRecomendacion = false;
    this.cdr.detectChanges();
  }

  handleSeleccionRecomendacion(valor: boolean) {
    this.CamposRecomendacion = valor;
    this.CamposSituacion = false;
    this.CamposAntecedente = false;
    this.CamposEvaluacion = false;
    this.cdr.detectChanges();
  }

  public listaDatosEncabezado?: IDatosEncabezado[] = [];
  validarModulo: boolean = false;

  public onGetDatosEncabezado(datos: IDatosEncabezado[]): void {
    if (datos && datos.length > 0) {
      this.datosIngresoEncabezadosData = datos;
      this.validarModulo = true;
      this.mostrarTablaBus = false;
      this.cdr.detectChanges();
    } else {
    }
  }

  public onGetDatosEncabezadoData(datos: IDatosEncabezado[]): void {
    if (datos && datos.length > 0) {
      this.datosIngresoEncabezadosData = datos;
      this.validarModulo = true;
      this.mostrarTablaBus = false;
      this.cdr.detectChanges();
    } else {
      console.log("Entrando al else");
    }
  }

  public onGetDatosSituacion(datosKardex: IDatosKardex[]) {
    if (datosKardex) {
      this.datosIngresoSituacion = datosKardex;
      this.validarModulo = true;
      this.cdr.detectChanges();
    } else {
      console.log("Entrando al else");
    }
  }

  public onGetDatosAntecedentes(datosKardex: IDatosKardex[]) {
    if (datosKardex) {
      this.datosIngresoAntecedentes = datosKardex; 
      this.validarModulo = true;
      this.cdr.detectChanges();
    } else {
      console.log("Entrando al else");
    }
  }

  public onGetDatosEvaluacion(datosKardex: IDatosKardex[]) {
    if (datosKardex) {
      this.datosIngresoEvaluacion = datosKardex; 
      this.validarModulo = true;
      this.cdr.detectChanges();
    } else {

      console.log("Entrando al else");
    }
  }

  public onGetDatosRecomendacion(datosKardex: IDatosKardex[]) {
    if (datosKardex) {
      this.datosIngresoRecomendacion = datosKardex; 
      this.validarModulo = true;
      this.cdr.detectChanges();
    } else {
      console.log("Entrando al else");
    }
  }


  public onGetListaPacientes(listPacientes: IlistaPacientes[]): void {
    if (listPacientes && listPacientes.length > 0) {
      this.listaPacientes = listPacientes;
      this.cdr.detectChanges();
    } else {
    }
  }

  public  onGetModuloBusqueda(valor: boolean) {
    this.mostrarModuloBusqueda = valor;
  }

  
  filtrar(filtro: string) {
    this.filtro = filtro;
    this.aplicarFiltro();
  }

  private aplicarFiltro() {
    this.dataFiltrada = this.data.filter((paciente: IlistaPacientes) => {
      return paciente.primerNombre.toLowerCase().includes(this.filtro.toLowerCase()) ||
             paciente.primerApellido.toLowerCase().includes(this.filtro.toLowerCase()) ||
             paciente.numeroIngreso.toString().includes(this.filtro.toLowerCase());
    });
  }

}
