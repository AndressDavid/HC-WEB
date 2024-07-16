import { Component, Input } from '@angular/core';
import { ViasService } from './vias.service';
import { BrowserModule } from '@angular/platform-browser';
import { FormGroup, FormsModule, ReactiveFormsModule } from '@angular/forms';

@Component({
  standalone: true,
  imports: [    
    FormsModule,
    ReactiveFormsModule,
    BrowserModule 
  ],
  selector: 'app-vias',
  templateUrl: './vias.component.html',
  styleUrl: './vias.component.css'
})
export class ViasComponent {
  constructor(private viasService: ViasService) {}

  vias: any;
  
  documentos: any;
  @Input()
   frmParametro!: FormGroup;

  ngOnInit() {
    this.recuperarViasIngreso();
  }

  recuperarViasIngreso(){
    this.viasService.recuperarViasIngreso().subscribe((result:any) => {
      this.vias = result;
    });

  }

}
