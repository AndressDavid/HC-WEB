import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { AppRoutingModule } from '../../app-routing.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { ConsultaComponent } from './componentes/consulta/consulta.component';
import { TablaConsultaComponent } from './componentes/tabla-consulta/tabla-consulta.component';
import { HabitacionInteligenteComponent } from './habitacion-inteligente.component';

@NgModule({
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    ConsultaComponent,
    TablaConsultaComponent,
  ],
  declarations: [HabitacionInteligenteComponent],
})
export class HabitacionInteligenteModule {}
