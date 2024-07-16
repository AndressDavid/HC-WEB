import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Output } from '@angular/core';

@Component({
  selector: 'app-footer-kardex',
  standalone: true,
  imports: [
    CommonModule,
  ],
  templateUrl: './footerKardex.component.html',
  styleUrl: './footerKardex.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FooterKardexComponent {
  lcGuardar: string = "Guardar";

  @Output() OutGuardarInformacion : EventEmitter<any> = new EventEmitter();
  
  guardarDatos(){
    this.OutGuardarInformacion.emit();
    console.log("Se emitio guardado");
  }
}
