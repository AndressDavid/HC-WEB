import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ViasComponent } from './vias.component';

describe('ViasComponent', () => {
  let component: ViasComponent;
  let fixture: ComponentFixture<ViasComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ViasComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(ViasComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
