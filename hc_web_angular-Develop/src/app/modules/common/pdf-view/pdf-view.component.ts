import { Component, EventEmitter, Input, Output } from '@angular/core';
import { BrowserModule, DomSanitizer } from '@angular/platform-browser';
import { IContenidoPDF } from './interfaces/IContenidoPDF';
import { ServiceRecuperarPDF } from './services/vistaPDF.service';

@Component({
  standalone: true,
  imports:[BrowserModule ],
  selector: 'app-pdf-view',
  templateUrl: './pdf-view.component.html',
  styleUrl: './pdf-view.component.css'
})
export class PdfViewComponent {

  constructor(
    private _sanitizer: DomSanitizer,
    private serviceRecuperarPDF : ServiceRecuperarPDF
  ){}

  @Input() contenido!:IContenidoPDF;
  
  @Input() isDisable : boolean = true;
  
  public vistaPDF : boolean = false;
  public carga:boolean = false;
  

  public pdf : String="";
  public pd : any = null;
  
  ngOnInit(): void{ 
  }

  cerrarModal(): void{
    this.vistaPDF = false;
  }

  abrirModal() : void{
    
    this.pdf="data:application/pdf;base64,";
    this.pd= null;
    this.carga = true;
    this.serviceRecuperarPDF.recuperarVistaPDF(this.contenido).subscribe((result:any) => {
      if(result["success"]){
        this.pdf = this.pdf + result["documento"];
        this.pd = this._sanitizer.bypassSecurityTrustResourceUrl(this.pdf.toString());
        this.carga = false;
      }
    });
    
    this.vistaPDF = true;
  }

}
