import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MedicosEspecialidadComponent } from './medicos-especialidad.component';

describe('MedicosEspecialidadComponent', () => {
  let component: MedicosEspecialidadComponent;
  let fixture: ComponentFixture<MedicosEspecialidadComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [MedicosEspecialidadComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(MedicosEspecialidadComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
