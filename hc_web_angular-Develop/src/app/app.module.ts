import {
  HttpClientModule,
  provideHttpClient,
  withInterceptors,
} from '@angular/common/http';
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { tokenInterceptorInterceptor } from './environments/token/token-interceptor.interceptor';
import { FooterComponent } from './modules/common/footer/footer.component';
import { MenuComponent } from './modules/common/menu/menu.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CirugiaModule } from './modules/cirugia/cirugia.module';
import { NotesModule } from './modules/notes/notes.module';
import { AnestesiaEcoPerioperatorioModule } from './modules/anestesia-eco-perioperatorio/anestesia-eco-perioperatorio.module';
import { HabitacionInteligenteModule } from './modules/habitacion-inteligente/habitacion-inteligente.module';
import { ProcedimientosAnestesiaComponent } from './modules/anestesia-eco-perioperatorio/componentes/procedimientos-anestesia/procedimientos-anestesia.component';

@NgModule({
  declarations: [AppComponent, FooterComponent, MenuComponent],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    FormsModule,
    ReactiveFormsModule,
    NotesModule,
    HabitacionInteligenteModule,
    AnestesiaEcoPerioperatorioModule,
  ],
  providers: [
    provideHttpClient(withInterceptors([tokenInterceptorInterceptor])),
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
