import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ProcedimientosComponent } from '../vias/vias.component.spec';

describe('ProcedimientosComponent', () => {
  let component: ProcedimientosComponent;
  let fixture: ComponentFixture<ProcedimientosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ProcedimientosComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(ProcedimientosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
