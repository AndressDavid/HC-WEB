import { HttpClientModule, provideHttpClient, withInterceptors } from "@angular/common/http"
import { NgModule } from "@angular/core"
import { FormsModule, ReactiveFormsModule } from "@angular/forms"
import { BrowserModule } from "@angular/platform-browser"
import { AppRoutingModule } from "../../app-routing.module"
import { AppComponent } from "../../app.component"
import { tokenInterceptorInterceptor } from "../../environments/token/token-interceptor.interceptor"
import { FormularioCirugiaComponent } from "./formulario/formulario.component"
import { InicioCirugiaComponent } from "./inicio/inicio.component"
import { AutoCompleteModule } from "primeng/autocomplete"
import { ButtonModule } from "primeng/button"
import { StyleClassModule } from "primeng/styleclass"
import { TableModule } from "primeng/table"
import { CabeceraPacienteComponent } from "../common/cabecera-paciente/cabecera-paciente.component"
import { ConvencionesComponent } from "../common/convenciones/convenciones.component"
import { DiagnosticosComponent } from "../common/diagnosticos/diagnosticos.component"
import { DocumentoPacienteComponent } from "../common/documento-paciente/documento-paciente.component"
import { EspecialidadesMedicosComponent } from "../common/especialidades-medicos/especialidades-medicos.component"
import { EspecialidadesComponent } from "../common/especialidades/especialidades.component"
import { LibroHCWebComponent } from "../common/libro-hcweb/libro-hcweb.component"
import { LoadModalComponent } from "../common/load-modal/load-modal.component"
import { MedicamentosQuirurgicosComponent } from "../common/medicamentos-quirurgicos/medicamentos-quirurgicos.component"
import { MedicosEspecialidadComponent } from "../common/medicos-especialidad/medicos-especialidad.component"
import { ModalDatosPacienteComponent } from "../common/modal-datos-paciente/modal-datos-paciente.component"
import { ModalDinamicoComponent } from "../common/modal-dinamico/modal-dinamico.component"
import { ModalPatologiaComponent } from "../common/modal-patologia/modal-patologia.component"
import { OtrasSalasComponent } from "../common/otras-salas/otras-salas.component"
import { PdfViewComponent } from "../common/pdf-view/pdf-view.component"
import { ProcedimientosComponent } from "../common/procedimientos/procedimientos.component"
import { ProfesionalesComponent } from "../common/profesionales/profesionales.component"
import { SalaProcedimientosComponent } from "../common/sala-procedimientos/sala-procedimientos.component"
import { TipoAnestesiaComponent } from "../common/tipo-anestesia/tipo-anestesia.component"
import { TipoCirugiaComponent } from "../common/tipo-cirugia/tipo-cirugia.component"
import { ViasComponent } from "../common/vias/vias.component"
import { VistaPreviaPdfComponent } from "../common/vista-previa-pdf/vista-previa-pdf.component"


@NgModule({
  declarations: [
    InicioCirugiaComponent,
    FormularioCirugiaComponent,
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    TableModule,
    ButtonModule,
    AutoCompleteModule,
    StyleClassModule,
    /** */
    ModalDinamicoComponent,
    ModalPatologiaComponent,
    LibroHCWebComponent,
    VistaPreviaPdfComponent,
    PdfViewComponent,
    EspecialidadesComponent,
    ViasComponent,
    DocumentoPacienteComponent,
    MedicamentosQuirurgicosComponent,
    MedicosEspecialidadComponent,
    EspecialidadesMedicosComponent,
    DiagnosticosComponent,
    ProcedimientosComponent,
    TipoAnestesiaComponent,
    TipoCirugiaComponent,
    ProfesionalesComponent,
    OtrasSalasComponent,
    SalaProcedimientosComponent,
    CabeceraPacienteComponent,
    LoadModalComponent,
    ModalDatosPacienteComponent,
    ConvencionesComponent,
  ],
  providers: [provideHttpClient(withInterceptors([tokenInterceptorInterceptor]))],
  bootstrap: [
  ]
})
export class CirugiaModule{
}