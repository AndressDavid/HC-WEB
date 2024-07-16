import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { AppRoutingModule } from '../../app-routing.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { IngresoComponent } from './componentes/ingreso/ingreso.component';
import { ProcedimientosAnestesiaComponent } from './componentes/procedimientos-anestesia/procedimientos-anestesia.component';
import { AnestesiaEcoPerioperatorioComponent } from './anestesia-eco-perioperatorio.component';

@NgModule({
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    IngresoComponent,
    ProcedimientosAnestesiaComponent,
  ],
  declarations: [AnestesiaEcoPerioperatorioComponent],
})
export class AnestesiaEcoPerioperatorioModule {}
