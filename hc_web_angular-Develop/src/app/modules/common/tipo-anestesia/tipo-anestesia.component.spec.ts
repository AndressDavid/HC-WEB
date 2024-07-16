import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TipoAnestesiaComponent } from './tipo-anestesia.component';

describe('TipoAnestesiaComponent', () => {
  let component: TipoAnestesiaComponent;
  let fixture: ComponentFixture<TipoAnestesiaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [TipoAnestesiaComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(TipoAnestesiaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
