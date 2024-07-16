import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalDatosPacienteComponent } from './modal-datos-paciente.component';

describe('ModalDatosPacienteComponent', () => {
  let component: ModalDatosPacienteComponent;
  let fixture: ComponentFixture<ModalDatosPacienteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ModalDatosPacienteComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(ModalDatosPacienteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
