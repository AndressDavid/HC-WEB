import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, Input } from '@angular/core';
import { FooterKardexComponent } from "../footerKardex/footerKardex.component";
import { IDatosKardex } from '../../interfaces/IdatosSituacion';
import { FormsModule } from '@angular/forms';

@Component({
    selector: 'app-recomendacion',
    standalone: true,
    templateUrl: './recomendacion.component.html',
    styleUrl: './recomendacion.component.css',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        CommonModule,
        FooterKardexComponent,
        FormsModule
    ]
})
export class RecomendacionComponent {

    @Input() datosDeRecomendaciones?: IDatosKardex;
 }
