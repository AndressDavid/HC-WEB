import { Component, EventEmitter, Input, Output } from '@angular/core';
import { DatosEncabezadoService } from './services/cabecera.service';
import { BrowserModule } from '@angular/platform-browser';

@Component({
  standalone: true,
  imports:[ BrowserModule  ],
  selector: 'app-cabecera-paciente',
  templateUrl: './cabecera-paciente.component.html',
  styleUrl: './cabecera-paciente.component.css'
})
export class CabeceraPacienteComponent {

  constructor(private datosEncabezadoService : DatosEncabezadoService){}

  public listaDatosEncabezado: any= [];
  public edadY: string = "";
  public edadMes : string = "";
  public edadDia : string = "";

  @Input()
  public ingreso: String ="0";

  @Output() datosIngreso = new EventEmitter<any>();

  ngOnInit(){
    this.recuperarDatosCabecera();
  }

  recuperarDatosCabecera() : any{
   this.datosEncabezadoService.obtenerDatosCabecera(this.ingreso).subscribe((result:any) => {
      this.edadY = result['datos'].aEdad.y;
      this.edadDia = result['datos'].aEdad.d;
      this.edadMes = result['datos'].aEdad.m;
      this.listaDatosEncabezado = result["datos"];
      this.datosIngreso.emit(result)
    });
  }

}
