import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CabeceraPacienteComponent } from './cabecera-paciente.component';

describe('CabeceraPacienteComponent', () => {
  let component: CabeceraPacienteComponent;
  let fixture: ComponentFixture<CabeceraPacienteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [CabeceraPacienteComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(CabeceraPacienteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
