import { Component, ElementRef, HostListener, ViewChild } from '@angular/core';
import { SessionUser } from './app.service';
import { FormGroup, FormControl } from '@angular/forms';


@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
})
export class AppComponent {
  
  sessionStorage?: any;
  public username: string = "";
  public tipo:number =0;
  public especialidad:string="999"
  public income:string = '';
  public messageError:string='';
  public chulo : string= ""

  public loginModal = new FormGroup({
    username: new FormControl(''),
    password: new FormControl(''),
    tipo: new FormControl(0),
    especialidad: new FormControl('')
  });

  public loginModalKey = {
    usuario: '',
    password: '',
    tipo:0,
    especialidad:''
  }

  @ViewChild('myModal') myModal!: ElementRef;
  
  constructor(
      private sessionUser: SessionUser,
    ){
      this.crearSessionStorage();
    }

    crearSessionStorage(){

      sessionStorage.clear();
      localStorage.clear();
      this.sessionStorage = null;

      this.sessionUser.crearSessionStoroge().subscribe((result:any) => {
        sessionStorage.setItem('userhcweb', result);
        this.chulo = JSON.parse(atob(result))['especialidad'];
        this.sessionStorage = result;
      });
    }

    @HostListener('window:focus', ['$event'])
    onFocus(): void {
      let jsonSession: any = JSON.parse(atob(this.sessionStorage));
      this.sessionUser.validarSession(this.sessionStorage).subscribe((result:any) => {
        if(result['error'] != ''){

          this.username =  jsonSession["usuario"];
          this.tipo = jsonSession["tipo"];
          this.especialidad = jsonSession["especialidad"];


          this.loginModal.controls['username'].setValue(this.username);
          this.loginModal.controls['tipo'].setValue(this.tipo);
          this.loginModal.controls['especialidad'].setValue(this.especialidad);

          this.myModal.nativeElement.style.display = 'block';
          this.messageError= result['error'];
          this.income = "error";
        }
      });
    }

  loginModalSubmit(){

    let body ={
      usuario: this.loginModal.controls.username.value?? '',
      password: this.loginModal.controls.password.value?? '',
      tipo: this.loginModal.controls.tipo.value?? '',
      especialidad: this.loginModal.controls.especialidad.value?? ''
    }

    this.sessionUser.loginUserModal(body).subscribe((result:any) => {
      
        if(result['error'] ==''){
          this.income = "success";
          this.loginModal.reset();

          setTimeout(() => { 
            this.myModal.nativeElement.style.display = 'none';
            this.income = "";
          }, 1000);  
  
        }else{
          this.messageError= result['error'];
          this.income = "error";
          this.loginModal.controls.password.reset();
        }

    });
  }

}
