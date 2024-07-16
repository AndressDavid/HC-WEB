import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InstrumentadorComponent } from './profesionales.component';

describe('InstrumentadorComponent', () => {
  let component: InstrumentadorComponent;
  let fixture: ComponentFixture<InstrumentadorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [InstrumentadorComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(InstrumentadorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
