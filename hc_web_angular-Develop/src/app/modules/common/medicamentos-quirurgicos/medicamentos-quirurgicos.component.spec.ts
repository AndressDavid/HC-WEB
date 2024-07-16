import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MedicamentosQuirurgicosComponent } from './medicamentos-quirurgicos.component';

describe('MedicamentosQuirurgicosComponent', () => {
  let component: MedicamentosQuirurgicosComponent;
  let fixture: ComponentFixture<MedicamentosQuirurgicosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [MedicamentosQuirurgicosComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(MedicamentosQuirurgicosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
