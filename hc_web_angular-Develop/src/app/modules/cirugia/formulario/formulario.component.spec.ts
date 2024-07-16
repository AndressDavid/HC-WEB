import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FormularioCirugiaComponent } from './formulario.component';

describe('FormularioComponent', () => {
  let component: FormularioCirugiaComponent;
  let fixture: ComponentFixture<FormularioCirugiaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [FormularioCirugiaComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(FormularioCirugiaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
