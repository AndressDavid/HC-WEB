import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'activo',
  standalone: true
})
export class ActivoPipe implements PipeTransform {

  transform(value: number): string {
    return value === 1 ? 'Activa' : 'Inactiva';
  }

}
