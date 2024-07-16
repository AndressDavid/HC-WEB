import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PaginaPrincipalComponent } from './paginaPrincipal.component';
import { SituacionComponent } from './componentes/situacion/situacion.component';
import { AntecedentesComponent } from "./componentes/antecedentes/antecedentes.component";
import { SeleccionComponent } from './componentes/seleccion/seleccion.component';
import { BusquedaComponent } from './componentes/busqueda/busqueda.component';
import { EncabezadoComponent } from './componentes/encabezado/encabezado.component';
import { FormsModule } from '@angular/forms';
import { ListaPacientesComponent } from './componentes/listaPacientes/listaPacientes.component';
import { FooterKardexComponent } from './componentes/footerKardex/footerKardex.component';
import { EvaluacionComponent } from './componentes/evaluacion/evaluacion.component';
import { RecomendacionComponent } from "./componentes/recomendacion/recomendacion.component";
import { BuscarComponent } from "./componentes/buscar/buscar.component";
import { MenuComponent } from '../../../common/menu/menu.component';


@NgModule({
    declarations: [
        PaginaPrincipalComponent,
    ],
    exports: [
        PaginaPrincipalComponent,
    ],
    imports: [
        CommonModule,
        SituacionComponent,
        AntecedentesComponent,
        SeleccionComponent,
        BusquedaComponent,
        EncabezadoComponent,
        FormsModule,
        ListaPacientesComponent,
        FooterKardexComponent,
        EvaluacionComponent,
        RecomendacionComponent,
        BuscarComponent,
        
    ]
})
export class PaginaPrincipalModule { }