import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EspecialidadesMedicosComponent } from './especialidades-medicos.component';

describe('EspecialidadesMedicosComponent', () => {
  let component: EspecialidadesMedicosComponent;
  let fixture: ComponentFixture<EspecialidadesMedicosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [EspecialidadesMedicosComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(EspecialidadesMedicosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
