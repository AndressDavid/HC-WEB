import { ComponentFixture, TestBed } from '@angular/core/testing';

import { OtrasSalasComponent } from './otras-salas.component';

describe('OtrasSalasComponent', () => {
  let component: OtrasSalasComponent;
  let fixture: ComponentFixture<OtrasSalasComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [OtrasSalasComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(OtrasSalasComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
