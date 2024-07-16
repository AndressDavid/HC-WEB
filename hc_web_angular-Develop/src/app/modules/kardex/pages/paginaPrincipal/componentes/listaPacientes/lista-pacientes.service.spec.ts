import { TestBed } from '@angular/core/testing';

import { ListaPacientesService } from './lista-pacientes.service';

describe('ListaPacientesService', () => {
  let service: ListaPacientesService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ListaPacientesService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
