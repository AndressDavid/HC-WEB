import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalDinamicoComponent } from './ModalDinamicoComponent';

describe('ModalDinamicoComponent', () => {
  let component: ModalDinamicoComponent;
  let fixture: ComponentFixture<ModalDinamicoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ModalDinamicoComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(ModalDinamicoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
