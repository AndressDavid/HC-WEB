import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DocumentoPacienteComponent } from './documento-paciente.component';

describe('DocumentoPacienteComponent', () => {
  let component: DocumentoPacienteComponent;
  let fixture: ComponentFixture<DocumentoPacienteComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [DocumentoPacienteComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(DocumentoPacienteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
