import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit } from '@angular/core';
import { FooterKardexComponent } from "../footerKardex/footerKardex.component";
import { IDatosKardex } from '../../interfaces/IdatosSituacion';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-evaluacion',
  standalone: true,
  templateUrl: './evaluacion.component.html',
  styleUrl: './evaluacion.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FooterKardexComponent,
    FormsModule
  ]
})
export class EvaluacionComponent implements OnInit {
  constructor(private cdr: ChangeDetectorRef) { }

  ngOnInit(): void {
    
  }

  lcNombreEvaluacion: string = "Evaluación";
  lcEscalas: string = "Escalas";
  lcRiesgoDeCaida: string = "Riesgo de caída - Downtown";
  lcHumptyDumpt: string = "Humpty Dumpt";
  lcPuntaje: string = "Puntaje";
  lcRondaFrecuente: string = "Ronda mas frecuente";
  lcRiesgoAlto: string = "Riesgo alto";
  lcRiesgoBajo = "Riesgo bajo";
  lcInformacionTimbreMano = "Información timbre a la mano";
  lcCamaNivel: string = "Cama a nivel bajo";
  lcCompaniaPermanente: string = "Compañia permanente";
  lcMedidasSujecion: string = "Medidas de sujeción";
  lcRiesgoLesiones: string = "Riesgo de lesiones por presión - Braden";
  lcPuntajeLesiones: string = "Puntaje";
  lcRiesgoMedio: string = "Riesgo medio";
  lcIntervencion: string = "INTERVENCIÓN:";
  lcICPielSana: string = "IC Piel sana";
  lcLubricacionDePiel: string = "Lubricación de piel";
  lcCambiosPosicion: string = "Cambios de posición C/2H";
  lcAcidosGrasos: string = "Ácidos grasos";
  lcAliviadorPresion: string = "Aliviador de presión";
  lcProtectoresCutaneos: string = "Protectores cutáneos";
  lcRiesgoFuga: string = "Riesgo de fuga";
  lcRiesgoSi: string = "RIESGO: SI";
  lcRiesgoNo: string = "RIESGO: NO";
  lcBataVerde: string = "Bata verde";
  lcPuntoVerde: string = "Punto verde";
  lcNotificacion: string = "NOTIFICACIÓN:";
  lcCalidad: string = "Calidad";
  lcSeguridad: string = "Seguridad";
  lcEnfermeria: string = "Enfermería";
  lcRiesgoSangrado: string = "Riesgo de sangrado (HASBLED)";
  lcEducacionPte: string = "Educación al pte";
  lcVigilarSangrado: string = "Vigilar signos de sangrado";
  lcEvitarGolpesLesiones: string = "Evitar golpes y lesiones";
  lcRiesgoEnfermedadCaprini: string = ">Riesgo de enfermedad tromboembólica - CAPRINI";
  lcDeambulacionTemprana: string = "Deambulación temprana";
  lcMediasAntiembolicas: string = "Medias antiembólicas";
  lcAnticoagulacion: string = "Anticoagulación";
  lcCompresorVascular = "Compresor vascular";
  lcControles: string = "Controles";
  lcGlasgow: string = "Glasgow";
  lcDolor: string = "Dolor";
  lcControlLiquidos: string = "Control liquidos";
  lcEvaluacionPielHeridas: string = "Evaluación de piel y heridas";
  lcPielHeridas: string = "Evaluación de piel y heridas";
  lcUsuarioPanal: string = "Usuario de pañal:";
  lcSi: string = "SI";
  lcNo: string = "NO";
  lcAseoGenital: string = "Aseo genital";
  lcTerapias: string = "Terapias";
  lcFisicaCH: string = "T. Física C/_H";
  lcRespiratoriaCH: string = "T. Respiratoria C/_H";
  lcFonoaudiologia: string = "Fonoaudiología";
  lcFecha:string="Fecha";
  lcLaboratorios: string="Laboratorios";
  lcInterconsultas: string ="Interconsultas";
  lcImagenesDiagnosticas: string ="Imágenes diagnósticas";
  lcProcedimientos: string="Procedimientos";
  lcTranfusiones: string ="Transfusiones";
  @Input() datosDeEvaluacion?: IDatosKardex;

}
