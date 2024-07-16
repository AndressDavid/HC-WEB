import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TipoCirugiaComponent } from './tipo-cirugia.component';

describe('TipoCirugiaComponent', () => {
  let component: TipoCirugiaComponent;
  let fixture: ComponentFixture<TipoCirugiaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [TipoCirugiaComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(TipoCirugiaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
