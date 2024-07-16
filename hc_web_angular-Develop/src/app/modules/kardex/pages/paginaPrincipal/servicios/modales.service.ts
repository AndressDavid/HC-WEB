import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ModalesService {
  private modalOpen = false;

  constructor() { }
  
  isOpen(): boolean {
    return this.modalOpen;
  }

  openModal(): void {
    // Lógica para abrir el modal
    this.modalOpen = true;
  }

  closeModal(): void {
    // Lógica para cerrar el modal
    this.modalOpen = false;
  }
}