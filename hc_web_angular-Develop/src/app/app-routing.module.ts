import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { canActivateAuth, canActivateRedirect } from './app.service';
import { FormularioCirugiaComponent } from './modules/cirugia/formulario/formulario.component';
import { InicioCirugiaComponent } from './modules/cirugia/inicio/inicio.component';
import { InicioComponentNotas } from './modules/notes/inicio/inicio.component';
import { HabitacionInteligenteComponent } from './modules/habitacion-inteligente/habitacion-inteligente.component';
import { ProcedimientosAnestesiaComponent } from './modules/anestesia-eco-perioperatorio/componentes/procedimientos-anestesia/procedimientos-anestesia.component';
import { AnestesiaEcoPerioperatorioComponent } from './modules/anestesia-eco-perioperatorio/anestesia-eco-perioperatorio.component';

const routes: Routes = [
  {
    path: 'control-cirugia-formulario',
    component: FormularioCirugiaComponent,
    canActivate: [canActivateAuth],
  },
  {
    path: 'inicio-control-cirugia',
    component: InicioCirugiaComponent,
    canActivate: [canActivateAuth],
  },
  {
    path: 'inicio-notas',
    component: InicioComponentNotas,
    canActivate: [canActivateAuth],
  },
  {
    path: 'habitacion-inteligente',
    component: HabitacionInteligenteComponent,
    canActivate: [canActivateAuth],
  },
  {
    path: 'anestesia-eco-perioperatorio',
    component: AnestesiaEcoPerioperatorioComponent,
    canActivate: [canActivateAuth],
  },
  { path: '', canActivate: [canActivateAuth, canActivateRedirect] },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
