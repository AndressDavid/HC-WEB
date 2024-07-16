import { Component, Input } from '@angular/core';
import { Environment } from '../../../environments/environment';
import { BrowserModule } from '@angular/platform-browser';

@Component({
  standalone: true,
  imports:[BrowserModule ],
  selector: 'app-libro-hcweb',
  templateUrl: './libro-hcweb.component.html',
  styleUrl: './libro-hcweb.component.css'
})
export class LibroHCWebComponent {

  constructor(private url: Environment) { }

  @Input() ingreso!: String | Number;

  @Input() tipoBtn: string = "P";
  
  public urlLibro ="";

  ngOnInit(){
    this.urlLibro = this.url.environmentPhp()+"/modulo-documentos";
  }

}
