import { Component, Input } from '@angular/core';
import { iModal } from './interfaces/IModal';

@Component({
  standalone: true,
  selector: 'app-load-modal',
  templateUrl: './load-modal.component.html',
  styleUrl: './load-modal.component.css'
})
export class LoadModalComponent {

  @Input() modal!: iModal;
  

}
