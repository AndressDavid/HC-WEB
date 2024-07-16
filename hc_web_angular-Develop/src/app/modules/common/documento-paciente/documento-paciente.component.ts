import { Component, Injectable, Input } from '@angular/core';
import { DocumentoPacienteService } from './documento.service';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [    
    FormsModule,
    ReactiveFormsModule,
    BrowserModule 
  ],
  selector: 'app-documento-paciente',
  templateUrl: './documento-paciente.component.html',
  styleUrl: './documento-paciente.component.css'
})


@Injectable({
  providedIn: 'root'
})
export class DocumentoPacienteComponent {

   documentos: any;
   @Input()
    frmParametro!: FormGroup;

  constructor(private documentoService: DocumentoPacienteService) {}

  ngOnInit() {
    this.recuperarDocumentosComponent();
  }

  recuperarDocumentosComponent(){
    this.documentoService.recuperarDocumento().subscribe((result:any) => {
      this.documentos = result['TIPOS'];
    });

  }

}
