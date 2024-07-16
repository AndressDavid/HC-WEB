import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InicioCirugiaComponent } from './inicio.component';

describe('InicioComponent', () => {
  let component: InicioCirugiaComponent;
  let fixture: ComponentFixture<InicioCirugiaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [InicioCirugiaComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InicioCirugiaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
