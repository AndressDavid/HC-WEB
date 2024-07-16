import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class EspecialidadService {
  private seleccioneEspecialidad = new BehaviorSubject<string | null>(null);
  selectedSpecialty$ = this.seleccioneEspecialidad.asObservable();

  setSeleccionaEspecialidad(specialty: string): void {
    this.seleccioneEspecialidad.next(specialty);
  }

  getSeleccionaEspecialidad(): string | null {
    return this.seleccioneEspecialidad.value;
  }
}