import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SalaProcedimientosComponent } from './sala-procedimientos.component';

describe('SalaProcedimientosComponent', () => {
  let component: SalaProcedimientosComponent;
  let fixture: ComponentFixture<SalaProcedimientosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [SalaProcedimientosComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(SalaProcedimientosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
