import { Component, Input, OnInit } from '@angular/core';
import { MenuService } from './services/api.service';
import { EspecialidadService } from './services/especialidad.service';
import { Environment } from '../../../environments/environment';
import { NgStyle, NgClass } from '@angular/common';

@Component({
  selector: 'app-menu',
  templateUrl: './menu.component.html',
  styleUrl: './menu.component.css'
})


export class MenuComponent implements OnInit {

  urlFija: String = '';
  menuItems: any[] = [];
  especialidad: any[] = [];
  servidor: string = '';
  nombres: any = [];
  recordatorios: number = 0;
  especialidades: any[] = [];
  mensajeCambioPerfil: String = '';
  respuesta: boolean = false;
  especialidadSeleccionada: any = null;

  @Input() chuloEspecialidad: string ='999'

  estiloMenu: any = {};
  colorLetraMenu: any = {};

  constructor(private especialidadService: EspecialidadService, private menuService: MenuService, private url: Environment) { }

  ngOnInit(): void {
    this.obtenerUrlFija();
    this.obtenerMenu();
    this.obtenerServidor();
    this.obtenerDatosNombres();
    this.obtenerRecordatorios();
    this.obtenerEspecialidad();
    this.especialidadSeleccionada = this.especialidadService.getSeleccionaEspecialidad();

    let entorno = this.url.entorno();

    if (entorno == 'produccion') {
      this.estiloMenu =  '#000000';
      this.colorLetraMenu = '#ffffff';
    } else {
      this.estiloMenu =  '#ffc107'; 
      
    }

  }

  obtenerUrlFija(): void {
    this.urlFija =  this.url.environmentPhp();
  }

  obtenerMenu(): void {
    this.menuService.obtenerMenu().subscribe(response => {

      this.menuItems = response.map((menuItem: any) => {
        if (menuItem && menuItem.PROMPT) {
          menuItem.PROMPT = menuItem.PROMPT.replace('\-', '');
        }

        if (menuItem.submenu) {
          menuItem.submenu = menuItem.submenu.map((submenu: any) => {
            if (submenu && submenu.cmd) {
              submenu.cmd = submenu.cmd.replace('\-', '');
            }
            submenu.toolbarLabel = submenu.PROMPT;
            return submenu;
          });
        }
        menuItem.toolbarLabel = menuItem.PROMPT;
        return menuItem;
      });
    });
  }

  obtenerServidor() {
    this.menuService.obtenerServidor().subscribe(response => {
      this.servidor = response;
    });
  }

  obtenerDatosNombres() {
    this.menuService.obtenerNombres().subscribe(response => {
      this.nombres = response;
    });
  }

  obtenerRecordatorios() {
    this.menuService.ontenerRecordatorios().subscribe(response => {
      this.recordatorios = response;
    });
  }


  obtenerEspecialidad(): void {
    this.menuService.obtenerEspecialidad().subscribe(response => {
      // console.log('Respuesta de obtenerEspecialidad:', response);
      this.especialidad = response;
    });
  }

  cambiarEspecialidad(especialidad: any): void {
    try {
      if (especialidad && especialidad.TIPO && especialidad.ESPECIALIDAD) {
        // let cambioTipo: any = especialidad.TIPO.nId;
        // let cambioEspecialidad: any = especialidad.ESPECIALIDAD.cId;

        // console.log('Cambio de tipo:', cambioTipo);
        // console.log('Cambio de especialidad:', cambioEspecialidad);

        this.menuService.cambiarEspecialidades([especialidad]).subscribe(
          (response: any) => {
            this.respuesta = true;
            this.especialidadSeleccionada = especialidad;
            this.especialidadService.setSeleccionaEspecialidad(especialidad);

            console.log('Respuesta del servidor:', response);
            window.location.href = this.url.environmentPhp().toString();
          },
          (error: any) => {
            console.error('Error al cambiar especialidades desde el servidor', error);
          }
        );
      } else {
        console.error('La estructura de datos es incorrecta o contiene valores nulos o undefined.');
      }
    } catch (error) {
      console.error('Error al procesar especialidad', error);
    }
  }

    // Propiedades para controlar el estado de la barra de navegación
    isNavbarCollapsed: boolean = true;

    // Método para alternar el estado de la barra de navegación
    toggleNavbar() {
      this.isNavbarCollapsed = !this.isNavbarCollapsed;
    }
}