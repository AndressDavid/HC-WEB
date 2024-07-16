import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, Input, OnInit } from '@angular/core';
import { FooterKardexComponent } from "../footerKardex/footerKardex.component";
import { IDatosKardex } from '../../interfaces/IdatosSituacion';
import { FormsModule } from '@angular/forms';

@Component({
    selector: 'app-antecedentes',
    standalone: true,
    templateUrl: './antecedentes.component.html',
    styleUrl: './antecedentes.component.css',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        CommonModule,
        FooterKardexComponent,
        FormsModule
    ]
})
export class AntecedentesComponent implements OnInit{

  ngOnInit(): void {
    this.limpiarTextoHTML();
  }
  lcTextoLimpio: string = '';
  lcNombreAntecedente: string = 'Antecedentes';
  lcDiagnosticoPrincipal: string = 'Diagnostico principal';
  lcAntecedentesMedicos: string = 'Antecedentes médicos y farmacológicos';
  lcTratamientoMedicamentoso: string = 'Tratamiento medicamentos para seguimiento';
  lcTipoDispositivoIntra: string = 'Tipo de dispositivo intravascular';
  lcSitio: string = 'Sitio';
  lcFechaInsercion = 'Fecha inserción';
  lcFechaCuracion: string = 'Fecha curación';
  lcLiquidoEndovenosos: string = 'Líquidos endovenosos';
  lcInfusion: string = 'Infusión';
  lcCCh: string = 'CC/h';
  lcControlEquipos: string = 'Control equipos de infusión';
  lcFechaEquipo: string = 'Fecha equipo';
  lcFechaCambio: string = 'Fecha cambio';
  lcEstadoNutricional: string = 'Estado nutricional';
  lcTalla: string = 'Talla';
  lcGluco: string = 'Gluco';
  lcRhidrica: string = 'R. hidrica';
  lcDieta: string = 'Dieta';
  lcSoporteNutricionalEnteral: string = 'Soporte nutricional enteral';
  lcSoporteOxigeno: string = 'Soporte de oxigeno';
  lcDispositivo: string = 'Dispositivo';
  lcFIO2: string = 'FIO2';
  lcDispositivos: string = 'Dispositivos';
  lcTipoDispositivo: String = 'Tipo de dispositvo';
  lcFechaRetiroCambio:String ='Fecha retiro/cambio';
  lcObservaciones:string='Observaciones';

  @Input() datosDeAntecedentes?: IDatosKardex;

  
  limpiarTextoHTML() {
    if (this.datosDeAntecedentes && this.datosDeAntecedentes.lcAlergia && this.datosDeAntecedentes.Liquidos) {
      const temp = document.createElement('div');
      temp.innerHTML = this.datosDeAntecedentes.lcAlergia.toString();
      this.lcTextoLimpio = temp.textContent || temp.innerText || '<br>';
    }
  }

}
