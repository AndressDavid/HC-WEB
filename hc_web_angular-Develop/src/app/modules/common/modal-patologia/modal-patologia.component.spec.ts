import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalPatologiaComponent } from './modal-patologia.component';

describe('ModalPatologiaComponent', () => {
  let component: ModalPatologiaComponent;
  let fixture: ComponentFixture<ModalPatologiaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ModalPatologiaComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(ModalPatologiaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
