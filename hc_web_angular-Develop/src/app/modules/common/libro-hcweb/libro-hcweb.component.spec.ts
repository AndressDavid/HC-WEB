import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LibroHCWebComponent } from './libro-hcweb.component';

describe('LibroHCWebComponent', () => {
  let component: LibroHCWebComponent;
  let fixture: ComponentFixture<LibroHCWebComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [LibroHCWebComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(LibroHCWebComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
