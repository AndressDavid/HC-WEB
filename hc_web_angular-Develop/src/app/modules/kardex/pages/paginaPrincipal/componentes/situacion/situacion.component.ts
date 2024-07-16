import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, Input, OnInit } from '@angular/core';
import { IDatosKardex } from '../../interfaces/IdatosSituacion';
import { FormsModule } from '@angular/forms';
import { FooterKardexComponent } from "../footerKardex/footerKardex.component";
import { GuardarDatosService } from './guardarDatos.service';
import { IDatosEncabezado } from '../../interfaces/IdatosEncabezado';

@Component({
  selector: 'app-situacion',
  standalone: true,
  templateUrl: 'situacion.component.html',
  styleUrl: './situacion.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FormsModule,
    FooterKardexComponent
  ]
})
export class SituacionComponent implements OnInit{


  constructor(private gurdarDatosService: GuardarDatosService) { }



  lcNombreSituacion: string = "Situación";
  lcNombreMedico: string = "Medico tratante";
  lcFechaIngresoInst: string = "Fecha ingreso Inst.";
  lcFechaIngresoSer: string = "Fecha ingreso Serv.";
  lcAlergias: string = "Alergias";
  lcPrecauciones: string = "Precauciones hospitalarias";
  precau_hosp: string = "";
  prueba_fecha: string = "";

  @Input() datosDeSituacion?: IDatosKardex;

  ngOnInit() {
    if (this.datosDeSituacion?.nIngresoFecha) {
      this.prueba_fecha = this.formatDate(this.datosDeSituacion.nIngresoFecha);
      console.log("TTTraemos fecha ==========>", this.prueba_fecha );
    } else {
      console.log("No traemos fecha");
      this.prueba_fecha = ""; 
    }
  }

  formatDate(dateString: string): string {
    if (dateString.includes('-')) {
      return dateString.split(' ')[0]; // if already in "yyyy-MM-dd HH:mm:ss.SSS" format
    }
    const year = dateString.slice(0, 4);
    const month = dateString.slice(4, 6);
    const day = dateString.slice(6, 8);
    return `${year}-${month}-${day}`;
  }

  guardarDatos() {
    this.gurdarDatosService.validarDatosKardex(this.datosDeSituacion?.nIngreso).subscribe(response => {
      if (response !== null) {
        console.log("YAAAAAA existe el ingreso por lo tanto SE ACTUALIZARAAAAAAA");
        const fechaIngInst = this.convertirFecha(this.prueba_fecha);
        const fechaIngServ = this.convertirFecha(this.prueba_fecha);
        const datosActualizar = {
          medico: this.datosDeSituacion?.cMedicoTratanteNombre,
          fecha_ing_inst: this.formatearFechaParaGuardar(fechaIngInst),
          fecha_ing_serv: this.formatearFechaParaGuardar(fechaIngServ),
          alergias: this.datosDeSituacion?.lcAlergias,
          precau_hosp: this.datosDeSituacion?.precau_hosp,
          usuario_modifica: 'SIDANVAR',
          fecha_modificacion: this.formatearFecha(new Date())
        }

        console.log("Esta es loa fecha que guardo ==============> ",this.formatearFechaParaGuardar(fechaIngInst) );
        this.gurdarDatosService.actualizarDatosSituacion(datosActualizar, this.datosDeSituacion?.nIngreso).subscribe(response => {
          console.log("Estos son los datos actualizados", response);
        }, error => {
          console.error('Error al actualizar los datos:', error);
        })
      } else {
        console.log("No existe el ingreso por lo tanto se ingresara");
        const fechaIngInst = this.convertirFecha(this.prueba_fecha);
        const fechaIngServ = this.convertirFecha(this.prueba_fecha);
        const datosParaGuardar = {
          ingreso: this.datosDeSituacion?.nIngreso,
          medico: this.datosDeSituacion?.cMedicoTratanteNombre,
          fecha_ing_inst: this.formatearFechaParaGuardar(fechaIngInst),
          fecha_ing_serv: this.formatearFechaParaGuardar(fechaIngServ),
          alergias: this.datosDeSituacion?.lcAlergias,
          precau_hosp:this.datosDeSituacion?.precau_hosp,
          usuario_crea: 'SIDANVAR',
          fecha_creacion: this.formatearFecha(new Date())
        };
        console.log("Estos son los datos de las mugrosas fechas:   ", datosParaGuardar.fecha_creacion, datosParaGuardar.fecha_ing_inst, datosParaGuardar.fecha_ing_serv);
        console.log("Estos son los datos:   ", datosParaGuardar);
        this.gurdarDatosService.guardarDatosSituacion(datosParaGuardar).subscribe(response => {
          console.log("Estos son los datos guardados", response);
        }, error => {
          console.error('Error al guardar los datos:', error);
        })
      }
    })
  }

  convertirFecha(cadenaFecha: any) {
    const año = parseInt(cadenaFecha.substring(0, 4), 10);
    const mes = parseInt(cadenaFecha.substring(4, 6), 10) - 1;
    const día = parseInt(cadenaFecha.substring(6, 8), 10);
    return new Date(año, mes, día);
  }

  convertirFechas(cadenaFecha: any) {
    return new Date(cadenaFecha);
  }

  formatearFecha(fecha: any) {
    const año = fecha.getFullYear();
    const mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
    const día = ('0' + fecha.getDate()).slice(-2);
    return `${año}/${mes}/${día}`;
  }


  formatearFechaParaGuardar(fecha: Date) {
    const año = fecha.getFullYear();
    const mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
    const día = ('0' + fecha.getDate()).slice(-2);
    return `${año}-${mes}-${día} 00:00:00.000`;
  }
}

