import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PaginaPrincipalModule } from './paginaPrincipal/paginaPrincipal.module';
import { PaginaPrincipalComponent } from './paginaPrincipal/paginaPrincipal.component';

@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    PaginaPrincipalModule
  ],
  exports: [
    PaginaPrincipalComponent
  ]
})
export class KardexModule { }
