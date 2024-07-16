import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-buscar',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule
  ],
  templateUrl: './buscar.component.html',
  styleUrl: './buscar.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BuscarComponent {

  @Output() filtroIngreso = new EventEmitter<string>();

  onInputChange(event: any) {
    this.filtroIngreso.emit(event.target.value);
  }

 }
