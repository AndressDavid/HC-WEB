import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VistaPreviaPdfComponent } from './vista-previa-pdf.component';

describe('VistaPreviaPdfComponent', () => {
  let component: VistaPreviaPdfComponent;
  let fixture: ComponentFixture<VistaPreviaPdfComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [VistaPreviaPdfComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(VistaPreviaPdfComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
