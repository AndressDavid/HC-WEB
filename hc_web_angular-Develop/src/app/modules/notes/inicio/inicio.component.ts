import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';
import { HeaderComponent } from "../header/header.component";
import { DetailsComponent } from "../details/details.component";

@Component({
    selector: 'app-inicio',
    standalone: true,
    templateUrl: './inicio.component.html',
    styleUrl: './inicio.component.css',
    imports: [
        CommonModule,
        HeaderComponent,
        DetailsComponent
    ]
})
export class InicioComponentNotas {

  @Input() datosNotas: String = '';

 }
